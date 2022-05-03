<?php

namespace Mcart\Xls\Spreadsheet;

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use CCatalogProduct;
use CCurrency;
use CFile;
use CIBlockElement;
use COption;
use CPrice;
use CSearch;
use Mcart\Xls\Helpers\Event;
use Mcart\Xls\McartXls;
use Mcart\Xls\ORM\Profile\Column\CustomFieldsTable;
use Mcart\Xls\ORM\Profile\ColumnTable;
use Mcart\Xls\ORM\Profile\ConstTable;
use Mcart\Xls\ORM\ProfileTable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use function ConvertTimeStamp;

Loc::loadMessages(__FILE__);

final class Import {
    const ERROR_PREF = 'Error spreadsheet import';
    const ERROR_CODE_PREF = 'SPREADSHEET_IMPORT';
	const EVENT__BEFORE_IMPORT_ELEMENT = 'onBeforeImportElement';
	const EVENT__AFTER_IMPORT_ELEMENT = 'onAfterImportElement';
	const EVENT__AFTER_IMPORT_STEP = 'onAfterImportStep';
	const EVENT__COMPLETE_IMPORT = 'onCompleteImport';

    /**
     * @var McartXls
     */
    private $obMcartXls;

    /**
     * @var Reader
     */
    private $obReader;

    private $arProfile;
    private $arDefaultValues = [
        ColumnTable::SAVE_IN_PREF__FIELD => [],
        ColumnTable::SAVE_IN_PREF__PROPERTY => [],
        ColumnTable::SAVE_IN_PREF__PRICE => [],
        ColumnTable::SAVE_IN_PREF__PRODUCT => [],
    ];
    private $arColumnsKeys = [];
    private $arColumnsByKeys = [];
    private $arPictures = [];
    private $processedRows = 0;
    private $addedElements = 0;
    private $updatedElements = 0;
    private $addedElementIds = [];
    private $updatedElementIds = [];
    private $isComplete = false;
    private $nextStartRow = false;
    private $arLog = [];
    private $arLogDebug = [];
    private $defaultQuantityTrace = 'N';
    private $arIblockPropertyEnum = [];

    /**
     * @param int $profileId
     * @param int|string|array $file May contain ID of the file, absolute path, relative path, url or array as in $_FILES[name]
     */
    public function __construct($profileId, $file) {
        $this->obMcartXls = McartXls::getInstance();
        $profileId = intval($profileId);
        if ($profileId <= 0) {
            $this->obMcartXls->addError(self::ERROR_PREF, self::ERROR_CODE_PREF.'#01');
            return;
        }
        $this->arProfile = ProfileTable::getById($profileId)->fetch();
        if (empty($this->arProfile) || $this->arProfile['IBLOCK_ID'] <= 0) {
            $this->obMcartXls->addError(self::ERROR_PREF, self::ERROR_CODE_PREF.'#02');
            return;
        }
        $dbProps = PropertyTable::getList([
            'select' => ['ID', 'PROPERTY_TYPE'],
            'filter' => [
                '=IBLOCK_ID' => $this->arProfile['IBLOCK_ID'],
                'PROPERTY_TYPE' => PropertyTable::TYPE_LIST
            ]
        ]);
        while ($arProp = $dbProps->fetch()){
            $dbPropsEnum = PropertyEnumerationTable::getList([
                'filter' => ['=PROPERTY_ID' => $arProp['ID']]
            ]);
            while ($arPropEnum = $dbPropsEnum->fetch()){
                $arProp['EnumIdsByValues'][$arPropEnum['VALUE']] = $arPropEnum['ID'];
            }
            $this->arIblockPropertyEnum[$arProp['ID']] = $arProp;
        }
        $readDataOnly = true;
        $obItems = ConstTable::getList(['filter' => ['PROFILE_ID' => $this->arProfile['ID']]]);
        while ($arItem = $obItems->fetch()) {
            $this->arDefaultValues[$arItem['SAVE_IN_PREF']][$arItem['SAVE_IN']] = $arItem['VALUE'];
        }
        $obItems = ColumnTable::getList(['filter' => ['PROFILE_ID' => $this->arProfile['ID']]]);
        while ($arItem = $obItems->fetch()) {
            if($readDataOnly===true && !empty($arItem['HANDLER'])){
                $readDataOnly = false;
            }
            if($arItem['IS_IDENTIFY_ELEMENT']==='Y'){
                $this->arProfile['IDENTIFY_ELEMENT_COLUMN'] = $arItem['COLUMN'];
                if($arItem['SAVE_IN_PREF']!='FIELD'){
                    $this->arProfile['IDENTIFY_ELEMENT_SAVE_IN'] = $arItem['SAVE_IN_PREF'].'_';
                }
                $this->arProfile['IDENTIFY_ELEMENT_SAVE_IN'] .= $arItem['SAVE_IN'];
                $arItem['DO_NOT_IMPORT_ROW_IF_EMPTY'] = 'Y';
            }
            $dbCustomFields = CustomFieldsTable::getList(['filter' => ['COLUMN_ID' => $arItem['ID']]]);
            while ($arCustomField = $dbCustomFields->fetch()) {
                $arItem['CUSTOM_FIELDS'][$arCustomField['NAME']] = $arCustomField['VALUE'];
            }
            $this->arColumnsKeys[$arItem['COLUMN']] = $arItem['COLUMN'];
            $this->arColumnsByKeys[$arItem['COLUMN']][$arItem['SAVE_IN_PREF'].'_'.$arItem['SAVE_IN']] = $arItem;
        }
        if (empty($this->arColumnsKeys)) {
            $this->obMcartXls->addError(self::ERROR_PREF, self::ERROR_CODE_PREF.'#03');
            return;
        }
        if (empty($this->arProfile['IDENTIFY_ELEMENT_SAVE_IN'])) {
            $this->obMcartXls->addError(self::ERROR_PREF, self::ERROR_CODE_PREF.'#04');
            return;
        }
        if($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog')){
            $this->defaultQuantityTrace = COption::GetOptionString("catalog", "default_quantity_trace", $this->defaultQuantityTrace);
        }
        $this->obReader = new Reader($file, $readDataOnly);
    }

    /**
     * @param int $startRow
     * @return array|false
     */
    public function exec($startRow = 0) {
        /* @var $obWorksheet Worksheet */
        if ($this->obMcartXls->hasErrors()) {
            $this->deleteOldFiles();
            return;
        }
        $this->processedRows = $startRow-$this->arProfile['START_ROW'];
        $obWorksheet = $this->obReader->getWorksheet(
            $startRow,
            $this->arProfile['QUANTITY_ELEMENTS_IMPORTED_PER_STEP'],
            null,
            $this->arProfile['END_ROW']
        );
        if (!$obWorksheet || $this->obMcartXls->hasErrors()) {
            $this->deleteOldFiles();
            return;
        }
        //[
        foreach ($obWorksheet->getDrawingCollection() as $drawing) {
            /* @var $drawing BaseDrawing */
            $coordinate = $drawing->getCoordinates();
            $this->arPictures[$coordinate]['coordinate'] = $coordinate;
            $this->arPictures[$coordinate]['name'] = $drawing->getName();
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $this->arPictures[$coordinate]['content'] = ob_get_contents();
                ob_end_clean();
                $this->arPictures[$coordinate]['extension'] = self::getExtensionByMimeType($drawing->getMimeType());
                $this->arPictures[$coordinate]['filename'] = 'image.'.$this->arPictures[$coordinate]['extension'];
            } else {
                $zipReader = fopen($drawing->getPath(),'r');
                $this->arPictures[$coordinate]['content'] = '';
                while (!feof($zipReader)) {
                    $this->arPictures[$coordinate]['content'] .= fread($zipReader,1024);
                }
                fclose($zipReader);
                $this->arPictures[$coordinate]['extension'] = $drawing->getExtension();
                $this->arPictures[$coordinate]['filename'] = $drawing->getFilename();
            }
        }
        //]
        foreach ($obWorksheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            if ($this->obReader->obReadFilter->isContinue($rowIndex)) {
                continue;
            }
            $this->importRow($rowIndex, $row->getCellIterator());
        }
        (new Event(
            $this->obMcartXls->getModuleID(),
            static::EVENT__AFTER_IMPORT_STEP,
            [
                'arProfile' => $this->arProfile,
                'arDefaultValues' => $this->arDefaultValues,
                'arColumnsKeys' => $this->arColumnsKeys,
                'arColumnsByKeys' => $this->arColumnsByKeys,
                'addedElementIds' => $this->addedElementIds,
                'updatedElementIds' => $this->updatedElementIds,
                'processedRows' => $this->processedRows
            ]
        ))->send();
        if($rowIndex === null || $this->obReader->obReadFilter->isComplete()){
            $this->isComplete = true;
            $this->arLog[] = Loc::getMessage("MCART_XLS_IMPORT_COMPLETED");
            if($this->obMcartXls->isExtensionLoaded('bitrix_module_search')){
                CSearch::ReIndexModule('iblock');
            }
            (new Event(
                $this->obMcartXls->getModuleID(),
                static::EVENT__COMPLETE_IMPORT,
                [
                    'arProfile' => $this->arProfile,
                    'arDefaultValues' => $this->arDefaultValues,
                    'arColumnsKeys' => $this->arColumnsKeys,
                    'arColumnsByKeys' => $this->arColumnsByKeys,
                    'processedRows' => $this->processedRows
                ]
            ))->send();
            $this->deleteOldFiles();
        }else{
            $this->nextStartRow = $rowIndex+1;
        }
    }

    /**
     * @param int $rowIndex
     * @param CellIterator $cellIterator
     * @return null
     */
    private function importRow($rowIndex, $cellIterator) {
        global $APPLICATION;

        $this->processedRows++;
        if (empty($cellIterator)) {
            return;
        }
        $arF = $this->arDefaultValues;
        $arFilter = ['IBLOCK_ID' => $this->arProfile['IBLOCK_ID']];
        $isEmptyRow = true;
        $arCells = [];
        foreach ($cellIterator as $cell) {
            /* @var $cell Cell */
            if ($cell === null) {
                continue;
            }
            $column = $cell->getColumn();
            $arCells[$column] = $cell;
            if(!in_array($column, $this->arColumnsKeys)){
                continue;
            }
            $arCell = [
                'value' => $cell->getCalculatedValue(),
                'coordinate' => $cell->getCoordinate(),
                'column' => $column,
            ];
            $arCell['value_format'] = $arCell['value'];
            foreach ($this->arColumnsByKeys[$column] as $arItem) {
                if (!empty($arItem['HANDLER'])) {
                    switch ($arItem['HANDLER']) {
                        case ColumnTable::HANDLER__DATE:
                            $arCell['isDateTime'] = Date::isDateTime($cell);
                            if ($arCell['isDateTime']) {
                                $arCell['timestamp'] = Date::excelToTimestamp($arCell['value']);
                                $arCell['value_format'] = ConvertTimeStamp($arCell['timestamp'], "SHORT");
                            }
                            break;
                        case ColumnTable::HANDLER__DATETIME:
                            $arCell['isDateTime'] = Date::isDateTime($cell);
                            if ($arCell['isDateTime']) {
                                $arCell['timestamp'] = Date::excelToTimestamp($arCell['value']);
                                $arCell['value_format'] = ConvertTimeStamp($arCell['timestamp'], "FULL");
                            }
                            break;
                        case ColumnTable::HANDLER__URL:
                            $arCell['isHyperlink'] = $cell->hasHyperlink();
                            if ($arCell['isHyperlink']) {
                                $arCell['value_format'] = $cell->getHyperlink()->getUrl();
                            }
                            break;
                        case ColumnTable::HANDLER__PICTURE:
                            if(!$this->pictureMakeFileArray($arCell['coordinate'])){
                                $arCell['value'] = $arCell['value_format'] = '';
                                break;
                            }
                            $arCell['value_format'] = $this->arPictures[$arCell['coordinate']]['arFile'];
                            break;
                    }
                }
                if($arItem['SAVE_IN_PREF'] == ColumnTable::SAVE_IN_PREF__PROPERTY && $this->arIblockPropertyEnum[$arItem['SAVE_IN']]['ID']>0){
                    $arCell['value_format'] = (string)$this->arIblockPropertyEnum[$arItem['SAVE_IN']]['EnumIdsByValues'][$arCell['value_format']];
                }
                if (empty($arCell['value_format']) && $arItem['DO_NOT_IMPORT_ROW_IF_EMPTY']==='Y') {
                    return;
                }
                if($arItem['IS_IDENTIFY_ELEMENT']=='Y'){
                    if (empty($arCell['value_format'])) {
                        return;
                    }
                    $arFilter[$this->arProfile['IDENTIFY_ELEMENT_SAVE_IN']] = $arCell['value_format'];
                }
                if($arItem['HANDLER']!==ColumnTable::HANDLER__PICTURE || !empty($arCell['value_format'])){
                    $arF[$arItem['SAVE_IN_PREF']][$arItem['SAVE_IN']] = $arCell['value_format'];
                    $isEmptyRow = false;
                }
            }
        }
        if($isEmptyRow){
            return;
        }
        //--
        $arSelect = ['ID', 'IBLOCK_ID'];
        if($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog')){
            $arSelect[] = 'CATALOG_QUANTITY';
            $arSelect[] = 'CATALOG_QUANTITY_TRACE';
        }
        $dbElement = CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
        $arElement = $dbElement->Fetch();
        $isNew = empty($arElement);
        if($isNew && ($this->arProfile['ONLY_UPDATE']=='Y' || $this->arProfile['IDENTIFY_ELEMENT_SAVE_IN'] == 'ID')){
            return;
        }
        if($isNew){
            if($this->arProfile['IBLOCK_SECTION_ID_FOR_NEW'] > 0){
                $arF[ColumnTable::SAVE_IN_PREF__FIELD]['IBLOCK_SECTION_ID'] = $this->arProfile['IBLOCK_SECTION_ID_FOR_NEW'];
            }
            $arF[ColumnTable::SAVE_IN_PREF__FIELD]['ACTIVE'] = 'Y';
            if($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog') && empty($arF[ColumnTable::SAVE_IN_PREF__PRODUCT]['QUANTITY'])){
                $arF[ColumnTable::SAVE_IN_PREF__PRODUCT]['QUANTITY'] = 0;
            }
        }
        if(!$this->obMcartXls->isExtensionLoaded('bitrix_module_catalog') || $isNew){
            $arElement['CATALOG_QUANTITY'] = 0;
            $arElement['CATALOG_QUANTITY_TRACE'] = $this->defaultQuantityTrace;
        }

        $issetElementFields = (!empty($arF[ColumnTable::SAVE_IN_PREF__FIELD]));
        if ($issetElementFields) {
            if($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog') && !$isNew){
                $basePrice = CPrice::GetBasePrice($arElement['ID'])['PRICE'];
            }
            if (key_exists('BASE_PRICE', $arF[ColumnTable::SAVE_IN_PREF__PRICE])) {
                $basePrice = $arF[ColumnTable::SAVE_IN_PREF__PRICE]['BASE_PRICE'];
            }
            $arCatalogProduct = array_merge(
                ['QUANTITY' => $arElement['CATALOG_QUANTITY'], 'QUANTITY_TRACE' => $arElement['CATALOG_QUANTITY_TRACE']],
                $arF[ColumnTable::SAVE_IN_PREF__PRODUCT]
            );
            if($this->arProfile['DEACTIVATE_IF_QUANTITY_0']=='Y' && $arCatalogProduct['QUANTITY'] <= 0 && $arCatalogProduct['QUANTITY_TRACE']=='Y'){
                $arF[ColumnTable::SAVE_IN_PREF__FIELD]['ACTIVE'] = 'N';
            }
            if($this->arProfile['DEACTIVATE_IF_PRICE_0']=='Y' && $basePrice <= 0){
                $arF[ColumnTable::SAVE_IN_PREF__FIELD]['ACTIVE'] = 'N';
            }
            if(
                $this->arProfile['ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0']=='Y' &&
                $basePrice > 0 &&
                ($arCatalogProduct['QUANTITY'] > 0 || $arCatalogProduct['QUANTITY_TRACE']!='Y')
            ){
                $arF[ColumnTable::SAVE_IN_PREF__FIELD]['ACTIVE'] = 'Y';
            }
            if($isNew && $this->arProfile['DEACTIVATE_IF_NEW']=='Y'){
                $arF[ColumnTable::SAVE_IN_PREF__FIELD]['ACTIVE'] = 'N';
            }
        }

        $obEvent = (new Event(
            $this->obMcartXls->getModuleID(),
            static::EVENT__BEFORE_IMPORT_ELEMENT,
            [
                'arFields' => $arF,
                'arProfile' => $this->arProfile,
                'arDefaultValues' => $this->arDefaultValues,
                'arColumnsKeys' => $this->arColumnsKeys,
                'arColumnsByKeys' => $this->arColumnsByKeys,
                'arCells' => $arCells,
                'ELEMENT_ID' => $arElement['ID']
            ]
        ));
        $obEvent->send();
        $arF = $obEvent->mergeFields($arF);
        if($obEvent->hasErrors()){
            return;
        }

        if($issetElementFields){
            $arFields = $arF[ColumnTable::SAVE_IN_PREF__FIELD];
            unset($arFields['ID']);
            if (!empty($arFields)) {
                $el = new CIBlockElement;
                if($isNew){
                    $arFields['IBLOCK_ID'] = $this->arProfile['IBLOCK_ID'];
                    $isSuccess = $arElement['ID'] = $el->Add($arFields, false, false, true);
                }else{
                    $isSuccess = $el->Update($arElement['ID'], $arFields, false, false, true);
                }
                if(!$isSuccess){
                    $this->arLog[] = '['.$rowIndex.'] '.$el->LAST_ERROR;
                    return;
                }
            }
        }
        if($arElement['ID'] <= 0){
            $this->arLog[] = '['.$rowIndex.'] Error "Element"';
            return;
        }

        if (!empty($arF[ColumnTable::SAVE_IN_PREF__PROPERTY])) {
            $FLAGS = [];
            if($isNew){
                $FLAGS['NewElement'] = $isNew;
            }
            CIBlockElement::SetPropertyValuesEx($arElement['ID'], $this->arProfile['IBLOCK_ID'], $arF[ColumnTable::SAVE_IN_PREF__PROPERTY], $FLAGS);
        }

        if ($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog') && !empty($arF[ColumnTable::SAVE_IN_PREF__PRODUCT])) {
            $arF[ColumnTable::SAVE_IN_PREF__PRODUCT]['ID'] = $arElement['ID'];
            if(!CCatalogProduct::Add($arF[ColumnTable::SAVE_IN_PREF__PRODUCT])){
                $err = $APPLICATION->GetException();
                $this->arLog[] = '['.$rowIndex.'] Error "CatalogProduct"'.(empty($err)?'':': '.$err);
                return;
            }
        }

        if ($this->obMcartXls->isExtensionLoaded('bitrix_module_catalog') && key_exists('BASE_PRICE', $arF[ColumnTable::SAVE_IN_PREF__PRICE])) {
            if (empty($arF[ColumnTable::SAVE_IN_PREF__PRICE]['BASE_PRICE_CURRENCY'])) {
                $arF[ColumnTable::SAVE_IN_PREF__PRICE]['BASE_PRICE_CURRENCY'] = CCurrency::GetBaseCurrency();
            }
            if(!CPrice::SetBasePrice(
                $arElement['ID'],
                $arF[ColumnTable::SAVE_IN_PREF__PRICE]['BASE_PRICE'],
                $arF[ColumnTable::SAVE_IN_PREF__PRICE]['BASE_PRICE_CURRENCY']
            )){
                $err = $APPLICATION->GetException();
                $this->arLog[] = '['.$rowIndex.'] Error "SetBasePrice"'.(empty($err)?'':': '.$err);
                return;
            }
        }

        if($isNew){
            $this->addedElements++;
            $this->addedElementIds[$arElement['ID']] = $arElement['ID'];
        }else{
            $this->updatedElements++;
            $this->updatedElementIds[$arElement['ID']] = $arElement['ID'];
        }

        (new Event(
            $this->obMcartXls->getModuleID(),
            static::EVENT__AFTER_IMPORT_ELEMENT,
            [
                'arFields' => $arF,
                'arProfile' => $this->arProfile,
                'arDefaultValues' => $this->arDefaultValues,
                'arColumnsKeys' => $this->arColumnsKeys,
                'arColumnsByKeys' => $this->arColumnsByKeys,
                'arCells' => $arCells,
                'ELEMENT_ID' => $arElement['ID']
            ]
        ))->send();
    }

    public function isComplete() {
        return $this->isComplete;
    }

    public function getNextStartRow() {
        return $this->nextStartRow;
    }

    public function getProcessedRows() {
        return $this->processedRows;
    }

    public function getAddedElements() {
        return $this->addedElements;
    }

    public function getUpdatedElements() {
        return $this->updatedElements;
    }

    public function getLogArray() {
        return $this->arLog;
    }

    public function getLogString() {
        return implode('<br />', $this->arLog);
    }

    public function getLogDebugArray() {
        return $this->arLogDebug;
    }

    private static function getExtensionByMimeType($mimeType){
        switch ($mimeType) {
            case MemoryDrawing::MIMETYPE_PNG :
                return 'png';
            case MemoryDrawing::MIMETYPE_GIF:
                return 'gif';
            case MemoryDrawing::MIMETYPE_JPEG :
                return 'jpg';
        }
    }

    private function pictureMakeFileArray($coordinate) {
        if (empty($this->arPictures[$coordinate]['filename']) || empty($this->arPictures[$coordinate]['content'])) {
            return false;
        }
        $this->arPictures[$coordinate]['fileHandle'] = tmpfile();
        fwrite($this->arPictures[$coordinate]['fileHandle'], $this->arPictures[$coordinate]['content']);
        $path = stream_get_meta_data($this->arPictures[$coordinate]['fileHandle'])['uri'];
        $this->arPictures[$coordinate]['arFile'] = CFile::MakeFileArray($path);
        if (empty($this->arPictures[$coordinate]['arFile'])) {
            return false;
        }
        $this->arPictures[$coordinate]['arFile']['name'] = $this->arPictures[$coordinate]['filename'];
        return true;
    }

    private function deleteOldFiles() {
        $fileId = $this->obReader->getFile()['ID'];
        $timestamp = AddToTimeStamp(array("DD" => -1));
        $dbFile = CFile::GetList([], ["MODULE_ID" => McartXls::getModuleID()]);
        while($arDbFile = $dbFile->GetNext()){
            if(
                $arDbFile['ID'] == $fileId ||
                empty($arDbFile['TIMESTAMP_X']) ||
                MakeTimeStamp($arDbFile['TIMESTAMP_X']) < $timestamp
            ){
                CFile::Delete($arDbFile['ID']);
            }
        }
    }

    public function __destruct() {
        foreach ($this->arPictures as $arPicture) {
            if($arPicture['fileHandle']){
                fclose($arPicture['fileHandle']);
            }
        }
    }

}
