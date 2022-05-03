<?
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\Admin\ProfileEdit;
use Mcart\Xls\Helpers\Html;
use Mcart\Xls\McartXls;
use Mcart\Xls\Spreadsheet\Reader as Spreadsheet_Reader;
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
    $obProfileEdit = new ProfileEdit(2);
    $pref = $obProfileEdit->getRequestPref();
    $arProfile = $obProfileEdit->getProfileForForm();
    $arColumns = $obProfileEdit->getColumns();
    $obHtml = new Html();

    $obSpreadsheetReader = new Spreadsheet_Reader($obProfileEdit->getFile());
    $arSheet = $obSpreadsheetReader->read($arProfile['START_ROW'], 5, $arProfile['HEADER_ROW']);
    if($arSheet===false){
        $obMcartXls->showErrors();
        return;
    }
} catch (Exception $e) {
    CAdminMessage::ShowMessage($obMcartXls->getErrorMessage($e, 'Error'));
    return;
}
//--------------------------//
?>
<form method="POST" action="<?=$obProfileEdit->getFormAction(3)?>" enctype="multipart/form-data"
      name="mcart_xls_profile_edit_form" id="mcart_xls_profile_edit_form" data-to_step="3">
    <?=bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>"> <?
    foreach ($arProfile as $k => $v) {
        if($k == 'COLUMN'){
            continue;
        }
        Html::showInputsHidden($pref, $k, $v);
    }
$obProfileEdit->getTabControl()->Begin(); 
$obProfileEdit->getTabControl()->BeginNextTab();
$obProfileEdit->getTabControl()->BeginNextTab();?>
    <tr>
        <td colspan="2">
            <table class="table table-hover"><?
            $optionsForExcelColumns = '';
            $optionsForColumns = '';
            $optionsForHandlers = '';
            $isFirst = true;
            foreach ($arSheet as $arRow) {
                echo '<tr>';
                foreach ($arRow as $arCell) {
                    if($isFirst){
                        echo '<th>'.$arCell['value_format'].'</th>';
                        $optionsForExcelColumns .= '<option value="'.$arCell['column'].'">'.$arCell['value_format'].'</option>';
                    }else{
                        echo '<td>'.$arCell['value_format'].'</td>';
                    }
                }
                echo '</tr>';
                if($isFirst){
                    $isFirst = false;
                }
            }
            foreach ($arColumns as $ar1) {
                $optionsForColumns .= '<optgroup label="'.$ar1['NAME'].'">';
                foreach ($ar1['ITEMS'] as $ar) {
                    $optionsForColumns .= '<option value="'.$ar['KEY'].'">'.$ar['NAME'].'</option>';
                }
                $optionsForColumns .= '</optgroup>';
            }
            $optionsForHandlers .= '<option value=""> </option>';
            foreach ($obProfileEdit->getProfileColumnFieldValues('HANDLER') as $k => $v) {
                $optionsForHandlers .= '<option value="'.$k.'">'.$v.'</option>';
            } ?>
            </table>
        </td>
    </tr>
    <tr class="heading">
      <td colspan="2"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD")?></td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="properties table table-hover">
                <thead>
                    <tr>
                      <th width="45%" align="right"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD1")?></th>
                      <th width="45%" align="center"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD2")?></th>
                      <th width="" align="center"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD3")?></th>
                      <th width="" align="center"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD4")
                            .ShowJSHint(Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD4_TOOLTIP"),['return'=>true])?></th>
                      <th width="" align="left"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD5")
                            .ShowJSHint(Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD5_TOOLTIP"),['return'=>true])?></th>
                      <th width="" align="left"><?=Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD6")?></th>
                      <th width="" align="left"><button class="column_add" type="button">+</button></th>
                    </tr>
                </thead>
                <tbody><?
                $js = '';
                $jsRowKey = 0;
                $winCustomFieldsLink = '<a class="set_custom_fields">'.Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_CUSTOM_FIELDS_BTN").'</a>';
                $winCustomFieldsTHead = '<thead>'
                    .'<tr class="heading"><th colspan="3">'.Loc::getMessage("MCART_XLS_PROFILE_STEP2_COLUMN_HEAD4").'</th></tr>'
                    .'<tr>'
                        .'<th width="200px">'.$obProfileEdit->getProfileColumnCustomFieldsFieldTitle('NAME').'</th>'
                        .'<th>'.$obProfileEdit->getProfileColumnCustomFieldsFieldTitle('VALUE').'</th>'
                        .'<th width="20px"><button class="field_add" type="button">+</button></th>'
                    .'</tr></thead>';
                if (!empty($arProfile['COLUMN'])) {
                    foreach ($arProfile['COLUMN'] as $k => $arProfileColumn) {
                        $keyForFields = 0;
                        $countFields = count($arProfileColumn['CUSTOM_FIELDS']);
                        if($countFields > 0){
                            $keyForFields = ($countFields-1);
                        } ?>
                        <tr class="column_row k<?=$k?>" data-key="<?=$k?>">
                            <td>
                                <input type="hidden" name="<?=$pref?>COLUMN[<?=$k?>][ID]" value="<?=$arProfileColumn['ID']?>" />
                                <select class="COLUMN_COLUMN" name="<?=$pref?>COLUMN[<?=$k?>][COLUMN]"><?=$optionsForExcelColumns?></select>
                            </td>
                            <td><select class="COLUMN_SAVE_IN" name="<?=$pref?>COLUMN[<?=$k?>][SAVE_IN]"><?=$optionsForColumns?></select></td>
                            <td><select class="COLUMN_HANDLER" name="<?=$pref?>COLUMN[<?=$k?>][HANDLER]"><?=$optionsForHandlers?></select></td>
                            <td class="custom_fields">
                                <?=$winCustomFieldsLink?>
                                <div class="win_custom_fields hidden">
                                    <table class="table table-hover">
                                        <?=$winCustomFieldsTHead?>
                                        <tbody><?
                                            if ($countFields == 0) {
                                                $prefName = $pref.'COLUMN['.$k.'][CUSTOM_FIELDS]['.$keyForFields.']';  ?>
                                                <tr class="field_row" data-key="<?=$keyForFields?>">
                                                    <td><input type="text" name="<?=$prefName?>[NAME]" value="" /></td>
                                                    <td><textarea name="<?=$prefName?>[VALUE]" rows="1" maxlength="255"></textarea></td>
                                                    <td><button class="field_del" type="button">x</button></td>
                                                </tr><?
                                            }else{
                                                foreach ($arProfileColumn['CUSTOM_FIELDS'] as $keyForFields => $arCustomField) {
                                                    $prefName = $pref.'COLUMN['.$k.'][CUSTOM_FIELDS]['.$keyForFields.']'; ?>
                                                    <tr class="field_row" data-key="<?=$keyForFields?>">
                                                        <td>
                                                            <input type="hidden" name="<?=$prefName?>[ID]" value="<?=$arCustomField['ID']?>" />
                                                            <input type="text" name="<?=$prefName?>[NAME]" value="<?=$arCustomField['NAME']?>" />
                                                        </td>
                                                        <td>
                                                            <textarea name="<?=$prefName?>[VALUE]" rows="1"
                                                                maxlength="255"><?=$arCustomField['VALUE']?></textarea>
                                                        </td>
                                                        <td><button class="field_del" type="button">x</button></td>
                                                    </tr><?
                                                }
                                            } ?>
                                        </tbody>
                                    </table>
                                    <button class="win_custom_fields_close adm-btn" type="button">OK</button>
                                </div>
                            </td>
                            <td>
                                <input class="COLUMN_DO_NOT_IMPORT_ROW_IF_EMPTY" type="checkbox"
                                    name="<?=$pref?>COLUMN[<?=$k?>][DO_NOT_IMPORT_ROW_IF_EMPTY]" value="Y"
                                    <?=($arProfileColumn['DO_NOT_IMPORT_ROW_IF_EMPTY']=='Y'?' checked="checked"':'')?> />
                            </td>
                            <td>
                                <input class="COLUMN_IS_IDENTIFY_ELEMENT" type="radio"
                                    name="<?=$pref?>COLUMN[<?=$k?>][IS_IDENTIFY_ELEMENT]" value="Y"
                                    <?=($arProfileColumn['IS_IDENTIFY_ELEMENT'][$k]=='Y'?' checked="checked"':'')?> />
                            </td>
                            <td><button class="column_del" type="button">x</button></td>
                        <tr><?
                        $js .= 'obPropertiesTr = obProperties.find("tr.k'.$k.'");'."\n"
                            . 'obPropertiesTr.find(\'select.COLUMN_COLUMN option[value="'.$arProfileColumn['COLUMN'].'"]\')'
                            . '.prop("selected", true);'."\n"
                            .'obPropertiesTr.find(\'select.COLUMN_SAVE_IN option[value="'.$arProfileColumn['SAVE_IN'].'"]\')'
                            . '.prop("selected", true);'."\n"
                            .'obPropertiesTr.find(\'select.COLUMN_HANDLER option[value="'.$arProfileColumn['HANDLER'].'"]\')'
                            . '.prop("selected", true);'."\n";
                    }
                    $jsRowKey = intval($k);
                }?>
                </tbody>
            </table>
        </td>
    </tr><?
$obProfileEdit->getTabControl()->BeginNextTab();
$obProfileEdit->getTabControl()->Buttons();?>
<input type="button" name="prev" value="<?=Loc::getMessage("MCART_XLS_PROFILE_PREV")?>" />
<input class="adm-btn-save" type="button" name="save_and_import" value="<?=Loc::getMessage("MCART_XLS_PROFILE_SAVE_AND_IMPORT")?>" />
<a class="adm-btn" href="/bitrix/admin/mcart_xls_index.php?lang=<?=LANGUAGE_ID?>"><?=Loc::getMessage("MCART_XLS_PROFILE_CANCEL")?></a><?
$obProfileEdit->getTabControl()->End();?>
<?
//----------//
?>
<script type="text/javascript">
$(document).ready(function() {
    var obProperties = $("form#mcart_xls_profile_edit_form table.properties"),
        obPropertiesTr,
        rowKey = <?=$jsRowKey?>;
    <?=$js?>

    McarXls.prototype.addCustomFieldsHtml = function (k){
        var tr = $('form#mcart_xls_profile_edit_form .properties > tbody').find('tr.column_row[data-key="'+k+'"]'),
            td = tr.find('.custom_fields'),
            k2 = 0;
        td.append('<div class="win_custom_fields hidden"><table class="table table-hover">'
        +'<?=$winCustomFieldsTHead?><tbody>'
            +'<tr class="field_row" data-key="'+k2+'">'
                +'<td><input name="'+this.pref+'COLUMN['+k+'][CUSTOM_FIELDS]['+k2+'][NAME]" value="" /></td>'
                +'<td><textarea name="'+this.pref+'COLUMN['+k+'][CUSTOM_FIELDS]['+k2+'][VALUE]" rows="1" maxlength="255"></textarea></td>'
                +'<td><button class="field_del" type="button">x</button></td>'
            +'</tr>'
        +'</tbody></table><button class="win_custom_fields_close adm-btn" type="button">OK</button></div>');
    }
    McarXls.prototype.addCustomFieldRowHtml = function (ob){
        var win = ob.closest('.win_custom_fields'),
            tr = win.closest('tr.column_row'),
            k = tr.attr('data-key'),
            k2 = (parseInt(win.find('tbody tr.field_row:last-child').attr('data-key'))+1);
        win.find('tbody').append(
            '<tr class="field_row" data-key="'+k2+'">'
                +'<td><input name="'+this.pref+'COLUMN['+k+'][CUSTOM_FIELDS]['+k2+'][NAME]" value="" /></td>'
                +'<td><textarea name="'+this.pref+'COLUMN['+k+'][CUSTOM_FIELDS]['+k2+'][VALUE]" rows="1" maxlength="255"></textarea></td>'
                +'<td><button class="field_del" type="button">x</button></td>'
            +'</tr>'
        );
    }

    $('form#mcart_xls_profile_edit_form .properties').on('click', 'button.column_add', function(){
        var IS_IDENTIFY_ELEMENT_checked = '',
            tbody = $('form#mcart_xls_profile_edit_form .properties > tbody');
        if(tbody.find('> tr.column_row').length <= 0){
            IS_IDENTIFY_ELEMENT_checked = ' checked="checked"';
        }
        rowKey++;
        tbody.append(
            '<tr class="column_row" data-key="'+rowKey+'">'
                +'<td><select class="COLUMN_COLUMN" name="<?=$pref?>COLUMN['+rowKey+'][COLUMN]"><?=$optionsForExcelColumns?></select></td>'
                +'<td><select class="COLUMN_SAVE_IN" name="<?=$pref?>COLUMN['+rowKey+'][SAVE_IN]"><?=$optionsForColumns?></select></td>'
                +'<td><select class="COLUMN_HANDLER" name="<?=$pref?>COLUMN['+rowKey+'][HANDLER]"><?=$optionsForHandlers?></select></td>'
                +'<td class="custom_fields"><?=$winCustomFieldsLink?></td>'
                +'<td><input class="COLUMN_DO_NOT_IMPORT_ROW_IF_EMPTY" type="checkbox" '
                    +'name="<?=$pref?>COLUMN['+rowKey+'][DO_NOT_IMPORT_ROW_IF_EMPTY]" value="Y" /></td>'
                +'<td><input class="COLUMN_IS_IDENTIFY_ELEMENT" type="radio" '
                    +'name="<?=$pref?>COLUMN['+rowKey+'][IS_IDENTIFY_ELEMENT]"'+IS_IDENTIFY_ELEMENT_checked+' value="Y" /></td>'
                +'<td><button class="column_del" type="button">x</button></td>'
            +'<tr>'
        );
        obMcarXls.addCustomFieldsHtml(rowKey);
    });
    $('form#mcart_xls_profile_edit_form .properties').on('click', 'button.column_del', function(){
        $(this).closest('tr').remove();
    });


    $('form#mcart_xls_profile_edit_form .properties').on('click', '.set_custom_fields', function(e){
        e.preventDefault();
        $('form#mcart_xls_profile_edit_form .properties .win_custom_fields').hide();
        $(this).closest('td').find('.win_custom_fields').show();
    });
    $('form#mcart_xls_profile_edit_form .properties').on('click', '.win_custom_fields_close', function(e){
        e.preventDefault();
        $(this).closest('.win_custom_fields').hide();
    });
    $('form#mcart_xls_profile_edit_form .properties').on('click', '.win_custom_fields button.field_add', function(){
        obMcarXls.addCustomFieldRowHtml($(this));
    });
    $('form#mcart_xls_profile_edit_form .properties').on('click', '.win_custom_fields button.field_del', function(){
        $(this).closest('tr.field_row').remove();
    });

    $('form#mcart_xls_profile_edit_form .properties').on('click', 'input.COLUMN_IS_IDENTIFY_ELEMENT', function(){
        $('form#mcart_xls_profile_edit_form input.COLUMN_IS_IDENTIFY_ELEMENT').prop("checked", false);
        $(this).prop("checked", true);
    });
    $('form#mcart_xls_profile_edit_form input[name="prev"]').click(function(){
        var obForm = $('form#mcart_xls_profile_edit_form');
        obForm.attr('action', '<?=$obProfileEdit->getFormAction(1)?>');
        obForm.attr('data-to_step', '1');
        obForm.submit();
    });
    $('form#mcart_xls_profile_edit_form input[name="save_and_import"]').click(function(){
        var obForm = $('form#mcart_xls_profile_edit_form');
        obForm.attr('action', '<?=$obProfileEdit->getFormAction(3)?>');
        obForm.attr('data-to_step', '3');
        obForm.submit();
    });
    $('form#mcart_xls_profile_edit_form').submit(function(){
        var obForm = $(this);
        if(obForm.attr('data-to_step') == '1'){
            return true;
        }
        if(obForm.find('.properties > tbody tr.column_row').length <= 0){
            alert('<?=Loc::getMessage("MCART_XLS_PROFILE_ERROR_PROPERTIES")?>');
            return false;
        }
        if(obForm.find('.properties input.COLUMN_IS_IDENTIFY_ELEMENT:checked').length <= 0){
            alert('<?=Loc::getMessage("MCART_XLS_PROFILE_ERROR_IDENTIFY_ELEMENT")?>');
            return false;
        }
        return true;
    });
});
</script><?
echo BeginNote();
echo Loc::getMessage("MCART_XLS_PROFILE_REQUIRED_FIELDS");
echo EndNote();
//--------------------------//
include_once 'inc/profile_edit.css.php';
include_once 'inc/profile_edit.js.php';
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
