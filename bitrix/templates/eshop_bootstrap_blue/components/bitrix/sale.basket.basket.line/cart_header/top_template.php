<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */
$compositeStub = (isset($arResult['COMPOSITE_STUB']) && $arResult['COMPOSITE_STUB'] == 'Y');
?>
<a href='<?= $arParams['PATH_TO_BASKET'] ?>' class="top-form top-form-minicart revo-minicart pull-right">
	<div class="top-minicart-icon pull-right">
<?
		if (!$arResult["DISABLE_USE_BASKET"])
		{
			?>
			<div class="cart-contents">
				<? if (!$compositeStub)
				{
					if ($arParams['SHOW_NUM_PRODUCTS'] == 'Y' && ($arResult['NUM_PRODUCTS'] > 0 || $arParams['SHOW_EMPTY_VALUES'] == 'Y'))
					{
						echo '<span class="minicart-number">'.$arResult['NUM_PRODUCTS'].'</span>';
					}

				} ?>
			</div>
			<?
		}

		if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'):?>
			<div style="padding-top: 4px;">
			<span class="icon_info"></span>
			<a href="<?=$arParams['PATH_TO_PERSONAL']?>"><?=GetMessage('TSB1_PERSONAL')?></a>
			</div>
		<?endif?>

	</div>
</a>
