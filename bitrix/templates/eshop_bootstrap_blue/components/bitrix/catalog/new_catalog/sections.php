<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
?>

		<div class='row'>
			<div class="col-md-3">
				<div class='sidebar_left'>
					<div class='btn_mobile_list_catalog'>Разделы каталога</div>
					<div class='menu_catalog_sidebar'>
						<div class='close_list_cat'></div>
						<?$APPLICATION->IncludeComponent(
								"bitrix:catalog.section.list",
								".default",
								Array(
										"ADD_SECTIONS_CHAIN" => "Y",
										"CACHE_GROUPS" => "Y",
										"CACHE_TIME" => "36000000",
										"CACHE_TYPE" => "A",
										"COMPONENT_TEMPLATE" => ".default",
										"COMPOSITE_FRAME_MODE" => "A",
										"COMPOSITE_FRAME_TYPE" => "AUTO",
										"COUNT_ELEMENTS" => "N",
										"IBLOCK_ID" => "2",
										"IBLOCK_TYPE" => "catalog",
										"SECTION_CODE" => "",
										"SECTION_FIELDS" => array(0=>"",1=>"",),
										"SECTION_ID" => $_REQUEST["SECTION_ID"],
										"SECTION_URL" => "",
										"SECTION_USER_FIELDS" => array(0=>"",1=>"",),
										"SHOW_PARENT_NAME" => "Y",
										"TOP_DEPTH" => "3",
										"VIEW_MODE" => "LIST"
								)
						);?>
					</div>
					<div class="overflow_list_cat"></div>
				</div>
				<div class="wpb_wrapper sidebar_left_hit">
					<div id="bestsale-14718236481517810979" class="sw-best-seller-product vc_element">
						<div class="box-title">
							<h3>
								<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
								<span>Хиты</span> продаж</h3></div>
							<?$APPLICATION->IncludeComponent(
								"bitrix:catalog.top",
								"hit_buy",
								array(
									"COMPONENT_TEMPLATE" => "slider_sale_mini",
									"IBLOCK_TYPE" => "catalog",
									"IBLOCK_ID" => "2",
									"FILTER_NAME" => "",
									"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[{\"CLASS_ID\":\"CondIBProp:2:48\",\"DATA\":{\"logic\":\"Equal\",\"value\":17}}]}",
									"HIDE_NOT_AVAILABLE" => "N",
									"HIDE_NOT_AVAILABLE_OFFERS" => "N",
									"ELEMENT_SORT_FIELD" => "sort",
									"ELEMENT_SORT_ORDER" => "asc",
									"ELEMENT_SORT_FIELD2" => "id",
									"ELEMENT_SORT_ORDER2" => "desc",
									"ELEMENT_COUNT" => "9",
									"LINE_ELEMENT_COUNT" => "3",
									"PROPERTY_CODE" => array(
										0 => "",
										1 => "",
									),
									"PROPERTY_CODE_MOBILE" => array(
									),
									"OFFERS_LIMIT" => "5",
									"VIEW_MODE" => "SECTION",
									"TEMPLATE_THEME" => "blue",
									"ADD_PICT_PROP" => "-",
									"LABEL_PROP" => array(
									),
									"SHOW_DISCOUNT_PERCENT" => "Y",
									"SHOW_OLD_PRICE" => "Y",
									"SHOW_MAX_QUANTITY" => "N",
									"SHOW_CLOSE_POPUP" => "Y",
									"PRODUCT_SUBSCRIPTION" => "Y",
									"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false}]",
									"ENLARGE_PRODUCT" => "STRICT",
									"PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons,compare",
									"SHOW_SLIDER" => "Y",
									"SLIDER_INTERVAL" => "3000",
									"SLIDER_PROGRESS" => "N",
									"MESS_BTN_BUY" => "Купить",
									"MESS_BTN_ADD_TO_BASKET" => "В корзину",
									"MESS_BTN_DETAIL" => "Подробнее",
									"MESS_NOT_AVAILABLE" => "Нет в наличии",
									"SECTION_URL" => "",
									"DETAIL_URL" => "",
									"PRODUCT_QUANTITY_VARIABLE" => "quantity",
									"SEF_MODE" => "N",
									"CACHE_TYPE" => "A",
									"CACHE_TIME" => "36000000",
									"CACHE_GROUPS" => "Y",
									"CACHE_FILTER" => "N",
									"ACTION_VARIABLE" => "action",
									"PRODUCT_ID_VARIABLE" => "id",
									"PRICE_CODE" => array(
										0 => "BASE",
									),
									"USE_PRICE_COUNT" => "N",
									"SHOW_PRICE_COUNT" => "1",
									"PRICE_VAT_INCLUDE" => "Y",
									"CONVERT_CURRENCY" => "N",
									"BASKET_URL" => "/basket/",
									"USE_PRODUCT_QUANTITY" => "N",
									"ADD_PROPERTIES_TO_BASKET" => "Y",
									"PRODUCT_PROPS_VARIABLE" => "prop",
									"PARTIAL_PRODUCT_PROPERTIES" => "Y",
									"PRODUCT_PROPERTIES" => array(
									),
									"ADD_TO_BASKET_ACTION" => "ADD",
									"DISPLAY_COMPARE" => "N",
									"MESS_BTN_COMPARE" => "Сравнить",
									"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
									"USE_ENHANCED_ECOMMERCE" => "N",
									"COMPATIBLE_MODE" => "Y",
									"DISCOUNT_PERCENT_POSITION" => "bottom-right"
								),
								false
							);?>
					</div>
				</div>
			</div>
			<div class="col-md-9">
	<? $APPLICATION->IncludeComponent(
		"bitrix:catalog.section.list",
		"",
		array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
			"TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
			"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
			"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
			"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
			"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
			"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : '')
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);

	if ($arParams["USE_COMPARE"] === "Y")
	{
		$APPLICATION->IncludeComponent(
			"bitrix:catalog.compare.list",
			"",
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"NAME" => $arParams["COMPARE_NAME"],
				"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
				"COMPARE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["compare"],
				"ACTION_VARIABLE" => (!empty($arParams["ACTION_VARIABLE"]) ? $arParams["ACTION_VARIABLE"] : "action"),
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				'POSITION_FIXED' => isset($arParams['COMPARE_POSITION_FIXED']) ? $arParams['COMPARE_POSITION_FIXED'] : '',
				'POSITION' => isset($arParams['COMPARE_POSITION']) ? $arParams['COMPARE_POSITION'] : ''
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}

	if ($arParams["SHOW_TOP_ELEMENTS"] !== "N")
	{
		if (isset($arParams['USE_COMMON_SETTINGS_BASKET_POPUP']) && $arParams['USE_COMMON_SETTINGS_BASKET_POPUP'] === 'Y')
		{
			$basketAction = isset($arParams['COMMON_ADD_TO_BASKET_ACTION']) ? $arParams['COMMON_ADD_TO_BASKET_ACTION'] : '';
		}
		else
		{
			$basketAction = isset($arParams['TOP_ADD_TO_BASKET_ACTION']) ? $arParams['TOP_ADD_TO_BASKET_ACTION'] : '';
		}

		$APPLICATION->IncludeComponent(
			"bitrix:catalog.top",
			"",
			array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ELEMENT_SORT_FIELD" => $arParams["TOP_ELEMENT_SORT_FIELD"],
				"ELEMENT_SORT_ORDER" => $arParams["TOP_ELEMENT_SORT_ORDER"],
				"ELEMENT_SORT_FIELD2" => $arParams["TOP_ELEMENT_SORT_FIELD2"],
				"ELEMENT_SORT_ORDER2" => $arParams["TOP_ELEMENT_SORT_ORDER2"],
				"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
				"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
				"BASKET_URL" => $arParams["BASKET_URL"],
				"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
				"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
				"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
				"ELEMENT_COUNT" => $arParams["TOP_ELEMENT_COUNT"],
				"LINE_ELEMENT_COUNT" => $arParams["TOP_LINE_ELEMENT_COUNT"],
				"PROPERTY_CODE" => $arParams["TOP_PROPERTY_CODE"],
				"PROPERTY_CODE_MOBILE" => $arParams["TOP_PROPERTY_CODE_MOBILE"],
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
				"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
				"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
				"PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
				"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
				"ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
				"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
				"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
				"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
				"OFFERS_FIELD_CODE" => $arParams["TOP_OFFERS_FIELD_CODE"],
				"OFFERS_PROPERTY_CODE" => $arParams["TOP_OFFERS_PROPERTY_CODE"],
				"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
				"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
				"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
				"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
				"OFFERS_LIMIT" => $arParams["TOP_OFFERS_LIMIT"],
				'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
				'CURRENCY_ID' => $arParams['CURRENCY_ID'],
				'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
				'VIEW_MODE' => (isset($arParams['TOP_VIEW_MODE']) ? $arParams['TOP_VIEW_MODE'] : ''),
				'ROTATE_TIMER' => (isset($arParams['TOP_ROTATE_TIMER']) ? $arParams['TOP_ROTATE_TIMER'] : ''),
				'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),

				'LABEL_PROP' => $arParams['LABEL_PROP'],
				'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
				'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
				'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
				'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
				'PRODUCT_BLOCKS_ORDER' => $arParams['TOP_PRODUCT_BLOCKS_ORDER'],
				'PRODUCT_ROW_VARIANTS' => $arParams['TOP_PRODUCT_ROW_VARIANTS'],
				'ENLARGE_PRODUCT' => $arParams['TOP_ENLARGE_PRODUCT'],
				'ENLARGE_PROP' => isset($arParams['TOP_ENLARGE_PROP']) ? $arParams['TOP_ENLARGE_PROP'] : '',
				'SHOW_SLIDER' => $arParams['TOP_SHOW_SLIDER'],
				'SLIDER_INTERVAL' => isset($arParams['TOP_SLIDER_INTERVAL']) ? $arParams['TOP_SLIDER_INTERVAL'] : '',
				'SLIDER_PROGRESS' => isset($arParams['TOP_SLIDER_PROGRESS']) ? $arParams['TOP_SLIDER_PROGRESS'] : '',

				'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
				'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
				'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
				'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
				'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
				'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
				'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
				'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
				'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
				'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
				'MESS_NOT_AVAILABLE' => $arParams['~MESS_NOT_AVAILABLE'],
				'ADD_TO_BASKET_ACTION' => $basketAction,
				'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
				'COMPARE_PATH' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['compare'],
				'USE_COMPARE_LIST' => 'Y',

				'COMPATIBLE_MODE' => (isset($arParams['COMPATIBLE_MODE']) ? $arParams['COMPATIBLE_MODE'] : '')
			),
			$component
		);
		unset($basketAction);
	} ?>
	<div class="bx-section-desc bx-blue">
		<p class="bx-section-desc-post"><? echo CIBlock::GetArrayByID(2, "DESCRIPTION"); ?></p>
	</div>
		</div>
	</div>
