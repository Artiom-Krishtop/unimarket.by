<?php
/**
 * Syncronization statuses with CRM Bitrix24
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

class ExtEventsQueue
{
	const MODULE_ID = 'sproduction.integration';
	const DB_FIELD = 'ext_events_queue';
	var $MYSQL_HOST;
	var $MYSQL_DATABASE;
	var $MYSQL_LOGIN = '';
	var $MYSQL_PASSWORD = '';

	function __construct($host, $name, $login, $password) {
		$this->MYSQL_HOST = $host;
		$this->MYSQL_DATABASE = $name;
		$this->MYSQL_LOGIN = $login;
		$this->MYSQL_PASSWORD = $password;
	}

	private function db_query($query) {
		$arRes = false;

		$db_link = mysqli_connect($this->MYSQL_HOST, $this->MYSQL_LOGIN, $this->MYSQL_PASSWORD)
		or die('Can not connect: ' . mysqli_error($db_link));
		mysqli_select_db($db_link, $this->MYSQL_DATABASE)
		or die('Can not select database');

		$db_res = mysqli_query($db_link, $query);
		if ($db_res) {
			$i = 0;
			while ($arRow = mysqli_fetch_array($db_res, MYSQLI_ASSOC)) {
				foreach ($arRow as $k => $val) {
					$arRes[$i][$k] = $val;
				}
				$i++;
			}
			if (!$arRes) {
				$arRes = true;
			}
		}

		if (mysqli_insert_id($db_link)) {
			$arRes = mysqli_insert_id($db_link);
		}

		mysqli_free_result($db_res);
		mysqli_close($db_link);

		return $arRes;
	}

	public function add($new_value) {
		$res = $this->db_query("SELECT VALUE FROM b_option WHERE MODULE_ID='".self::MODULE_ID."' && NAME='".self::DB_FIELD."'");
		if (is_array($res) && count($res)) {
			$value = $res[0]['VALUE'];
			$list = unserialize($value);
			$list[] = $new_value;
			$list = array_unique($list);
			$list_db = serialize($list);
			$this->db_query("UPDATE `b_option` SET `VALUE` = '$list_db' WHERE `b_option`.`MODULE_ID` = '".self::MODULE_ID."' && `b_option`.`NAME`='".self::DB_FIELD."';");
		}
		else {
			$list_db = serialize([$new_value]);
			$this->db_query("INSERT INTO `b_option` (`MODULE_ID`, `NAME`, `VALUE`)
            VALUES ('".self::MODULE_ID."', '".self::DB_FIELD."', '$list_db');");
		}
	}

	public function getAndClear($limit=0) {
		$list = [];
		$res = $this->db_query("SELECT VALUE FROM b_option WHERE MODULE_ID='".self::MODULE_ID."' && NAME='".self::DB_FIELD."'");
		if (is_array($res)) {
			$value = $res[0]['VALUE'];
			$list = unserialize($value);
			if ($limit) {
				$list_db = serialize(array_slice($list, $limit));
				$list = array_slice($list, 0, $limit);
			}
			else {
				$list_db = '';
			}
			$this->db_query("UPDATE `b_option` SET `VALUE` = '$list_db' WHERE `b_option`.`MODULE_ID` = '".self::MODULE_ID."' && `b_option`.`NAME`='".self::DB_FIELD."';");
		}
		return $list;
	}

	public function saveLog($string) {
		$log = false;
		$res = $this->db_query("SELECT VALUE FROM b_option WHERE MODULE_ID='".self::MODULE_ID."' && NAME='filelog'");
		if (is_array($res)) {
			$log = $res[0]['VALUE'];
		}
		if ($log == 'Y') {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/sprod_integr_log.txt', $string, FILE_APPEND);
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/sprod_integr_log.txt', "\n---\n" . date('d.m.Y H:i:s') . "\n\n", FILE_APPEND);
		}
	}
}