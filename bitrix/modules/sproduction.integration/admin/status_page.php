<?
require_once( $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php" );
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_lib.php");

$MODULE_ID = "sproduction.integration";

CModule::IncludeModule($MODULE_ID);

use \SProduction\Integration\Integration,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Page\Asset,
	\Bitrix\Sale;

Loc::LoadMessages(__FILE__);
$loc_messages = Loc::loadLanguageFile(__FILE__);

$scripts = ['/bitrix/js/'.$MODULE_ID.'/page_status.js'];
$styles = [];
require_once( $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $MODULE_ID . "/admin/include/header.php" );
?>
    <script>
        messages.ru.page = {
		<?foreach ($loc_messages as $k => $message):?>
		<?=$k;?>: '<?=str_replace(array("\n", "\r"), '', $message);?>',
		<?endforeach;?>
        };
    </script>
    <div id="app">
        <div class="sprod-integr-page" id="sprod_integr_status_page">
            <div class="wrapper iframe-wrapper">
                <div class="container-fluid pl-3 pr-3">
                    <div class="page-title-box">
                        <h4 class="page-title"><?=Loc::getMessage('SP_CI_PAGE_STATUS_TITLE');?></h4>
                    </div>

                    <main-errors :errors="errors" :warnings="warnings"></main-errors>

                    <div class="row">
                        <div class="col-md-6">

                            <status-table @block_update="updateBlocks"></status-table>

                        </div><!-- end col -->
                        <div class="col-md-6">

                            <status-filelog @block_update="updateBlocks"></status-filelog>

                        </div><!-- end col -->
                    </div>
                    <!-- end row -->
                </div> <!-- end container -->
            </div>
        </div>
    </div>
<?
require_once( $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $MODULE_ID . "/admin/include/footer.php" );
