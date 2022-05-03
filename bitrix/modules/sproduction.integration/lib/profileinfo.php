<?php
/**
 *    ProfileInfo
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

\Bitrix\Main\Loader::includeModule('sale');

use Bitrix\Main,
    Bitrix\Main\DB\Exception,
    Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	SProduction\Integration\Rest;

Loc::loadMessages(__FILE__);

class ProfileInfo
{
	const PROPS_AVAILABLE = ['STRING', 'LOCATION', 'ENUM', 'Y/N', 'DATE', 'FILE', 'NUMBER'];
	const SYNC_NONE = 0;
	const SYNC_STOC = 1;
	const SYNC_CTOS = 2;
	const SYNC_ALL = 3;
	const DATE_FORMAT_PORTAL = 'Y-m-d\TH:i:sO';
	const DATE_FORMAT_PORTAL_SHORT = 'Y-m-d';

	// Deals directions
	public static function getCrmDirections($profile) {
		global $APPLICATION;
		$result = [
			0 => Loc::getMessage("SP_CI_MAIN_CATEGORY"),
		];
		$list = Rest::execute('crm.dealcategory.list', [
			'IS_LOCKED' => 'N',
		]);
		if (is_array($list)) {
			foreach ($list as $item) {
				$name = $item['NAME'];
				if (!\SProdIntegration::isUtf()) {
					$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
				}
				$result[$item['ID']] = $name;
			}
		}
		return $result;
	}

	// Portal users
	public static function getCrmUsers($profile) {
		$result = [];
		$params = [
			'sort' => 'LAST_NAME',
			'order' => 'asc',
			'FILTER' => [
				'ACTIVE' => 'Y',
				'USER_TYPE' => 'employee',
			],
		];
		$resp = Rest::getList('user.get', '', $params);
		if (!empty($resp)) {
			foreach ($resp as $item) {
				$result[$item['ID']] = $item['LAST_NAME'].' '.$item['NAME'].' ('.$item['EMAIL'].')';
			}
		}
		if (!\SProdIntegration::isUtf()) {
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($result, "UTF-8", "Windows-1251");
		}
		return $result;
	}

	// Deal stages
	public static function getCrmStages($profile) {
		global $APPLICATION;
		$result = [];
		$dealcateg_id = (int)$profile['options']['deal_category'];
		if (!$dealcateg_id) {
			$list = Rest::execute('crm.status.list', [
				'order' => ['SORT' => 'ASC'],
				'filter' => [
					'ENTITY_ID' => 'DEAL_STAGE',
				]
			]);
		}
		else {
			$list = Rest::execute('crm.dealcategory.stage.list', [
				'id' => $dealcateg_id,
			]);
		}
		if (is_array($list)) {
			foreach ($list as $item) {
				$result[] = [
					'id' => $item['STATUS_ID'],
					'name' => $item['NAME'],
				];
			}
		}
		if (strtolower(LANG_CHARSET) == 'windows-1251') {
			$result = $APPLICATION->ConvertCharset($result, "UTF-8", "CP1251");
		}
		return $result;
	}

	// Deal fields
	public static function getCrmFields($profile) {
		global $APPLICATION;
		$result = [];
		// Main
		$result['ID'] = Loc::getMessage("SP_CI_MAIN_CRM_FIELDS_ID");
		$result['LINK'] = Loc::getMessage("SP_CI_MAIN_CRM_FIELDS_LINK");
		// UTM
		$list = Rest::execute('crm.deal.fields');
		if (!empty($list)) {
			foreach ($list as $f_code => $item) {
				if (strpos($f_code, 'UTM_') === 0 || in_array($f_code, ['COMMENTS'])) {
					$result[$f_code] = $item['title'];
				}
			}
		}
		// User fields
		$list = Rest::execute('crm.deal.userfield.list');
		if (is_array($list) && !empty($list)) {
			$req_count = ceil(count($list) / 50);
			for ($r=0; $r<$req_count; $r++) {
				$next = $r * 50;
				$list_part = [];
				for ($j=$next; $j<$next+50 && $j<count($list); $j++) {
					$list_part[] = $list[$j];
				}
				// Get name from lang info
				$req_list = [];
				foreach ($list_part as $i => $field) {
					$req_list[$i] = 'crm.deal.userfield.get' . '?' . http_build_query([
							'id' => $field['ID'],
						]);
				}
				$resp = Rest::execute('batch', [
					"halt"  => false,
					"cmd" => $req_list,
				]);
				if ($resp['result']) {
					foreach ($list_part as $i => $field) {
						$field_details = $resp['result'][$i];
						if ( ! empty($field_details)) {
							$result[$field_details['FIELD_NAME']] = $field_details['EDIT_FORM_LABEL']['ru'];
						}
					}
				}
			}
		}
		if (strtolower(LANG_CHARSET) == 'windows-1251') {
			$result = $APPLICATION->ConvertCharset($result, "UTF-8", "CP1251");
		}
		return $result;
	}

	// Order statuses
	public static function getSiteStatuses($profile) {
		$result = [];
		$filter = [
			'LID' => LANGUAGE_ID,
			'TYPE' => 'O',
		];
		$select = ['ID', 'NAME'];
		$db = \CSaleStatus::GetList(['SORT' => 'ASC'], $filter, false, false, $select);
		while ($item = $db->Fetch()) {
			$result[] = [
				'id' => $item['ID'],
				'name' => $item['NAME'],
			];
		}
		return $result;
	}

	// List of person types
	public static function getSitePersonTypes($profile) {
		$result = [];
		$filter = [];
		$select = ['ID', 'NAME'];
		$db = \CSalePersonType::GetList(['SORT' => 'ASC'], $filter, false, false, $select);
		while ($item = $db->Fetch()) {
			$result[$item['ID']] = $item['NAME'];
		}
		return $result;
	}

	// Order properties
	public static function getSiteProps($profile) {
		$result = [];
		$db = \Bitrix\Sale\Property::getList([
			'order' => ['ID' => 'asc'],
			'select' => ['ID', 'NAME', 'PERSON_TYPE_ID', 'TYPE', 'MULTIPLE'],
		]);
		while ($prop = $db->Fetch()) {
			// Check props sync availibility
			if (!in_array($prop['TYPE'], self::PROPS_AVAILABLE)) {
				continue;
			}
			$prop['SYNC_DIR'] = self::SYNC_ALL;
			switch ($prop['TYPE']) {
				case 'FILE':
					$prop['SYNC_DIR'] = self::SYNC_STOC;
					if ($prop['MULTIPLE'] == 'Y') {
						continue 2;
					}
					break;
				case 'CHECKBOX':
				case 'RADIO':
					if ($prop['MULTIPLE'] == 'Y') {
						continue 2;
					}
					break;
				case 'LOCATION':
					$prop['SYNC_DIR'] = self::SYNC_STOC;
					break;
				default:
			}
			// Hints
			$prop['HINT'] = Loc::getMessage("SP_CI_PROP_".$prop['TYPE']."_HINT");
			// Add to the result
			$result[$prop['PERSON_TYPE_ID']][] = $prop;
		}
		return $result;
	}

	// Payment and delivery
	public static function getSitePayDeliv($profile) {
		$result = [];
		// Payment type
		$result[] = [
			'ID' => 'PAY_TYPE',
			'NAME' => Loc::getMessage("SP_CI_SPOSOB_OPLATY"),
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Payment status
		$result[] = [
			'ID' => 'PAY_STATUS',
			'NAME' => Loc::getMessage("SP_CI_STATUS_OPLATY"),
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Delivery type
		$result[] = [
			'ID' => 'DELIV_TYPE',
			'NAME' => Loc::getMessage("SP_CI_SPOSOB_DOSTAVKI"),
			'SYNC_DIR' => self::SYNC_STOC,
		];
		return $result;
	}

	// Other properties
	public static function getSiteOtherProps($profile) {
		$result = [];
		// Order ID
		$result[] = [
			'ID' => 'ORDER_ID',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order number
		$result[] = [
			'ID' => 'ORDER_NUMBER',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order status code
		$result[] = [
			'ID' => 'ORDER_STATUS',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order status name
		$result[] = [
			'ID' => 'ORDER_STATUS_NAME',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order number
		$result[] = [
			'ID' => 'USER_TYPE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Date created
		$result[] = [
			'ID' => 'DATE_CREATE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order link
		$result[] = [
			'ID' => 'ORDER_LINK',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Order public link
		$result[] = [
			'ID' => 'ORDER_LINK_PUBLIC',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// User comment
		$result[] = [
			'ID' => 'USER_DESCRIPTION',
			'SYNC_DIR' => self::SYNC_ALL,
		];
        // Manager comment
		$result[] = [
            'ID' => 'COMMENTS',
            'SYNC_DIR' => self::SYNC_ALL,
        ];
		// Delivery data
		$result[] = [
			'ID' => 'DELIV_TYPE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Company name for delivery type
		$result[] = [
			'ID' => 'DELIVERY_COMPANY_NAME',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_STORE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_PRICE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_STATUS',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_STATUS_NAME',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_ALLOW',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		$result[] = [
			'ID' => 'DELIVERY_DEDUCTED',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Delivery type
		$result[] = [
			'ID' => 'DELIV_TRACKNUM',
			'SYNC_DIR' => self::SYNC_ALL,
		];
		// Total amount
		$result[] = [
			'ID' => 'PAY_SUM',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Actually paid amount
		$result[] = [
			'ID' => 'PAY_FACT',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// The remaining amount
		$result[] = [
			'ID' => 'PAY_LEFT',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Payment type
		$result[] = [
			'ID' => 'PAY_TYPE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Payment ID
		$result[] = [
			'ID' => 'PAY_ID',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Payment status
		$result[] = [
			'ID' => 'PAY_STATUS',
			'SYNC_DIR' => self::SYNC_ALL,
		];
		// Payment date
		$result[] = [
			'ID' => 'PAY_DATE',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Payment number
		$result[] = [
			'ID' => 'PAY_NUM',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		// Coupon
		$result[] = [
			'ID' => 'COUPON',
			'SYNC_DIR' => self::SYNC_STOC,
		];
		foreach ($result as $k => $prop) {
			$result[$k]['NAME'] = Loc::getMessage('SP_CI_' . $prop['ID']);
			$result[$k]['HINT'] = Loc::getMessage('SP_CI_OTHER_PROP_' . $prop['ID'] . '_HINT');
		}
		return $result;
	}

	// Site users group
	public static function getSiteUGroups($profile) {
		$result = [];
		$filter = [];
		$db = \CGroup::GetList(($by="c_sort"), ($order="asc"), $filter);
		while ($item = $db->Fetch()) {
			$result[$item['ID']] = $item['NAME'];
		}
		return $result;
	}

	// Filter conditions
	public static function getFiltConditions($profile) {
		$result = [];
		// Site of order
		$result['site'] = [
			'title' => Loc::getMessage("SP_CI_SITE"),
			'items' => [],
			'values' => [],
		];
		$res = \Bitrix\Main\SiteTable::getList(array());
		while ($site = $res->fetch()) {
			$result['site']['values'][$site['LID']] = $site['NAME'];
		}
		// Person type
		$result['person_type'] = [
			'title' => Loc::getMessage("SP_CI_PERSON_TYPE"),
			'items' => [],
			'values' => [],
		];
		$result['person_type']['values'] = self::getSitePersonTypes($profile);
		// Payment type
		$result['pay_type'] = [
			'title' => Loc::getMessage("SP_CI_PAY_TYPE"),
			'items' => [],
			'values' => [],
		];
		$db = \CSalePaySystem::GetList(["SORT"=>"ASC", "PSA_NAME"=>"ASC"], ["ACTIVE"=>"Y"]);
		while ($item = $db->Fetch()) {
			$result['pay_type']['values'][$item['ID']] = $item['NAME'];
		}
		// Delivery type
		$result['deliv_type'] = [
			'title' => Loc::getMessage("SP_CI_DELIV_TYPE"),
			'items' => [],
			'values' => [],
		];
		$db = \CSaleDelivery::GetList(["SORT" => "ASC", "NAME" => "ASC"], ["ACTIVE"=>"Y"]);
		while ($item = $db->Fetch()) {
			$result['deliv_type']['values'][$item['ID']] = $item['NAME'];
		}
		// Status
		$result['status'] = [
			'title' => Loc::getMessage("SP_CI_FILTER_STATUS"),
			'items' => [],
			'values' => [],
		];
		$db = \CSaleStatus::GetList(['SORT' => 'ASC'], ['LID' => LANGUAGE_ID, 'TYPE' => 'O'], false, false, ['ID', 'NAME']);
		while ($item = $db->Fetch()) {
			$result['status']['values'][$item['ID']] = $item['NAME'];
		}
		// User group
		$result['user_group'] = [
			'title' => Loc::getMessage("SP_CI_FILTER_USER_GROUP"),
			'items' => [],
			'values' => [],
		];
		$db = \Bitrix\Main\GroupTable::getList([
			'select'  => ['NAME','ID'],
		]);
		while ($item = $db->fetch()) {
			$result['user_group']['values'][$item['ID']] = $item['NAME'];
		}
		// Order properties
		$result['prop'] = [
			'title' => Loc::getMessage("SP_CI_PROPERTIES"),
			'items' => [],
			'values' => [],
		];
		$filter = [
			'!MULTIPLE' => 'Y',
		];
		$select = ['ID', 'NAME', 'PERSON_TYPE_ID', 'TYPE', 'MULTIPLE'];
		$db = \CSaleOrderProps::GetList(["ID" => "ASC"], $filter, false, false, $select);
		while ($prop = $db->Fetch()) {
			if (!in_array($prop['TYPE'], ['TEXT', 'LOCATION', 'RADIO', 'SELECT'])) {
				continue;
			}
			$values = [];
			if (in_array($prop['TYPE'], ['RADIO', 'SELECT'])) {
				$db_values = \CSaleOrderPropsVariant::GetList(
					['SORT' => 'ASC'],
					[
						'ORDER_PROPS_ID' => $prop['ID'],
					]
				);
				while ($item = $db_values->Fetch()) {
					$values[$item['VALUE']] = $item['NAME'];
				}
			}
			$result['prop']['items'][$prop['ID']] = [
				'title' => $prop['NAME'],
				'values' => $values,
			];
		}
		return $result;
	}


	/**
	 * Fields for the contact
	 */

	public static function getSiteContactFields($profile) {
		$result = [];
		// Main user fields
		$result['user'] = [
			'title' => Loc::getMessage("SP_CI_SCF_USER_FIELDS"),
			'items' => [],
		];
		$result['user']['items'] = [
			'ID' => Loc::getMessage("SP_CI_SCF_USER_ID"),
			'LAST_NAME' => Loc::getMessage("SP_CI_SCF_LAST_NAME"),
			'NAME' => Loc::getMessage("SP_CI_SCF_NAME"),
			'SECOND_NAME' => Loc::getMessage("SP_CI_SCF_SECOND_NAME"),
			'EMAIL' => Loc::getMessage("SP_CI_SCF_EMAIL"),
		];
		// Order properties
		$result['props'] = [
			'title' => Loc::getMessage("SP_CI_SCF_PROPERTIES"),
			'items' => [],
		];
		$filter = [
			'!MULTIPLE' => 'Y',
		];
		$select = ['ID', 'NAME', 'PERSON_TYPE_ID', 'TYPE', 'MULTIPLE'];
		$db = \CSaleOrderProps::GetList(["ID" => "ASC"], $filter, false, false, $select);
		while ($prop = $db->Fetch()) {
			if (!in_array($prop['TYPE'], ['TEXT'])) {
				continue;
			}
			$result['props']['items'][$prop['PERSON_TYPE_ID']][$prop['ID']] = $prop['NAME'];
		}
		// Personal data of user
		$result['personal'] = [
			'title' => Loc::getMessage("SP_CI_SCF_USER_PERSONAL"),
			'items' => [],
		];
		$result['personal']['items'] = [
			'PERSONAL_PROFESSION' => Loc::getMessage("SP_CI_SCF_PERSONAL_PROFESSION"),
			'PERSONAL_WWW' => Loc::getMessage("SP_CI_SCF_PERSONAL_WWW"),
			'PERSONAL_ICQ' => Loc::getMessage("SP_CI_SCF_PERSONAL_ICQ"),
			'PERSONAL_GENDER' => Loc::getMessage("SP_CI_SCF_PERSONAL_GENDER"),
			'PERSONAL_BIRTHDAY' => Loc::getMessage("SP_CI_SCF_PERSONAL_BIRTHDAY"),
//			'PERSONAL_PHOTO' => Loc::getMessage("SP_CI_SCF_PERSONAL_PHOTO"),
			'PERSONAL_PHONE' => Loc::getMessage("SP_CI_SCF_PERSONAL_PHONE"),
			'PERSONAL_FAX' => Loc::getMessage("SP_CI_SCF_PERSONAL_FAX"),
			'PERSONAL_MOBILE' => Loc::getMessage("SP_CI_SCF_PERSONAL_MOBILE"),
			'PERSONAL_PAGER' => Loc::getMessage("SP_CI_SCF_PERSONAL_PAGER"),
			'PERSONAL_STREET' => Loc::getMessage("SP_CI_SCF_PERSONAL_STREET"),
			'PERSONAL_MAILBOX' => Loc::getMessage("SP_CI_SCF_PERSONAL_MAILBOX"),
			'PERSONAL_CITY' => Loc::getMessage("SP_CI_SCF_PERSONAL_CITY"),
			'PERSONAL_STATE' => Loc::getMessage("SP_CI_SCF_PERSONAL_STATE"),
			'PERSONAL_ZIP' => Loc::getMessage("SP_CI_SCF_PERSONAL_ZIP"),
//			'PERSONAL_COUNTRY' => Loc::getMessage("SP_CI_SCF_PERSONAL_COUNTRY"),
			'PERSONAL_NOTES' => Loc::getMessage("SP_CI_SCF_PERSONAL_NOTES"),
		];
		// User fields
		$result['uf'] = [
			'title' => Loc::getMessage("SP_CI_SCF_UF"),
			'items' => [],
		];
		$db = \Bitrix\Main\UserFieldTable::getList([
			'filter' => ['ENTITY_ID' => 'USER'],
			'select' => ['ID'],
		]);
		while ($item = $db->fetch()) {
			$item = \CUserTypeEntity::GetByID($item['ID']);
			$result['uf']['items'][$item['FIELD_NAME']] = $item['EDIT_FORM_LABEL'][LANGUAGE_ID];
		}
		return $result;
	}

	// Fields for the contact
	public static function getCrmContactFields($profile) {
		$result = [
			'LAST_NAME' => [
				'name' => Loc::getMessage("SP_CI_LAST_NAME"),
				'direction' => self::SYNC_STOC,
				'default' => '',//LAST_NAME
				'hint' => Loc::getMessage("SP_CI_CONTACT_LAST_NAME_HINT"),
			],
			'NAME' => [
				'name' => Loc::getMessage("SP_CI_NAME"),
				'direction' => self::SYNC_STOC,
				'default' => '',//NAME
				'hint' => Loc::getMessage("SP_CI_CONTACT_NAME_HINT"),
			],
			'SECOND_NAME' => [
				'name' => Loc::getMessage("SP_CI_SECOND_NAME"),
				'direction' => self::SYNC_STOC,
				'default' => '',//SECOND_NAME
				'hint' => Loc::getMessage("SP_CI_CONTACT_SECOND_NAME_HINT"),
			],
			'EMAIL' =>[
				'name' => Loc::getMessage("SP_CI_EMAIL"),
				'direction' => self::SYNC_STOC,
				'default' => '',//EMAIL
				'hint' => Loc::getMessage("SP_CI_CONTACT_EMAIL_HINT"),
			],
			'PHONE' => [
				'name' => Loc::getMessage("SP_CI_PHONE"),
				'direction' => self::SYNC_STOC,
				'default' => '',
				'hint' => Loc::getMessage("SP_CI_CONTACT_PHONE_HINT"),
			],
		];
		$list = Rest::execute('crm.contact.fields');
		if (!empty($list)) {
			foreach ($list as $f_code => $item) {
				if (strpos($f_code, 'UF_') === 0) {
					$name = $item['formLabel'];
					if (!\SProdIntegration::isUtf()) {
						$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
					}
					$result[$f_code] = [
						'name' => $name,
						'direction' => self::SYNC_STOC,
						'default' => '',
						'hint' => Loc::getMessage("SP_CI_CONTACT_".$f_code."_HINT"),
					];
				}
			}
		}
		return $result;
	}

	// Fields for the contact
	public static function getCrmContactSFields($profile) {
		$result = [
			'' => Loc::getMessage("SP_CI_CCSF_PHONEMAIL"),
		];
		$list = Rest::execute('crm.contact.fields');
		if (!empty($list)) {
			foreach ($list as $f_code => $item) {
				if (strpos($f_code, 'UF_') === 0) {
					$name = $item['formLabel'];
					if (!\SProdIntegration::isUtf()) {
						$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
					}
					$result[$f_code] = $name;
				}
			}
		}
		return $result;
	}

	// Fields for the company
	public static function getCrmCompanyFields($profile) {
		$presets = self::getCrmCompanyPresets($profile);
		$result = [
			'company' => [
				'title' => Loc::getMessage("SP_CI_INFO_COMPANY_COMPANY_TITLE"),
				'items' => [
					'NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_COMPANY_NAME"),
					'PHONE' => Loc::getMessage("SP_CI_INFO_COMPANY_COMPANY_PHONE"),
					'EMAIL' => Loc::getMessage("SP_CI_INFO_COMPANY_COMPANY_EMAIL"),
				],
			],
			'requisite' => [
				'title' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_TITLE"),
				'items' => [
					'PRESET_ID' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_PRESET_ID"),
					'RQ_FIRST_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_FIRST_NAME"),
					'RQ_LAST_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_LAST_NAME"),
					'RQ_SECOND_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_SECOND_NAME"),
					'RQ_COMPANY_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_COMPANY_NAME"),
					'RQ_COMPANY_FULL_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_COMPANY_FULL_NAME"),
					'RQ_DIRECTOR' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_DIRECTOR"),
					'RQ_INN' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_INN"),
					'RQ_KPP' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_KPP"),
					'RQ_OGRN' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_OGRN"),
					'RQ_OGRNIP' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_OGRNIP"),
					'RQ_OKPO' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_OKPO"),
					'RQ_OKTMO' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_OKTMO"),
					'RQ_OKVED' => Loc::getMessage("SP_CI_INFO_COMPANY_REQUISITE_RQ_OKVED"),
				],
				'values' => [
					'PRESET_ID' => $presets,
				],
				'value_def' => [
					'PRESET_ID' => 1,
				],
			],
			'bankdetail' => [
				'title' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_TITLE"),
				'items' => [
					'RQ_BANK_NAME' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_BANK_NAME"),
					'RQ_BANK_ADDR' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_BANK_ADDR"),
					'RQ_BIK' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_BIK"),
					'RQ_ACC_NUM' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_ACC_NUM"),
					'RQ_ACC_CURRENCY' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_ACC_CURRENCY"),
					'RQ_COR_ACC_NUM' => Loc::getMessage("SP_CI_INFO_COMPANY_BANKDETAIL_RQ_COR_ACC_NUM"),
				],
			],
			'address_jur' => [
				'title' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_JUR_TITLE"),
				'items' => [
					'ADDRESS_1' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_ADDRESS_1"),
					'ADDRESS_2' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_ADDRESS_2"),
					'CITY' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_CITY"),
					'POSTAL_CODE' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_POSTAL_CODE"),
					'REGION' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_REGION"),
					'PROVINCE' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_PROVINCE"),
					'COUNTRY' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_COUNTRY"),
				],
			],
			'address_fact' => [
				'title' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_FACT_TITLE"),
				'items' => [
					'ADDRESS_1' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_ADDRESS_1"),
					'ADDRESS_2' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_ADDRESS_2"),
					'CITY' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_CITY"),
					'POSTAL_CODE' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_POSTAL_CODE"),
					'REGION' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_REGION"),
					'PROVINCE' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_PROVINCE"),
					'COUNTRY' => Loc::getMessage("SP_CI_INFO_COMPANY_ADDRESS_COUNTRY"),
				],
			],
		];
		return $result;
	}

	// Presets of org types for the company
	public static function getCrmCompanyPresets($profile) {
		$list = Rest::execute('crm.requisite.preset.list');
		if (!empty($list)) {
			foreach ($list as $item) {
				$name = $item['NAME'];
				if (!\SProdIntegration::isUtf()) {
					$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
				}
				$result[$item['ID']] = $name;
			}
		}
		return $result;
	}


	// Iblocks catalogs list
	public static function getStoreIblockList($offers=false) {
		$list = [];
		$catalog_iblocks_ids = [];
		$filter = [];
		if (!$offers) {
			$filter['PRODUCT_IBLOCK_ID'] = 0;
		}
		$catalog_iblocks = \Bitrix\Catalog\CatalogIblockTable::getList([
			'filter' => $filter,
		])->fetchAll();
		foreach ($catalog_iblocks as $catalog_iblock) {
			$catalog_iblocks_ids[] = $catalog_iblock['IBLOCK_ID'];
		}
		$res = \Bitrix\Iblock\IblockTable::getList([
			'select' => ['ID', 'NAME'],
		]);
		while ($item = $res->fetch()) {
			if (in_array($item['ID'], $catalog_iblocks_ids)) {
				$list[] = [
					'id' => $item['ID'],
					'name' => $item['NAME'],
				];
			}
		}
		return $list;
	}

	// Sections of iblock
	public static function getStoreSectionsList($iblock_id) {
		$list = [];
		if ($iblock_id) {
			$res = \Bitrix\Iblock\SectionTable::getList([
				'select' => ['ID', 'NAME', 'DEPTH_LEVEL'],
				'filter' => [
					'IBLOCK_ID' => $iblock_id,
				],
				'order'  => ['LEFT_MARGIN' => 'ASC'],
			]);
			while ($item = $res->fetch()) {
				$dots = '';
				for ($i = 0; $i < $item['DEPTH_LEVEL']; $i ++) {
					$dots .= '. ';
				}
				$list[$item['ID']] = $dots . $item['NAME'];
			}
		}
		return $list;
	}

	/**
	 * Get list of delivery systems
	 */
	public static function getStoreDeliveryList() {
		$list = [];
		$result = \Bitrix\Sale\Delivery\Services\Table::getList(array(
			'filter' => array('ACTIVE'=>'Y'),
		));
		while ($delivery = $result->fetch()) {
			$list[] = [
				'id' => $delivery['ID'],
				'name' => $delivery['NAME'],
			];
		}
		return $list;
	}


	/**
	 * Get all data
	 */
	public static function getAll($profile_id) {
		$info = [];
		if ($profile_id) {
			$profile = ProfilesTable::getById($profile_id);
			if ($profile) {
				$info['crm']['users']      = self::getCrmUsers($profile);
				$info['crm']['directions'] = self::getCrmDirections($profile);
				$info['crm']['stages']     = self::getCrmStages($profile);
				$info['crm']['fields']     = self::getCrmFields($profile);
				$info['crm']['contact_fields'] = self::getCrmContactFields($profile);
				$info['crm']['contact_search_fields'] = self::getCrmContactSFields($profile);
				$info['crm']['company_fields'] = self::getCrmCompanyFields($profile);
				$info['crm']['sources'] = self::getCrmSources($profile);
				$info['site']['user_groups']     = self::getSiteUGroups($profile);
				$info['site']['statuses']  = self::getSiteStatuses($profile);
				$info['site']['person_types'] = self::getSitePersonTypes($profile);
				$info['site']['props']     = self::getSiteProps($profile);
				$info['site']['other_props']     = self::getSiteOtherProps($profile);
				$info['site']['contact_fields'] = self::getSiteContactFields($profile);
				$info['site']['conditions'] = self::getFiltConditions($profile);
			}
		}
		return $info;
	}


	/**
	 * CRM products fields for storing of order ID
	 */
	public static function getCRMOrderIDFields() {
		$result = [
			'' => Loc::getMessage("SP_CI_INFO_CRM_ORDERID_FIELD_ORIGIN_ID"),
		];
		$list = Rest::getList('crm.deal.userfield.list');
		if (is_array($list) && !empty($list)) {
			$new_list = [];
			foreach ($list as $item) {
				if (in_array($item['USER_TYPE_ID'], ['string','double'])) {
					$new_list[] = $item;
				}
			}
			$req_count = ceil(count($new_list) / 50);
			for ($r=0; $r<$req_count; $r++) {
				$next = $r * 50;
				$list_part = [];
				for ($j=$next; $j<$next+50 && $j<count($new_list); $j++) {
					$list_part[] = $new_list[$j];
				}
				// Get name from lang info
				$req_list = [];
				foreach ($list_part as $i => $field) {
					$req_list[$i] = 'crm.deal.userfield.get' . '?' . http_build_query([
							'id' => $field['ID'],
						]);
				}
				$resp = Rest::execute('batch', [
					"halt"  => false,
					"cmd" => $req_list,
				]);
				if ($resp['result']) {
					foreach ($list_part as $i => $field) {
						$field_details = $resp['result'][$i];
						if ( ! empty($field_details)) {
							$result[$field_details['FIELD_NAME']] = $field_details['EDIT_FORM_LABEL']['ru'];
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * CRM sources list
	 */
	public static function getCrmSources() {
		$result = [
			[
				'id' => '',
				'name' => Loc::getMessage("SP_CI_INFO_CRM_SOURCES_DEFAULT"),
			]
		];
		$list = Rest::execute('crm.status.list', [
			'sort' => ['SORT' => 'ASC'],
			'filter' => ['ENTITY_ID' => 'SOURCE'],
		]);
		if (!empty($list)) {
			foreach ($list as $item) {
				$name = $item['NAME'];
				if (!\SProdIntegration::isUtf()) {
					$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
				}
				$result[] = [
					'id' => $item['STATUS_ID'],
					'name' => $name,
				];
			}
		}
		return $result;
	}

}