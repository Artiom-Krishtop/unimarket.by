<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

if (empty($arResult["ALL_ITEMS"]))
	return;

CUtil::InitJSCore();

if (file_exists($_SERVER["DOCUMENT_ROOT"].$this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css'))
	$APPLICATION->SetAdditionalCSS($this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css');

$menuBlockId = "catalog_menu_".$this->randString();
?>
<div id="<?=$menuBlockId?>">
	<nav id="cont_<?=$menuBlockId?>">
		<ul  class="nav vertical-megamenu revo-mega revo-menures" id="ul_<?=$menuBlockId?>">
		<?
		$count = 0;
		foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns): $count++; ?>     <!-- first level-->
			<?$existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] || $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;?>
			<li class="dropdown menu-fashion revo-mega-menu level1 revo-menu-img">
				<a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>" class="item-link dropdown-toggle" data-toogle="dropdown">
					<span class="have-title">
						<span class="menu-img">
							<? $arFilter = array('IBLOCK_ID' => 2,'NAME'=>$arResult["ALL_ITEMS"][$itemID]["TEXT"]);
						   $rsSect = CIBlockSection::GetList(false,$arFilter,false,array("IBLOCK_ID", "ID", "NAME", 'CODE', "UF_ICO_CAT"));
							 $idIt = '';
						   while ($arSect = $rsSect->GetNext()){ $idIt = $arSect['ID']; ?>
								 <img class='img_left_menu_index' src="<?=CFile::GetPath($arSect['UF_ICO_CAT']);?>" />
						   <? } ?>
						</span>
						<span class="menu-title">
							<?=$arResult['ALL_ITEMS'][$itemID]['TEXT']?>
						</span>
					</span>
				</a>
				<?
				if(!empty($idIt)){
					$arSelect_child = Array("IBLOCK_ID", "ID", "NAME", "SECTION_PAGE_URL");
					$arFilter_child = Array("IBLOCK_ID"=>2,"SECTION_ID"=>$idIt,"ACTIVE"=>"Y");
					$res_child = CIBlockSection::GetList(array('sort'=>'asc'), $arFilter_child, false, $arSelect_child, false);
					if(!empty($res_child)){
						echo '<div class="child_menu_left">';
						while($ar_fields_child = $res_child->GetNext()){ ?>
							<a href='<?=$ar_fields_child['SECTION_PAGE_URL']?>' class='a_liset_cat'>
								<span><?=$ar_fields_child['NAME']?></span>
							</a>
						<? }
						echo '</div>';
					}
				} ?>
			</li>
			<?
			if($count == 9 && $APPLICATION->GetCurPage(false) === '/'){ ?>
				<li class="dropdown menu-fashion revo-mega-menu level1 revo-menu-img last_list_men">
					<a href="/catalog/" class="item-link dropdown-toggle" data-toogle="dropdown">
						<span class="have-title">
							<span class="menu-img">
							</span>
							<span class="menu-title">
								Показать все
							</span>
						</span>
					</a>
				</li>
			<? break; }
		endforeach;?>
		</ul>
		<div style="clear: both;"></div>
	</nav>
</div>

<script>
	BX.ready(function () {
		window.obj_<?=$menuBlockId?> = new BX.Main.Menu.CatalogHorizontal('<?=CUtil::JSEscape($menuBlockId)?>', <?=CUtil::PhpToJSObject($arResult["ITEMS_IMG_DESC"])?>);
	});
</script>
