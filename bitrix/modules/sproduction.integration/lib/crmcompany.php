<?php
/**
 *    CrmCompany
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

use Bitrix\Main,
	Bitrix\Main\Entity,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


/**
 * Class CrmCompany
 *
 * @package SProduction\Integration
 **/

class CrmCompany
{
	/**
	 * Companies search
	 */
	public static function find($s_params) {
		$company_id = false;
		// Find by INN, OGRN, RS, email, phone
		$req_list = [];
		if ($s_params['inn']) {
			$req_list['requisites_inn'] = [
				'method' => 'crm.requisite.list',
				'params' => [
					'filter' => [
						'RQ_INN' => $s_params['inn'],
						'ENTITY_TYPE_ID' => 4,
					],
				],
			];
		}
		if ($s_params['ogrn']) {
			$req_list['requisites_ogrn'] = [
				'method' => 'crm.requisite.list',
				'params' => [
					'filter' => [
						'RQ_OGRN' => $s_params['ogrn'],
						'ENTITY_TYPE_ID' => 4,
					],
				],
			];
		}
		elseif ($s_params['ogrnip']) {
			$req_list['requisites_ogrn'] = [
				'method' => 'crm.requisite.list',
				'params' => [
					'filter' => [
						'RQ_OGRNIP' => $s_params['ogrnip'],
						'ENTITY_TYPE_ID' => 4,
					],
				],
			];
		}
		if ($s_params['account']) {
			$req_list['bankdetail'] = [
				'method' => 'crm.requisite.bankdetail.list',
				'params' => [
					'filter' => [
						'RQ_ACC_NUM' => $s_params['account'],
					],
				],
			];
			$req_list['bankdetail_req'] = [
				'method' => 'crm.requisite.get',
				'params' => [
					'id' => '$result[bankdetail][0][ENTITY_ID]',
				],
			];
		}
		if ($s_params['phone']) {
			$req_list['companies_phone'] = [
				'method' => 'crm.company.list',
				'params' => [
					'filter' => [
						'PHONE' => $s_params['phone'],
					],
				],
			];
		}
		if ($s_params['email']) {
			$req_list['companies_email'] = [
				'method' => 'crm.company.list',
				'params' => [
					'filter' => [
						'EMAIL' => $s_params['email'],
					],
				],
			];
		}
		if (!empty($req_list)) {
			$res_list = Rest::batch($req_list);
			if ($res_list['requisites_inn'][0]) {
				$company_id = $res_list['requisites_inn'][0]['ENTITY_ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by inn');
			} elseif ($res_list['requisites_ogrn'][0]) {
				$company_id = $res_list['requisites_ogrn'][0]['ENTITY_ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by ogrn');
			} elseif ($res_list['requisites_ogrnip'][0]) {
				$company_id = $res_list['requisites_ogrnip'][0]['ENTITY_ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by ogrnip');
			} elseif ($res_list['bankdetail'][0]) {
				$company_id = $res_list['bankdetail_req']['ENTITY_ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by bank account');
			} elseif ($res_list['companies_phone'][0]) {
				$company_id = $res_list['companies_phone'][0]['ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by phone');
			} elseif ($res_list['companies_email'][0]) {
				$company_id = $res_list['companies_email'][0]['ID'];
				\SProdIntegration::Log('(CrmCompany::find) finded by email');
			}
		}
		return $company_id;
	}

	/**
	 * Get company info
	 */
	public static function get($id) {
		$result = false;
		if ($id) {
			$req_list = [];
			$req_list['company'] = [
				'method' => 'crm.company.get',
				'params' => [
					'id' => $id,
				],
			];
			$req_list['requisite'] = [
				'method' => 'crm.requisite.list',
				'params' => [
					'filter' => [
						'ENTITY_ID' => $id,
					],
				],
			];
			$req_list['bankdetail'] = [
				'method' => 'crm.requisite.bankdetail.list',
				'params' => [
					'filter' => [
						'ENTITY_ID' => '$result[requisite][0][ID]',
					],
				],
			];
			$req_list['address'] = [
				'method' => 'crm.address.list',
				'params' => [
					'filter' => [
						'ENTITY_ID' => '$result[requisite][0][ID]',
					],
				],
			];
			$res_list = Rest::batch($req_list);
			if ($res_list) {
				$result = $res_list;
			}
		}
		return $result;
	}

	/**
	 * Create new company
	 */
	public static function add($params) {
		$result = false;
		if (!$params['company']['NAME']) {
			return $result;
		}
		$fields = [
			'TITLE' => $params['company']['NAME'],
		];
		if ($params['company']['PHONE']) {
			$fields['PHONE'] = [['VALUE' => $params['company']['PHONE']]];
		}
		if ($params['company']['EMAIL']) {
			$fields['EMAIL'] = [['VALUE' => $params['company']['EMAIL']]];
		}
		$resp = Rest::execute('crm.company.add', [
			'fields' => $fields,
		], false, true, false);
		if ($resp['error_description']) {
			\SProdIntegration::Log('(CrmCompany::add) company error '.$resp['error_description']);
		}
		else {
			$company_id = $resp['result'];
		}
		if ($company_id) {
			$result = $company_id;
			$fields = [
				'ENTITY_ID'    => $company_id,
				'ENTITY_TYPE_ID' => 4,
				'PRESET_ID' => $params['company']['PRESET_ID'],
				'NAME' => $params['company']['NAME'],
			];
			foreach ($params['requisite'] as $param => $value) {
				$fields[$param] = $value;
			}
			$resp = Rest::execute('crm.requisite.add', [
				'fields' => $fields,
			], false, true, false);
			if ($resp['error_description']) {
				\SProdIntegration::Log('(CrmCompany::add) requisite error '.$resp['error_description']);
			}
			else {
				$requisite_id = $resp['result'];
			}
			if ($requisite_id) {
				if (!empty($params['bankdetail'])) {
					$fields = [
						'ENTITY_ID' => $requisite_id,
						'NAME'      => Loc::getMessage('SP_CI_CRMCOMPANY_BANKDETAIL_NAME_DEF'),
						'CODE'      => 'SHOP_REQ',
					];
					foreach ($params['bankdetail'] as $param => $value) {
						$fields[$param] = $value;
					}
					$resp = Rest::execute('crm.requisite.bankdetail.add', [
						'fields' => $fields,
					], false, true, false);
					if ($resp['error_description']) {
						\SProdIntegration::Log('(CrmCompany::add) bankdetail error '.$resp['error_description']);
					}
				}
				if (!empty($params['address_fact'])) {
					$fields = [
						'ENTITY_ID'      => $requisite_id,
						'ENTITY_TYPE_ID' => 8,
						'TYPE_ID'        => 1,
					];
					foreach ($params['address_fact'] as $param => $value) {
						$fields[$param] = $value;
					}
					$resp = Rest::execute('crm.address.add', [
						'fields' => $fields,
					], false, true, false);
					if ($resp['error_description']) {
						\SProdIntegration::Log('(CrmCompany::add) address_fact error '.$resp['error_description']);
					}
				}
				if (!empty($params['address_jur'])) {
					$fields = [
						'ENTITY_ID'      => $requisite_id,
						'ENTITY_TYPE_ID' => 8,
						'TYPE_ID'        => 6,
					];
					foreach ($params['address_jur'] as $param => $value) {
						$fields[$param] = $value;
					}
					$resp = Rest::execute('crm.address.add', [
						'fields' => $fields,
					], false, true, false);
					if ($resp['error_description']) {
						\SProdIntegration::Log('(CrmCompany::add) address_jur error '.$resp['error_description']);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Update company info
	 */
	public static function update($id, $params) {
		$result = false;
		if ($id && !empty($params['company'])) {
			foreach ($params['company'] as $param => $value) {
				$fields[$param] = $value;
			}
			if ($params['company']['PHONE']) {
				$fields['PHONE'] = [['VALUE' => $params['company']['PHONE']]];
			}
			if ($params['company']['EMAIL']) {
				$fields['EMAIL'] = [['VALUE' => $params['company']['EMAIL']]];
			}
			if (UpdateLock::isChanged($id, 'company_stoc', $fields, true)) {
				Rest::execute('crm.company.update', [
					'id'     => $id,
					'fields' => $fields,
				]);
			}
		}
		return $result;
	}
}