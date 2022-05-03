<?
/*
 * Prepare check
 */

error_reporting( E_ERROR );

use SProduction\Integration\ExtEventsQueue;

if (!in_array($_REQUEST['event'], array('ONCRMDEALUPDATE'))) {
	return;
}

include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sproduction.integration/lib/exteventsqueue.php';
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/dbconn.php';

$deals_ids = [];

$queue = new ExtEventsQueue($DBHost, $DBName, $DBLogin, $DBPassword);

$deal_id = (int)$_REQUEST['data']['FIELDS']['ID'];
$queue->saveLog('(crm handler prepare) input deal: ' . $deal_id);

// Split the processing of events that came simultaneously
usleep(rand(100000, 1000000));

if ($deal_id) {
	$queue->add($deal_id);
	sleep(4);
	$deals_ids = $queue->getAndClear(50);
}

if (empty($deals_ids)) {
	return;
}


/*
 * Main part
 */

$time = microtime();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

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
	case Bitrix\Main\Loader::MODULE_DEMO:
		echo 'Module sproduction.integration demo mode.';
		break;
	default: // MODULE_INSTALLED
}

use Bitrix\Main\Config\Option,
    Bitrix\Sale,
    SProduction\Integration\Rest,
    SProduction\Integration\Integration,
	SProduction\Integration\OfflineEvents;

if (!Integration::checkConnection()) {
    return;
}

// Check source of event
$auth_info = Rest::getAuthInfo();
if ($_REQUEST['auth']['member_id'] != $auth_info['member_id']) {
    return;
}

// Check all last events and process
OfflineEvents::processEvents($deals_ids);
