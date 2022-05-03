<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */
?>
<? if($item['PROPERTIES']['HITBUY']['VALUE'] == 'Да'){ ?>
	<div class='hit_prod'>ХИТ</div>
<? } ?>
<? if($item['PROPERTIES']['EXCLUSIVE']['VALUE'] == 'Да'){ ?>
	<div class='exclusive_prod'><img src="<?=SITE_TEMPLATE_PATH?>/img/exclusive_distributor.png" /></div>
<? } ?>
<div class="item-img products-thumb" data-entity="image-wrapper">
	<span id="<?=$itemIds['PICT_SLIDER']?>"></span>
	<? if ($price['PERCENT'] > 0)
	{
		?>
		<div class="sale-off" id="<?=$itemIds['DSC_PERC']?>"><?=-$price['PERCENT']?>%</div>
		<?
	}?>
	<a href="<?=$item["DETAIL_PAGE_URL"]?>">
		<? $img = CFile::ResizeImageGet($item['~PREVIEW_PICTURE'], array('width'=>190, 'height'=>190), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
		<img data-src="<?=$img['src']?>" id="<?=$itemIds['SECOND_PICT']?>" class="lazy attachment-shop_catalog size-shop_catalog wp-post-image">
	</a>

	<a href="<?=$item["DETAIL_PAGE_URL"]?>" class="sw-quickview" data-ajax_url="/themes/sw_revo/?wc-ajax=%%endpoint%%"><i class="fa fa-eye" aria-hidden="true"></i></a>
</div>
<div class="item-content">
	<h4><a href="<?=$item["DETAIL_PAGE_URL"]?>" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></a></h4>
	<div class="item-price" data-entity="price-block">
		<span>
			<del>
				<span class="woocommerce-Price-amount amount" id="<?=$itemIds['PRICE_OLD']?>" <?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
					<span class="woocommerce-Price-currencySymbol"></span>
					<?=$item["PRICES"]["BASE"]["PRINT_VALUE_VAT"]?>
				</span>
			</del>
			<ins>
				<span class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol" id="<?=$itemIds['PRICE']?>">
					<?=$item["PRICES"]["BASE"]["PRINT_DISCOUNT_VALUE"]?>
				</span>
			</ins>
		</span>
	</div>
	<div style='display: none;' data-entity="quantity-block">
		<input id="<?=$itemIds['QUANTITY']?>" type="tel"
			name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>"
			value="1">
	</div>
	<div class="item-bottom clearfix" data-entity="buttons-block">
		<? if ($actualItem['CAN_BUY']){ ?>
		<div id="<?=$itemIds['BASKET_ACTIONS']?>">
			<a id="<?=$itemIds['BUY_LINK']?>" href="javascript:void(0)" rel="nofollow" class="button product_type_simple add_to_cart_button ajax_add_to_cart">В корзину</a>
		</div>
		<? }else{ ?>
			<a class="btn btn-link bnt_net_n <?=$buttonSizeClass?>"
			   id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" href="javascript:void(0)" rel="nofollow">
				<?php if($item['PROPERTIES']['ZAKAZ']['VALUE']):?>
					Предзаказ, <?=$item['PROPERTIES']['ZAKAZ']['VALUE']?>
				<?php else:?>
					<?=$arParams['MESS_NOT_AVAILABLE']?>
				<?php endif;?>
			</a>

		<? } ?>
	</div>
</div>
