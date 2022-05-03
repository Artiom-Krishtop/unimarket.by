<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/* @var $obProfileEdit \Mcart\Xls\Admin\ProfileEdit */
if(!$obProfileEdit){
    return;
} ?>
<script type="text/javascript">
function McarXls(pref) {
    this.pref = pref;
}
obMcarXls = new McarXls('<?=$obProfileEdit->getRequestPref()?>');
BX.ready(function(){ <?
    switch ($obProfileEdit->getStep()) {
        case 1: ?>
            tabControl.SelectTab('step1');
            tabControl.DisableTab('step2');
            tabControl.DisableTab('step3'); <?
            break;
        case 2: ?>
            tabControl.DisableTab('step1');
            tabControl.SelectTab('step2');
            tabControl.DisableTab('step3'); <?
            break;
        case 3: ?>
            tabControl.DisableTab('step1');
            tabControl.DisableTab('step2');
            tabControl.SelectTab('step3'); <?
            break;
        default:?>
            tabControl.DisableTab('step1');
            tabControl.DisableTab('step2');
            tabControl.DisableTab('step3'); <?
            break;
    } ?>
});
$(function() {
    $('form#mcart_xls_profile_edit_form').on('click', '#tab_cont_step1', function(){
        window.location.href='mcart_xls_profile_edit_step_1.php?ID=<?=$obProfileEdit->getProfileID()?>&lang=<?=LANGUAGE_ID?>';
    });
});
</script>