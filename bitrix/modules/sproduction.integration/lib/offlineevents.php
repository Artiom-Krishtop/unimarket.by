<?php
/**
 * Offline events
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

use Bitrix\Main,
    Bitrix\Main\DB\Exception,
    Bitrix\Main\Config\Option;

class OfflineEvents
{
    const MODULE_ID = 'sproduction.integration';

	public static function eventsBind() {
		try {
			Rest::execute('event.bind', [
				'event'      => 'ONCRMDEALUPDATE',
				'event_type' => 'offline',
			]);
		} catch (\Exception $e) {}
	}

	public static function eventsUnbind() {
		try {
			Rest::execute('event.unbind', [
				'event'      => 'ONCRMDEALUPDATE',
				'event_type' => 'offline',
			]);
		} catch (\Exception $e) {}
	}

    public static function getChangedDeals($type='update') {
    	$list = [];
	    $res = Rest::execute('event.offline.get', []);
	    foreach ($res['events'] as $item) {
		    if ($type == 'update' && $item['EVENT_NAME'] == 'ONCRMDEALUPDATE') {
		    	$deal_id = (int)$item['EVENT_DATA']['FIELDS']['ID'];
		    	if ($deal_id) {
		    		$list[] = $deal_id;
			    }
		    }
	    }
	    return $list;
    }

    public static function processEvents($other_deals_ids=[]) {
	    $deals_ids = self::getChangedDeals();
	    $deals_ids = array_unique(array_merge($deals_ids, $other_deals_ids));
	    \SProdIntegration::Log('(processEvents) changed deals: ' . print_r($deals_ids, true));
		// Deal data
	    $deals = Integration::getDeal($deals_ids);
	    foreach ($deals as $deal) {
		    $order_id = (int) $deal[Integration::getOrderIDField()];
		    \SProdIntegration::Log('(processEvents) deal ' . print_r($deal, true));
		    if ( ! $order_id) {
			    continue;
		    }
		    // Change fields in the order
		    $opt_direction = \SProduction\Integration\Settings::get("direction");
		    if ( ! $opt_direction || $opt_direction == 'full' || $opt_direction == 'ctos') {
			    Integration::syncDealToOrder($deal);
		    }
	    }
	}
}
