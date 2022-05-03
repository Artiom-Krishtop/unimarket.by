<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init(array('jquery2', 'window', 'ajax'));

Bitrix\Main\Loader::includeModule('mcart.xls');
define("ADMIN_MODULE_NAME", "mcart.xls");
define("ADMIN_MODULE_ICON", '<img src="/bitrix/images/mcart.xls/mcartexcel.png" width="33" height="33" border="0" alt="" />');
Mcart\Xls\McartXls::getInstance(); // module init
