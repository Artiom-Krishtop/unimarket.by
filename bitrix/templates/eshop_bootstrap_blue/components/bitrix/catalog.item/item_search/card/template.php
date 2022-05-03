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
	<a href="<?=$item["DETAIL_PAGE_URL"]?>" class="sw-quickview" data-ajax_url="/themes/sw_revo/?wc-ajax=%%endpoint%%"><i class="fa fa-eye" aria-hidden="true"></i></a>
</div>
<div class="item-content">
	<h4><a href="<?=$item["DETAIL_PAGE_URL"]?>" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></a></h4>
	<div class="item-price"  data-entity="price-block">
		<span>
			<del>
				<span class="woocommerce-Price-amount amount" id="<?=$itemIds['PRICE_OLD']?>" <?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
					<span class="woocommerce-Price-currencySymbol"></span>
					<?=$item["PRICES"]["BASE"]["PRINT_VALUE_VAT"]?>
				</span>
			</del>
			<ins>
				<span class="woocommerce-Price-currencySymbol" id="<?=$itemIds['PRICE']?>">
					<?
                    $db_res = CPrice::GetList( array(), array( "PRODUCT_ID" => $item['ID']) );
                    if ($ar_res = $db_res->Fetch()) {
                        echo CurrencyFormat($ar_res["PRICE"], $ar_res["CURRENCY"]);
                    }
                    ?>
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
        <?php
        $rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' =>$item['ID']), false, false, array('AMOUNT'));
        if ($arStore = $rsStore->Fetch())
        {
            if($arStore['AMOUNT'] > 0){ ?>
                <a id="<?=$itemIds['BUY_LINK']?>" href="<?=$item["DETAIL_PAGE_URL"]?>" rel="nofollow" class="button product_type_simple add_to_cart_button ajax_add_to_cart">Подробнее</a>
            <? }else{ ?>
                <a class="btn btn-link bnt_net_n btn-md" id="bx_2581286570_422_507bde831b9444f09329ce3189be5662_not_avail" href="javascript:void(0)" rel="nofollow">Нет в наличии</a>
            <? }
        }
        ?>
        </div>
	</div>



</div>
