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
<ul id="menu-primary-menu-1" class="nav nav-pills nav-mega revo-mega revo-menures">
	<?foreach($arResult as $itemIdex => $arItem):?>
		<?if ($arItem["DEPTH_LEVEL"] == "1"):?>
			<li class="menu-home revo-mega-menu level1">
				<a <? if($GLOBALS["APPLICATION"]->GetCurPage() != $arItem["LINK"]){ ?> href="<?=$arItem["LINK"]?>" <? } ?> class="item-link dropdown-toggle" data-toogle="dropdown">
					<span class="have-title">
						<span class="menu-title"><?=htmlspecialcharsbx($arItem["TEXT"])?></span>
					</span>
				</a>
				<?if ($arItem["LINK"] == "#pokupat"):?>
				<ul class="dropdown-menu nav-level1">
					<li class="menu-left-sidebar-grid">
						<a <? if($GLOBALS["APPLICATION"]->GetCurPage() != "/about/"){ ?> href="/about/" <? } ?>>
							<span class="have-title">
								<span class="menu-title">О магазине</span>
							</span>
						</a>
					</li>
					<li class="menu-left-sidebar-grid">
						<a <? if($GLOBALS["APPLICATION"]->GetCurPage() != "/garantiya/"){ ?> href="/garantiya/" <? } ?>>
							<span class="have-title">
								<span class="menu-title">Гарантия</span>
							</span>
						</a>
					</li>
					<li class="menu-left-sidebar-grid">
						<a <? if($GLOBALS["APPLICATION"]->GetCurPage() != "/oplata/"){ ?> href="/oplata/" <? } ?>>
							<span class="have-title">
								<span class="menu-title">Оплата</span>
							</span>
						</a>
					</li>
					<li class="menu-left-sidebar-grid">
						<a <? if($GLOBALS["APPLICATION"]->GetCurPage() != "/dostavka/"){ ?> href="/dostavka/" <? } ?>>
							<span class="have-title">
								<span class="menu-title">Доставка</span>
							</span>
						</a>
					</li>
				</ul>
				<?endif?>
			</li>
		<?endif?>
	<?endforeach;?>
</ul>
