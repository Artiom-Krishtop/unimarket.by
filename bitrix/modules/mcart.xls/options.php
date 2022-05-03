<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\McartXls;
use Mcart\Xls\ModuleOptions;
/* @var $obMcartXls McartXls */
Loc::loadMessages(__FILE__);

$module_id = "mcart.xls";
Loader::includeModule($module_id);
if(!McartXls::checkAccess('W')){
    return;
}
$obMcartXls = McartXls::getInstance();
if(!$obMcartXls->checkRequirements()){
    $obMcartXls->showErrors();
    return;
}
CJSCore::Init(array('jquery2', 'window', 'ajax'));

$aTabs = array(
	array("DIV" => "settings", "TAB" => Loc::getMessage("MAIN_TAB_SET"), "ICON" => "main_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "rights", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => Loc::getMessage("MAIN_TAB_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();?>
<form method="POST" action="<?= $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>" name="mcart_xls_settings" id="mcart_xls_settings"><?
echo bitrix_sessid_post();
$tabControl->BeginNextTab();
    $obModuleOptions = new ModuleOptions();
    $obModuleOptions->show();
$tabControl->BeginNextTab();?>
    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=Loc::getMessage('MCART_XLS__OPTIONS__SAVE')?>" class="adm-btn-save" />
	<input type="hidden" name="Update" value="Y">
	<input type="reset" name="reset" value="<?=Loc::getMessage('MCART_XLS__OPTIONS__RESET')?>" />
	<input type="button" value="<?=Loc::getMessage('MCART_XLS__OPTIONS__RESTORE_DEFAULTS'); ?>"
        onclick="javascript: if (confirm('<?= CUtil::JSEscape(Loc::getMessage("MCART_XLS__OPTIONS__HINT_RESTORE_DEFAULTS_WARNING")); ?>')){
		window.location = '<?= $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&mid=<?=$module_id?>&RestoreDefaults=Y&<?=bitrix_sessid_get()?>';
    }" />
<?$tabControl->End();?>
</form>

