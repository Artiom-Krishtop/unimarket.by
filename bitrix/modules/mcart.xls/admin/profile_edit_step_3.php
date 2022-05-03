<?
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\Admin\ProfileEdit;
use Mcart\Xls\Helpers\Html;
use Mcart\Xls\McartXls;
use Mcart\Xls\Spreadsheet\Import;
/* @var $obMcartXls McartXls */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mcart.xls/prolog.php");
Loc::loadMessages(__FILE__);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
//--------------------------//
if(!McartXls::checkAccess('W')){
    return;
}
$obMcartXls = McartXls::getInstance();
if(!$obMcartXls->checkRequirements()){
    $obMcartXls->showErrors();
    return;
}
try {
    $obProfileEdit = new ProfileEdit(3);
    $pref = $obProfileEdit->getRequestPref();
    if(!($isSaveProfile = $obProfileEdit->saveProfile())){
        $obMcartXls->showErrors();
    }
    $arProfile = $obProfileEdit->getProfileForForm();
} catch (Exception $e) {
    CAdminMessage::ShowMessage($obMcartXls->getErrorMessage($e, 'Error'));
    return;
}
//--------------------------//
?>
<form method="POST" action="<?=$obProfileEdit->getFormAction(2)?>" enctype="multipart/form-data"
      name="mcart_xls_profile_edit_form" id="mcart_xls_profile_edit_form">
    <?=bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>"> <?
    foreach ($arProfile as $k => $v) {
        Html::showInputsHidden($pref, $k, $v);
    }
$obProfileEdit->getTabControl()->Begin(); 
$obProfileEdit->getTabControl()->BeginNextTab();
$obProfileEdit->getTabControl()->BeginNextTab();
$obProfileEdit->getTabControl()->BeginNextTab();?>
    <tr>
        <td colspan="2"><?
            if(!$obMcartXls->hasErrors()){?>
                <div class="import">
                    <div class="errors" style="color:red;"></div>
                    <div class="processedRows"><?=Loc::getMessage("MCART_XLS_IMPORT__PROCESSED_ROWS")?>: <span class="value">0</span></div>
                    <div class="addedElements"><?=Loc::getMessage("MCART_XLS_IMPORT__ADDED_ELEMENTS")?>: <span class="value">0</span></div>
                    <div class="updatedElements"><?=Loc::getMessage("MCART_XLS_IMPORT__UPDATED_ELEMENTS")?>: <span class="value">0</span></div>
                    <div class="log"></div>
                </div><?
            }
        ?></td>
    </tr>
<?
$obProfileEdit->getTabControl()->Buttons();
?>
<input class="adm-btn" type="submit" name="prev" value="<?=Loc::getMessage("MCART_XLS_PROFILE_PREV")?>" />
<a class="adm-btn" href="/bitrix/admin/mcart_xls_index.php?lang=<?=LANGUAGE_ID?>"><?=Loc::getMessage("MCART_XLS_PROFILE_CANCEL")?></a><?

$obProfileEdit->getTabControl()->End();
?>
<script type="text/javascript">
$(function() {
    <?if($isSaveProfile){ ?>
        var obImport = $('form#mcart_xls_profile_edit_form .import'),
            obImportErrors = obImport.find('.errors'),
            obImportProcessedRows = obImport.find('.processedRows .value'),
            obImportAddedElements = obImport.find('.addedElements .value'),
            obImportUpdatedElements = obImport.find('.updatedElements .value'),
            obImportLog = obImport.find('.log'),
            obBtns = $('form#mcart_xls_profile_edit_form .adm-btn');
        function mcartXlsImport(startRow){
            $.ajax({
                type: "POST",
                dataType: "json",
                url: 'mcart_xls_ajax.php',
                data: {
                    sessid: '<?=bitrix_sessid()?>',
                    <?=$pref?>act: 'Import',
                    <?=$pref?>PROFILE_ID: '<?=$arProfile['ID']?>',
                    <?=$pref?>FILE_ID: '<?=$obProfileEdit->getFile()['ID']?>',
                    <?=$pref?>START_ROW: startRow
                }
            }).done(function(arResult){
                if(!arResult.processedRows){
                    arResult.processedRows = 0;
                }
                if(!arResult.addedElements){
                    arResult.addedElements = 0;
                }
                if(!arResult.updatedElements){
                    arResult.updatedElements = 0;
                }
                if(!arResult.log){
                    arResult.log = '';
                }
                if(!arResult.ERRORS){
                    arResult.ERRORS = '';
                }
                obImportProcessedRows.html(arResult.processedRows);
                obImportAddedElements.html(Number(obImportAddedElements.html())+arResult.addedElements);
                obImportUpdatedElements.html(Number(obImportUpdatedElements.html())+arResult.updatedElements);
                obImportLog.append('<div>'+arResult.log+'</div>');
                if(!arResult.RESULT){
                    obImportErrors.html(arResult.ERRORS);
                    BX.closeWait();
                    return;
                }
                if(!arResult.isComplete && arResult.nextStartRow){
                    mcartXlsImport(arResult.nextStartRow);
                }else{
                    BX.closeWait();
                    obBtns.show();
                }
            });
        };
        var wait = BX.showWait();
        obBtns.hide();
        mcartXlsImport('<?=$arProfile['START_ROW']?>');  <?
    }?>
});
</script><?
//--------------------------//
include_once 'inc/profile_edit.css.php';
include_once 'inc/profile_edit.js.php';
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

