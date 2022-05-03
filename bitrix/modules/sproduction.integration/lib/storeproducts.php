<?php
/**
 *    ProductsEdit
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


class StoreProducts
{
	const LIST_FIELDS_DEF = [
		'ID',
		'IBLOCK_ID',
		'NAME',
		'CODE',
		'PICTURE',
		'DETAIL_PAGE_URL',
		'QUANTITY',
	];
	const LIST_FIELDS_SETTINGS_FIELD = 'store_prod_fields_sel';

	/**
	 * Get parent products
	 */
	public static function getParentProds($filter=[], $order=[], $select=[], $get_count=false, $limit=10, $page=1) {
		$req_filter = [
			'INCLUDE_SUBSECTIONS' => 'Y',
			'ACTIVE'              => 'Y',
		];
		if ($filter['iblock']) {
			$req_filter['IBLOCK_ID'] = $filter['iblock'];
		}
		else {
			$catalog_iblocks = ProfileInfo::getStoreIblockList();
			if (!empty($catalog_iblocks)) {
				foreach ($catalog_iblocks as $item) {
					$req_filter['IBLOCK_ID'][] = $item['id'];
				}
			}
		}
		if ($filter['name']) {
			$req_filter['NAME'] = '%' . $filter['name'] . '%';
		}
		if ($filter['section']) {
			$req_filter['SECTION_ID'] = $filter['section'];
		}
		if ($get_count) {
			return self::getCount($req_filter);
		}
		else {
			return self::getList($req_filter, $order, $select, $limit, $page, true);
		}
	}

	/**
	 * Get sku products
	 */
	public static function getSkuProds($iblock_id, $product_id, $get_count=false, array $fields=[], $limit=0, $page=1) {
		$catalog_info = \CCatalogSKU::GetInfoByProductIBlock($iblock_id);
		if (!$catalog_info) {
			return false;
		}
		$req_filter = [
			'IBLOCK_ID' => $catalog_info['IBLOCK_ID'],
			'PROPERTY_' . $catalog_info['SKU_PROPERTY_ID'] => $product_id,
			'ACTIVE' => 'Y',
		];
		if ($get_count) {
			return self::getCount($req_filter);
		}
		else {
			return self::getList($req_filter, [], $fields, $limit, $page);
		}
	}

	/**
	 * Get count of products
	 */
	public static function getCount($filter=[]) {
		$count = \CIBlockElement::GetList([], $filter, []);
		return $count;
	}

	/**
	 * Get list of products
	 */
	public static function getList($filter=[], $order=[], $select=[], $limit=10, $page=1, $sku_count=false) {
		$store_prod_list = [];
		// Prepare params
		$site = Settings::get("site");
		if (empty($order)) {
			$order = ['NAME' => 'ASC', 'ID' => 'ASC'];
		}
		if (empty($select)) {
			$select = self::LIST_FIELDS_DEF;
		}
		else {
			if (!in_array('ID', $select)) {
				$select[] = 'ID';
			}
			if (!in_array('IBLOCK_ID', $select)) {
				$select[] = 'IBLOCK_ID';
			}
		}
		if (in_array('PICTURE', $select)) {
			if (!in_array('PREVIEW_PICTURE', $select)) {
				$select[] = 'PREVIEW_PICTURE';
			}
			if (!in_array('DETAIL_PICTURE', $select)) {
				$select[] = 'DETAIL_PICTURE';
			}
		}
		$page = (int) $page;
		$nav_params = [
			'nTopCount'       => false,
			'nPageSize'       => $limit,
			'iNumPage'        => $page,
			'checkOutOfRange' => true
		];
		$res = \CIBlockElement::GetList($order, $filter, false, $nav_params, $select);
		while ($fields = $res->GetNext()) {
			// Link on product page
			if ($fields['DETAIL_PAGE_URL']) {
				$link = $site . $fields['DETAIL_PAGE_URL'];
				$fields['PAGE_URL'] = '<a href="' . $link . '" target="_blank">' . Loc::getMessage("SP_CI_STOREPRODUCTS_GETLIST_SHOW") . '</a>';
			}
			// Preview
			$fields['PICTURE'] = '';
			if ($fields['PREVIEW_PICTURE'] || $fields['DETAIL_PICTURE']) {
				$image_resized = \CFile::ResizeImageGet(
					$fields['PREVIEW_PICTURE'] ? $fields['PREVIEW_PICTURE'] : $fields['DETAIL_PICTURE'],
					["width" => 100, "height" => 100],
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$fields['PICTURE'] = '<img src="' . $image_resized["src"] . '" width="' . $image_resized["width"] . '" height="' . $image_resized["height"] . '" />';
			}
			// Count sku
			if ($sku_count) {
				$fields['SKU_COUNT'] = self::getSkuProds($fields['IBLOCK_ID'], $fields['ID'], true);
			}
			$store_prod_list[] = $fields;
		}
		return $store_prod_list;
	}


	/**
	 * Get list of iblock fields
	 */
	public static function getIblockFieldsList($iblock_id=false, array $selected=[]) {
		$result = [];
		// IBlock fields
		$list = [
			[
				'id' => 'ID',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_ID")
			],
			[
				'id' => 'IBLOCK_ID',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_IBLOCK_ID")
			],
			[
				'id' => 'SORT',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_SORT")
			],
			[
				'id' => 'NAME',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_NAME")
			],
			[
				'id' => 'CODE',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_CODE")
			],
			[
				'id' => 'ACTIVE',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_ACTIVE")
			],
			[
				'id' => 'DATE_ACTIVE_FROM',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_DATE_ACTIVE_FROM")
			],
			[
				'id' => 'DATE_ACTIVE_TO',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_DATE_ACTIVE_TO")
			],
			[
				'id' => 'TAGS',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_TAGS")
			],
			[
				'id' => 'PICTURE',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_PICTURE")
			],
			[
				'id' => 'PREVIEW_PICTURE',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_PREVIEW_PICTURE")
			],
			[
				'id' => 'DETAIL_PICTURE',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_DETAIL_PICTURE")
			],
			[
				'id' => 'PREVIEW_TEXT',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_PREVIEW_TEXT")
			],
			[
				'id' => 'DETAIL_TEXT',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_DETAIL_TEXT")
			],
			[
				'id' => 'DETAIL_PAGE_URL',
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_DETAIL_PAGE_URL")
			]
		];
		// IBlock properties
		if ($iblock_id) {
			$ob = \CIBlockProperty::GetList(["sort" => "asc", "name" => "asc"], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock_id]);
			while ($arProp = $ob->GetNext()) {
				if ($arProp['CODE']) {
					$list[] = [
						'id'   => 'PROPERTY_' . $arProp['CODE'],
						'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_PROP", ['#NAME#' => trim($arProp['NAME']), '#ID#' => $arProp['ID']]),
					];
				}
			}
		}
		// Catalog prices
		$list[] = [
			'id' => 'QUANTITY',
			'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_QUANTITY"),
		];
		$res = \Bitrix\Catalog\GroupTable::getList([
			'filter' => [],
			'order' => ['ID' => 'asc'],
		]);
		while ($item = $res->fetch()) {
			$list[] = [
				'id' => 'PRICE_'.$item['ID'],
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_PRICE", ['#NAME#' => trim($item['NAME']), '#ID#' => $item['ID']])
			];
			$list[] = [
				'id' => 'CURRENCY_'.$item['ID'],
				'name' => GetMessage("SP_CI_STOREPRODUCTS_FIELD_CURRENCY", ['#NAME#' => trim($item['NAME']), '#ID#' => $item['ID']])
			];
		}
		if (empty($selected)) {
			$result = $list;
		}
		else {
			foreach ($list as $item) {
				if (in_array($item['id'], $selected)) {
					$result[] = $item;
				}
			}
		}
		return $result;
	}

	/**
	 * Get list of displayed fields
	 */
	public static function getIblockFieldsSelected($iblock_id=false) {
		$iblock_id = (int)$iblock_id;
		$iblocks_fields = Settings::get(self::LIST_FIELDS_SETTINGS_FIELD, true);
		if (is_array($iblocks_fields) && isset($iblocks_fields[$iblock_id])) {
			$list = $iblocks_fields[$iblock_id];
		}
		else {
			$list = self::LIST_FIELDS_DEF;
		}
		return $list;
	}

	/**
	 * Change list of displayed fields
	 */
	public static function setIblockFieldsSelected($iblock_id, array $fields) {
		$iblock_id = (int)$iblock_id;
		$iblocks_fields = Settings::get(self::LIST_FIELDS_SETTINGS_FIELD, true);
		if (!is_array($iblocks_fields)) {
			$iblocks_fields = [];
		}
		$iblocks_fields[$iblock_id] = $fields;
		Settings::save(self::LIST_FIELDS_SETTINGS_FIELD, $iblocks_fields, true);
		return true;
	}

}
