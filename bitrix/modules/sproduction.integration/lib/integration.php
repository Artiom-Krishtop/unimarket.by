<?php
/**
 * Integration
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("sale");

use Bitrix\Main,
	Bitrix\Main\Type,
	Bitrix\Main\Entity,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\SiteTable,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

class Integration
{
	const MODULE_ID = 'sproduction.integration';
	const APP_HANDLER = '/bitrix/sprod_integr_auth.php';
	const EVENTS_HANDLER = '/bitrix/sprod_integr_handler.php';
	const FILELOG = '/upload/sprod_integr_log.txt';
	const DEAL_MEASURE_CODE_DEF = 796;
    public static $SERVER_ADDR;
    protected static $MANUAL_RUN = false;

	public static function setBulkRun() {
		self::$MANUAL_RUN = true;
		Rest::setBulkRun();
    }

	public static function isBulkRun() {
		return self::$MANUAL_RUN;
    }

	public static function setBaseParams() {
		if (!self::$SERVER_ADDR) {
			self::$SERVER_ADDR = Settings::get("site");
		}
        return true;
    }

	public static function getServerAddr() {
		return self::$SERVER_ADDR;
	}

	public static function getAppLink() {
        $link = false;
        if (self::setBaseParams()) {
            $link = self::getServerAddr() . self::APP_HANDLER;
        }
        return $link;
    }

	public static function getCrmHandlersLink() {
        $link = false;
        if (self::setBaseParams()) {
            $link = self::getServerAddr() . self::EVENTS_HANDLER;
        }
        return $link;
    }

    public static function getFilelogLink() {
        $link = false;
        if (self::setBaseParams()) {
            $link = self::getServerAddr() . self::FILELOG;
        }
        return $link;
    }

    public static function getFilelogInfo() {
        $info = false;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . self::FILELOG;
        if (file_exists($file_path)) {
	        $info['size'] = filesize($file_path);
	        $info['size_f'] = \SProdIntegration::getFileSizeFormat($info['size']);
        }
        return $info;
    }

    public static function resetFilelog() {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . self::FILELOG;
        return file_put_contents($file_path, '');
    }

	public static function checkConnection() {
        $res = false;
        if (Rest::getAppInfo() && Rest::getAuthInfo()) {
	        $res = true;
        }
        return $res;
    }

    public static function getOrderIDField() {
		$field = 'ORIGIN_ID';
		if (Settings::get('crm_orderid_field')) {
			$field = Settings::get('crm_orderid_field');
		}
		return $field;
    }


	/**
	 * Get a suitable profile for this order
	 */

    function getOrderProfile(array $order_data) {
        $profile = ProfilesTable::getByFilter($order_data);
	    \SProdIntegration::Log('(getOrderProfile) selected profile "' . $profile['id'] . '"');
        return $profile;
    }


	/**
	 * Event handlers
	 */

    // Check store handlers
    public static function checkStoreHandlers() {
    	$res = false;
	    $handlers = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("sale", "OnSaleOrderSaved");
	    if (!empty($handlers)) {
		    foreach ($handlers as $handler) {
			    if (
			    	$handler['TO_MODULE_ID'] == self::MODULE_ID &&
			        $handler['TO_CLASS'] == '\SProduction\Integration\Integration' &&
				    $handler['TO_METHOD'] == 'eventOnSaleOrderSaved'
		        ) {
				    $res = true;
			    }
	    	}
	    }
    	return $res;
    }

	// Listen events of the store
	public static function regStoreHandlers() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("sale", "OnSaleOrderSaved", self::MODULE_ID, '\SProduction\Integration\Integration', 'eventOnSaleOrderSaved');
	}

	// Remove store listeners
	public static function unregStoreHandlers() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("sale", "OnSaleOrderSaved", self::MODULE_ID, '\SProduction\Integration\Integration', 'eventOnSaleOrderSaved');
	}

	// Check portal listeners
	public static function checkCrmHandlers() {
        $is_exist = false;
	    $list = Rest::execute('event.get');
        if (is_array($list) && !empty($list)) {
            $e_add_exist = false;
            $e_upd_exist = false;
            foreach ($list as $event) {
                if ($event['event'] == 'ONCRMDEALUPDATE' && $event['handler'] == self::getCrmHandlersLink()) {
                    $e_upd_exist = true;
                }
            }
            if ($e_upd_exist) {
                $is_exist = true;
            }
        }
        return $is_exist;
    }

	// Check active profiles
	public static function checkActiveProfiles() {
        $is_exist = false;
		$list = ProfilesTable::getList([
			'filter' => ['active' => 'Y'],
			'select' => ['id'],
		]);
		if (count($list)) {
			$is_exist = true;
		}
        return $is_exist;
    }

	// Listen events of the portal
	public static function regCrmHandlers() {
		$sync_direction = Settings::get('direction');
		if (!$sync_direction || $sync_direction == 'full' || $sync_direction == 'ctos') {
			try {
				Rest::execute('event.bind', [
					'event'   => 'onCrmDealUpdate',
					'handler' => self::getCrmHandlersLink(),
				]);
			}
			catch (\Exception $e) {
			}
			OfflineEvents::eventsBind();
		}
    }

	// Remove portal listeners
	public static function unregCrmHandlers() {
		try {
	        Rest::execute('event.unbind', [
	            'event' => 'onCrmDealUpdate',
	            'handler' => self::getCrmHandlersLink(),
	        ]);
		}
		catch (\Exception $e) {
		}
		OfflineEvents::eventsUnbind();
    }


	/**
	 * Create new deal from order
	 */

	private static function createDealFromOrder($order_data, $deal_info, $profile, $deal_fields) {
		$deal = [];
		if (self::checkConnection()) {
			$order_id = $order_data['ID'];
			// Add deal
			$category_id = (int)$profile['options']['deal_category'];
			$deal_title = self::getOrdTitleWithPrefix($order_data, $profile);
			$fields = [
				'TITLE'     => $deal_title,
				self::getOrderIDField() => $order_id,
				'CATEGORY_ID' => $category_id,
				'CURRENCY_ID' => $order_data['CURRENCY'],
				'OPPORTUNITY' => $order_data['PRICE'],
			];
			$fields = array_merge($deal_fields, $fields);
			// Source of deal
			$source_id = Settings::get("source_id");
			if ($source_id) {
				$fields['ORIGINATOR_ID'] = $source_id;
			}
			// Responsible user
			if (!$fields['ASSIGNED_BY_ID']) {
				$responsible_id = (int) $profile['options']['deal_respons_def'];
			}
			if ($responsible_id) {
				$fields['ASSIGNED_BY_ID'] = $responsible_id;
			}
			// Deal source
			if ($profile['options']['deal_source']) {
				$fields['SOURCE_ID'] = $profile['options']['deal_source'];
			}
			// Create deal
			\SProdIntegration::Log('(createDealFromOrder) crm.deal.add for order '.$order_id.' '.print_r($fields, 1));
			$resp   = Rest::execute('crm.deal.add', ['fields' => $fields]);
			if ($resp) {
				$deal_id = $resp;
				// Return deal details
				$deals = self::getDeal([$deal_id]);
				$deal = $deals[0];
				\SProdIntegration::Log('(createDealFromOrder) order ' . $order_id . ' deal ' . $deal_id . ' created');
			}
		}
		return $deal;
	}


	/**
	 * Sync goods and delivery
	 */

	public static function updateDealProducts($deal_id, $order_data, $deal_info) {
		$result = false;
		if (self::checkConnection()) {
			$old_prod_rows = Rest::execute('crm.deal.productrows.get', [
				'id' => $deal_id
			]);
			// --- DEAL PRODUCTS LIST ---
			$new_rows = [];
			// Products list of deal
			foreach ($order_data['PRODUCTS'] as $item) {
				// Discount
				if (Settings::get('products_no_discounts')) {
					$discount = 0;
					$price = $item['PRICE'] + $item['DISCOUNT_SUM'];
				}
				else {
					$discount = $item['DISCOUNT_SUM'];
					$price = $item['PRICE'];
				}
				// Product fields
				$deal_prod = [
					'PRODUCT_NAME' => $item['PRODUCT_NAME'],
					'QUANTITY' => $item['QUANTITY'],
					'DISCOUNT_TYPE_ID' => 1,
					'DISCOUNT_SUM' => $discount,
					'MEASURE_CODE' => $item['MEASURE_CODE'],
					'TAX_RATE' => $item['VAT_RATE'],
					'TAX_INCLUDED' => $item['VAT_INCLUDED'],
				];
				if ($item['VAT_INCLUDED'] == 'N') {
					$deal_prod['PRICE_EXCLUSIVE'] = $price;
					$deal_prod['PRICE'] = $price + $price * 0.01 * (int)$item['VAT_RATE'];
				}
				else {
					$deal_prod['PRICE'] = $price;
				}
				$new_rows[] = $deal_prod;
			}
			// --- DELIVERY ---
			$delivery_sync_type = Settings::get('products_delivery');
			if (!$delivery_sync_type || ($delivery_sync_type == 'notnull' && $order_data['DELIVERY_PRICE'])) {
				$deliv_row = [
					'PRODUCT_NAME' => Loc::getMessage("SP_CI_PRODUCTS_DELIVERY"),
					'PRICE'        => $order_data['DELIVERY_PRICE'],
					'QUANTITY'     => 1,
					'MEASURE_CODE' => self::DEAL_MEASURE_CODE_DEF,
				];
				if ($order_data['DELIVERY_TYPE']) {
					$deliv_row['PRODUCT_NAME'] = Loc::getMessage("SP_CI_PRODUCTS_DELIVERY") . ': ' . $order_data['DELIVERY_TYPE'];
				}
				// Find delivery product
				if (Settings::get('products_deliv_prod_active')) {
					$deliv_prods = (array)Settings::get('products_deliv_prod_list', true);
					if ($order_data['DELIVERY_TYPE_ID'] && $deliv_prods[$order_data['DELIVERY_TYPE_ID']]) {
						$deliv_row['PRODUCT_ID'] = $deliv_prods[$order_data['DELIVERY_TYPE_ID']];
					}
				}
				$new_rows[] = $deliv_row;
			}
			// --- LINKING CRM PRODUCTS ---
			$sync_type = Settings::get("products_sync_type");
			$root_section = (int)Settings::get("products_root_section");
			// Section for order products
			if ($sync_type == 'create') {
				$group_by_orders = Settings::get("products_group_by_orders");
				if ($group_by_orders) {
					$deal_section = CrmProducts::findSection($order_data['ID'], $root_section);
					if (!$deal_section) {
						$deal_section_id = CrmProducts::addSection($order_data['ID'], $root_section);
					}
					else {
						$deal_section_id = $deal_section['ID'];
					}
				}
			}
			if ($deal_section_id) {
				$parent_section = $deal_section_id;
			}
			else {
				$parent_section = $root_section;
			}
			// Get CRM products by order products IDs
			if ($sync_type == 'create') {
				// Find product
				$store_prod_ids = [];
				foreach ($order_data['PRODUCTS'] as $item) {
					$store_prod_ids[] = CrmProducts::XML_ID_PREFIX . $item['PRODUCT_ID'];
				}
				\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' search filter '.print_r(['ids' => $store_prod_ids, 'section' => $parent_section], true));
				$crm_prod_list = CrmProducts::find($store_prod_ids, 'XML_ID', $parent_section);
				\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' finded products ' . print_r($crm_prod_list, true));
			}
			elseif ($sync_type == 'find' || $sync_type == 'mixed') {
				// Find product
				$store_prod_ids = [];
				$store_prod_cmpr = [];
				$store_fields = CrmProducts::getSearchFields();
				$search_crm_field = Settings::get("products_search_crm_field");
				foreach ($order_data['PRODUCTS'] as $item) {
					// Get iblock of product
					$res = \CIBlockElement::GetList(["SORT" => "ASC"], ["ID" => $item['PRODUCT_ID']], false, false, ['IBLOCK_ID']);
					if ($ob = $res->GetNextElement()) {
						$ib_product = $ob->GetFields();
						$product_iblock = $ib_product['IBLOCK_ID'];
						$search_store_field = $store_fields[$product_iblock];
						if ($search_store_field) {
							// Get value of id field
							$res = \CIBlockElement::GetList(["SORT" => "ASC"], ["ID" => $item['PRODUCT_ID']], false, false, [$search_store_field]);
							if ($ob = $res->GetNextElement()) {
								$ib_product = $ob->GetFields();
								if (strpos($search_store_field, 'PROPERTY_') !== false) {
									$value = $ib_product[$search_store_field . '_VALUE'];
								} else {
									$value = $ib_product[$search_store_field];
								}
								if ($value) {
									$store_prod_ids[]                     = $value;
									$store_prod_cmpr[$item['PRODUCT_ID']] = $value;
								}
							}
						}
					}
				}
				\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' search filter '.print_r(['ids' => $store_prod_ids, 'section' => $parent_section], true));
				$crm_prod_list = CrmProducts::find($store_prod_ids, $search_crm_field, $parent_section, true);
				\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' finded products '.print_r($crm_prod_list, true));
			}
			// Products list of deal
			foreach ($order_data['PRODUCTS'] as $k => $item) {
				// Position of product in the deal
				$k_old = false;
				foreach ($old_prod_rows as $j => $row) {
					if ($item['PRODUCT_NAME'] == $row['PRODUCT_NAME']) {
						$k_old = $j;
					}
				}
				// Link CRM products
				if ( !self::isBulkRun() && $k_old && $old_prod_rows[$k_old]['PRODUCT_ID']) {
					$new_rows[$k]['PRODUCT_ID'] = $old_prod_rows[$k_old]['PRODUCT_ID'];
				}
				else {
					if ($sync_type == 'create') {
						if ($crm_prod_list[CrmProducts::XML_ID_PREFIX . $item['PRODUCT_ID']]) {
							$crm_prod_id             = $crm_prod_list[CrmProducts::XML_ID_PREFIX . $item['PRODUCT_ID']]['ID'];
							$new_rows[$k]['PRODUCT_ID'] = $crm_prod_id;
							// Update product
							$fields = CrmProducts::getCRMProductFields($item['PRODUCT_ID'], $deal_info['product_fields']);
							\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' update product '.$crm_prod_id.' fields '.print_r($fields, true));
							CrmProducts::update($crm_prod_id, 'XML_ID', $fields);
						} else {
							// Create product
							$fields      = CrmProducts::getCRMProductFields($item['PRODUCT_ID'], $deal_info['product_fields']);
							\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' new product fields '.print_r($fields, true));
							$crm_prod_id = CrmProducts::add(CrmProducts::XML_ID_PREFIX . $item['PRODUCT_ID'], 'XML_ID', $fields, $parent_section);
							\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' new product id '.$crm_prod_id);
							if ($crm_prod_id) {
								$new_rows[$k]['PRODUCT_ID'] = $crm_prod_id;
								$crm_prod_list[CrmProducts::XML_ID_PREFIX . $item['PRODUCT_ID']]['ID'] = $crm_prod_id;
							}
						}
					}
					elseif ($sync_type == 'find') {
						$crm_prod_id = $crm_prod_list[$store_prod_cmpr[$item['PRODUCT_ID']]]['ID'];
						if ($crm_prod_id) {
							$new_rows[$k]['PRODUCT_ID'] = $crm_prod_id;
						}
					}
					elseif ($sync_type == 'mixed') {
						if ($search_crm_field) {
							$crm_prod_id = $crm_prod_list[$store_prod_cmpr[$item['PRODUCT_ID']]]['ID'];
							if ($crm_prod_id) {
								$new_rows[$k]['PRODUCT_ID'] = $crm_prod_id;
								// Update product
								$fields = CrmProducts::getCRMProductFields($item['PRODUCT_ID'], $deal_info['product_fields']);
								\SProdIntegration::Log('(updateDealProducts) deal ' . $deal_id . ' update product ' . $crm_prod_id . ' fields ' . print_r($fields, true));
								CrmProducts::update($crm_prod_id, $search_crm_field, $fields);
							} else {
								// Create product
								$fields = CrmProducts::getCRMProductFields($item['PRODUCT_ID'], $deal_info['product_fields']);
								\SProdIntegration::Log('(updateDealProducts) deal ' . $deal_id . ' new product fields ' . print_r($fields, true));
								$crm_prod_id = CrmProducts::add($item['PRODUCT_ID'], $search_crm_field, $fields, $parent_section);
								\SProdIntegration::Log('(updateDealProducts) deal ' . $deal_id . ' new product id ' . $crm_prod_id);
								if ($crm_prod_id) {
									$new_rows[$k]['PRODUCT_ID'] = $crm_prod_id;
									$crm_prod_list[$item['PRODUCT_ID']]['ID'] = $crm_prod_id;
								}
							}
						}
					}
				}
			}
			// Check changes
			$new_rows = self::convEncForDeal($new_rows);
			$has_changes = false;
			if (count($new_rows) != count($old_prod_rows)) {
				$has_changes = true;
			}
			else {
				foreach ($new_rows as $j => $row) {
					foreach ($row as $k => $value) {
						if ($value != $old_prod_rows[$j][$k]) {
							$has_changes = true;
							continue 2;
						}
					}
				}
			}
			// Send request
			if ($has_changes) {
				\SProdIntegration::Log('(updateDealProducts) deal '.$deal_id.' changed products '.print_r($new_rows, true));
				$resp = Rest::execute('crm.deal.productrows.set', [
					'id'   => $deal_id,
					'rows' => $new_rows
				]);
				if ($resp) {
					$result = true;
				}
			}
		}
		return $result;
	}


	/**
	 * Get contact data by profile
	 */

	public static function getDealContactDataByProfile(array $order_data, $contact, $profile) {
		$cont_fields = [];
		$person_type = $order_data['PERSON_TYPE_ID'];
		$comp_table = (array)$profile['contact']['comp_table'][$person_type];
		$res = \CUser::GetByID($order_data['USER_ID']);
		$user_fields = $res->Fetch();
		//\SProdIntegration::Log('(getDealContactDataByProfile) user_fields '.print_r($user_fields, true));
		foreach ($comp_table as $deal_f_id => $sync_params) {
			$order_f_id = $sync_params['value'];
			// User fields
			if ($order_f_id) {
				$value = false;
				if ( ! (int) $order_f_id) {
					$value = $user_fields[$order_f_id];
				} // Properties
				else {
					foreach ($order_data['PROPERTIES'] as $prop) {
						if ($prop['ID'] == $order_f_id) {
							$value = $prop['VALUE'][0];
						}
					}
				}
				if ($value) {
					if (in_array($deal_f_id, ['EMAIL', 'PHONE'])) {
						$phonemail_mode = Settings::get('contacts_phonemail_mode');
						if ($phonemail_mode == 'replace' && ! empty($contact[$deal_f_id])) {
							foreach ($contact[$deal_f_id] as $i => $item) {
								if ($i == 0) {
									$cont_fields[$deal_f_id][] = ['ID'         => $item['ID'],
									                              'VALUE'      => $value,
									                              'VALUE_TYPE' => 'WORK'
									];
								} else {
									$cont_fields[$deal_f_id][] = ['ID' => $item['ID'], 'DELETE' => 'Y'];
								}
							}
						} else {
							$cont_fields[$deal_f_id][] = ['VALUE' => $value, 'VALUE_TYPE' => 'WORK'];
						}
					} else {
						$cont_fields[$deal_f_id] = $value;
					}
				} else {
					$cont_fields[$deal_f_id] = '';
				}
			}
		}
		return $cont_fields;
	}


	/**
	 * Sync the deal contact data
	 */

	public static function syncOrderToDealContact(array $order_data, $deal_info, $profile) {
		$result = false;
		if (self::checkConnection()) {
			$sync_new_type = (int) $profile['contact']['sync_new_type'];
			$deal = $deal_info['deal'];
			$contact = $deal_info['contact'];
			//\SProdIntegration::Log('(syncOrderToDealContact) current contact '.print_r($contact, true));
			// Find contact
			if (!$contact['ID']) {
				$contact = self::findContact($order_data, $deal_info, $profile);
			}
			// Get contacts data
			$cont_fields = self::getDealContactDataByProfile($order_data, $contact, $profile);
			$cont_fields = self::convEncForDeal($cont_fields);
			\SProdIntegration::Log('(syncOrderToDealContact) cont_fields '.print_r($cont_fields, true));
			// Add contact
			if (!$contact['ID']) {
				$responsible_id = (int)$profile['options']['deal_respons_def'];
				if ($responsible_id) {
					$cont_fields['ASSIGNED_BY_ID'] = $responsible_id;
				}
				if (!$cont_fields['NAME'] && !$cont_fields['LAST_NAME']) {
					$cont_fields['NAME'] = Loc::getMessage("SP_CI_SYNC_CONTACT_NAME_DEFAULT");
				}
				$contact_id = Rest::execute('crm.contact.add', [
					'fields' => $cont_fields,
				]);
				if (!$contact_id) {
					$res = Rest::execute('crm.contact.add', [
						'fields' => $cont_fields,
					], false, true, false);
					\SProdIntegration::Log('(syncOrderToDealContact) add contact error '.print_r($res, true));
				}
			}
			// Update contact
			else {
				$contact_id = $contact['ID'];
				if (((!$deal['ID'] && $sync_new_type == 1) || $sync_new_type == 2) && UpdateLock::isChanged($contact_id, 'contact_stoc', $cont_fields, true)) {
					Rest::execute('crm.contact.update', [
						'id'     => $contact_id,
						'fields' => $cont_fields,
					]);
				}
			}
			if ($contact_id) {
				$result = $contact_id;
			}
		}
		return $result;
	}


	/**
	 * Get company data by profile
	 */

	public static function getDealCompanyDataByProfile(array $order_data, $profile) {
		$comp_fields = [];
		$person_type = $order_data['PERSON_TYPE_ID'];
		$comp_table = (array)$profile['contact']['company_comp_table'][$person_type];
		$res = \CUser::GetByID($order_data['USER_ID']);
		$user_fields = $res->Fetch();
		foreach ($comp_table as $section_code => $section) {
			foreach ($section as $deal_f_id => $sync_params) {
				$order_f_id = $sync_params['value'];
				$value      = false;
				// User fields
				if (in_array($deal_f_id, ['PRESET_ID'])) {
					$value = $order_f_id;
				}
				elseif ( ! (int) $order_f_id) {
					$value = $user_fields[$order_f_id];
				} // Properties
				else {
					foreach ($order_data['PROPERTIES'] as $prop) {
						if ($prop['ID'] == $order_f_id) {
							$value = $prop['VALUE'][0];
						}
					}
				}
				if ($value) {
					$comp_fields[$section_code][$deal_f_id] = $value;
				} else {
					$comp_fields[$section_code][$deal_f_id] = '';
				}
			}
		}
		return $comp_fields;
	}

	/**
	 * Create new company for deal
	 */

	public static function syncOrderToDealCompany(array $order_data, $profile) {
		// Get contacts data
		$comp_fields = self::getDealCompanyDataByProfile($order_data, $profile);
		$comp_fields = self::convEncForDeal($comp_fields);
		\SProdIntegration::Log('(syncOrderToDealCompany) order '.$order_data['ID'].' comp_fields '.print_r($comp_fields, true));
		$filter = [
			'inn' => $comp_fields['requisite']['RQ_INN'],
			'ogrn' => $comp_fields['requisite']['RQ_OGRN'],
			'ogrnip' => $comp_fields['requisite']['RQ_OGRNIP'],
			'account' => $comp_fields['bankdetail']['RQ_ACC_NUM'],
			'phone' => $comp_fields['company']['PHONE'],
			'email' => $comp_fields['company']['EMAIL'],
		];
		//\SProdIntegration::Log('(syncOrderToDealCompany) order '.$order_data['ID'].' search company by '.print_r($filter, true));
		$company_id = CrmCompany::find($filter);
		\SProdIntegration::Log('(syncOrderToDealCompany) order '.$order_data['ID'].' company "'.$company_id.'"');
		try {
			if (!$company_id) {
				$company_id = CrmCompany::add($comp_fields);
			}
			else {
				CrmCompany::update($company_id, $comp_fields);
			}
		}
		catch (\Exception $e) {
			\SProdIntegration::Log('(syncOrderToDealCompany) can\'t sync of company');
		}
		return $company_id;
	}


//	public static function syncDealContactToOrder($contact_id) {
//		$func_result = false;
//		if (self::checkConnection()) {
//			$resp = Rest::execute('crm.contact.get', [
//				'id' => $contact_id,
//			]);
//			if ($resp) {
//				$contact = $resp;
//				echo '<pre>'; print_r($contact); echo '</pre>';
//			}
//		}
//		return $func_result;
//	}

	private static function getPhonesFormats($phone){
		$phones = [];
		if(strlen($phone)){
			$phoneUnformatted = preg_replace('/[^+\d]/', '', $phone);
			$phoneFormatted1 = preg_replace(
				[
					'/^\+?7([\d]{3})([\d]{3})([\d]{2})([\d]{2})$/m',
					'/^\+?380([\d]{2})([\d]{3})([\d]{2})([\d]{2})$/m',
					'/^\+?996([\d]{3})([\d]{3})([\d]{3})$/m',
					'/^\+?998([\d]{2})([\d]{3})([\d]{4})$/m',
				],
				[
					'+7 (${1}) ${2}-${3}-${4}', // +7 (___) ___-__-__
					'+380 (${1}) ${2}-${3}-${4}', // +380 (__) ___-__-__
					'+996 (${1}) ${2}-${3}', // +996 (___) ___-___
					'+998-${1}-${2}-${3}', // +998-__- ___-____
				],
				$phoneUnformatted
			);
			$phoneFormatted2 = preg_replace(
				[
					'/^\+?7([\d]{3})([\d]{3})([\d]{2})([\d]{2})$/m',
					'/^\+?380([\d]{2})([\d]{3})([\d]{2})([\d]{2})$/m',
					'/^\+?996([\d]{3})([\d]{3})([\d]{3})$/m',
					'/^\+?998([\d]{2})([\d]{3})([\d]{4})$/m',
				],
				[
					'7(${1})${2}${3}${4}', // +7 (___) ___-__-__
					'380(${1})${2}${3}${4}', // +380 (__) ___-__-__
					'996(${1})${2}${3}', // +996 (___) ___-___
					'998${1}${2}${3}', // +998-__- ___-____
				],
				$phoneUnformatted
			);
			$phones = array_unique([$phone, $phoneFormatted1, $phoneFormatted2, $phoneUnformatted]);
		}
		return $phones;
	}


	/**
	 * Contacts search
	 */

	public static function findContact(array $order_data, $deal_info, $profile) {
		$contact = false;
		if (self::checkConnection()) {
			$cont_fields = self::getDealContactDataByProfile($order_data, [], $profile);
			$cont_s_field = $profile['contact']['contact_search_fields'];
			if ($cont_s_field) {
				if ($cont_fields[$cont_s_field]) {
					$filter = [
						$cont_s_field => $cont_fields[$cont_s_field],
					];
					$request = [
						'list' => [
							'method' => 'crm.contact.list',
							'params' => [
								'filter' => $filter,
							]
						],
						'get' => [
							'method' => 'crm.contact.get',
							'params' => [
								'id' => '$result[list][0][ID]',
							]
						]
					];
					$res = Rest::batch($request);
					if ($res['get']) {
						$contact = $res['get'];
					}
				}
			}
			else {
				if ($cont_fields['PHONE'] && $cont_fields['PHONE'][0]['VALUE']) {
					$search_phone = $cont_fields['PHONE'][0]['VALUE'];
				}
				if ($cont_fields['EMAIL'] && $cont_fields['EMAIL'][0]['VALUE']) {
					$search_email = $cont_fields['EMAIL'][0]['VALUE'];
				}
				// Find by phone
				if ($search_phone) {
					$phones = self::getPhonesFormats($search_phone);
					foreach ($phones as $phone) {
						$filter = [
							'PHONE' => $phone,
						];
						$request = [
							'list' => [
								'method' => 'crm.contact.list',
								'params' => [
									'filter' => $filter,
								]
							],
							'get' => [
								'method' => 'crm.contact.get',
								'params' => [
									'id' => '$result[list][0][ID]',
								]
							]
						];
						$res = Rest::batch($request);
						if ($res['get']['ID']) {
							$contact = $res['get'];
						}
					}
				}
				// Find by email
				if ( ! $contact && $search_email) {
					$filter = [
						'EMAIL' => $search_email,
					];
					$request = [
						'list' => [
							'method' => 'crm.contact.list',
							'params' => [
								'filter' => $filter,
							]
						],
						'get' => [
							'method' => 'crm.contact.get',
							'params' => [
								'id' => '$result[list][0][ID]',
							]
						]
					];
					$res = Rest::batch($request);
					if ($res['get']['ID']) {
						$contact = $res['get'];
					}
				}
			}
			\SProdIntegration::Log('(findContact) finded contact "' . $contact['ID'] . '" by ' . print_r($filter, true));
		}
		return $contact;
	}


	/**
	 * Get changed fields of the deal
	 */

	public static function getDealChangedFields(array $order_data, array $deal_info, $profile) {
		$d_new_fields = [];
		$deal = $deal_info['deal'];
		// Changes of status
		$d_new_fields = array_merge($d_new_fields, self::getDealChangedStatus($order_data, $deal_info, $profile));
		// Changes of props
		$d_new_fields = array_merge($d_new_fields, self::getDealChangedProps($order_data, $deal_info, $profile));
		// Changes of other order data
		$d_new_fields = array_merge($d_new_fields, self::getDealChangedOther($order_data, $deal_info, $profile));
		// Changes of responsible
		$d_new_fields = array_merge($d_new_fields, self::getDealChangedBasic($order_data, $deal_info, $profile));
		// Check contacts

		// Link company
		if (!$deal['COMPANY_ID']) {
			$company_id = self::syncOrderToDealCompany($order_data, $profile);
			if ($company_id) {
				$d_new_fields['COMPANY_ID'] = $company_id;
			}
		}

		return $d_new_fields;
	}


	/**
	 * Updating of deal data
	 */

	public static function updateDealFields($deal_id, $order_id, $d_new_fields) {
		// Send changes
		if (!empty($d_new_fields)) {
			foreach ($d_new_fields as $k => $value) {
				if ($value === null) {
					$d_new_fields[$k] = '';
				}
			}
			if (UpdateLock::isChanged($order_id, 'order_stoc', $d_new_fields, true)) {
				\SProdIntegration::Log('(updateDealFields) deal '.$deal_id.' update fields ' . print_r($d_new_fields, true));
				Rest::execute('crm.deal.update', [
					'id'     => $deal_id,
					'fields' => $d_new_fields,
				]);
			}
		}
	}


	/**
	 * Information of status changes
	 */

	public static function getDealChangedStatus(array $order_data, array $deal_info, $profile) {
		$changed_fields = [];
		$status_table = (array)$profile['statuses']['comp_table'];
//		\SProdIntegration::Log('(getDealChangedStatus) status_table '.print_r($status_table, true));
		$cancel_table = (array)$profile['statuses']['cancel_stages'];
		$reverse_disable = $profile['statuses']['reverse_disable'] ? true : false;
		$deal = $deal_info['deal'];
		// Stage of canceled order
		$new_stage = false;
		if ($order_data['IS_CANCELED']) {
			if ( ! in_array($deal['STAGE_ID'], $cancel_table)) {
				$new_stage = $cancel_table[0];
			}
		}
		// Change stage if set conformity of status and statuses is different
		else {
			$sync_params = $status_table[$order_data['STATUS_ID']];
			$deal_stages = (array) $sync_params['stages'];
			$deal_stages = array_diff($deal_stages, ['']);
			if ( ! empty($deal_stages) && ($sync_params['direction'] == 'all' || $sync_params['direction'] == 'stoc')) {
				if ( ! in_array($deal['STAGE_ID'], $deal_stages)) {
					$new_stage = $deal_stages[0];
				}
			}
		}
		// Check if is reverse stage
		if ($new_stage && $reverse_disable) {
			$stages_list = [];
			foreach ($deal_info['stages'] as $item) {
				$stages_list[$item['STATUS_ID']] = count($stages_list);
			}
			if ($stages_list[$new_stage] <= $stages_list[$deal['STAGE_ID']]) {
				$new_stage = false;
			}
		}
		if ($new_stage) {
			$changed_fields['STAGE_ID'] = $new_stage;
		}
		return $changed_fields;
	}


	/**
	 * Information of properties changes
	 */

	public static function getDealChangedProps(array $order_data, array $deal_info, $profile) {
		$changed_fields = [];
		$deal = $deal_info['deal'];
		$deal_fields = $deal_info['fields'];
		$person_type = $order_data['PERSON_TYPE_ID'];
		$comp_table = (array)$profile['props']['comp_table'];
		foreach ($comp_table as $o_prop_id => $sync_params) {
			$d_f_code = $sync_params['value'];
			if ($deal_fields[$d_f_code] && ($sync_params['direction'] == 'all' || $sync_params['direction'] == 'stoc')) {
				$new_value = false;
				$deal_value = $deal[$d_f_code];
				// Properties
				foreach ($order_data['PROPERTIES'] as $prop) {
					$value = false;
					if ($prop['ID'] == $o_prop_id && $prop['PERSON_TYPE_ID'] == $person_type) {
//						\SProdIntegration::Log('(syncOrderToDeal) $prop: ' . print_r($prop, true));
						switch ($prop['TYPE']) {
							case 'ENUM':
								foreach ($prop['VALUE'] as $value_code) {
									foreach ($deal_fields[$d_f_code]['items'] as $deal_f_value) {
										if ($deal_f_value['VALUE'] == self::convEncForDeal($prop['OPTIONS'][$value_code])) {
											$new_value[] = $deal_f_value['ID'];
										}
									}
								}
								break;
							case 'FILE':
								foreach ($prop['VALUE'] as $file) {
									if ($file['ID']) {
										$path = $_SERVER['DOCUMENT_ROOT'] . $file['SRC'];
										$data = file_get_contents($path);
										$new_value[] = array(
											"fileData" => array(
												$file['ORIGINAL_NAME'],
												base64_encode($data)
											)
										);
									}
									else {
										$path = $file['tmp_name'];
										$data = file_get_contents($path);
										$new_value[] = array(
											"fileData" => array(
												$file['name'],
												base64_encode($data)
											)
										);
									}
								}
								break;
							case 'LOCATION':
								if (!is_array($prop['VALUE'])) {
									$prop['VALUE'] = array($prop['VALUE']);
								}
								foreach ($prop['VALUE'] as $p_value) {
									if ($p_value) {
										$new_value[] = self::convEncForDeal(\Bitrix\Sale\Location\Admin\LocationHelper::getLocationPathDisplay($p_value));
									}
									else {
										$new_value[] = '';
									}
								}
								break;
							case 'Y/N':
								$new_value[] = $prop['VALUE'][0] == 'Y' ? 1 : 0;
								break;
							case 'DATE':
								if ($deal_fields[$d_f_code]['type'] == 'date') {
									$value[] = date(ProfileInfo::DATE_FORMAT_PORTAL_SHORT, strtotime($prop['VALUE'][0]));
									$deal_value = date(ProfileInfo::DATE_FORMAT_PORTAL_SHORT, strtotime($deal_value));
								}
								else {
									$value[] = date(ProfileInfo::DATE_FORMAT_PORTAL, strtotime($prop['VALUE'][0]));
									$deal_value = date(ProfileInfo::DATE_FORMAT_PORTAL, strtotime($deal_value));
								}
								$new_value = self::convEncForDeal($value);
								break;
							default:
								if (is_array($prop['VALUE']) && count($prop['VALUE']) === 1 && !$prop['VALUE'][0]) {
									$prop['VALUE'] = [];
								}
								$new_value = self::convEncForDeal($prop['VALUE']);
						}
						break;
					}
				}

				if ($new_value !== false) {
//					\SProdIntegration::Log('(syncOrderToDeal) $new_value: ' . print_r($new_value, true));
//					\SProdIntegration::Log('(syncOrderToDeal) $deal_value: ' . print_r($deal_value, true));
					$deal_value = is_array($deal_value) ? $deal_value : (! $deal_value ? [] : [$deal_value]);
					if ( ! self::isEqual($new_value, $deal_value)) {
						if ($deal_fields[$d_f_code]['isMultiple']) {
							$changed_fields[$d_f_code] = $new_value;
						} else {
							$changed_fields[$d_f_code] = $new_value[0];
						}
					}
				}
			}
		}

		return $changed_fields;
	}


	/**
	 * Information of other order fields changes
	 */

	public static function getDealChangedOther(array $order_data, array $deal_info, $profile) {
		$changed_fields = [];
		$deal = $deal_info['deal'];
		$deal_fields = $deal_info['fields'];
		if ($deal_fields) {
			$comp_table = (array)$profile['other']['comp_table'];
			foreach ($comp_table as $o_prop_id => $sync_params) {
				$deal_code = $sync_params['value'];
				$deal_value = $deal[$deal_code];
				if ($deal_fields[$deal_code] && ($sync_params['direction'] == 'all' || $sync_params['direction'] == 'stoc')) {
					$new_value = [];
					// Order prop id
					$o_prop_id_cmp = $o_prop_id;
					$cmp_props = [
						'ORDER_ID' => 'ID',
						'ORDER_NUMBER' => 'ACCOUNT_NUMBER',
						'USER_TYPE' => 'PERSON_TYPE_NAME',
						'DATE_CREATE' => 'DATE_INSERT',
						'ORDER_LINK' => 'ID',
						'ORDER_LINK_PUBLIC' => 'PUBLIC_LINK',
						'DELIV_TYPE' => 'DELIVERY_TYPE',
						'PAY_STATUS' => 'IS_PAID',
						'PAY_DATE' => 'PAYMENT_DATE',
						'PAY_NUM' => 'PAYMENT_NUM',
						'PAY_SUM' => 'PAYMENT_SUM',
						'PAY_FACT' => 'PAYMENT_FACT',
						'PAY_LEFT' => 'PAYMENT_LEFT',
						'COUPON' => 'COUPONS',
						'DELIV_TRACKNUM' => 'TRACKING_NUMBER',
						'ORDER_STATUS' => 'STATUS_ID',
						'ORDER_STATUS_NAME' => 'STATUS_NAME',
						'DELIVERY_STORE' => 'STORE_NAME',
					];
					if (isset($cmp_props[$o_prop_id])) {
						$o_prop_id_cmp = $cmp_props[$o_prop_id];
					}
					// Process
					switch ($o_prop_id) {
						// Number
						case 'ORDER_ID':
						case 'ORDER_NUMBER':
						case 'DELIVERY_PRICE':
						case 'PAY_SUM':
						case 'PAY_FACT':
						case 'PAY_LEFT':
							$value       = $order_data[$o_prop_id_cmp];
							$new_value[] = $value;
							break;
						// String
						case 'USER_TYPE':
						case 'ORDER_LINK_PUBLIC':
						case 'USER_DESCRIPTION':
						case 'PAY_NUM':
						case 'DELIV_TRACKNUM':
							$value       = $order_data[$o_prop_id_cmp];
							$new_value[] = self::convEncForDeal($value);
							break;
						// String or list
						case 'DELIV_TYPE':
						case 'ORDER_STATUS':
						case 'PAY_TYPE':
						case 'PAY_ID':
						case 'ORDER_STATUS_NAME':
						case 'DELIVERY_STATUS':
						case 'DELIVERY_STATUS_NAME':
						case 'DELIVERY_COMPANY_NAME':
						case 'DELIVERY_STORE':
							if ($order_data[$o_prop_id_cmp]) {
								$store_value = $order_data[$o_prop_id_cmp];
								if ( ! empty($deal_fields[$deal_code]['items'])) {
									foreach ($deal_fields[$deal_code]['items'] as $deal_f_value) {
										if ($deal_f_value['VALUE'] == self::convEncForDeal($store_value)) {
											$new_value[] = $deal_f_value['ID'];
										}
									}
								} else {
									$new_value[] = self::convEncForDeal($store_value);
								}
							} else {
								$new_value[] = false;
							}
							break;
						// Date
						case 'DATE_CREATE':
							if ($deal_fields[$deal_code]['type'] == 'date') {
								$value = date(ProfileInfo::DATE_FORMAT_PORTAL_SHORT, $order_data[$o_prop_id_cmp]);
								$deal_value = date(ProfileInfo::DATE_FORMAT_PORTAL_SHORT, strtotime($deal_value));
							}
							else {
								$value = date(ProfileInfo::DATE_FORMAT_PORTAL, $order_data[$o_prop_id_cmp]);
								$deal_value = date(ProfileInfo::DATE_FORMAT_PORTAL, strtotime($deal_value));
							}
							$new_value[] = $value;
							break;
						case 'ORDER_LINK':
							$site_url    = Settings::get("site");
							$order_link  = $site_url . '/bitrix/admin/sale_order_view.php?ID=' . $order_data[$o_prop_id_cmp];
							$new_value[] = $order_link;
							break;
						// Manager comment
						case 'COMMENTS':
							$comments_val = $order_data[$o_prop_id_cmp];
							if ($deal_code == $o_prop_id_cmp) {
								$comments_val = nl2br($comments_val);
							}
							$new_value[] = self::convEncForDeal($comments_val);
							break;
						case 'PAY_STATUS':
							if ($order_data[$o_prop_id_cmp]) {
								$new_value[] = true;
							} else {
								$new_value[] = false;
							}
							break;
						case 'PAY_DATE':
							if ($order_data[$o_prop_id_cmp]) {
								$value       = $order_data[$o_prop_id_cmp]->format(ProfileInfo::DATE_FORMAT_PORTAL_SHORT);
								$new_value[] = self::convEncForDeal($value);
							}
							break;
						case 'COUPON':
							$value = implode(', ', $order_data['COUPONS']);
							$new_value[] = self::convEncForDeal($value);
							break;
						case 'DELIVERY_ALLOW':
						case 'DELIVERY_DEDUCTED':
							$new_value[] = $order_data[$o_prop_id_cmp] == 'Y' ? 1 : 0;
							break;
					}
					$deal_value = is_array($deal_value) ? $deal_value : (!$deal_value ? [] : [$deal_value]);
					if (!self::isEqual($new_value, $deal_value)) {
						if ($deal_fields[$deal_code]['isMultiple']) {
							$changed_fields[$deal_code] = $new_value;
						} else {
							$changed_fields[$deal_code] = $new_value[0];
						}
					}
				}
			}
		}
		return $changed_fields;
	}

	/**
	 * Get changes of basic deal fields
	 */

	public static function getDealChangedBasic(array $order_data, array $deal_info, $profile) {
		$changed_fields = [];
		$deal = $deal_info['deal'];
		// Assigned user
		if (Settings::get('link_responsibles') && $order_data['RESPONSIBLE_ID']) {
			$new_user_id = self::findCrmUser($order_data['RESPONSIBLE_ID'], $deal_info);
			if ($new_user_id && $deal['ASSIGNED_BY_ID'] != $new_user_id) {
				$changed_fields['ASSIGNED_BY_ID'] = $new_user_id;
			}
		}
		// Currency
		if ($order_data['CURRENCY'] && $deal['CURRENCY_ID'] != $order_data['CURRENCY']) {
			$changed_fields['CURRENCY_ID'] = $order_data['CURRENCY'];
		}
		return $changed_fields;
	}

	/**
	 * Search of deal
	 */

	public static function findDeal(array $order_data, $profile, $wo_categ=false) {
		$deal_id = false;
		$filter = [
			self::getOrderIDField() => $order_data['ID'],
		];
		if (!$wo_categ) {
			$category_id           = (int) $profile['options']['deal_category'];
			$filter['CATEGORY_ID'] = $category_id;
		}
		$source_id = Settings::get("source_id");
		if ($source_id) {
			$filter['ORIGINATOR_ID'] = $source_id;
		}
		$i = 0;
		while (!$deal_id && $i < 3) {
			if ($i > 0) {
				usleep(500000);
			}
			$res = Rest::execute('crm.deal.list', [
				'filter' => $filter,
			]);
			if ($res) {
				$deal_id = (int) $res[0]['ID'];
			}
			$i++;
		}
		\SProdIntegration::Log('(findDeal) order '.$order_data['ID'].' find deal '.$deal_id);
		return $deal_id;
	}

	/**
	 * Find CRM user
	 */

	public static function findCrmUser($store_user_id, array $deal_info=[]) {
		$crm_user_id = false;
		$res = \CUser::GetByID($store_user_id);
		$store_user = $res->Fetch();
		$user_email = $store_user['EMAIL'];
		if ($deal_info['assigned_user']['EMAIL'] && $user_email == $deal_info['assigned_user']['EMAIL']) {
			$crm_user_id = $deal_info['assigned_user']['ID'];
		}
		else {
			$res = Rest::execute('user.get', [
				'FILTER' => [
					'EMAIL' => $user_email,
				]
			]);
			if ($res[0]['ID']) {
				$crm_user_id = $res[0]['ID'];
			}
		}
		return $crm_user_id;
	}


	/**
	 * Find store user
	 */

	public static function findStoreUser(array $deal_info) {
		$store_user_id = false;
		if ($deal_info['assigned_user']['EMAIL']) {
			$user_email = $deal_info['assigned_user']['EMAIL'];
			$res = \Bitrix\Main\UserTable::getList([
				'select' => ['ID'],
				'filter' => [
					'EMAIL' => $user_email,
				],
			]);
			if ($user = $res->fetch()) {
				$store_user_id = $user['ID'];
			}
		}
		return $store_user_id;
	}



	/**
	 * Sync order with deal
	 */

	public static function syncOrderToDeal(array $order_data) {
		$incl_res = \Bitrix\Main\Loader::includeSharewareModule(self::MODULE_ID);
		if ($incl_res == \Bitrix\Main\Loader::MODULE_NOT_FOUND || $incl_res == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
			return;
		}
		if (!self::checkConnection()) {
			return;
		}
		// Check module active
		$sync_active = Settings::get("active");
		if (!$sync_active) {
			return;
		}
		// Check order data
		if (!$order_data) {
			return false;
		}
		// Check start date
		$start_date_ts = self::getStartDateTs();
		if ($start_date_ts && $order_data['DATE_INSERT'] < $start_date_ts) {
			return;
		}
		// Get profile
		$profile = self::getOrderProfile($order_data);
		if (!$profile) {
			return;
		}
		// Get deal
		$deal_id = self::findDeal($order_data, $profile);
		$order_id = $order_data['ID'];
		// Update fields of the deal
		if ($deal_id) {
			OrderAddLock::delete($order_id);
			$deal_info = self::getDealInfo($profile, $deal_id);
			$deal = $deal_info['deal'];
			$deal_new_fields = self::getDealChangedFields($order_data, $deal_info, $profile);
			// Update contact
			try {
				$contact_id = self::syncOrderToDealContact($order_data, $deal_info, $profile);
				if ($deal_info['deal']['CONTACT_ID'] != $contact_id) {
					$deal_new_fields['CONTACT_ID'] = $contact_id;
				}
			}
			catch (\Exception $e) {
				\SProdIntegration::Log('(syncOrderToDeal) can\'t sync of contact');
			}
			// Update deal
			self::updateDealFields($deal_id, $order_id, $deal_new_fields);
		}
		// Create a new deal
		else {
			if (!OrderAddLock::check($order_id, true)) {
				return;
			}
			// Check if deal of order doesn't exist on other categs
			if (!self::findDeal($order_data, $profile, true)) {
				$deal_info   = self::getDealInfo($profile);
				$deal_fields = self::getDealChangedFields($order_data, $deal_info, $profile);
				// Add contact
				try {
					$contact_id = self::syncOrderToDealContact($order_data, $deal_info, $profile);
					if ($contact_id) {
						$deal_fields['CONTACT_ID'] = $contact_id;
					}
				}
				catch (\Exception $e) {
					\SProdIntegration::Log('(syncOrderToDeal) can\'t sync of contact');
				}
				// Add company
				$company_id = self::syncOrderToDealCompany($order_data, $profile);
				if ($company_id) {
					$deal_fields['COMPANY_ID'] = $company_id;
				}
				// Add deal
				$deal        = self::createDealFromOrder($order_data, $deal_info, $profile, $deal_fields);
				$deal_id     = $deal['ID'];
				$deal_info   = self::getDealInfo($profile, $deal_id);
				$opt_direction = Settings::get("direction");
				if ( ! $opt_direction || $opt_direction == 'full' || $opt_direction == 'ctos') {
					self::updateOrderByNewDeal($deal, $order_data, $deal_info, $profile);
				}
			}
		}
		if ($deal) {
			// Update products
			self::updateDealProducts($deal_id, $order_data, $deal_info);
		}
	}


	/**
	 * Update order status
	 */

	public static function updateOrderStatus(array $deal, array $order_data, $profile, &$order) {
		$has_changes = false;
		// Formation of a table of correspondence fields
		$status_table = [];
		$tmp_table = (array)$profile['statuses']['comp_table'];
		foreach ($tmp_table as $o_status => $sync_params) {
			$d_stages = $sync_params['stages'];
			$d_stages = array_diff($d_stages, ['']);
			if (!empty($d_stages) && $sync_params['direction'] == 'all' || $sync_params['direction'] == 'ctos') {
				foreach ($d_stages as $d_stage) {
					$status_table[$d_stage][] = $o_status;
				}
			}
		}
		$cancel_table = (array)$profile['statuses']['cancel_stages'];
		// Formation of a data for saving
		$new_d_stage = $deal['STAGE_ID'];
		$new_o_statuses = $status_table[$new_d_stage];
		$cur_o_status = $order_data['STATUS_ID'];
		if ($new_d_stage && !empty($new_o_statuses) && !in_array($cur_o_status, $new_o_statuses)) {
			$order->setField('STATUS_ID', $new_o_statuses[0]);
			$has_changes = true;
		}
		if ($new_d_stage && !empty($cancel_table)) {
			if (in_array($new_d_stage, $cancel_table) && $order_data['IS_CANCELED'] != 'Y') {
				$order->setField('CANCELED', 'Y');
				if (Settings::get('cancel_pays_by_cancel_order')) {
					$payments = $order->getPaymentCollection();
					foreach ($payments as $payment) {
						$payment->setPaid('N');
						$payment->save();
					}
				}
				$has_changes = true;
			}
			elseif (!in_array($new_d_stage, $cancel_table) && $order_data['IS_CANCELED'] == 'Y') {
				$order->setField('CANCELED', 'N');
				$has_changes = true;
			}
		}
		return $has_changes;
	}


	/**
	 * Update order properties
	 */

	public static function updateOrderProps(array $deal, array $order_data, array $deal_f_info, $profile, &$order) {
		$has_changes = false;
		$person_type = $order_data['PERSON_TYPE_ID'];
		// Formation of a table of correspondence fields
		$comp_table = [];
		$tmp_table = (array)$profile['props']['comp_table'];
		foreach ($tmp_table as $o_prop_id => $sync_params) {
			$d_field_code = $sync_params['value'];
			if ($d_field_code && ($sync_params['direction'] == 'all' || $sync_params['direction'] == 'ctos')
			&& $order_data['PROPERTIES'][$o_prop_id]['PERSON_TYPE_ID'] == $person_type) {
				$comp_table[$d_field_code] = $o_prop_id;
			}
		}
		// Formation of a data for saving
		$property_collection = $order->getPropertyCollection();
		foreach ($comp_table as $d_field_code => $o_prop_id) {
			$prop = $order_data['PROPERTIES'][$o_prop_id];
			$prop_value = $property_collection->getItemByOrderPropertyId($prop['ID']);
			$new_value = [];
			if (!is_array($deal[$d_field_code])) {
				$deal[$d_field_code] = [$deal[$d_field_code]];
			}
			// Deal types
			foreach ($deal[$d_field_code] as $k => $deal_value) {
				if ($deal_f_info[$d_field_code]['type'] == 'money') {
					$arTmp = explode('|', $deal_value);
					$deal[$d_field_code][$k] = $arTmp[0];
				}
				if ($deal_f_info[$d_field_code]['type'] == 'boolean') {
					$deal[$d_field_code][$k] = $deal_value ? 'Y' : 'N';
				}
			}
			// Store types
			if ($prop['TYPE'] == 'LOCATION' || $prop['TYPE'] == 'FILE') {
				continue;
			}
			if ($prop['TYPE'] == 'ENUM') {
				foreach ($deal[$d_field_code] as $deal_value) {
					if (isset($deal_f_info[$d_field_code]['items'])) {
						foreach ($deal_f_info[$d_field_code]['items'] as $arDFValue) {
							if ($arDFValue['ID'] == $deal_value) {
								$deal_value = $arDFValue['VALUE'];
								break;
							}
						}
					}
					foreach ($prop['OPTIONS'] as $prop_code => $prop_val) {
						if ($prop_val == self::convEncForOrder($deal_value)) {
							$new_value[] = $prop_code;
						}
					}
				}
			}
//			elseif ($prop['TYPE'] == 'FILE') {
//				foreach ($deal[$d_field_code] as $deal_value) {
//					if ($deal_value['downloadUrl']) {
//						$app_info = Rest::getAppInfo();
//						$file = $app_info['portal'] . $deal_value['downloadUrl'];
//						$arFile = \CFile::MakeFileArray($file);
//						$arFile['name'] = strtolower(base64_encode($deal_value['id']));
//						$all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
//						$all_mimes = json_decode($all_mimes, true);
//						foreach ($all_mimes as $ext => $arTypes) {
//							if (array_search($arFile['type'], $arTypes) !== false) {
//								$arFile['name'] .= '.' . $ext;
//							}
//						}
//						$file_id = \CFile::SaveFile($arFile, "sale");
//						if ($file_id) {
//							$new_value[] = $file_id;
//						}
//					}
//				}
//			}
			elseif ($prop['TYPE'] == 'DATE') {
				$new_value = $deal[$d_field_code];
				if ($new_value[0]) {
					if ($prop['TIME'] == 'Y') {
						$new_value[0] = ConvertTimeStamp(strtotime($new_value[0]), "FULL", SITE_ID);
					}
					else {
						$new_value[0] = ConvertTimeStamp(strtotime($new_value[0]), "SHORT", SITE_ID);
					}
				}
				$new_value = self::convEncForOrder($new_value);
			}
			else {
				$new_value = self::convEncForOrder($deal[$d_field_code]);
			}
			// Has new value
			if (!self::isEqual($prop['VALUE'], $new_value)) {
				$new_value = count($new_value) == 1 ? $new_value[0] : $new_value;
				$prop_value->setValue($new_value);
				$has_changes = true;
				\SProdIntegration::Log('(updateOrderProps) order ' . $order_data['ID'] . ' new ' . $o_prop_id . ': ' . print_r($new_value, true));
			}
		}
		return $has_changes;
	}


	/**
	 * Update other order properties by deal fields
	 */

	public static function updateOrderParams(array $deal, array $order_data, array $deal_f_info, $profile, &$order) {
		$has_changes = false;
		// Formation of a table of correspondence fields
		$comp_table = [];
		$tmp_table = (array)$profile['other']['comp_table'];
		foreach ($tmp_table as $o_field_type => $sync_params) {
			$d_field_code = $sync_params['value'];
			if ($d_field_code && ($sync_params['direction'] == 'all' || $sync_params['direction'] == 'ctos')) {
				$comp_table[$d_field_code] = $o_field_type;
			}
		}
		// Formation of a data for saving
		foreach ($comp_table as $d_field_code => $o_field_type) {
			$f_has_changes = false;
			$new_value = [];
			if (!is_array($deal[$d_field_code])) {
				$deal[$d_field_code] = [$deal[$d_field_code]];
			}
			// Deal types
			foreach ($deal[$d_field_code] as $k => $deal_value) {
				if ($deal_f_info[$d_field_code]['type'] == 'money') {
					$arTmp = explode('|', $deal_value);
					$deal[$d_field_code][$k] = $arTmp[0];
				}
				if ($deal_f_info[$d_field_code]['type'] == 'date' && $deal_value) {
					$deal[$d_field_code][$k] = ConvertTimeStamp(strtotime($deal_value), "FULL", SITE_ID);
				}
				if ($deal_f_info[$d_field_code]['type'] == 'boolean') {
					$deal[$d_field_code][$k] = $deal_value ? 'Y' : 'N';
				}
			}
			// Store types
			switch ($o_field_type) {
				case 'DELIV_TRACKNUM':
					$new_value = self::convEncForOrder($deal[$d_field_code]);
					$new_value = $new_value[0];
					if ($new_value != $order_data['TRACKING_NUMBER']) {
						$shipment_collection = $order->getShipmentCollection();
						$shipment = $shipment_collection->current();
						if (is_object($shipment) && !$shipment->isSystem()) {
							$shipment->setFields([
								'TRACKING_NUMBER' => $new_value
							]);
							$has_changes = true;
							$f_has_changes = true;
						}
					}
					break;
				case 'PAY_STATUS':
					$new_value = self::convEncForOrder($deal[$d_field_code]);
					$new_value = $new_value[0];
					$cur_value = $order_data['IS_PAID'] ? 'Y' : 'N';
					if ($new_value != $cur_value) {
						$payments = $order->getPaymentCollection();
						foreach ($payments as $payment) {
							$payment->setPaid($new_value);
							$payment->save();
						}
						$has_changes = true;
						$f_has_changes = true;
					}
					break;
				case 'COMMENTS':
				case 'USER_DESCRIPTION':
					$new_value = self::convEncForOrder($deal[$d_field_code]);
					$new_value = $new_value[0];
					if ($new_value != $order_data[$o_field_type]) {
						$order->setField($o_field_type, $new_value);
						$has_changes = true;
						$f_has_changes = true;
					}
					break;
				default:
					continue;
			}
			if ($f_has_changes) {
				\SProdIntegration::Log('(updateOrderParams) order ' . $order_data['ID'] . ' new ' . $o_field_type . ': ' . print_r($new_value, true));
			}
		}
		return $has_changes;
	}

	/**
	 * Update other order properties
	 */

	public static function updateOrderOther(array $deal, array $order_data, array $deal_info, $profile, &$order) {
		$has_changes = false;
		// Responsible user
		if (Settings::get('link_responsibles')) {
			$user_id = self::findStoreUser($deal_info);
			if ($user_id && $user_id != $order_data['RESPONSIBLE_ID']) {
				$order->setField('RESPONSIBLE_ID', $user_id);
				$has_changes = true;
				\SProdIntegration::Log('(updateOrderOther) order ' . $order_data['ID'] . ' new responsible "' . $user_id . '"');
			}
		}
		return $has_changes;
	}

	/**
	 * Update order products
	 */

	public static function updateOrderProducts(array $deal_products, array $order_data, $profile, &$order) {
		$has_changes = false;
		// Get order products
		$order_products = [];
		foreach ($order_data['PRODUCTS'] as $item) {
			$order_products[$item['PRODUCT_ID']] = $item;
		}
		// Find changes
		$basket = $order->getBasket();
		foreach ($basket as $item) {
			$order_product = $order_products[$item->getProductId()];
			$crm_product = false;
			foreach ($deal_products as $deal_product) {
				if ($deal_product['PRODUCT_NAME'] == $order_product['PRODUCT_NAME']) {
					$crm_product = $deal_product;
					break;
				}
			}
			if ($crm_product) {
				// Change quantity
				if ((int)$crm_product['QUANTITY'] > 0 && (int)$crm_product['QUANTITY'] != (int)$order_product['QUANTITY']) {
					$item->setField('QUANTITY', (int)$crm_product['QUANTITY']);
					$has_changes = true;
					\SProdIntegration::Log('(updateOrderProducts) order '.$order_data['ID'].' product '.$item->getProductId().' new quantity '.(int)$crm_product['QUANTITY']);
				}
			}
			/*else {
				// Delete products
				\SProdIntegration::Log('(updateOrderProducts) order '.$order_data['ID'].' product '.$item->getProductId().' delete');
				$item->delete();
				$has_changes = true;
			}*/
		}
		return $has_changes;
	}


    /**
     * Sync deal with order
     */

	public static function syncDealToOrder($deal) {
        global $INTEGRATION_O_LOCK;
        $incl_res = \Bitrix\Main\Loader::includeSharewareModule(self::MODULE_ID);
        if ($incl_res == \Bitrix\Main\Loader::MODULE_NOT_FOUND || $incl_res == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
            return;
        }
        if (!self::checkConnection()) {
            return;
        }
	    // Check module active
	    $sync_active = Settings::get("active");
	    if (!$sync_active) {
		    return;
	    }
        //\SProdIntegration::Log(print_r($deal, true));
	    // Check source of deal
	    $source_id = Settings::get("source_id");
	    if ($source_id && $source_id != $deal['ORIGINATOR_ID']) {
		    \SProdIntegration::Log('(syncDealToOrder) source of deal error');
		    return;
	    }
	    // Check order data
	    $order_id = $deal[self::getOrderIDField()];
	    $order_data = false;
	    if ($order_id) {
		    $order = Sale\Order::load($order_id);
		    $order_data = self::getOrderInfo($order);
	    }
	    //\SProdIntegration::Log('(syncDealToOrder) order_data ' . print_r($order_data, true));
	    if (!$order_data) {
		    \SProdIntegration::Log('(syncDealToOrder) order data error');
		    return false;
	    }
	    // Check start date
		$start_date_ts = self::getStartDateTs();
	    if ($start_date_ts && $order_data['DATE_INSERT'] < $start_date_ts) {
		    \SProdIntegration::Log('(syncDealToOrder) start date error');
		    return;
	    }
	    // Get profile
	    $profile = self::getOrderProfile($order_data);
	    if (!$profile) {
		    \SProdIntegration::Log('(syncDealToOrder) profile info error');
		    return;
	    }
	    // Check category
		if ($profile['options']['deal_category'] != $deal['CATEGORY_ID']) {
			\SProdIntegration::Log('(syncDealToOrder) deal category error');
			return;
		}
		// Get info
	    $deal_info = self::getDealInfo($profile, $deal['ID']);
        // Update status
        $status_changed = self::updateOrderStatus($deal, $order_data, $profile, $order);
        // Update properties
        $props_changed = self::updateOrderProps($deal, $order_data, $deal_info['fields'], $profile, $order);
        // Update different params by fields
        $params_changed = self::updateOrderParams($deal, $order_data, $deal_info['fields'], $profile, $order);
        // Update other data
        $other_changed = self::updateOrderOther($deal, $order_data, $deal_info, $profile, $order);
        // Update order products
        $products_changed = self::updateOrderProducts($deal_info['products'], $order_data, $profile, $order);
	    \SProdIntegration::Log('(syncDealToOrder) order ' . $order_id . ' changed fields [status:' . $status_changed . ', props:' . $props_changed . ', params:' . $params_changed . ', other:' . $other_changed . ', products:' . $products_changed . ']');
        // Save changes
        if ($status_changed || $props_changed || $params_changed || $other_changed || $products_changed) {
	        $INTEGRATION_O_LOCK = $order_data['ID'];
	        \SProdIntegration::Log('(syncDealToOrder) update order ' . $order_id);
	        if (Settings::get('run_save_final_action') != 'disabled') {
		        $order->doFinalAction(true);
	        }
	        $result = $order->save();
	        if (!$result->isSuccess()) {
		        \SProdIntegration::Log('(syncDealToOrder) save order error: ' . print_r($result->getErrors(), true));
	        }
        }
    }

	public static function updateOrderByNewDeal($deal, $order_data, $deal_info, $profile) {
		// Check order data
		$order_id = $order_data['ID'];
		if ($order_id) {
			$order = Sale\Order::load($order_id);
		}
		// Update properties
		$props_changed = self::updateOrderProps($deal, $order_data, $deal_info['fields'], $profile, $order);
		// Save changes
		if ($props_changed) {
			\SProdIntegration::Log('(updateOrderByNewDeal) update order ' . $order_id);
			$order->doFinalAction(true);
			$order->save();
		}
	}


	/**
	 * Sync by event of property changed
	 *
	 * @param $order_id
	 * @param $status_id
	 */

	function eventOnSaleOrderSaved(\Bitrix\Main\Event $event) {
		global $INTEGRATION_O_LOCK, $USER;
		\SProdIntegration::setLogLabel();
		if (!\Bitrix\Main\Loader::includeModule(self::MODULE_ID)) {
			return;
		}
		if (!self::checkConnection()) {
			return;
		}
		// Get the order
		$order = $event->getParameter("ENTITY");
		$order_data = self::getOrderInfo($order);
		$order_id = $order_data['ID'];
		\SProdIntegration::Log('(eventOnSaleOrderSaved) call for order ' . $order_id . ' by user ' . $USER->GetID());
		if ($order_id) {
			\SProdIntegration::Log('(eventOnSaleOrderSaved) run sync');
			// Protection against duplication
			$is_new = $event->getParameter("IS_NEW");
			if ($is_new) {
				\SProdIntegration::Log('(eventOnSaleOrderSaved) new order ' . $order_id);
			}
			// Sync
			Rest::sendBgrRequest("/bitrix/sprod_integr_bgr_run.php", [
				'order_data' => serialize($order_data),
				'new' => $is_new,
			]);
		}
	}


	/**
	 * Deal data
	 */

	public static function getDeal($deals_ids) {
		$deals = [];
		if (is_array($deals_ids) && !empty($deals_ids)) {
			$req_list = [];
			foreach ($deals_ids as $i => $deals_id) {
				$req_list[$i] = 'crm.deal.get' . '?' . http_build_query([
						'id' => $deals_id,
					]);
			}
			$resp = Rest::execute('batch', [
				"halt"  => false,
				"cmd" => $req_list,
			]);
			if ($resp['result']) {
				foreach ($resp['result'] as $deal) {
					$deal['LINK'] = Settings::get("portal") . '/crm/deal/details/' . $deal['ID'] . '/';
					$deals[] = $deal;
				}
			}
		}
		return $deals;
	}

	/**
	 * CRM info for sync process
	 */

	public static function getDealInfo($profile, $deal_id=0) {
		$info = [
			'deal' => [],
			'fields' => [],
			'stages' => [],
			'contact' => [],
			'company' => [],
			'products' => [],
			'product_fields' => [],
			'assigned_user' => [],
		];
		$request = [];
		if ($deal_id) {
			$request['deal'] = [
				'method' => 'crm.deal.get',
				'params' => ['id' => $deal_id]
			];
			$request['contact'] = [
				'method' => 'crm.contact.get',
				'params' => [
					'id' => '$result[deal][CONTACT_ID]',
				]
			];
			$request['assigned_user'] = [
				'method' => 'user.get',
				'params' => [
					'id' => '$result[deal][ASSIGNED_BY_ID]',
				]
			];
			$request['products'] = [
				'method' => 'crm.deal.productrows.get',
				'params' => [
					'id' => $deal_id,
				]
			];
		}
		$request['fields'] = [
			'method' => 'crm.deal.fields',
		];
		$dealcateg_id = (int)$profile['options']['deal_category'];
		if (!$dealcateg_id) {
			$request['stages'] = [
				'method' => 'crm.status.list',
				'params' => [
					'order' => ['SORT' => 'ASC'],
					'filter' => [
						'ENTITY_ID' => 'DEAL_STAGE',
					]
				]
			];
		}
		else {
			$request['stages'] = [
				'method' => 'crm.dealcategory.stage.list',
				'params' => [
					'id' => $dealcateg_id,
				]
			];
		}
		$request['product_fields'] = [
			'method' => 'crm.product.fields',
		];
		$info = array_merge($info, Rest::batch($request));
		if (!empty($info['assigned_user'])) {
			$info['assigned_user'] = $info['assigned_user'][0];
		}
		return $info;
	}


	/**
	 * Order data
	 */

	public static function getOrderInfo($order) {
		$order_data = false;
		if ($order) {
			$order_data['ID'] = $order->getId();
			$order_data['SITE_ID'] = $order->getSiteId();
			if ($order->getDateInsert()) {
				$order_data['DATE_INSERT'] = $order->getDateInsert()->getTimestamp();
			}
			$order_data['STATUS_ID'] = $order->getField('STATUS_ID');
			$res = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
				'filter' => [
					'STATUS.ID'=>$order_data['STATUS_ID'],
					'LID'=>LANGUAGE_ID,
				],
				'select' => ['NAME'],
			));
			if ($status_lang = $res->fetch()) {
				$order_data['STATUS_NAME'] = $status_lang['NAME'];
			}
			$order_data['PERSON_TYPE_ID'] = $order->getPersonTypeId();
			$persons_types = \Bitrix\Sale\PersonType::load(false, $order->getPersonTypeId());
			$order_data['PERSON_TYPE_NAME'] = $persons_types[$order->getPersonTypeId()]['NAME'];
			$order_data['USER_ID'] = $order->getUserId();
			$order_data['USER_GROUPS_ID'] = [];
			$db = \Bitrix\Main\UserGroupTable::getList(array(
				'filter' => array('USER_ID'=>$order->getUserId(), 'GROUP.ACTIVE'=>'Y'),
				'select' => array('GROUP_ID', 'GROUP_CODE'=>'GROUP.STRING_ID'),
				'order' => array('GROUP.C_SORT'=>'ASC'),
			));
			while ($item = $db->fetch()) {
				$order_data['USER_GROUPS_ID'][] = $item['GROUP_ID'];
			}
			$order_data['RESPONSIBLE_ID'] = $order->getField('RESPONSIBLE_ID');
			$order_data['PRICE'] = $order->getPrice();
			$order_data['DISCOUNT_PRICE'] = $order->getDiscountPrice();
			$order_data['DELIVERY_PRICE'] = $order->getDeliveryPrice();
			$order_data['SUM_PAID'] = $order->getSumPaid();
			$order_data['CURRENCY'] = $order->getCurrency();
			$order_data['IS_PAID'] = $order->isPaid();
			$order_data['ID_ALLOW_DELIVERY'] = $order->isAllowDelivery();
			$order_data['IS_SHIPPED'] = $order->isShipped();
			$order_data['IS_CANCELED'] = $order->isCanceled();
			$order_data['ACCOUNT_NUMBER'] = $order->getField('ACCOUNT_NUMBER');
			if ($order->getField('DATE_UPDATE')) {
				$order_data['DATE_UPDATE'] = $order->getField('DATE_UPDATE')->getTimestamp();
			}
			$order_data['COMMENTS'] = $order->getField('COMMENTS');
			$order_data['USER_DESCRIPTION'] = $order->getField('USER_DESCRIPTION');
			if (\Bitrix\Sale\Helpers\Order::isAllowGuestView($order)) {
				$order_data['PUBLIC_LINK'] = \Bitrix\Sale\Helpers\Order::getPublicLink($order);
			}
			// Properties
			$property_collection = $order->getPropertyCollection();
			$property_data = $property_collection->getArray();
			$order_data['PROPERTIES'] = [];
			foreach ($property_data['properties'] as $prop) {
				$order_data['PROPERTIES'][$prop['ID']] = $prop;
			}
			$order_data['PROP_GROUPS'] = $property_data['groups'];
			// Delivery data
			$shipment_collection = $order->getShipmentCollection();
			$shipment = $shipment_collection->current();
			if (is_object($shipment)) {
				$order_data['DELIVERY_TYPE_ID'] = $shipment->getField('DELIVERY_ID');
				$order_data['DELIVERY_TYPE'] = $shipment->getField('DELIVERY_NAME');
				$order_data['DELIVERY_STATUS'] = $shipment->getField('STATUS_ID');
				$stat_res = \Bitrix\Sale\StatusLangTable::getList([
					'filter' => [
						'STATUS_ID' => $shipment->getField('STATUS_ID'),
						'LID' => LANGUAGE_ID,
					]
				]);
				$order_data['DELIVERY_STATUS_NAME'] = '';
				if ($item = $stat_res->fetch()) {
					$order_data['DELIVERY_STATUS_NAME'] = $item['NAME'];
				}
				$order_data['DELIVERY_ALLOW'] = $shipment->getField('ALLOW_DELIVERY');
				$order_data['DELIVERY_DEDUCTED'] = $shipment->getField('DEDUCTED');
				$order_data['TRACKING_NUMBER'] = $shipment->getField('TRACKING_NUMBER');
				$order_data['STORE_ID'] = $shipment->getStoreId();
				if ($order_data['STORE_ID']) {
					$res = \Bitrix\Catalog\StoreTable::getById($order_data['STORE_ID']);
					$store = $res->fetch();
					$order_data['STORE_NAME'] = $store['TITLE'];
				}
			}
			$order_data['DELIVERY_COMPANY_NAME'] = '';
			if ($order->getField('COMPANY_ID')) {
				$res = \Bitrix\Sale\CompanyTable::getById($order->getField('COMPANY_ID'));
				$company = $res->fetch();
				$order_data['DELIVERY_COMPANY_NAME'] = $company['NAME'];
			}
			// Payment data
//			$order_data['IS_PAID'] = false;
			$payment_collection = $order->getPaymentCollection();
			if (is_object($payment_collection->current())) {
				$order_data['PAY_TYPE'] = $payment_collection->current()->getPaymentSystemName();
				$order_data['PAY_ID'] = $payment_collection->current()->getId();
//				if ($payment_collection->isPaid()) {
//					$order_data['IS_PAID'] = true;
//				}
			}
			$order_data['PAYMENT_NUM'] = $order->getField("PAY_VOUCHER_NUM");
			$order_data['PAYMENT_DATE'] = $order->getField("PAY_VOUCHER_DATE");
			// Paid sum
			$order_data['PAYMENT_SUM'] = $payment_collection->getSum();
			$order_data['PAYMENT_FACT'] = $payment_collection->getPaidSum();
			$order_data['PAYMENT_LEFT'] = $order_data['PAYMENT_SUM'] - $order_data['PAYMENT_FACT'];
			// Coupons
			$discount = $order->getDiscount()->getApplyResult();
			$coupons = [];
			if (!empty($discount['COUPON_LIST'])) {
				foreach ($discount['COUPON_LIST'] as $coupon) {
					$coupons[] = $coupon['COUPON'];
				}
			}
			$order_data['COUPONS'] = $coupons;
			// Products (with properties)
			$prod_res = \Bitrix\Sale\Basket::getList([
				'filter' => [
					'=ORDER_ID' => $order->getId(),
				]
			]);
			$product_items = [];
			while ($item = $prod_res->fetch()) {
				$bskt_res = \Bitrix\Sale\Internals\BasketPropertyTable::getList([
					'order' => [
						"SORT" => "ASC",
						"ID" => "ASC"
					],
					'filter' => [
						"BASKET_ID" => $item['ID'],
					],
				]);
				$item['PROPS'] = [];
				while ($property = $bskt_res->fetch()) {
					$k = $property['CODE'] ? $property['CODE'] : $property['ID'];
					$item['PROPS'][$k] = $property['VALUE'];
				}
				$product_items[] = $item;
			}
			$complects_sync_type = Settings::get('products_complects');
			$order_data['PRODUCTS'] = [];
			foreach ($product_items as $item) {
				if (!$item['SET_PARENT_ID']) {
					// Name of product
					$prod_name = $item['NAME'];
					$opt_prod_name_props = Settings::get("products_name_props", true);
					$opt_prod_name_props_delim = Settings::get("products_name_props_delim");
					foreach ($opt_prod_name_props as $prop_code) {
						if ($prop_code && isset($item['PROPS'][$prop_code])) {
							$prod_name .= $opt_prod_name_props_delim . $item['PROPS'][$prop_code];
						}
					}
					$order_data['PRODUCTS'][] = [
						'PRODUCT_ID'   => $item['PRODUCT_ID'],
						'PRODUCT_NAME' => $prod_name,
						'PRICE'        => $item['PRICE'],
						'DISCOUNT_SUM' => $item['DISCOUNT_PRICE'],
						'QUANTITY'     => $item['QUANTITY'],
						'MEASURE_NAME' => $item['MEASURE_NAME'],
						'MEASURE_CODE' => $item['MEASURE_CODE'],
						'VAT_RATE'     => $item['VAT_RATE'] * 100,
						'VAT_INCLUDED' => $item['VAT_INCLUDED'],
						'PROPS'        => $item['PROPS'],
					];
					if ($complects_sync_type == 'prod') {
						foreach ($product_items as $item2) {
							if ($item2['SET_PARENT_ID'] == $item['ID']) {
								$order_data['PRODUCTS'][] = [
									'PRODUCT_ID'   => $item2['PRODUCT_ID'],
									'PRODUCT_NAME' => $item['NAME'] . ': ' . $item2['NAME'],
									'PRICE'        => 0,
									'DISCOUNT_SUM' => 0,
									'QUANTITY'     => $item2['QUANTITY'],
									'MEASURE_NAME' => $item2['MEASURE_NAME'],
									'MEASURE_CODE' => $item2['MEASURE_CODE'],
									'VAT_RATE'     => $item2['VAT_RATE'] * 100,
									'VAT_INCLUDED' => $item2['VAT_INCLUDED'],
								];
							}
						}
					}
				}
			}
		}
		return $order_data;
	}


    /**
     * Blocking orders
     */

	public static function resetAllOrdersLocks() {
		Settings::save("orders_lock", [], true);
		return true;
	}


    /**
     * Sync all orders by period
     *
     * @param $sync_period
     */

    function syncStoreToCRM($sync_period=0) {
        global $DB;
	    if (self::checkConnection()) {
		    Rest::setBulkRun();
		    \SProdIntegration::Log('(syncStoreToCRM) run period ' . $sync_period);
		    // List of orders, changed by last period (if period is not set than get all orders)
	        $filter = [];
	        if ($sync_period > 0) {
	            $filter['>DATE_UPDATE'] = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")), time() - $sync_period);
	        }
	        $select = ['ID'];
	        $db = \CSaleOrder::GetList(["DATE_UPDATE" => "DESC"], $filter, false, false, $select);
		    while ($order_item = $db->Fetch()) {
			    $order      = Sale\Order::load($order_item['ID']);
			    $order_data = Integration::getOrderInfo($order);
			    OrderAddLock::add($order_data['ID']);
			    try {
				    self::syncOrderToDeal($order_data);
			    }
			    catch (\Exception $e) {
				    \SProdIntegration::Log('(syncStoreToCRM) can\'t sync of order ' . $order_data['ID']);
			    }
	        }
		    \SProdIntegration::Log('(syncStoreToCRM) success');
	    }
    }


	/**
	 * Check system parameters
	 */

	function checkModuleStatus() {
		$res = [
			'auth_file' => false,
			'store_handler_file' => false,
			'crm_handler_file' => false,
			'app_info' => false,
			'auth_info' => false,
			'connect' => false,
			'store_events' => false,
			'crm_events' => false,
			'profiles' => false,
			'crm_events_uncheck' => false,
		];
		// Site base directory
		$site_default = \SProdIntegration::getSiteDef();
		$abs_root_path = $_SERVER['DOCUMENT_ROOT'] . $site_default['DIR'];
		// Check auth file
		if (file_exists($abs_root_path . 'bitrix/sprod_integr_auth.php')) {
			$res['auth_file'] = true;
		}
		// Check handler files
		if (file_exists($abs_root_path . 'bitrix/sprod_integr_bgr_run.php')) {
			$res['store_handler_file'] = true;
		}
		if (file_exists($abs_root_path . 'bitrix/sprod_integr_handler.php')) {
			$res['crm_handler_file'] = true;
		}
		// Availability of B24 application data
		if (Rest::getAppInfo()) {
			$res['app_info'] = true;
			// Availability of connection data
			if (Rest::getAuthInfo()) {
				$res['auth_info'] = true;
			}
		}
		// Has active profiles
		if (self::checkActiveProfiles()) {
			$res['profiles'] = true;
		}
		if ($res['app_info'] && $res['auth_info']) {
			// Availability of an order change handler
			if (self::checkStoreHandlers()) {
				$res['store_events'] = true;
			}
			// Relevance of data for connecting to B24
			$resp = Rest::execute('app.info', [], false, true, false);
			if ($resp && !$resp['error']) {
				$res['connect'] = true;
				// Availability of a deal change handler
				if (self::checkCrmHandlers()) {
					$res['crm_events'] = true;
				}
				if (Settings::get('direction') == 'ctos') {
					$res['crm_events_uncheck'] = true;
				}
			}
		}

		return $res;
	}


    /**
     * Utilites
     */

    // Convert encoding
    function convEncForDeal($value) {
	    if (!\SProdIntegration::isUtf()) {
		    $value = \Bitrix\Main\Text\Encoding::convertEncoding($value, "Windows-1251", "UTF-8");
	    }
        return $value;
    }

    // Convert encoding
    function convEncForOrder($value) {
	    if (!\SProdIntegration::isUtf()) {
		    $value = \Bitrix\Main\Text\Encoding::convertEncoding($value, "UTF-8", "Windows-1251");
	    }
        return $value;
    }

    // Get prefix option
    public static function getPrefix($profile) {
        $prefix = $profile['options']['prefix'];
        return $prefix;
    }

    // Get CRM order title
    function getOrdTitleWithPrefix(array $order_data, $profile) {
        $prefix = self::getPrefix($profile);
        $order_num = $order_data['ACCOUNT_NUMBER'];
	    $title = $prefix . $order_num;
        return $title;
    }

    // Check CRM order title
    function checkPrefix($deal, $profile) {
        $title = self::convEncForOrder($deal['TITLE']);
        $res = true;
        $prefix = self::getPrefix($profile);
        if ($prefix) {
            if (strpos($title, $prefix) !== 0) {
                $res = false;
            }
        }
        return $res;
    }

    // Values equal check
	public static function isEqual($order_value, $deal_value) {
    	$res = false;
    	if ($order_value == [false]) {
		    $order_value = [];
	    }
    	if ($deal_value == [false]) {
		    $deal_value = [];
	    }
    	if ( !is_array($order_value) && !is_array($deal_value)) {
    		if ($order_value == $deal_value) {
			    $res = true;
		    }
	    }
    	elseif (is_array($order_value) && is_array($deal_value)) {
    		if (count($order_value) == count($deal_value)) {
    			$res = true;
			    foreach ($order_value as $k => $value) {
				    if ($value != $deal_value[$k]) {
					    $res = false;
				    }
    			}
			    foreach ($deal_value as $k => $value) {
				    if ($value != $order_value[$k]) {
					    $res = false;
				    }
    			}
		    }
	    }
    	return $res;
	}

	public static function getStartDateTs() {
		$start_date_ts = false;
		$start_date = Settings::get("start_date");
		if ($start_date) {
			$start_date_ts = strtotime(date('d.m.Y 00:00:00', strtotime($start_date)));
		}
		return $start_date_ts;
	}
}