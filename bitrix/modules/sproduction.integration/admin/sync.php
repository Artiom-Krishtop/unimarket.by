<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_lib.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("sproduction.integration");

use SProduction\Integration\Integration,
	SProduction\Integration\Settings,
	SProduction\Integration\OrderAddLock,
    Bitrix\Main\Config\Option,
    Bitrix\Sale;

Integration::setBulkRun();

$params = json_decode(file_get_contents('php://input'), true);

$next_item     = $params['next_item'] ? $params['next_item'] : 0;
SProdIntegration::Log('(sync) next_item '.$next_item);
$step_time = 5;
$start_time = time();

$filter = [];
$start_sync_ts = false;

$sync_period_opt = Settings::get('man_sync_period');
if ($sync_period_opt == '1d') {
    $sync_period = 3600 * 24;
}
elseif ($sync_period_opt == '1w') {
    $sync_period = 3600 * 24 * 7;
}
elseif ($sync_period_opt == '1m') {
    $sync_period = 3600 * 24 * 31;
}
elseif ($sync_period_opt == '3m') {
    $sync_period = 3600 * 24 * 31 * 3;
}
if ($sync_period) {
	$start_sync_ts = time() - $sync_period;
}

$start_date_ts = Integration::getStartDateTs();
if ($start_date_ts) {
	if ($start_date_ts > $start_sync_ts) {
		$start_sync_ts = $start_date_ts;
	}
}

if ($start_sync_ts) {
	$filter['>DATE_INSERT'] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $start_sync_ts);
}

$cnt = \CSaleOrder::GetList(["ID" => "ASC"], $filter, []);
SProdIntegration::Log('(sync) cnt '.$cnt);
$db = \CSaleOrder::GetList(["ID" => "ASC"], $filter, false, false, ['ID']);
if ($next_item < $cnt) {
    $i = 0;
    while ($order_item = $db->Fetch()) {
    	if ($i < $next_item) {
		    $i++;
    		continue;
	    }
    	$exec_time = time() - $start_time;
        if ($exec_time >= $step_time) {
	        SProdIntegration::Log('(sync) break on '.$i);
        	break;
        }
	    $order      = Sale\Order::load($order_item['ID']);
	    $order_data = Integration::getOrderInfo($order);
	    if (Settings::get('man_sync_only_new')) {
		    $profile = Integration::getOrderProfile($order_data);
		    $deal_id = Integration::findDeal($order_data, $profile);
		    if (!$deal_id) {
			    OrderAddLock::add($order_data['ID']);
			    try {
				    Integration::syncOrderToDeal($order_data);
			    }
			    catch (\Exception $e) {
				    \SProdIntegration::Log('(sync) can\'t sync of order ' . $order_data['ID']);
			    }
		    }
	    }
	    else {
		    OrderAddLock::add($order_data['ID']);
		    try {
			    Integration::syncOrderToDeal($order_data);
		    }
		    catch (\Exception $e) {
			    \SProdIntegration::Log('(sync) can\'t sync of order ' . $order_data['ID']);
		    }
	    }
        $i++;
    }
}
$next_item = $i;

echo json_encode([
    'status' => 'success',
    'count' => (int)$cnt,
    'next_item' => (int)$next_item,
]);
