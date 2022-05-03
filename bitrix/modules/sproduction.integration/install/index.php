<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

IncludeModuleLangFile(__FILE__);

Class sproduction_integration extends CModule
{
    var $MODULE_ID = "sproduction.integration";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct() {
        include(__DIR__ . DIRECTORY_SEPARATOR . 'version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage("SP_CI_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SP_CI_DESC");

        $this->PARTNER_NAME = GetMessage("SP_CI_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("SP_CI_PARTNER_URI");
    }

    function DoInstall()
    {
	    $this->ResetDemo();
        if (!$this->InstallFiles()) {
			return false;
        }
	    RegisterModule($this->MODULE_ID);
        $this->InstallDB();
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler("main", "OnBuildGlobalMenu", $this->MODULE_ID, "SProdIntegration", "OnBuildGlobalMenu");
        $eventManager->registerEventHandler("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "SProdIntegration", "appendScriptsToPage");
        return true;
    }
	
    function DoUninstall()
    {
        CModule::IncludeModule($this->MODULE_ID);
        if (!$this->UnInstallFiles()) {
			return false;
        }
	    $eventManager = \Bitrix\Main\EventManager::getInstance();
	    $eventManager->unRegisterEventHandler("main", "OnBuildGlobalMenu", $this->MODULE_ID, "SProdIntegration", "OnBuildGlobalMenu");
	    $eventManager->unRegisterEventHandler("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "SProdIntegration", "appendScriptsToPage");
	    \SProduction\Integration\AddSync::remove();
	    \SProduction\Integration\Integration::unregStoreHandlers();
	    \SProduction\Integration\Integration::unregCrmHandlers();
	    \SProdIntegration::removePortalPlacements();
	    $this->UnInstallDB(array(
		    "savedata" => $_REQUEST["savedata"],
	    ));
	    Option::delete($this->MODULE_ID);
	    UnRegisterModule($this->MODULE_ID);
	    $this->ResetDemo();
        
        return true;
    }

    public function InstallFiles() {
	    CopyDirFiles(__DIR__."/assets/scripts", Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID."/", true, true);
	    CopyDirFiles(__DIR__."/assets/styles", Application::getDocumentRoot()."/bitrix/themes/.default/".$this->MODULE_ID."/", true, true);
	    CopyDirFiles(__DIR__."/assets/images", Application::getDocumentRoot()."/bitrix/themes/.default/".$this->MODULE_ID."/images/", true, true);
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_settings.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/settings.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_settings_page.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/settings_page.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_general.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/general.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_general_page.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/general_page.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_profile_edit_page.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/profile_edit_page.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_profiles.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/profiles.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_profiles_page.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/profiles_page.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_status.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/status.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_status_page.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/status_page.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_ajax.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/ajax.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_sync.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/sync.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_order_edit.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/order_edit.php");?'.'>');
	    if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_order_create.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/order_create.php");?'.'>');
	    if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/sprod_integr_products_edit.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/products_edit_page.php");?'.'>');
	    if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/sprod_integr_bgr_run.php'))
		    file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/scripts/bgr_run.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/sprod_integr_auth.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/scripts/auth.php");?'.'>');
        if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/sprod_integr_handler.php'))
            file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/scripts/handler.php");?'.'>');
        return true;
    }
    
    public function UnInstallFiles() {
	    DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID."/");
	    DeleteDirFilesEx("/bitrix/themes/.default/".$this->MODULE_ID."/");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_settings.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_settings_page.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_general.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_general_page.php");
	    DeleteDirFilesEx("/bitrix/admin/sprod_integr_profile_edit_page.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_profiles.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_profiles_page.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_status.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_status_page.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_ajax.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_sync.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_order_edit.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_order_create.php");
        DeleteDirFilesEx("/bitrix/admin/sprod_integr_products_edit.php");
        DeleteDirFilesEx("/bitrix/sprod_integr_bgr_run.php");
	    DeleteDirFilesEx(\SProduction\Integration\Integration::APP_HANDLER);
	    DeleteDirFilesEx(\SProduction\Integration\Integration::EVENTS_HANDLER);
        DeleteDirFilesEx(\SProduction\Integration\Integration::FILELOG);
		return true;
    }

    function InstallDB() {
	    global $DB, $DBType, $APPLICATION;
	    $this->errors = false;
	    $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/".$DBType."/install.sql");
	    if ($this->errors !== false) {
		    $APPLICATION->ThrowException(implode("", $this->errors));
		    return false;
	    }
        return true;
    }

    function UnInstallDB($arParams = array()) {
	    global $DB, $DBType, $APPLICATION;
	    $this->errors = false;
	    if (!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y") {
		    $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/".$DBType."/uninstall.sql");
		    if($this->errors !== false) {
			    $APPLICATION->ThrowException(implode("", $this->errors));
			    return false;
		    }
	    }
        return true;
    }

	function ResetDemo(){
		global $DB;
		$DB->Query("DELETE FROM b_option WHERE `MODULE_ID`='".$this->MODULE_ID."' AND `NAME`='~bsm_stop_date';");
	}
}
