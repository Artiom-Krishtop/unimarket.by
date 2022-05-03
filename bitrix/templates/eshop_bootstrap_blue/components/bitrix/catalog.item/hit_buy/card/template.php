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

		<div class="item-img">
			<a href="<?=$item["DETAIL_PAGE_URL"]?>" title="<?=$item["NAME"]?>">
				<? $img = CFile::ResizeImageGet($item['~PREVIEW_PICTURE'], array('width'=>78, 'height'=>78), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
				<img src="<?=$img['src']?>" class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image" alt="">
			</a>
		</div>
		<div class="item-content">
			<h4>
				<a href="<?=$item["DETAIL_PAGE_URL"]?>" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></a>
			</h4>
			<div class="item-price">
				<del>
					<span class="woocommerce-Price-amount amount" id="<?=$itemIds['PRICE_OLD']?>" <?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
						<?=$item["PRICES"]["BASE"]["PRINT_VALUE_VAT"]?>
					</span>
				</del>
				<ins>
					<span class="woocommerce-Price-amount amount">
						<?=$item["PRICES"]["BASE"]["PRINT_DISCOUNT_VALUE"]?>
					</span>
				</ins>
			</div>
		</div>
