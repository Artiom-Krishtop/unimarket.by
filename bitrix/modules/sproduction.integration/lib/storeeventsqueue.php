<?php
/**
 * Syncronization statuses with CRM Bitrix24
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

class StoreEventsQueue
{
	const MODULE_ID = 'sproduction.integration';
	const DB_FIELD = 'store_events_queue';

	public function add($new_value) {
		$connection = \Bitrix\Main\Application::getConnection();
		$sql = "SELECT VALUE FROM b_option WHERE MODULE_ID='".self::MODULE_ID."' && NAME='".self::DB_FIELD."'";
		$res = $connection->query($sql);
		if ($record = $res->fetch()) {
			$value = $record['VALUE'];
			$list = unserialize($value);
			$list[] = $new_value;
			$list = array_unique($list);
			$list_db = serialize($list);
			$sql = "UPDATE `b_option` SET `VALUE` = '$list_db' WHERE `b_option`.`MODULE_ID` = '".self::MODULE_ID."' && `b_option`.`NAME`='".self::DB_FIELD."';";
			$connection->query($sql);
		}
		else {
			$list_db = serialize([$new_value]);
			$sql = "INSERT INTO `b_option` (`MODULE_ID`, `NAME`, `VALUE`)
            VALUES ('".self::MODULE_ID."', '".self::DB_FIELD."', '$list_db');";
			$connection->query($sql);
		}
	}

	public function getAndClear($limit=0) {
		$connection = \Bitrix\Main\Application::getConnection();
		$list = [];
		$sql = "SELECT VALUE FROM b_option WHERE MODULE_ID='".self::MODULE_ID."' && NAME='".self::DB_FIELD."'";
		$res = $connection->query($sql);
		if ($record = $res->fetch()) {
			$value = $record['VALUE'];
			$list = unserialize($value);
			if ($limit) {
				$list_db = serialize(array_slice($list, $limit));
				$list = array_slice($list, 0, $limit);
			}
			else {
				$list_db = '';
			}
			$sql = "UPDATE `b_option` SET `VALUE` = '$list_db' WHERE `b_option`.`MODULE_ID` = '".self::MODULE_ID."' && `b_option`.`NAME`='".self::DB_FIELD."';";
			$connection->query($sql);
		}
		return $list;
	}
}
