<?
error_reporting( E_ERROR );

use SProduction\Integration\BgrRunLock;
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sproduction.integration/lib/bgrrunlock.php';
for ($i=0; $i<10 && BgrRunLock::isBgrRunLock(); $i++) {
	sleep(1);
}
BgrRunLock::setBgrRunLock();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use SProduction\Integration\Integration,
	SProduction\Integration\Settings,
	SProduction\Integration\Rest,
	SProduction\Integration\ProfilesTable,
	SProduction\Integration\ProfileInfo,
	SProduction\Integration\StoreEventsQueue,
	SProduction\Integration\OrderAddLock,
	SProduction\Integration\OfflineEvents,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Sale;

Loader::includeModule('sale');
$incl_res = Loader::includeSharewareModule('sproduction.integration');
switch ($incl_res) {
	case Loader::MODULE_NOT_FOUND:
		echo 'Module sproduction.integration not found.';
		BgrRunLock::clearBgrRunLock();
		die();
		break;
	case Loader::MODULE_DEMO_EXPIRED:
		echo 'Module sproduction.integration demo expired.';
		BgrRunLock::clearBgrRunLock();
		die();
		break;
	default: // MODULE_INSTALLED
}

$secret     = $_REQUEST['secret_key'];
$order_data = unserialize($_REQUEST['order_data']);
$order_id = $order_data['ID'];
$is_new     = $_REQUEST['new'];
$order_ids = [];
$queue = new StoreEventsQueue();

\SProdIntegration::Log('(bgr_run) run for order ' . $order_id);

$pause = Settings::get('new_order_pause') ? Settings::get('new_order_pause') : 5;

if ($secret != Rest::getBgrRequestSecret()) {
	\SProdIntegration::Log('(bgr_run) access error');
	BgrRunLock::clearBgrRunLock();
	return;
}
if (!$order_id) {
	\SProdIntegration::Log('(bgr_run) empty order id');
	BgrRunLock::clearBgrRunLock();
	return;
}

if ($is_new) {
	$queue->add($order_id);
	\SProdIntegration::Log('(bgr_run) pause ' . $pause);
	sleep($pause);
	$order_ids = $queue->getAndClear(50);
	\SProdIntegration::Log('(bgr_run) orders for adding ' . print_r($order_ids, 1));
	if (empty($order_ids)) {
		BgrRunLock::clearBgrRunLock();
		return;
	}
	foreach ($order_ids as $order_id) {
		\SProdIntegration::Log('(bgr_run) order ' . $order_id . ' send');
		$order      = Sale\Order::load($order_id);
		$order_data = Integration::getOrderInfo($order);
		\SProdIntegration::Log('(bgr_run) new order data ' . print_r($order_data, 1));
		OrderAddLock::add($order_id);
		Integration::syncOrderToDeal($order_data);
	}
}
elseif ($order_data['ID']) {
	// Check updates on deals
	OfflineEvents::processEvents();
	// Update deal by order
	$order      = Sale\Order::load($order_id);
	$order_data = Integration::getOrderInfo($order);
	Integration::syncOrderToDeal($order_data);
}

sleep(5);
BgrRunLock::clearBgrRunLock();
