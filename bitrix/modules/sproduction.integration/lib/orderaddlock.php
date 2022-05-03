<?php
/**
 *    Orders adding lock
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

use Bitrix\Main,
    Bitrix\Main\DB\Exception,
    Bitrix\Main\Config\Option;

class OrderAddLock
{
	function add($order_id) {
		LockTable::add([
			'type' => 'new_order',
			'entity_id' => $order_id,
		]);
		\SProdIntegration::Log('(OrderAddLock::add) order '.$order_id.' add lock');
		return true;
	}

	function delete($order_id) {
		LockTable::delLock($order_id, 'new_order');
		\SProdIntegration::Log('(OrderAddLock::delete) order '.$order_id.' delete');
		return true;
	}

	function check($order_id, $delete=false) {
		$res = false;
		$orders = LockTable::getList([
			'filter' => [
				'type' => 'new_order',
				'entity_id' => $order_id
			]
		]);
		if (!empty($orders)) {
			$res = $orders[0]['time'];
		}
		if ($delete) {
			self::delete($order_id);
		}
		\SProdIntegration::Log('(OrderAddLock::check) order '.$order_id.' check '.$res);
		return $res;
	}
}
