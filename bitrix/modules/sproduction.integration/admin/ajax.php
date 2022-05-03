<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_lib.php");

error_reporting(E_ERROR);

global $USER;

if (!$USER->IsAuthorized()) {
	return false;
}

CModule::IncludeModule("sproduction.integration");
CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");

use SProduction\Integration\Integration,
	SProduction\Integration\Settings,
	SProduction\Integration\Rest,
	SProduction\Integration\ProfilesTable,
	SProduction\Integration\ProfileInfo,
	SProduction\Integration\AddSync,
	SProduction\Integration\CrmProducts,
	SProduction\Integration\CrmCompany,
	SProduction\Integration\StoreProducts,
	Bitrix\Main\Context,
    Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Currency\CurrencyManager,
    Bitrix\Sale;

Loc::loadMessages(__FILE__);

$params = json_decode(file_get_contents('php://input'), true);

$action = trim($_REQUEST['action'] ?? '');
$arResult = [];
$arResult['status'] = 'error';
$arResult['log'] = [];
$lock_result = false;

\SProdIntegration::Log('action: '.$action."\n".'params: '.print_r($params, true));

try {
	switch ($action) {

		/**
		 * Global functions
		 */

		// Main check
		case 'main_check':
			$errors = [];
			$warnings = [];
			$main_errors = \SProdIntegration::mainCheck();
			$errors = array_merge($errors, $main_errors);
			$incl_res = \Bitrix\Main\Loader::includeSharewareModule('sproduction.integration');
			if ($incl_res == \Bitrix\Main\Loader::MODULE_DEMO) {
				$days = \SProdIntegration::getDemoDaysLeft();
				$term_phrase = \SProdIntegration::declineWord($days, Loc::getMessage("SP_CI_WARN_MODULE_DEMO_DAYS_TERM1", ['#DAYS#' => $days]), Loc::getMessage("SP_CI_WARN_MODULE_DEMO_DAYS_TERM2", ['#DAYS#' => $days]), Loc::getMessage("SP_CI_WARN_MODULE_DEMO_DAYS_TERM3", ['#DAYS#' => $days]));
				$warnings[] = Loc::getMessage("SP_CI_WARN_MODULE_DEMO_DAYS", ['#TERM_PHRASE#' => $term_phrase]);
			}
			$arResult['errors'] = $errors;
			$arResult['warnings'] = $warnings;
			break;

		/**
		 * Main settings
		 */

		// Data of all blocks
		case 'settings_get':
			$main_errors = \SProdIntegration::mainCheck();
			if ( ! empty($main_errors)) {
				$arResult['blocks']['connect']['state']['display'] = false;
				$arResult['blocks']['sync']['state']['display'] = false;
				$arResult['blocks']['active']['state']['display'] = false;
				$arResult['blocks']['profiles']['state']['display'] = false;
				$arResult['blocks']['add_sync']['state']['display'] = false;
				$arResult['blocks']['man_sync']['state']['display'] = false;
				$arResult['status'] = 'ok';
			} else {
				$app_info = Rest::getAppInfo();
				$auth_info = Rest::getAuthInfo();
				$sync_active = Settings::get('active');
				// Connection settings
				$arResult['blocks']['connect']['state'] = [
					'display' => true,
					'active'  => true,
				];
				$site_def = SProdIntegration::getSiteDef();
				$site_def_addr = 'https://' . $site_def['SERVER_NAME'];
				$site = Settings::hasRecord("site") ? Settings::get("site") : $site_def_addr;
				$arResult['blocks']['connect']['fields'] = [
					'site'   => $site,
					'portal' => Settings::get("portal"),
					'app_id' => Settings::get("app_id"),
					'secret' => Settings::get("secret"),
				];
				if ($app_info && ! $auth_info) {
					$arResult['blocks']['connect']['fields']['auth_link'] = Rest::getAuthLink();
				}
				$arResult['blocks']['connect']['info'] = [
					'has_cred' => ($auth_info),
				];
				// Synchronization params
				$arResult['blocks']['sync']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info),
				];
				$direction = Settings::get("direction");
				$arResult['blocks']['sync']['fields'] = [
					'source_id'   => Settings::get("source_id"),
					'source_name' => Settings::get("source_name"),
					'direction'   => $direction ? $direction : 'stoc',
					'start_date'  => Settings::get("start_date"),
				];
				// Synchronization active
				$arResult['blocks']['active']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info),
				];
				$arResult['blocks']['active']['fields'] = [
					'active' => Settings::get("active"),
				];
				// Profiles warning
				$arResult['blocks']['profiles']['state'] = [
					'display' => (Settings::get("active") && ! Integration::checkActiveProfiles()),
				];
				// Additional synchronization
				$arResult['blocks']['add_sync']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info && $sync_active),
				];
				$arResult['blocks']['add_sync']['fields'] = [
					'add_sync_schedule' => Settings::get("add_sync_schedule"),
				];
				// Manual synchronization
				$arResult['blocks']['man_sync']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info && $sync_active),
				];
				$arResult['blocks']['man_sync']['fields'] = [
					'man_sync_period'   => Settings::get("man_sync_period"),
					'man_sync_only_new' => Settings::get("man_sync_only_new"),
				];
				$arResult['errors'] = [];
				$arResult['status'] = 'ok';
			}
			break;

		// Data save
		// Connection settings (saving)
		case 'settings_connect_save':
			$fields = $params['fields'];
			$old_data = [
				'site'   => Settings::get("site"),
				'portal' => Settings::get("portal"),
				'app_id' => Settings::get("app_id"),
				'secret' => Settings::get("secret"),
			];
			$new_data = [
				'site'   => $fields["site"],
				'portal' => $fields["portal"],
				'app_id' => $fields["app_id"],
				'secret' => $fields["secret"],
			];
			// Reset placements and event handlers
			$sync_active = Settings::get('active');
			if ( ! empty(array_diff($new_data, $old_data)) && $sync_active) {
				SProdIntegration::removePortalPlacements();
				Integration::unregCrmHandlers();
			}
			// Save data
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Reset connection data
			if ( ! empty(array_diff($new_data, $old_data))) {
				Rest::saveAuthInfo('');
			}
			break;
		// Reset connection
		case 'settings_connect_reset':
			// Reset placements and event handlers
			$sync_active = Settings::get('active');
			if ($sync_active) {
				SProdIntegration::removePortalPlacements();
				Integration::unregCrmHandlers();
			}
			// Reset connection
			Rest::saveAuthInfo('');
			$arResult['status'] = 'ok';
			break;
		// Synchronization params (saving)
		case 'settings_sync_save':
			$old_data = [
				'source_id'   => Settings::get("source_id"),
				'source_name' => Settings::get("source_name"),
				'direction'   => Settings::get("direction"),
				'start_date'  => Settings::get("start_date"),
			];
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Additional actions
			$new_data = [
				'source_id'   => Settings::get("source_id"),
				'source_name' => Settings::get("source_name"),
				'direction'   => Settings::get("direction"),
				'start_date'  => Settings::get("start_date"),
			];
			// Refresh placements
			if ($new_data['source_name'] != $old_data['source_name']) {
				SProdIntegration::removePortalPlacements();
				SProdIntegration::setPortalPlacements();
			}
			// Refresh event handlers
			if ($new_data['direction'] != $old_data['direction']) {
				Integration::unregCrmHandlers();
				Integration::regCrmHandlers();
			}
			break;
		// Synchronization active (saving)
		case 'settings_active_save':
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Additional actions
			$sync_active = Settings::get('active');
			if ($sync_active) {
				if ( ! Integration::checkStoreHandlers()) {
					Integration::regStoreHandlers();
				}
				if ( ! Integration::checkCrmHandlers()) {
					Integration::regCrmHandlers();
				}
				SProdIntegration::setPortalPlacements();
			} else {
				if (Integration::checkStoreHandlers()) {
					Integration::unregStoreHandlers();
				}
				if (Integration::checkCrmHandlers()) {
					Integration::unregCrmHandlers();
				}
				SProdIntegration::removePortalPlacements();
			}
			break;
		// Additional synchronization (saving)
		case 'settings_add_sync_save':
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Additional actions
			AddSync::set();
			break;
		// Manual synchronization (saving)
		case 'settings_man_sync_save':
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Additional actions
			break;

//	// Connection settings (check)
//	case 'settings_connect_check':
//		$arResult['errors'] = [];
//		$arResult['status'] = 'ok';
//		break;


		/**
		 * General settings
		 */

		// Data of all blocks
		case 'general_get':
			$main_errors = \SProdIntegration::mainCheck();
			if ( ! empty($main_errors)) {
				$arResult['blocks']['main']['state']['display'] = false;
				$arResult['blocks']['products']['state']['display'] = false;
				$arResult['blocks']['prodsync']['state']['display'] = false;
				$arResult['blocks']['prodsearch']['state']['display'] = false;
				$arResult['status'] = 'ok';
			} else {
				$app_info = Rest::getAppInfo();
				$auth_info = Rest::getAuthInfo();
				// Main params
				$arResult['blocks']['main']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info),
				];
				$arResult['blocks']['main']['fields'] = [
					'contacts_sync_mode'          => Settings::get("contacts_sync_mode"),
					'contacts_phonemail_mode'     => Settings::get("contacts_phonemail_mode"),
					'cancel_pays_by_cancel_order' => Settings::get("cancel_pays_by_cancel_order"),
					'link_responsibles'           => Settings::get("link_responsibles"),
					'crm_orderid_field'           => Settings::get("crm_orderid_field"),
				];
				$arResult['blocks']['main']['info'] = [];
				$arResult['blocks']['main']['info']['crm_orderid_fields'] = ProfileInfo::getCRMOrderIDFields();
				// Product list params
				$arResult['blocks']['products']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info),
				];
				$arResult['blocks']['products']['fields'] = [
					'products_no_discounts'     => Settings::get("products_no_discounts"),
					'products_name_props'       => Settings::get("products_name_props", true),
					'products_name_props_delim' => Settings::get("products_name_props_delim"),
					'products_complects'        => Settings::get("products_complects"),
					'products_delivery'         => Settings::get("products_delivery"),
					'products_deliv_prod_active' => Settings::get("products_deliv_prod_active"),
					'products_deliv_prod_list'  => Settings::get("products_deliv_prod_list", true),
					'products_sync_type'        => Settings::get("products_sync_type"),
					'products_root_section'     => Settings::get("products_root_section"),
					'products_group_by_orders'  => Settings::get("products_group_by_orders"),
					'products_iblock'           => Settings::get("products_iblock"),
				];
				$arResult['blocks']['products']['info'] = [
					'sections' => CrmProducts::getSectHierarchy(),
					'iblocks'  => ProfileInfo::getStoreIblockList(true),
					'delivery_list'  => ProfileInfo::getStoreDeliveryList(),
				];
				// Params of products synchronization
				$products_iblock = (int) Settings::get("products_iblock");
				$arResult['blocks']['prodsync']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info &&
						(Settings::get("products_sync_type") == 'create' || Settings::get("products_sync_type") == 'mixed') &&
						$products_iblock),
				];
				$arResult['blocks']['prodsync']['fields'] = [
					'products_comp_table' => Settings::get("products_comp_table", true),
				];
				$arResult['blocks']['prodsync']['info'] = [
					'products_iblock'   => $products_iblock,
					'store_prod_fields' => false,
					'crm_prod_fields'   => false,
				];
				if ($products_iblock) {
					$arResult['blocks']['prodsync']['info']['store_prod_fields'] = CrmProducts::getStoreFields($products_iblock);
					$arResult['blocks']['prodsync']['info']['crm_prod_fields'] = CrmProducts::getCRMFields();
				}
				// Params of products search
				$products_iblock = (int) Settings::get("products_iblock");
				$arResult['blocks']['prodsearch']['state'] = [
					'display' => true,
					'active'  => ($app_info && $auth_info &&
						(Settings::get("products_sync_type") == 'find' || Settings::get("products_sync_type") == 'mixed')),
				];
				$arResult['blocks']['prodsearch']['fields'] = [
					'products_search_store_fields' => CrmProducts::getSearchFields(),
					'products_search_crm_field'    => Settings::get("products_search_crm_field"),
				];
				$iblocks = ProfileInfo::getStoreIblockList(true);
				$arResult['blocks']['prodsearch']['info'] = [
					'products_iblock'   => $products_iblock,
					'store_prod_fields' => false,
					'crm_prod_fields'   => false,
					'iblocks'           => $iblocks,
				];
				$info_store_prod_fields = [];
				foreach ($iblocks as $iblock) {
					$info_store_prod_fields[$iblock['id']] = CrmProducts::getStoreFieldsForID($iblock['id']);
				}
				$arResult['blocks']['prodsearch']['info']['store_prod_fields'] = $info_store_prod_fields;
				$arResult['blocks']['prodsearch']['info']['crm_prod_fields'] = CrmProducts::getCRMFieldsForID();
				$arResult['errors'] = [];
				$arResult['status'] = 'ok';
			}
			break;

		// Data save
		// Main params (saving)
		case 'general_main_save':
			// Product list params (saving)
		case 'general_products_save':
			// Products sync params (saving)
		case 'general_prodsync_save':
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields as $name => $value) {
					if ($name == 'products_name_props' ||
						$name == 'products_comp_table' ||
						$name == 'products_deliv_prod_list') {
						$value = serialize($value);
					}
					Settings::save($name, $value);
				}
			}
			$arResult['status'] = 'ok';
			// Additional actions
			break;
		// Products search params (saving)
		case 'general_prodsearch_save':
			// Save data
			$fields = $params['fields'];
			if ( ! empty($fields)) {
				foreach ($fields['products_search_store_fields'] as $iblock => $field) {
					CrmProducts::setSearchFields($iblock, $field);
				}
				Settings::save('products_search_crm_field', $fields['products_search_crm_field']);
			}
			$arResult['status'] = 'ok';
			// Additional actions
			break;


		/**
		 * Profiles list
		 */

		// Profiles list
		case 'profiles_list':
			$main_errors = \SProdIntegration::mainCheck();
			if ( ! empty($main_errors)) {
				$list = [];
			} else {
				$list = ProfilesTable::getList([
					'order'  => ['id' => 'asc'],
					'select' => ['id', 'sort', 'name', 'active'],
				]);
			}
			if ( ! \SProdIntegration::isUtf()) {
				$list = \Bitrix\Main\Text\Encoding::convertEncoding($list, "UTF-8", "Windows-1251");
			}
			$arResult['list'] = $list;
			$arResult['status'] = 'ok';
			break;

		// Profile creation
		case 'profiles_add':
			$profile_name = Loc::getMessage("SP_CI_NEW_PROFILE");
			if ( ! \SProdIntegration::isUtf()) {
				$profile_name = \Bitrix\Main\Text\Encoding::convertEncoding($profile_name, "Windows-1251", "UTF-8");
			}
			$result = ProfilesTable::add([
				'name'   => $profile_name,
				'active' => 'N',
			]);
			if ($result->isSuccess()) {
				$id = $result->getId();
				$arResult['id'] = $id;
				$arResult['status'] = 'ok';
			} else {
				$errors = $result->getErrorMessages();
				$arResult['errors'] = $result->getErrorMessages();
			}
			break;



		/**
		 * Profile edit
		 */

		// Information for profile viewing
		case 'profile_info':
			$id = $params['id'];
			$main_errors = \SProdIntegration::mainCheck();
			if ( ! empty($main_errors)) {
				$list = [];
			} else {
				$info = ProfileInfo::getAll($id);
			}
			if ($info) {
				$arResult['info'] = $info;
			}
			$arResult['status'] = 'ok';
			break;

		// Profile data
		case 'profile_get':
			$id = $params['id'];
			$main_errors = \SProdIntegration::mainCheck();
			if ( ! empty($main_errors)) {
				$list = [];
			} else {
				$profile = ProfilesTable::getById($id);
			}
			if ($profile) {
				$profile['main'] = $profile['options'];
				if ( ! $profile['main']) {
					$prefix = Loc::getMessage("SP_CI_PROFILE_PREFIX_DEFAULT");
					if ( ! \SProdIntegration::isUtf()) {
						$prefix = \Bitrix\Main\Text\Encoding::convertEncoding($prefix, "Windows-1251", "UTF-8");
					}
					$profile['main']['prefix'] = $prefix;
				}
				$profile['main']['active'] = $profile['active'];
				$profile['main']['name'] = $profile['name'];
				$profile['main']['deal_category'] = $profile['main']['deal_category'] ? $profile['main']['deal_category'] : 0;
				// TODO: Filter values not shown in profile information
				$info = ProfileInfo::getAll($id);
				$new_list = [];
				$stage_list = [];
				foreach ($info['crm']['stages'] as $stage) {
					$stage_list[] = $stage['id'];
				}
				foreach ($profile['statuses']['cancel_stages'] as $k => $stage_id) {
					if (in_array($stage_id, $stage_list)) {
						$new_list[] = $stage_id;
					}
				}
				$profile['statuses']['cancel_stages'] = $new_list;
				if ( ! \SProdIntegration::isUtf()) {
					$profile = \Bitrix\Main\Text\Encoding::convertEncoding($profile, "UTF-8", "Windows-1251");
				}
				$arResult['blocks'] = $profile;
			}
			$arResult['status'] = 'ok';
			break;

		// Profile update
		case 'profile_save':
			$id = $params['id'];
			$block_code = $params['block'];
			$fields = [];
			$inp_fields = $params['fields'];
			if ( ! empty($inp_fields)) {
				foreach ($inp_fields as $name => $value) {
					if ($block_code == 'main') {
						switch ($name) {
							case 'active':
							case 'name':
								$fields[$name] = $value;
								break;
							default:
								$fields['options'][$name] = $value;
						}
					} else {
						$fields[$block_code] = $inp_fields;
					}
				}
			}
			$result = ProfilesTable::update($id, $fields);
			if ($result->isSuccess()) {
				$arResult['status'] = 'ok';
			} else {
				$errors = $result->getErrorMessages();
				$arResult['errors'] = $result->getErrorMessages();
			}
			break;

		// Profile delete
		case 'profile_del':
			$id = $params['id'];
			ProfilesTable::delete($id);
			$arResult['status'] = 'ok';
			break;


		/**
		 * State of system
		 */

		// All data blocks
		case 'status_get':
			$arResult['blocks']['table']['state'] = [
				'display' => true,
				'active'  => true,
			];
			$arResult['blocks']['filelog']['state'] = [
				'display' => true,
				'active'  => true,
			];
			$arResult['blocks']['table']['fields'] = Integration::checkModuleStatus();
			$arResult['blocks']['filelog']['fields']['active'] = (Settings::get("filelog") == 'Y');
			$arResult['blocks']['filelog']['fields']['link'] = Integration::getFilelogLink();
			$arResult['blocks']['filelog']['fields']['info'] = Integration::getFilelogInfo();
			$arResult['status'] = 'ok';
			break;

		case 'status_filelog_reset':
			Integration::resetFilelog();
			$arResult['status'] = 'ok';
			break;

		// Table of system state
		case 'status_table_save':
			$arResult['status'] = 'ok';
			break;

		// File log
		case 'status_filelog_save':
			$fields = $params['fields'];
			$active = $fields['active'] ? 'Y' : 'N';
			Settings::save('filelog', $active);
			$arResult['status'] = 'ok';
			break;


		/**
		 * Products edit
		 */

		// Products list
		case 'products_edit_items_list':
			$main_errors = \SProdIntegration::mainCheck();
			$site = Settings::get("site");
			$list = [];
			$count = 0;
			if (empty($main_errors)) {
				$filter = [
					'iblock' => $params['filter']['iblock'],
					'name' => $params['filter']['name'],
					'section' => $params['filter']['section'],
				];
				$page = (int) $params['page'];
				$fields_sel = StoreProducts::getIblockFieldsSelected($filter['iblock']);
				$fields_list = StoreProducts::getIblockFieldsList($filter['iblock'], $fields_sel);
				$fields_all = StoreProducts::getIblockFieldsList($filter['iblock']);
				$count = StoreProducts::getParentProds($filter, [], $fields_sel, true);
				$list = StoreProducts::getParentProds($filter, [], $fields_sel, false, 10, $page);
			}
			$arResult['list'] = $list;
			$arResult['count'] = $count;
			$arResult['fields_sel'] = $fields_sel;
			$arResult['fields_list'] = $fields_list;
			$arResult['fields_all'] = $fields_all;
			$arResult['status'] = 'ok';
			break;

		// Sku list
		case 'products_edit_sku_list':
			$main_errors = \SProdIntegration::mainCheck();
			$site = Settings::get("site");
			$fields_sel = [];
			$fields_list = [];
			$fields_all = [];
			$list = [];
			$sku_iblock_id = false;
			if (empty($main_errors)) {
				$catalog_info = \CCatalogSKU::GetInfoByProductIBlock($params['iblock']);
				if ($catalog_info) {
					$sku_iblock_id = $catalog_info['IBLOCK_ID'];
				}
				if ($sku_iblock_id) {
					$fields_sel = StoreProducts::getIblockFieldsSelected($sku_iblock_id);
					$fields_list = StoreProducts::getIblockFieldsList($sku_iblock_id, $fields_sel);
					$fields_all = StoreProducts::getIblockFieldsList($sku_iblock_id);
				}
				$list = StoreProducts::getSkuProds($params['iblock'], $params['product_id'], false, $fields_sel);
			}
			$arResult['list'] = $list;
			$arResult['iblock_id'] = $sku_iblock_id;
			$arResult['fields_sel'] = $fields_sel;
			$arResult['fields_list'] = $fields_list;
			$arResult['fields_all'] = $fields_all;
			$arResult['status'] = 'ok';
			break;

		// Get filter data
		case 'products_edit_filter_data':
			$main_errors = \SProdIntegration::mainCheck();
			$iblocks = [];
			$sections = [];
			if (empty($main_errors)) {
				// Iblocks
				$iblocks = ProfileInfo::getStoreIblockList();
				// Sections
				if ($params['iblock']) {
					$sections = ProfileInfo::getStoreSectionsList($params['iblock']);
				}
			}
			$arResult['iblocks'] = $iblocks;
			$arResult['sections'] = $sections;
			$arResult['status'] = 'ok';
			break;

		// Save fields list
		case 'products_edit_save_fields':
			\SProduction\Integration\StoreProducts::setIblockFieldsSelected($params['iblock'], $params['fields']);
			$arResult['status'] = 'ok';
			break;

		// Add product to order
		case 'products_edit_add_item':
			$order_id = (int) $params['order_id'];
			$item_id = (int) $params['item_id'];
			if ($order_id && $item_id) {
				if ($order = Sale\Order::load($order_id)) {
					$basket = $order->getBasket();
					// If product exist
					$product_exist = false;
					foreach ($basket as $item) {
						if ($item->getProductId() == $item_id) {
							// Change quantity
							$item->setField('QUANTITY', $item->getQuantity() + 1);
							$res = $order->save();
							if ( ! $res->isSuccess()) {
								\SProdIntegration::Log('order ' . $order_id . ' add quantity for product ' . $item_id . ' error "' . $res->getErrorMessages() . '"');
							} else {
								\SProdIntegration::Log('order ' . $order_id . ' add quantity for product ' . $item_id . ' success');
							}
							$product_exist = true;
							$arResult['status'] = 'ok';
							break;
						}
					}
					// If new product
					if ( ! $product_exist) {
						$currency_code = CurrencyManager::getBaseCurrency();
						if ($product = \Bitrix\Iblock\ElementTable::getById($item_id)->fetch()) {
							$item = $basket->createItem('catalog', $item_id);
							$product_name = $product['NAME'];
							$item->setFields([
								'QUANTITY'               => 1,
								'CURRENCY'               => $currency_code,
								'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName(),
								'NAME'                   => $product_name,
							]);
							$res = $order->save();
							if ( ! $res->isSuccess()) {
								\SProdIntegration::Log('order ' . $order_id . ' add product ' . $item_id . ' error "' . $res->getErrorMessages() . '"');
							} else {
								\SProdIntegration::Log('order ' . $order_id . ' add product ' . $item_id . ' success');
							}
							$arResult['status'] = 'ok';
						}
					}
				}
			}
			break;

		// Cart list
		case 'products_edit_cart_list':
			$main_errors = \SProdIntegration::mainCheck();
			$list = [];
			if (empty($main_errors)) {
				if ( ! \SProdIntegration::isUtf()) {
					$list = \Bitrix\Main\Text\Encoding::convertEncoding($list, "UTF-8", "Windows-1251");
				}
			}
			$arResult['list'] = $list;
			$arResult['status'] = 'ok';
			break;


		/**
		 * testing
		 */

		case 'test':
			$arResult['data'] = 123;
			$arResult['status'] = 'ok';
			break;

	}
} catch (Exception $e) {
	$arResult['status'] = 'error';
	$arResult['message'] = $e->getMessage().' ['.$e->getCode().']';
}

if (!$lock_result) {
	echo \Bitrix\Main\Web\Json::encode($arResult);
}
