<?php
/**
 *    CrmProducts
 *
 * @mail support@s-production.online
 * @link s-production.online
 */

namespace SProduction\Integration;

\Bitrix\Main\Loader::includeModule("catalog");

use Bitrix\Main,
	Bitrix\Main\Entity,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


/**
 * Class CrmProducts
 *
 * @package SProduction\Integration
 **/

class CrmProducts
{
	const XML_ID_PREFIX = 'SHOP_PROD_';

	/**
	 * Products search by XML_ID (or other ID field)
	 */
	public static function find($store_prod_ids, $crm_field_code='XML_ID', $parent_sect_id=0, $subsections=false) {
		$crm_prod_res = [];
		$filter = [];
		foreach ($store_prod_ids as $store_prod_id) {
			$filter[$crm_field_code][] = $store_prod_id;
		}
		if ($subsections) {
			$filter['SECTION_ID'] = (int)$parent_sect_id;
			$filter['INCLUDE_SUBSECTIONS'] = 'Y';
		}
		elseif ($parent_sect_id) {
			$filter['SECTION_ID'] = (int)$parent_sect_id;
		}
		$crm_prod = Rest::getList('crm.product.list', '', [
			'filter' => $filter,
			'select' => ['*', 'PROPERTY_*'],
		]);
		foreach ($crm_prod as $product) {
			$store_prod_id = $product[$crm_field_code];
			if (is_array($store_prod_id)) {
				if ($store_prod_id['value']) {
					$crm_prod_res[$store_prod_id['value']] = $product;
				}
			}
			elseif ($store_prod_id) {
				$crm_prod_res[$store_prod_id] = $product;
			}
		}
		return $crm_prod_res;
	}

	/**
	 * Create new product
	 */
	public static function add($store_prod_id, $id_field, $fields, $parent_sect_id=0) {
		$fields[$id_field] = $store_prod_id;
		if ($parent_sect_id) {
			$fields['SECTION_ID'] = $parent_sect_id;
		}
		$crm_prod_id = Rest::execute('crm.product.add', [
			'fields' => $fields,
		]);
		return $crm_prod_id;
	}

	/**
	 * Update product info
	 */
	public static function update($crm_prod_id, $id_field, $fields) {
		$result = false;
		unset($fields[$id_field]);
		if ($crm_prod_id && !empty($fields) && UpdateLock::isChanged($crm_prod_id, 'product_stoc', $fields, true)) {
			$result = Rest::execute('crm.product.update', [
				'id' => $crm_prod_id,
				'fields' => $fields,
			]);
		}
		return $result;
	}

	/**
	 * Create new section for order
	 */
	public static function addSection($order_id, $parent_sect_id=0) {
		$crm_section_id = Rest::execute('crm.productsection.add', [
			'fields' => [
				'NAME' => GetMessage("SP_CI_CRMPRODUCTS_ADD_SECTION_ORDER") . ' ' . $order_id,
				'XML_ID' => Integration::convEncForDeal(self::XML_ID_PREFIX . $order_id),
				'SECTION_ID' => $parent_sect_id,
			],
		]);
		return $crm_section_id;
	}

	/**
	 * Find section of order
	 */
	public static function findSection($order_id, $parent_sect_id=0) {
		$result = false;
		$crm_section_list = Rest::execute('crm.productsection.list', [
			'filter' => [
				'XML_ID' => self::XML_ID_PREFIX . $order_id,
				'SECTION_ID' => $parent_sect_id,
			],
		]);
		if (!empty($crm_section_list)) {
			$result = $crm_section_list[0];
		}
		return $result;
	}

	/**
	 * Get sections hierarchy
	 */
	public static function getSectHierarchy() {
		$crm_section_list = Rest::getList('crm.productsection.list', '', []);
		$list = [];
		foreach($crm_section_list as $item) {
			if (!$item['XML_ID'] || strpos($item['XML_ID'], 'SHOP_PROD_') === false) {
				$list[] = $item;
			}
		}
		$result = self::getSectHierarchyFindSub([], 0, 0, $list);
		return $result;
	}

	public static function getSectHierarchyFindSub($result, $section_id, $level, $list) {
		foreach ($list as $item) {
			if ($item['SECTION_ID'] == $section_id) {
				$dots = '';
				for ($i=0; $i<$level; $i++) {
					$dots .= '. ';
				}
				$name = $item['NAME'];
				if (!\SProdIntegration::isUtf()) {
					$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
				}
				$result[] = [
					'id' => $item['ID'],
					'name' => $dots . $name,
				];
				$result = self::getSectHierarchyFindSub($result, $item['ID'], $level + 1, $list);
			}
		}
		return $result;
	}

	/**
	 * Store products fields
	 */
	public static function getStoreFields($iblock_id) {
		$list = [];
		if (!$iblock_id) {
			return;
		}
		// IBlock fields
		$list['main'] = [
			'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OSNOVNYE_PARAMETRY"),
		];
		$list['main']['items'] = [
			[
				'id' => 'SORT',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_INDEKS_SORTIROVKI")
			],
			[
				'id' => 'NAME',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_IMA_ELEMENTA")
			],
			[
				'id' => 'CODE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_KOD_ELEMENTA")
			],
			[
				'id' => 'ACTIVE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_ACTIVE")
			],
			[
				'id' => 'DATE_ACTIVE_FROM',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_NACALO_AKTIVNOSTI")
			],
			[
				'id' => 'DATE_ACTIVE_TO',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OKONCANIE_AKTIVNOSTI")
			],
			[
				'id' => 'TAGS',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TEGI")
			],
			[
				'id' => 'PREVIEW_PICTURE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_IZOBRAJENIE_DLA_ANON")
			],
			[
				'id' => 'DETAIL_PICTURE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_DETALQNOE_IZOBRAJENI")
			],
			[
				'id' => 'PREVIEW_TEXT_TYPE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TIP_OPISANIA_DLA_ANO")
			],
			[
				'id' => 'PREVIEW_TEXT',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OPISANIE_DLA_ANONSA")
			],
			[
				'id' => 'DETAIL_TEXT_TYPE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TIP_DETALQNOGO_OPISA")
			],
			[
				'id' => 'DETAIL_TEXT',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_DETALQNOE_OPISANIE")
			],
		];
		// IBlock properties
		$list['props'] = [
			'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_SVOYSTVA"),
		];
		if ($iblock_id) {
			$ob = \CIBlockProperty::GetList(["sort" => "asc", "name" => "asc"], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock_id]);
			while ($arProp = $ob->GetNext()) {
				$list['props']['items']['PROP_'.$arProp['ID']] = [
					'id' => 'PROP_'.$arProp['ID'],
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_SVOYSTVO").$arProp['NAME'].'"',
				];
			}
		}
		// Catalog prices
		$list['prices'] = [
			'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_PRICES"),
		];
		$res = \Bitrix\Catalog\GroupTable::getList([
			'filter' => [],
			'order' => ['ID' => 'asc'],
		]);
		while ($item = $res->fetch()) {
			$list['prices']['items']['PRICE_'.$item['ID']] = [
				'id' => 'PRICE_'.$item['ID'],
				'name' => $item['NAME'].', ID '.$item['ID']
			];
		}
		// PARENT IBLOCK DATA
		$catalog_iblocks = \Bitrix\Catalog\CatalogIblockTable::getList([
			'filter' => ['IBLOCK_ID' => $iblock_id]
		])->fetch();
		$parent_iblock_id = $catalog_iblocks['PRODUCT_IBLOCK_ID'];
		if ($parent_iblock_id) {
			// IBlock fields
			$list['parent_main'] = [
				'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_PARENT_FIELDS_OSNOVNYE_PARAMETRY"),
			];
			$list['parent_main']['items'] = [
				[
					'id' => 'PARENT_SORT',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_INDEKS_SORTIROVKI")
				],
				[
					'id' => 'PARENT_NAME',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_IMA_ELEMENTA")
				],
				[
					'id' => 'PARENT_CODE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_KOD_ELEMENTA")
				],
				[
					'id' => 'PARENT_ACTIVE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_ACTIVE")
				],
				[
					'id' => 'PARENT_DATE_ACTIVE_FROM',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_NACALO_AKTIVNOSTI")
				],
				[
					'id' => 'PARENT_DATE_ACTIVE_TO',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OKONCANIE_AKTIVNOSTI")
				],
				[
					'id' => 'PARENT_TAGS',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TEGI")
				],
				[
					'id' => 'PARENT_PREVIEW_PICTURE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_IZOBRAJENIE_DLA_ANON")
				],
				[
					'id' => 'PARENT_DETAIL_PICTURE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_DETALQNOE_IZOBRAJENI")
				],
				[
					'id' => 'PARENT_PREVIEW_TEXT_TYPE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TIP_OPISANIA_DLA_ANO")
				],
				[
					'id' => 'PARENT_PREVIEW_TEXT',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OPISANIE_DLA_ANONSA")
				],
				[
					'id' => 'PARENT_DETAIL_TEXT_TYPE',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_TIP_DETALQNOGO_OPISA")
				],
				[
					'id' => 'PARENT_DETAIL_TEXT',
					'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_DETALQNOE_OPISANIE")
				],
			];
			// IBlock properties
			$list['parent_props'] = [
				'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_PARENT_FIELDS_SVOYSTVA"),
			];
			if ($parent_iblock_id) {
				$ob = \CIBlockProperty::GetList(["sort" => "asc", "name" => "asc"], ["ACTIVE" => "Y", "IBLOCK_ID" => $parent_iblock_id]);
				while ($arProp = $ob->GetNext()) {
					$list['parent_props']['items']['PARENT_PROP_'.$arProp['ID']] = [
						'id' => 'PARENT_PROP_'.$arProp['ID'],
						'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_SVOYSTVO").$arProp['NAME'].'"',
					];
				}
			}
		}
		return $list;
	}

	/**
	 * CRM products fields
	 */
	public static function getCRMFields() {
		$list = [];
		$fields_list = Rest::execute('crm.product.fields');
		if (!empty($fields_list)) {
			$i = 0;
			foreach ($fields_list as $code => $crm_field) {
				switch ($code) {
					// Hidden fields
					case 'ID':
					case 'XML_ID':
					case 'DATE_CREATE':
					case 'TIMESTAMP_X':
					case 'MODIFIED_BY':
					case 'CREATED_BY':
					case 'SECTION_ID':
					case 'CURRENCY_ID':
						break;
					// Visible fields
					default:
						$name = $crm_field['title'];
						if (!\SProdIntegration::isUtf()) {
							$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
						}
						$field = [
							'id' => $code,
							'name' => $name,
							'required' => $crm_field['isRequired'],
							'multiple' => $crm_field['isMultiple'],
						];
						$list[] = $field;
				}
				$i ++;
			}
		}
		return $list;
	}

	/**
	 * Store products fields
	 */
	public static function getStoreFieldsForID($iblock_id) {
		$list = [];
		if (!$iblock_id) {
			return;
		}
		// IBlock fields
		$list['main'] = [
			'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_OSNOVNYE_PARAMETRY"),
		];
		$list['main']['items'] = [
			[
				'id' => 'ID',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_ID")
			],
			[
				'id' => 'NAME',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_IMA_ELEMENTA")
			],
			[
				'id' => 'CODE',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_KOD_ELEMENTA")
			],
			[
				'id' => 'XML_ID',
				'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_XML_ID")
			],
		];
		// IBlock properties
		$list['props'] = [
			'title' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_SVOYSTVA"),
		];
		if ($iblock_id) {
			$ob = \CIBlockProperty::GetList(["sort" => "asc", "name" => "asc"], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock_id]);
			while ($prop = $ob->GetNext()) {
				if ($prop['MULTIPLE'] != 'Y' && !in_array($prop['PROPERTY_TYPE'], ['F'])) {
					$list['props']['items']['PROPERTY_' . $prop['ID']] = [
						'id'   => 'PROPERTY_' . $prop['ID'],
						'name' => GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_SVOYSTVO") . $prop['NAME'] . '"',
					];
				}
			}
		}
		return $list;
	}

	/**
	 * CRM products fields
	 */
	public static function getCRMFieldsForID() {
		$list = [];
		$fields_list = Rest::execute('crm.product.fields');
		if (!empty($fields_list)) {
			$i = 0;
			foreach ($fields_list as $code => $crm_field) {
				switch ($code) {
					// Hidden fields
					case 'ID':
					case 'DATE_CREATE':
					case 'TIMESTAMP_X':
					case 'MODIFIED_BY':
					case 'CREATED_BY':
					case 'SECTION_ID':
					case 'PRICE':
					case 'CATALOG_ID':
					case 'CURRENCY_ID':
					case 'DESCRIPTION':
					case 'DESCRIPTION_TYPE':
					case 'ACTIVE':
					case 'SORT':
					case 'VAT_ID':
					case 'VAT_INCLUDED':
					case 'MEASURE':
					case 'PREVIEW_PICTURE':
					case 'DETAIL_PICTURE':
						break;
					// Visible fields
					default:
						if (!$crm_field['isMultiple']) {
							$name = $crm_field['title'];
							if (!\SProdIntegration::isUtf()) {
								$name = \Bitrix\Main\Text\Encoding::convertEncoding($name, "UTF-8", "Windows-1251");
							}
							if ($code == 'CODE') {
								$name = GetMessage("SP_CI_CRMPRODUCTS_STORE_FIELDS_KOD_ELEMENTA");
							}
							$field  = [
								'id'   => $code,
								'name' => $name,
							];
							$list[] = $field;
						}
				}
				$i ++;
			}
		}
		return $list;
	}


	/**
	 * Get product fields by compare table
	 */
	public static function getCRMProductFields($store_prod_id, $fields_info) {
		$fields = [];
		$comp_table_full = unserialize(Settings::get("products_comp_table"));
		// Product data
		$store_prod = false;
		$filter = ["ID" => $store_prod_id];
		$res = \CIBlockElement::GetList(["SORT" => "ASC"], $filter);
		if ($ob = $res->GetNextElement()) {
			$store_prod = $ob->GetFields();
			if ($store_prod['PREVIEW_PICTURE']) {
				$file                          = \CFile::GetFileArray($store_prod['PREVIEW_PICTURE']);
				$store_prod['PREVIEW_PICTURE'] = $file['SRC'];
			}
			if ($store_prod['DETAIL_PICTURE']) {
				$file                         = \CFile::GetFileArray($store_prod['DETAIL_PICTURE']);
				$store_prod['DETAIL_PICTURE'] = $file['SRC'];
			}
			$properties = $ob->GetProperties();
			foreach ($properties as $property) {
				$store_prod['properties'][$property['ID']] = $property;
			}
			$res_price = \Bitrix\Catalog\PriceTable::getList([
				'filter' => ['PRODUCT_ID' => $store_prod_id]
			]);
			while ($price = $res_price->fetch()) {
				$store_prod['prices'][$price['CATALOG_GROUP_ID']] = $price;
			}
		}
		// Product parent data
		$store_parent_prod = false;
		$store_parent_prod_info = \CCatalogSKU::GetProductInfo($store_prod_id);
		if ($store_parent_prod_info['ID']) {
			$filter = ["ID" => $store_parent_prod_info['ID']];
			$res = \CIBlockElement::GetList(["SORT" => "ASC"], $filter);
			if ($ob = $res->GetNextElement()) {
				$store_parent_prod = $ob->GetFields();
				if ($store_parent_prod['PREVIEW_PICTURE']) {
					$file                          = \CFile::GetFileArray($store_parent_prod['PREVIEW_PICTURE']);
					$store_parent_prod['PREVIEW_PICTURE'] = $file['SRC'];
				}
				if ($store_parent_prod['DETAIL_PICTURE']) {
					$file                         = \CFile::GetFileArray($store_parent_prod['DETAIL_PICTURE']);
					$store_parent_prod['DETAIL_PICTURE'] = $file['SRC'];
				}
				$properties = $ob->GetProperties();
				foreach ($properties as $property) {
					$store_parent_prod['properties'][$property['ID']] = $property;
				}
			}
		}
		// Get compare table for product
		if ($store_prod) {
			$price_id = false;
			$comp_table = (array)$comp_table_full[$store_prod['IBLOCK_ID']];
			foreach ($comp_table as $crm_prod_f_id => $sync_params) {
				// Get value of store field
				$store_prod_f_id = $sync_params['value'];
				$order_value = false;
				if ($store_prod_f_id) {
					// Offers
					if (strpos($store_prod_f_id, 'PARENT_') === false) {
						// Prices
						if (strpos($store_prod_f_id, 'PRICE_') !== false) {
							$price_id = (int) str_replace('PRICE_', '', $store_prod_f_id);
							if ($store_prod['prices'][$price_id]) {
								$order_value = $store_prod['prices'][$price_id]['PRICE'];
							}
						} // Properties
						elseif (strpos($store_prod_f_id, 'PROP_') !== false) {
							$prop_id = (int) str_replace('PROP_', '', $store_prod_f_id);
							if ($store_prod['properties'][$prop_id]) {
								if ($store_prod['properties'][$prop_id]['USER_TYPE'] == 'directory') {
									$value_code = $store_prod['properties'][$prop_id]['VALUE'];
									$table_name = $store_prod['properties'][$prop_id]['USER_TYPE_SETTINGS']['TABLE_NAME'];
									$order_value = self::getHLValue($table_name, $value_code);
								}
								else {
									$order_value = $store_prod['properties'][$prop_id]['VALUE'];
								}
							}
						} // Other fields
						else {
							$order_value = $store_prod[$store_prod_f_id];
						}
					}
					// Parent products
					elseif ($store_parent_prod) {
						// Properties
						$store_prod_f_id = str_replace('PARENT_', '', $store_prod_f_id);
						if (strpos($store_prod_f_id, 'PROP_') !== false) {
							$prop_id = (int) str_replace('PROP_', '', $store_prod_f_id);
							if ($store_parent_prod['properties'][$prop_id]) {
								if ($store_parent_prod['properties'][$prop_id]['USER_TYPE'] == 'directory') {
									$value_code = $store_parent_prod['properties'][$prop_id]['VALUE'];
									$table_name = $store_parent_prod['properties'][$prop_id]['USER_TYPE_SETTINGS']['TABLE_NAME'];
									$order_value = self::getHLValue($table_name, $value_code);
								}
								else {
									$order_value = $store_parent_prod['properties'][$prop_id]['VALUE'];
								}
							}
						} // Other fields
						else {
							$order_value = $store_parent_prod[$store_prod_f_id];
						}
					}
				}
				// Adapt value for crm product field
				// Temporary value
				$deal_value = [];
				$value = !is_array($order_value) ? [$order_value] : $order_value;
				// File
				if ($fields_info[$crm_prod_f_id]['type'] == 'product_file' ||
				    ($fields_info[$crm_prod_f_id]['type'] == 'product_property' && $fields_info[$crm_prod_f_id]['propertyType'] == 'F')) {
					// Add new values
					foreach ($value as $path) {
						$path = $_SERVER['DOCUMENT_ROOT'] . $path;
						$name = pathinfo($path, PATHINFO_BASENAME);
						$data = file_get_contents($path);
						$deal_value[] = array("fileData" => array(
							$name,
							base64_encode($data)
						));
					}
//					// TODO: Delete old values
//					if ($arRemoteFields[$crm_prod_f_id] && is_array($arRemoteFields[$crm_prod_f_id])) {
//						foreach ($arRemoteFields[$crm_prod_f_id] as $arRFValue) {
//							$arNewValue[] = array(
//								"valueId" => $arRFValue['valueId'],
//								"value" => array('remove' => 'Y'),
//							);
//						}
//					}
				}
				// List
				elseif ($fields_info[$crm_prod_f_id]['type'] == 'product_property' && $fields_info[$crm_prod_f_id]['propertyType'] == 'L') {
					if ($fields_info[$crm_prod_f_id] && is_array($fields_info[$crm_prod_f_id]['values'])) {
						foreach ($fields_info[$crm_prod_f_id]['values'] as $f_info_value) {
							if (in_array($f_info_value['VALUE'], $value)) {
								$deal_value[] = $f_info_value['ID'];
							}
						}
					}
				}
				// Other types
				else {
					$deal_value = $value;
				}
				// Returned value
				if (count($deal_value) == 1) {
					$deal_value = $deal_value[0];
				}
				elseif (count($deal_value) == 0) {
					$deal_value = '';
				}
				$fields[$crm_prod_f_id] = Integration::convEncForDeal($deal_value);
			}
			// Default values
			if ($fields['PRICE'] && $price_id) {
				$fields['CURRENCY_ID'] = $store_prod['prices'][$price_id]['CURRENCY'];
			}
			if ($fields['SECTION_ID'] === false) {
				unset($fields['SECTION_ID']);
			}
		}
		return $fields;
	}

	protected static function getHLValue($hl_table, $value_code) {
		$hl_value = false;
		$hl_block = \Bitrix\Highloadblock\HighloadBlockTable::getList(
			array("filter" => array(
				'TABLE_NAME' => $hl_table
			))
		)->fetch();
		if (isset($hl_block['ID'])) {
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hl_block);
			$entity_data_class = $entity->getDataClass();
			$res = $entity_data_class::getList(['filter' => ['UF_XML_ID' => $value_code]]);
			if ($item = $res->fetch()) {
				$hl_value = $item['UF_NAME'];
			}
		}
		return $hl_value;
	}

	/**
	 * Params for search products in the CRM products DB
	 */

	public static function getSearchFields() {
		$iblocks = ProfileInfo::getStoreIblockList(true);
		$comp_table = [];
		$saved_table = Settings::get('products_search_store_fields', true);
		if ($saved_table) {
			foreach ($iblocks as $iblock) {
				$comp_table[$iblock['id']] = isset($saved_table[$iblock['id']]) ? $saved_table[$iblock['id']] : '';
			}
		}
		else {
			foreach ($iblocks as $iblock) {
				$comp_table[$iblock['id']] = '';
			}
			$products_iblock = (int)Settings::get('products_iblock');
			$store_field = Settings::get('products_search_store_field');
			if ($products_iblock && $store_field) {
				$comp_table[$products_iblock] = $store_field;
			}
		}
		return $comp_table;
	}

	public static function setSearchFields($iblock_id, $store_field) {
		$comp_table = self::getSearchFields();
		$comp_table[$iblock_id] = $store_field;
		Settings::save('products_search_store_fields', $comp_table, true);
	}
}
