<?

use \Bitrix\Main\Localization\Loc;

global $APPLICATION;
global $arErrors;

if (!check_bitrix_sessid())
    return;

foreach ($arErrors as $errorMessage) {
    echo \CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => $errorMessage,
        "DETAILS" => '',
        "HTML" => true,
    ));
}
?>

<form action="<? echo $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<? echo LANGUAGE_ID ?>">
    <input type="submit" name="" value="<? echo Loc::getMessage("MOD_BACK"); ?>">
</form>