<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$APPLICATION->SetAdditionalCSS("/bitrix/panel/main/mcart_xls_menu.css");

if($APPLICATION->GetGroupRight('mcart.xls') < "R"){
    return false;
}
$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section"   => 'mcart.xls',
    "sort"      => 800,
    "text"      => Loc::getMessage("MCART_XLS_TITLE"),
    "title"     => Loc::getMessage("MCART_XLS_TITLE"),
    "url"       => "mcart_xls_index.php?lang=".LANGUAGE_ID,
    "icon"      => "mcart_xls_menu_icon",
    "page_icon" => "mcart_xls_menu_icon",
    "items_id"  => "menu_mcart_xls",
    'more_url'  => array('mcart_xls_index.php', 'mcart_xls_profile_edit_step_1.php', 'mcart_xls_profile_edit_step_2.php', 'mcart_xls_profile_edit_step_3.php'),
    "items"     => array()
);
return $aMenu;

