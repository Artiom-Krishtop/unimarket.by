<?php

namespace Mcart\Xls\Admin;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\TypeLanguageTable as IblockTypeLanguageTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use CAdminTabControl;
use CFile;
use COption;
use Mcart\Xls\McartXls;
use Mcart\Xls\ModuleOptions;
use Mcart\Xls\ORM\Profile\Column\CustomFieldsTable;
use Mcart\Xls\ORM\Profile\ColumnTable;
use Mcart\Xls\ORM\Profile\ConstTable;
use Mcart\Xls\ORM\ProfileTable;
use const LANGUAGE_ID;
use function AddToTimeStamp;
use function LocalRedirect;
use function MakeTimeStamp;

Loc::loadMessages(__FILE__);

final class ProfileEdit {
    private $entityProfile;
    private $entityProfileConst;
    private $entityProfileColumn;
    private $entityProfileColumnCustomFields;
    private $PROFILE_ID = 0;
    private $arProfile = [];
    private $arProfileForForm = [];
    private $arFile;
    private $tabControl;
    private $step;
    private $requestPref = 'MCART_XLS_PROFILE__';
    private $obRequest;
    private $tabName = 'tabControl';
    private $arIBlocksByTypes = [];
    private $arIBlockTypesByIds = [];
    private $isSimpleInterface = false;

    public function isSimpleInterface() {
        return $this->isSimpleInterface;
    }

    private function checkOptionUsersGroups() {
        global $USER;
        $module_id = McartXls::getModuleID();
        $optionUsersGroups = COption::GetOptionString($module_id, ModuleOptions::OPTION_PREF.'USERS_GROUPS');
        if (empty($optionUsersGroups)) {
            return $this->isSimpleInterface;
        }
        $arOptionUsersGroups = explode(',', $optionUsersGroups);
        if (empty($arOptionUsersGroups)) {
            return $this->isSimpleInterface;
        }
        $userGroups = $USER->GetUserGroupArray();
        $intersect = array_intersect($userGroups, $arOptionUsersGroups);
        $this->isSimpleInterface = (!empty($intersect));
        return $this->isSimpleInterface;
    }

    private function checkOptions() {
        if(!$this->isSimpleInterface){
            return;
        }
        $module_id = McartXls::getModuleID();
        foreach (ModuleOptions::getOptions() as $arOption) {
            $this->arProfileForForm[$arOption['FIELD']] = COption::GetOptionString($module_id, ModuleOptions::OPTION_PREF.$arOption['FIELD']);
        }
        if($this->arProfileForForm['IBLOCK_ID']>0){
            $this->arProfileForForm['IBLOCK_TYPE_ID'] = $this->arIBlockTypesByIds[$this->arProfileForForm['IBLOCK_ID']];
        }
    }

    public function __construct($step, $requestPref = null, $setTitle = true, $tabName = '') {
        $this->step = intval($step);
        if($this->step < 1){
            $this->step = 1;
        }
        if($requestPref !== null){
            $this->requestPref = (string)filter_var($requestPref, FILTER_VALIDATE_REGEXP,
                array('options' => array('regexp' => '/^[_0-9A-z]+$/mis')));
        }

        $this->entityProfile = ProfileTable::getEntity();
        $this->entityProfileConst = ConstTable::getEntity();
        $this->entityProfileColumn = ColumnTable::getEntity();
        $this->entityProfileColumnCustomFields = CustomFieldsTable::getEntity();

        $context = Application::getInstance()->getContext();
        $this->obRequest = $context->getRequest();
        $isPost = $this->obRequest->isPost();
        if($isPost){
            $this->PROFILE_ID = (int)$this->obRequest->getPost($this->requestPref.'ID');
        }else{
            $this->PROFILE_ID = (int)$this->obRequest->getQuery('ID');
            if($this->step > 1){
                LocalRedirect($this->getFormAction(1));
            }
        }
        if($this->PROFILE_ID <= 0){
            $this->PROFILE_ID = 0;
        }
        if($this->PROFILE_ID){
            $this->arProfile = ProfileTable::getById($this->PROFILE_ID)->fetch();
            $this->arProfile['FILE'] = (string)$this->arProfile['FILE'];
        }
        if (empty($this->arProfile)){
            $this->PROFILE_ID = 0;
        }else{
            $this->arProfileForForm = $this->arProfile;
            if(!$isPost){
                $dbItems = ConstTable::getList(['filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
                $i = -1;
                while ($ar = $dbItems->fetch()) {
                    $i++;
                    $this->arProfileForForm['CONST_ID'][$i] = $ar['ID'];
                    $this->arProfileForForm['CONST_CODE'][$i] = $ar['SAVE_IN_PREF'].'_'.$ar['SAVE_IN']; //$ar['CODE'];
                    $this->arProfileForForm['CONST_VALUE'][$i] = $ar['VALUE'];
                }
            }
            $dbItems = ColumnTable::getList(['filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
            $i = -1;
            while ($ar = $dbItems->fetch()) {
                $i++;
                $this->arProfileForForm['COLUMN'][$i]['ID'] = $ar['ID'];
                $this->arProfileForForm['COLUMN'][$i]['COLUMN'] = $ar['COLUMN'];
                $this->arProfileForForm['COLUMN'][$i]['SAVE_IN'] = $ar['SAVE_IN_PREF'].'_'.$ar['SAVE_IN'];
                $this->arProfileForForm['COLUMN'][$i]['HANDLER'] = $ar['HANDLER'];
                $this->arProfileForForm['COLUMN'][$i]['DO_NOT_IMPORT_ROW_IF_EMPTY'] = $ar['DO_NOT_IMPORT_ROW_IF_EMPTY'];
                $this->arProfileForForm['COLUMN'][$i]['IS_IDENTIFY_ELEMENT'] = $ar['IS_IDENTIFY_ELEMENT'];

                $dbItems2 = CustomFieldsTable::getList(['filter' => ['COLUMN_ID' => $ar['ID']]]);
                $i2 = -1;
                while ($ar2 = $dbItems2->fetch()) {
                    $i2++;
                    $this->arProfileForForm['COLUMN'][$i]['CUSTOM_FIELDS'][$i2]['ID'] = $ar2['ID'];
                    $this->arProfileForForm['COLUMN'][$i]['CUSTOM_FIELDS'][$i2]['NAME'] = $ar2['NAME'];
                    $this->arProfileForForm['COLUMN'][$i]['CUSTOM_FIELDS'][$i2]['VALUE'] = $ar2['VALUE'];
                }

            }
        }
        $this->initTitleAndTabs($setTitle, $tabName);

        $dbResult = IblockTypeLanguageTable::getList([
            'select' => ['IBLOCK_TYPE_ID', 'NAME'],
            'filter' => ['LANGUAGE_ID' => LANGUAGE_ID],
            'order' => ['NAME' => 'ASC']
        ]);
        while ($arItem = $dbResult->fetch()) {
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBLOCK_TYPE_ID'] = $arItem['IBLOCK_TYPE_ID'];
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBLOCK_TYPE_NAME'] = $arItem['NAME'];
            if(!$isPost && !$this->PROFILE_ID){
                $this->arProfileForForm['IBLOCK_TYPE_ID'] = $arItem['IBLOCK_TYPE_ID'];
            }
        }
        $dbResult = IblockTable::getList([
            'select' => ['ID', 'IBLOCK_TYPE_ID', 'NAME'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['NAME' => 'ASC']
        ]);
        while ($arItem = $dbResult->fetch()) {
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBlocks'][] = $arItem;
            $this->arIBlockTypesByIds[$arItem['ID']] = $arItem['IBLOCK_TYPE_ID'];
            if(!$this->arProfileForForm['IBLOCK_ID'] && $arItem['IBLOCK_TYPE_ID'] == $this->arProfileForForm['IBLOCK_TYPE_ID']){
                $this->arProfileForForm['IBLOCK_ID'] = $arItem['ID'];
            }
        }
        if($this->arProfileForForm['IBLOCK_ID']>0){
            $this->arProfileForForm['IBLOCK_TYPE_ID'] = $this->arIBlockTypesByIds[$this->arProfileForForm['IBLOCK_ID']];
        }

        $FIELD = 'QUANTITY_ELEMENTS_IMPORTED_PER_STEP';
        if (empty($this->arProfileForForm[$FIELD])) {
            $this->arProfileForForm[$FIELD] = $this->getProfileFieldDefaultValue($FIELD);
        }
        $FIELD = 'START_ROW';
        if ($this->arProfileForForm[$FIELD]<=0) {
            $this->arProfileForForm[$FIELD] = $this->getProfileFieldDefaultValue($FIELD);
        }
        $FIELD = 'HEADER_ROW';
        if ($this->arProfileForForm[$FIELD]<=0) {
            $this->arProfileForForm[$FIELD] = $this->getProfileFieldDefaultValue($FIELD);
        }

        $this->checkOptionUsersGroups();
        if(!$isPost){
            $this->checkOptions();
            return;
        }
        $pref_len = strlen($this->requestPref);
        foreach ($this->obRequest->getPostList() as $k => $v) {
            $part1 = substr($k, 0, $pref_len);
            if($part1 !== $this->requestPref){
                continue;
            }
            $part2 = substr($k, $pref_len);
            $this->arProfileForForm[$part2] = $v;
        }
        $FIELD = 'START_ROW';
        $this->arProfileForForm[$FIELD] = intval($this->arProfileForForm[$FIELD]);
        if ($this->arProfileForForm[$FIELD]<=0) {
            $this->arProfileForForm[$FIELD] = $this->getProfileFieldDefaultValue($FIELD);
        }
        $FIELD = 'HEADER_ROW';
        $this->arProfileForForm[$FIELD] = intval($this->arProfileForForm[$FIELD]);
        if ($this->arProfileForForm[$FIELD]<=0) {
            $this->arProfileForForm[$FIELD] = $this->arProfileForForm['START_ROW'];
        }
        $this->loadFile();
        $this->checkOptions();
    }

    private function loadFile() {
        $FIELD_FILE = 'FILE';
        $FIELD_FILE_ID = 'FILE_ID';
        if($this->arProfileForForm[$FIELD_FILE_ID] > 0){
            $this->arFile = CFile::MakeFileArray($this->arProfileForForm[$FIELD_FILE_ID]);
            $this->arFile['ID'] = $this->arProfileForForm[$FIELD_FILE_ID];
            $this->arFile['PATH'] = CFile::GetPath($this->arFile['ID']);
            return;
        }
        $arFile = $this->obRequest->getFile($this->requestPref.$FIELD_FILE);
        $post = $this->arProfileForForm[$FIELD_FILE];
        if((!$arFile || $arFile['error']>0) && !empty($post)){
            $arFile = CFile::MakeFileArray($post);
        }
        $arFile['MODULE_ID'] = McartXls::getModuleID();

        $this->arProfileForForm[$FIELD_FILE_ID] = $arFile['ID'] = CFile::SaveFile($arFile, $arFile['MODULE_ID']);
        if($this->arProfileForForm[$FIELD_FILE_ID] > 0){
            $this->arFile = $arFile;
            $this->arFile['PATH'] = CFile::GetPath($this->arFile['ID']);
        }
    }

    private function initTitleAndTabs($setTitle = true, $tabName = '') {
        global $APPLICATION;
        if($setTitle){
            if($this->PROFILE_ID){
                $APPLICATION->SetTitle(Loc::getMessage("MCART_XLS_PROFILE_EDIT_TITLE").' "'.$this->arProfile['NAME'].'" - '.Loc::getMessage("MCART_XLS_TITLE"));
            }else{
                $APPLICATION->SetTitle(Loc::getMessage("MCART_XLS_PROFILE_NEW_TITLE").' - '.Loc::getMessage("MCART_XLS_TITLE"));
            }
        }
        if (!empty($tabName)) {
            $this->tabName = $tabName;
        }
        $this->tabControl = new CAdminTabControl(
            $this->tabName,
            [
                [
                    "DIV" => "step1",
//                    "ICON" => "main_user_edit",
                    "TAB" => Loc::getMessage("MCART_XLS_PROFILE_STEP1"),
                    "TITLE" => Loc::getMessage("MCART_XLS_PROFILE_STEP1")
                ],
                [
                    "DIV" => "step2",
//                    "ICON" => "main_user_edit",
                    "TAB" => Loc::getMessage("MCART_XLS_PROFILE_STEP2"),
                    "TITLE" => Loc::getMessage("MCART_XLS_PROFILE_STEP2")
                ],
                [
                    "DIV" => "step3",
//                    "ICON" => "main_user_edit",
                    "TAB" => Loc::getMessage("MCART_XLS_PROFILE_STEP3"),
                    "TITLE" => Loc::getMessage("MCART_XLS_PROFILE_STEP3_TITLE")
                ],
            ],
            false
        );
    }

    public function saveProfile() {
        /* @var $obMcartXls McartXls */
        $obMcartXls = McartXls::getInstance();
        $arFields = [];
        foreach ($this->entityProfile->getFields() as $obField) {
            $fieldName = $obField->getName();
            if($fieldName == 'ID' || ($obField instanceof Entity\ReferenceField)){
                continue;
            }
            $arFields[$fieldName] = (string)$this->arProfileForForm[$fieldName];
            if($arFields[$fieldName] == '' && ($obField instanceof Entity\BooleanField)){
                $arFields[$fieldName] = 'N';
            }
        }
        $conn = Application::getConnection();
        $conn->startTransaction();
        try {
            if($this->PROFILE_ID){
                $result = ProfileTable::update($this->PROFILE_ID, $arFields);
            }else{
                $result = ProfileTable::add($arFields);
            }
            if (!$result->isSuccess()) {
                $obMcartXls->addErrors($result->getErrors());
                $conn->rollbackTransaction();
                return false;
            }
            $this->PROFILE_ID = $result->getId();
            $this->arProfile['ID'] = $this->PROFILE_ID;
            $this->arProfileForForm['ID'] = $this->PROFILE_ID;
            if(!$this->saveConsts()){
                $conn->rollbackTransaction();
                return false;
            }
            if(!$this->saveColumns()){
                $conn->rollbackTransaction();
                return false;
            }
        } catch(\Throwable $e) {
            $obMcartXls->addError($obMcartXls->getErrorMessage($e, 'Error save profile'));
            $conn->rollbackTransaction();
            return false;
        } catch(\Exception $e) {
            $obMcartXls->addError($obMcartXls->getErrorMessage($e, 'Error save profile'));
            $conn->rollbackTransaction();
            return false;
        }
        $conn->commitTransaction();
        return true;
    }

    private function saveConsts() {
        if(!is_array($this->arProfileForForm['CONST_CODE']) || empty($this->arProfileForForm['CONST_CODE'])){
            $dbItems = ConstTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
            while ($ar = $dbItems->fetch()) {
                ConstTable::delete($ar['ID']);
            }
            return true;
        }
        $oldIds = [];
        $newIds = [];
        $dbItems = ConstTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
        while ($ar = $dbItems->fetch()) {
            $oldIds[$ar['ID']] = $ar['ID'];
        }
        foreach ($this->arProfileForForm['CONST_CODE'] as $k => $v) {
            $v = trim((string)$v);
            if (empty($v)) {
                continue;
            }
            $arSaveIn = explode('_', $v);
            $ID = intval($this->arProfileForForm['CONST_ID'][$k]);
            $arFields = [
                'PROFILE_ID' => $this->PROFILE_ID,
                'SAVE_IN_PREF' => array_shift($arSaveIn),
                'SAVE_IN' => implode('_', $arSaveIn),
                'VALUE' => (string)$this->arProfileForForm['CONST_VALUE'][$k],
            ];
            if($ID && in_array($ID, $oldIds)){
                $result = ConstTable::update($ID, $arFields);
            }else{
                $result = ConstTable::add($arFields);
            }
            if (!$result->isSuccess()) {
                McartXls::getInstance()->addErrors($result->getErrors());
                return false;
            }
            $ID = $result->getId();
            $newIds[$ID] = $ID;
        }
        if (empty($newIds)) {
            $dbItems = ConstTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
        }else{
            $dbItems = ConstTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID, '!@ID' => $newIds]]);
        }
        while ($ar = $dbItems->fetch()) {
            ConstTable::delete($ar['ID']);
        }
        return true;
    }

    private function saveColumns() {
        if(!is_array($this->arProfileForForm['COLUMN']) || empty($this->arProfileForForm['COLUMN'])){
            $dbItems = ColumnTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
            while ($ar = $dbItems->fetch()) {
                ColumnTable::delete($ar['ID']);
            }
            return true;
        }
        $oldIds = [];
        $newIds = [];
        $dbItems = ColumnTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
        while ($ar = $dbItems->fetch()) {
            $oldIds[$ar['ID']] = $ar['ID'];
        }
        foreach ($this->arProfileForForm['COLUMN'] as $ar) {
            $v = trim((string)$ar['SAVE_IN']);
            $COLUMN = trim((string)$ar['COLUMN']);
            if (empty($v) || empty($COLUMN)) {
                continue;
            }
            $arSaveIn = explode('_', $v);
            $ID = intval($ar['ID']);
            $arFields = [
                'PROFILE_ID' => $this->PROFILE_ID,
                'SAVE_IN_PREF' => array_shift($arSaveIn),
                'SAVE_IN' => implode('_', $arSaveIn),
                'COLUMN' => $COLUMN,
                'HANDLER' => trim((string)$ar['HANDLER']),
                'DO_NOT_IMPORT_ROW_IF_EMPTY' => (trim((string)$ar['DO_NOT_IMPORT_ROW_IF_EMPTY'])=='Y'?'Y':'N'),
                'IS_IDENTIFY_ELEMENT' => (trim((string)$ar['IS_IDENTIFY_ELEMENT'])=='Y'?'Y':'N'),
            ];
            if($ID && in_array($ID, $oldIds)){
                $result = ColumnTable::update($ID, $arFields);
            }else{
                $result = ColumnTable::add($arFields);
            }
            if (!$result->isSuccess()) {
                McartXls::getInstance()->addErrors($result->getErrors());
                return false;
            }
            $ID = $result->getId();
            $newIds[$ID] = $ID;
            if(!$this->saveCustomFields($ID, $ar['CUSTOM_FIELDS'])){
                return false;
            }
        }
        if (empty($newIds)) {
            $dbItems = ColumnTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID]]);
        }else{
            $dbItems = ColumnTable::getList(['select' => ['ID'], 'filter' => ['PROFILE_ID' => $this->PROFILE_ID, '!@ID' => $newIds]]);
        }
        while ($ar = $dbItems->fetch()) {
            ColumnTable::delete($ar['ID']);
        }
        return true;
    }

    private function saveCustomFields($columnId, $arCustomFields) {
        if($columnId <= 0){
            return true;
        }
        if(!is_array($arCustomFields) || empty($arCustomFields)){
            $dbItems = CustomFieldsTable::getList(['select' => ['ID'], 'filter' => ['COLUMN_ID' => $columnId]]);
            while ($ar = $dbItems->fetch()) {
                CustomFieldsTable::delete($ar['ID']);
            }
            return true;
        }
        $oldIds = [];
        $newIds = [];
        $dbItems = CustomFieldsTable::getList(['select' => ['ID'], 'filter' => ['COLUMN_ID' => $columnId]]);
        while ($ar = $dbItems->fetch()) {
            $oldIds[$ar['ID']] = $ar['ID'];
        }
        foreach ($arCustomFields as $arCustomField) {
            $ID = intval($arCustomField['ID']);
            $arFields = [
                'COLUMN_ID' => $columnId,
                'NAME' => trim((string)$arCustomField['NAME']),
                'VALUE' => (string)$arCustomField['VALUE'],
            ];
            if (empty($arFields['NAME'])) {
                continue;
            }
            if($ID && in_array($ID, $oldIds)){
                $result = CustomFieldsTable::update($ID, $arFields);
            }else{
                $result = CustomFieldsTable::add($arFields);
            }
            if (!$result->isSuccess()) {
                McartXls::getInstance()->addErrors($result->getErrors());
                return false;
            }
            $ID = $result->getId();
            $newIds[$ID] = $ID;
        }
        if (empty($newIds)) {
            $dbItems = CustomFieldsTable::getList(['select' => ['ID'], 'filter' => ['COLUMN_ID' => $columnId]]);
        }else{
            $dbItems = CustomFieldsTable::getList(['select' => ['ID'], 'filter' => ['COLUMN_ID' => $columnId, '!@ID' => $newIds]]);
        }
        while ($ar = $dbItems->fetch()) {
            CustomFieldsTable::delete($ar['ID']);
        }
        return true;
    }

    public function getFile() {
        return $this->arFile;
    }

    public function getTabParam($tab){
		return $this->tabName."_active_tab=".urlencode($tab);
	}

    public function getFormAction($to_step){
        $to_step = intval($to_step);
        if($to_step <= 0){
            $to_step = 1;
        }
        $s = "mcart_xls_profile_edit_step_$to_step.php?lang=ru";
        if($this->PROFILE_ID){
            $s .= '&ID='.$this->PROFILE_ID;
        }
		return $s;
	}

    public function getStep() {
        return $this->step;
    }

    public function getRequestPref() {
        return $this->requestPref;
    }

    public function getEntityProfile() {
        return $this->entityProfile;
    }

    public function getEntityProfileConst() {
        return $this->entityProfileConst;
    }

    public function getEntityProfileColumn() {
        return $this->entityProfileColumn;
    }

    public function getEntityProfileColumnCustomFields() {
        return $this->entityProfileColumnCustomFields;
    }
    public function getProfileColumnCustomFieldsFieldTitle($FIELD) {
        return $this->entityProfileColumnCustomFields->getField($FIELD)->getTitle();
    }

    public function getProfileID() {
        return $this->PROFILE_ID;
    }

    public function getProfile() {
        return $this->arProfile;
    }

    public function getProfileForForm() {
        return $this->arProfileForForm;
    }

    public function getTabControl() {
        return $this->tabControl;
    }

    public function getProfileFieldTitle($FIELD) {
        return $this->entityProfile->getField($FIELD)->getTitle();
    }
    public function getProfileFieldDefaultValue($FIELD) {
        return $this->entityProfile->getField($FIELD)->getDefaultValue();
    }

    public function getProfileFieldValues($FIELD) {
        $ar = $this->entityProfile->getField($FIELD)->getValues();
        return (is_array($ar)? array_flip($ar) : []);
    }

    public function getProfileColumnFieldValues($FIELD) {
        $ar = $this->entityProfileColumn->getField($FIELD)->getValues();
        return (is_array($ar)? array_flip($ar) : []);
    }

    public function getIBlocksByTypes() {
        return $this->arIBlocksByTypes;
    }

    public function getCatalogFields() {
        $arCatalogFields = [];
        $arFields = [
            'QUANTITY',
            'QUANTITY_TRACE',
            'MEASURE',
//            'VAT_ID',
            'VAT_RATE',
            'VAT_INCLUDED',
            'PURCHASING_PRICE',
            'PURCHASING_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arCatalogFields[] = [
                'CODE' => 'PRODUCT_'.$key,
                'NAME' => '['.Loc::getMessage("MCART_XLS_CATALOG").'] '.Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }
        $arFields = [
            'BASE_PRICE',
            'BASE_PRICE_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arCatalogFields[] = [
                'CODE' => 'PRICE_'.$key,
                'NAME' => '['.Loc::getMessage("MCART_XLS_CATALOG").'] '.Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }
        return $arCatalogFields;
    }


    public function getColumnsForConst() {
        $arColumns = [];

        $arFields = [
            'NAME',
            'SORT',
            'PREVIEW_TEXT',
        ];
        $arColumns['FIELDS']['NAME'] = Loc::getMessage("MCART_XLS_FIELDS");
        foreach ($arFields as $key) {
            $arColumns['FIELDS']['ITEMS'][] = [
                'KEY' => ConstTable::SAVE_IN_PREF__FIELD.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_FIELD_'.$key)
            ];
        }

        $arColumns['PROPERTIES']['NAME'] = Loc::getMessage("MCART_XLS_PROPERTIES");
        $arColumns['PROPERTIES']['ITEMS'] = [];

        $arColumns['CATALOG']['NAME'] = Loc::getMessage("MCART_XLS_CATALOG");
        $arFields = [
            'QUANTITY',
            'QUANTITY_TRACE',
            'MEASURE',
//            'VAT_ID',
            'VAT_RATE',
            'VAT_INCLUDED',
            'PURCHASING_PRICE',
            'PURCHASING_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arColumns['CATALOG']['ITEMS'][] = [
                'KEY' => ConstTable::SAVE_IN_PREF__PRODUCT.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }
        $arFields = [
            'BASE_PRICE',
            'BASE_PRICE_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arColumns['CATALOG']['ITEMS'][] = [
                'KEY' => ConstTable::SAVE_IN_PREF__PRICE.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }

        return $arColumns;
    }

    public function getColumns() {
        $arColumns = [];
        $IBLOCK_ID = intval($this->arProfileForForm['IBLOCK_ID']);
        if($IBLOCK_ID <= 0){
            return $arColumns;
        }

        $arFields = [
            'ID',
            'CODE',
            'XML_ID',
            'NAME',
            'SORT',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_PICTURE',
            'DETAIL_TEXT',
        ];
        $arColumns['FIELDS']['NAME'] = Loc::getMessage("MCART_XLS_FIELDS");
        foreach ($arFields as $key) {
            $arColumns['FIELDS']['ITEMS'][] = [
                'KEY' => ColumnTable::SAVE_IN_PREF__FIELD.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_FIELD_'.$key)
            ];
        }

        $dbProps = PropertyTable::getList([
            'order'  => ["NAME" => "ASC"],
            'select' => ['ID', 'NAME', 'PROPERTY_TYPE'],
            'filter' => [
                '=IBLOCK_ID' => $IBLOCK_ID,
                '@PROPERTY_TYPE' => [
                    PropertyTable::TYPE_STRING,
                    PropertyTable::TYPE_NUMBER,
                    PropertyTable::TYPE_FILE,
                    PropertyTable::TYPE_LIST
                ]
            ]
        ]);
        $arColumns['PROPERTIES']['NAME'] = Loc::getMessage("MCART_XLS_PROPERTIES");
        $arColumns['PROPERTIES']['ITEMS'] = [];
        while ($arProp = $dbProps->fetch()){
            $arColumns['PROPERTIES']['ITEMS'][] = [
                'KEY' => ColumnTable::SAVE_IN_PREF__PROPERTY.'_'.$arProp['ID'],
                'NAME' => '['.$arProp['ID'].'] '.$arProp['NAME']
            ];
        }

        $arColumns['CATALOG']['NAME'] = Loc::getMessage("MCART_XLS_CATALOG");
        $arFields = [
            'QUANTITY',
            'QUANTITY_TRACE',
            'VAT_RATE',
            'PURCHASING_PRICE',
            'PURCHASING_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arColumns['CATALOG']['ITEMS'][] = [
                'KEY' => ColumnTable::SAVE_IN_PREF__PRODUCT.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }
        $arFields = [
            'BASE_PRICE',
            'BASE_PRICE_CURRENCY',
        ];
        foreach ($arFields as $key) {
            $arColumns['CATALOG']['ITEMS'][] = [
                'KEY' => ColumnTable::SAVE_IN_PREF__PRICE.'_'.$key,
                'NAME' => Loc::getMessage('MCART_XLS_CATALOG_'.$key)
            ];
        }

        return $arColumns;
    }

}
