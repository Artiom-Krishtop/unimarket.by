<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

if (empty($arResult))
	return;
?>
<ul id="menu-primary-menu" class="revo_resmenu">
	<li class="res-dropdown menu-home">
		<a class="item-link dropdown-toggle" href="/catalog/novinki/">Новинки</a>
	</li>
	<li class="res-dropdown menu-home">
		<a class="item-link dropdown-toggle" href="/catalog/aktsii/">Акции</a>
	</li>
	<?foreach($arResult as $itemIdex => $arItem):?>
		<?if ($arItem["DEPTH_LEVEL"] == "1"):?>
			<li class="res-dropdown menu-home">
				<a class="item-link dropdown-toggle" href="<?=$arItem["LINK"]?>"><?=htmlspecialcharsbx($arItem["TEXT"])?></a>
			</li>
		<?endif?>
	<?endforeach;?>
</ul>
