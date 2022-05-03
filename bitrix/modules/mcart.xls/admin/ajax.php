<?
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\McartXls;
use Mcart\Xls\Ajax\Ajax;

define("NO_KEEP_STATISTIC", true);
//define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mcart.xls/prolog.php");
Loc::loadMessages(__FILE__);

if(!McartXls::checkAccess('W', false)){
    return;
}
$obAjax = new Ajax('MCART_XLS_PROFILE__');
$obAjax->execAction();