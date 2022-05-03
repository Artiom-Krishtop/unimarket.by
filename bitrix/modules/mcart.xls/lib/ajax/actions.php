<?php
namespace Mcart\Xls\Ajax;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\McartXls;
use Mcart\Xls\Spreadsheet\Import;

Loc::loadMessages(__FILE__);

abstract class Actions {

    protected function execActionGetPropertiesAndSections() {
        $IBLOCK_ID = intval($this->obRequest->getPost($this->requestPref.'IBLOCK_ID'));
        $this->arResult[$this->requestPref.'IBLOCK_ID'] = $IBLOCK_ID;
        if($IBLOCK_ID <= 0){
            return false;
        }
        $this->arResult['RESULT'] = 1;
        $dbProps = PropertyTable::getList([
            'order'  => ["NAME" => "ASC"],
            'select' => ['ID', 'NAME', 'PROPERTY_TYPE'],
            'filter' => [
                '=IBLOCK_ID' => $IBLOCK_ID,
                '@PROPERTY_TYPE' => [PropertyTable::TYPE_STRING, PropertyTable::TYPE_NUMBER]
            ]
        ]);
        while ($arProp = $dbProps->fetch()){
            $this->arResult['PROPERTIES'][] = ['ID' => $arProp['ID'], 'NAME' => '['.$arProp['ID'].'] '.$arProp['NAME']];
        }
        $dbSections = SectionTable::getList([
            'order'  => ["LEFT_MARGIN" => "ASC"],
            'select' => ['ID', 'NAME', 'DEPTH_LEVEL'],
            'filter' => ['=IBLOCK_ID' => $IBLOCK_ID]
        ]);
        while ($arSection = $dbSections->fetch()){
            $NAME_PREF = '';
            if($arSection['DEPTH_LEVEL'] > 1){
                $NAME_PREF .= str_repeat("&nbsp;.", ($arSection['DEPTH_LEVEL']-1)).'&nbsp;';
            }
            $this->arResult['SECTIONS'][] = [
                'ID' => $arSection['ID'],
                'NAME' => $NAME_PREF.'['.$arSection['ID'].'] '.$arSection['NAME']
            ];
        }
        return true;
    }

    protected function execActionImport() {
        /* @var $obMcartXls McartXls */
        $profileId = intval($this->obRequest->getPost($this->requestPref.'PROFILE_ID'));
        $file = intval($this->obRequest->getPost($this->requestPref.'FILE_ID'));
        $startRow = intval($this->obRequest->getPost($this->requestPref.'START_ROW'));
        $this->arResult['RESULT'] = 0;
        $this->arResult['ERRORS'] = '';
        $this->arResult['isComplete'] = null;
        $this->arResult['nextStartRow'] = false;
        $this->arResult['log'] = '';
        $this->arResult['processedRows'] = 0;
        $this->arResult['addedElements'] = 0;
        $this->arResult['updatedElements'] = 0;
        if($profileId <= 0 || $file <= 0){
            $this->arResult['RESULT'] = 0;
            $this->arResult['ERRORS'] = 'Error params';
            $this->arResult['params'] = [$profileId, $file, $startRow];
            return false;
        }
        try {
            $obMcartXls = McartXls::getInstance();
        } catch(\Throwable $e) {
            $this->arResult['ERRORS'] .= 'Error McartXls instance';
            return;
        } catch(\Exception $e) {
            $this->arResult['ERRORS'] .= 'Error McartXls instance';
            return;
        }
        try{
            $obImport = new Import($profileId, $file);
            $obImport->exec($startRow);
            $this->arResult['isComplete'] = $obImport->isComplete();
            $this->arResult['nextStartRow'] = $obImport->getNextStartRow();
            $this->arResult['arLogDebug'] = $obImport->getLogDebugArray();
            $this->arResult['log'] = $obImport->getLogString();
            $this->arResult['processedRows'] = $obImport->getProcessedRows();
            $this->arResult['addedElements'] = $obImport->getAddedElements();
            $this->arResult['updatedElements'] = $obImport->getUpdatedElements();
            if(!$obMcartXls->hasErrors()){
                $this->arResult['RESULT'] = 1;
            }else{
                foreach ($obMcartXls->getErrors() as $obError) {
                    $this->arResult['ERRORS'] .= '['.$obError->getCode().'] '.$obError->getMessage();
                }
            }
        } catch(\Throwable $e) {
            $this->arResult['ERRORS'] .= $obMcartXls->getErrorMessage($e, 'Error import');
        } catch(\Exception $e) {
            $this->arResult['ERRORS'] .= $obMcartXls->getErrorMessage($e, 'Error import');
        }
    }

}
