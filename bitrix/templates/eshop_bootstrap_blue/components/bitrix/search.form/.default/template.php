<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
$this->setFrameMode(true);?>
<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<div id="sw_woo_search_1" class="search input-group" data-height_image="50" data-width_image="50" data-show_image="1" data-show_price="1" data-character="3" data-limit="10" data-search_type="1">
		<div class="content-search">
			<?if($arParams["USE_SUGGEST"] === "Y"):?><?$APPLICATION->IncludeComponent(
					"bitrix:search.suggest.input",
					"",
					array(
						"NAME" => "q",
						"VALUE" => "",
						"INPUT_SIZE" => 15,
						"DROPDOWN_SIZE" => 10,
					),
					$component, array("HIDE_ICONS" => "Y")
				);?>
			<?else:?>
				<input class="autosearch-input" type="text" name="q" value="" size="50" maxlength="50"  placeholder="Поиск..."/>
			<?endif;?>
		</div>
		<span class="input-group-btn">
			<button type="submit" class="fa fa-search button-search-pro form-button" name="s"></button>
		</span>
	</div>
</form>
</div>
