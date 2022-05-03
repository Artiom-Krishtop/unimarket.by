<?php

namespace Mcart\Xls;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\TypeLanguageTable as IblockTypeLanguageTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use CGroup;
use COption;
use Mcart\Xls\Helpers\Html;
use Mcart\Xls\McartXls;
use Mcart\Xls\ORM\ProfileTable;
use const LANGUAGE_ID;

Loc::loadMessages(__FILE__);

final class ModuleOptions {
    const OPTION_PREF = 'PROFILE__';
    const REQUEST_PREF = 'MCART_XLS__';
    private $entityProfile;
    private $arIBlocksByTypes = [];
    private $arIBlockTypeByIBlock = [];
    private $arUsersGroups = [];
    private static $arOptions = [
        'USERS_GROUPS' => [
            'FIELD' => 'USERS_GROUPS',
            'TYPE' => 'select',
            'MULTIPLE' => 'multiple'
        ],
        'IBLOCK_ID' => [
            'FIELD' => 'IBLOCK_ID',
            'TYPE' => 'select_iblock',
        ],
        'QUANTITY_ELEMENTS_IMPORTED_PER_STEP' => [
            'FIELD' => 'QUANTITY_ELEMENTS_IMPORTED_PER_STEP',
            'TYPE' => 'text',
            'SIZE' => 10
        ],
        'ONLY_UPDATE' => [
            'FIELD' => 'ONLY_UPDATE',
            'TYPE' => 'boolean',
        ],
        'DEACTIVATE_IF_NEW' => [
            'FIELD' => 'DEACTIVATE_IF_NEW',
            'TYPE' => 'boolean',
        ],
        'DEACTIVATE_IF_QUANTITY_0' => [
            'FIELD' => 'DEACTIVATE_IF_QUANTITY_0',
            'TYPE' => 'boolean',
        ],
        'DEACTIVATE_IF_PRICE_0' => [
            'FIELD' => 'DEACTIVATE_IF_PRICE_0',
            'TYPE' => 'boolean',
        ],
        'ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0' => [
            'FIELD' => 'ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0',
            'TYPE' => 'boolean',
        ],
        'END_ROW' => [
            'FIELD' => 'END_ROW',
            'TYPE' => 'text',
            'SIZE' => 10,
            'TOOLTIP_KEY' => 'MCART_XLS__PROFILE__END_ROW_TOOLTIP'
        ],
    ];

    /**
     * @var \Bitrix\Main\HttpRequest
     */
    private $obRequest;

    public static function getOptions() {
        return ModuleOptions::$arOptions;
    }

    public function __construct() {
        global $APPLICATION;

        $this->entityProfile = ProfileTable::getEntity();
        $context = Application::getInstance()->getContext();
        $this->obRequest = $context->getRequest();
        $module_id = McartXls::getModuleID();

        if ($this->obRequest->getQuery('RestoreDefaults') === 'Y' && check_bitrix_sessid()){
            COption::RemoveOption($module_id);
            $rsGroups = CGroup::GetList($by="id", $order="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
            while($arGroup = $rsGroups->Fetch()){
                $APPLICATION->DelGroupRight($module_id, array($arGroup["ID"]));
            }
            LocalRedirect($APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&mid='.$module_id);
        }
        if($this->obRequest->isPost() && check_bitrix_sessid() && $this->obRequest->getPost('Update') === 'Y') {
            $pref = ModuleOptions::REQUEST_PREF.ModuleOptions::OPTION_PREF;
            foreach (ModuleOptions::$arOptions as $arOption) {
                $VALUE = $this->obRequest->getPost($pref.$arOption['FIELD']);
                if($arOption['FIELD']=='MULTIPLE' && is_array($VALUE)){
                    $VALUE = implode(',', $VALUE);
                    foreach ($VALUE as $k => $v) {
                        if($k == 0 || $k == ''){
                            unset($VALUE[$k]);
                        }
                    }
                }
                COption::SetOptionString($module_id, ModuleOptions::OPTION_PREF.$arOption['FIELD'], $VALUE);
            }
            ob_start();
            require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
            ob_end_clean();
            LocalRedirect($APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&mid='.$module_id);
        }

        $dbResult = IblockTypeLanguageTable::getList([
            'select' => ['IBLOCK_TYPE_ID', 'NAME'],
            'filter' => ['LANGUAGE_ID' => LANGUAGE_ID],
            'order' => ['NAME' => 'ASC']
        ]);
        while ($arItem = $dbResult->fetch()) {
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBLOCK_TYPE_ID'] = $arItem['IBLOCK_TYPE_ID'];
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBLOCK_TYPE_NAME'] = $arItem['NAME'];
        }
        $dbResult = IblockTable::getList([
            'select' => ['ID', 'IBLOCK_TYPE_ID', 'NAME'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['NAME' => 'ASC']
        ]);
        while ($arItem = $dbResult->fetch()) {
            $this->arIBlocksByTypes[$arItem['IBLOCK_TYPE_ID']]['IBlocks'][] = $arItem;
            $this->arIBlockTypeByIBlock[$arItem['ID']] = $arItem['IBLOCK_TYPE_ID'];
        }

        $this->arUsersGroups[0] = ['ID' => 0, 'NAME' => ' '];
        $rsGroups = CGroup::GetList($by="dropdown", $order="asc", Array("ACTIVE" => "Y"));
        while ($arGroup = $rsGroups->Fetch()){
            $this->arUsersGroups[$arGroup["ID"]] = ['ID' => $arGroup["ID"], 'NAME' => '['.$arGroup["ID"].'] '.$arGroup["NAME"]];
        }
    }

    public function show() {
        $obHtml = new Html();
        $pref = ModuleOptions::REQUEST_PREF.ModuleOptions::OPTION_PREF;
        $module_id = McartXls::getModuleID();

        $FIELD = 'USERS_GROUPS';
        $VALUES = [];
        $value = COption::GetOptionString($module_id, ModuleOptions::OPTION_PREF.$FIELD);
        if (!empty($value)) {
            $VALUES = explode(',', $value);
        }
        if (empty($VALUES)) {
            $VALUES = [0];
        }
        echo $obHtml->getRowInput(
            Loc::getMessage($pref.$FIELD),
            Loc::getMessage($pref.$FIELD.'_TOOLTIP'),
            false,
            'select',
            [
                'name' => $pref.$FIELD,
                'selected' => $VALUES,
                'options' => $this->arUsersGroups,
                'options_as_key_value' => false,
                'option_key_for_value' => 'ID',
                'option_key_for_name' => 'NAME',
                'multiple' => 'multiple'
            ],
            ['width' => '40%', 'style' => 'border-bottom: 1px solid #c4ced2;'],
            ['width' => '60%', 'style' => 'border-bottom: 1px solid #c4ced2;']
        );

        foreach (ModuleOptions::$arOptions as $arOption) {
            if($arOption['FIELD']=='USERS_GROUPS'){
                continue;
            }
            if($arOption['TYPE']=='select_iblock'){
                $IBLOCK_ID = COption::GetOptionString($module_id, ModuleOptions::OPTION_PREF.$arOption['FIELD']);
                $input_options = [
                    'arIBlocksByTypes' => $this->arIBlocksByTypes,
                    'iblock_type_id' => ['name' => $pref.'IBLOCK_TYPE_ID', 'selected' => $this->arIBlockTypeByIBlock[$IBLOCK_ID]],
                    'iblock_id' => ['name' => $pref.$arOption['FIELD'], 'selected' => $IBLOCK_ID],
                    'selectorJquery' => '#mcart_xls_settings',
                ];
                echo $obHtml->getRowInput(
                    $this->getProfileFieldTitle($arOption['FIELD']),
                    (empty($arOption['TOOLTIP_KEY'])? '' : Loc::getMessage($arOption['TOOLTIP_KEY'])),
                    false,
                    $arOption['TYPE'],
                    $input_options
                );
                continue;
            }
            $input_options = [
                'name'=> $pref.$arOption['FIELD'],
                'value'=> COption::GetOptionString($module_id, ModuleOptions::OPTION_PREF.$arOption['FIELD'])
            ];
            if($arOption['SIZE'] > 0){
                $input_options['size'] = $arOption['SIZE'];
            }
            echo $obHtml->getRowInput(
                $this->getProfileFieldTitle($arOption['FIELD']),
                (empty($arOption['TOOLTIP_KEY'])? '' : Loc::getMessage($arOption['TOOLTIP_KEY'])),
                false,
                $arOption['TYPE'],
                $input_options
            );
        }
    }

    public function getEntityProfile() {
        return $this->entityProfile;
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

}
