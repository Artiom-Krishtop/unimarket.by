<?
error_reporting( E_ERROR );

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application,
	SProduction\Integration\Rest,
	SProduction\Integration\Integration,
	SProduction\Integration\Settings;

$incl_res = Bitrix\Main\Loader::includeSharewareModule('sproduction.integration');
switch ($incl_res) {
	case Bitrix\Main\Loader::MODULE_NOT_FOUND:
		echo 'Module sproduction.integration not found.';
		die();
		break;
	case Bitrix\Main\Loader::MODULE_DEMO_EXPIRED:
		echo 'Module sproduction.integration demo expired.';
		die();
		break;
	default: // MODULE_INSTALLED
}

if (!$USER->IsAdmin()) {
	die();
}

Rest::restToken($_REQUEST['code']);

// Add placements and event handlers
$sync_active = Settings::get('active');
if (Integration::checkConnection() && $sync_active) {
	Integration::regCrmHandlers();
	SProdIntegration::setPortalPlacements();
}

if (!$arRes['error']) {
    LocalRedirect('/bitrix/admin/sprod_integr_settings.php?lang=' . LANGUAGE_ID);
}
else {
    echo 'Authorization error';
}
