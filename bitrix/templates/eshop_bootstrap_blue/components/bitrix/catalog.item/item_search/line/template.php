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

<div class="item-img products-thumb" data-entity="image-wrapper">
	<span id="<?=$itemIds['PICT_SLIDER']?>"></span>
	<? $bgImage = !empty($item['PREVIEW_PICTURE_SECOND']) ? $item['PREVIEW_PICTURE_SECOND']['SRC'] : $item['PREVIEW_PICTURE']['SRC']; ?>
	<span id="<?=$itemIds['SECOND_PICT']?>" style="background-image: url('<?=$bgImage?>'); display: none;"></span>

	<? if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && $price['PERCENT'] > 0)
	{
		?>
		<div class="sale-off" id="<?=$itemIds['DSC_PERC']?>"><?=-$price['PERCENT']?>%</div>
		<?
	}?>
	<a href="<?=$item["DETAIL_PAGE_URL"]?>">
		<img src="<?=$item['PREVIEW_PICTURE']['SRC']?>" id="<?=$itemIds['PICT']?>" class="attachment-shop_catalog size-shop_catalog wp-post-image">
	</a>
	<a href="javascript:void(0)" class="sw-quickview" data-ajax_url="/themes/sw_revo/?wc-ajax=%%endpoint%%"></a>
</div>
<div class="item-content">
	<h4><a href="<?=$item["DETAIL_PAGE_URL"]?>" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></a></h4>
	<div class="item-price"  data-entity="price-block">
		<span>
			<del>
				<span class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol"></span>
					<?=$item["PRICES"]["BASE"]["PRINT_VALUE_VAT"]?>
				</span>
			</del>
			<ins>
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
		<div id="<?=$itemIds['BASKET_ACTIONS']?>">
			<a id="<?=$itemIds['BUY_LINK']?>" href="javascript:void(0)" rel="nofollow" class="button product_type_simple add_to_cart_button ajax_add_to_cart">В корзину</a>
		</div>
	</div>
</div>
