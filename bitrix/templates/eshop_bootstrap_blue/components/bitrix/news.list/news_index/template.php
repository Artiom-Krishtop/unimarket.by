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
$this->setFrameMode(true);
?>

<div id="sw_reponsive_post_slider_1105650" class="responsive-post-slider responsive owl-carousel-slider clearfix">
	<div class="resp-slider-container">
		<div class="block-title">
			<h3>Статьи</h3>
		</div>
		<div class="slider responsive owl-carousel owl-carousel">
			<?if($arParams["DISPLAY_TOP_PAGER"]):?>
				<?=$arResult["NAV_STRING"]?><br />
			<?endif;?>
			<?foreach($arResult["ITEMS"] as $arItem):?>
				<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>

				<div class="item widget-pformat-detail" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
					<div class="item-inner">
						<div class="item-detail">
							<div class="img_over">
								<a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
									<? $img = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], array('width'=>355, 'height'=>220), BX_RESIZE_IMAGE_EXACT, true, false, false, 95); ?>
									<img src="<?=$img['src']?>" class="attachment-revo_blog-responsive1 size-revo_blog-responsive1 wp-post-image" alt="">
								</a>
								<div class="entry-date">
									<div class="day-time"><?=substr($arItem['TIMESTAMP_X'], 0, 2)?></div>
									<div class="month-time">
										<?
											$mont_news = substr($arItem['TIMESTAMP_X'], 3, 2);
											if($mont_news == '01'){
												echo 'Янв';
											}elseif($mont_news == '02'){
												echo 'Фев';
											}elseif($mont_news == '03'){
												echo 'Мар';
											}elseif($mont_news == '04'){
												echo 'Апр';
											}elseif($mont_news == '05'){
												echo 'Май';
											}elseif($mont_news == '06'){
												echo 'Июн';
											}elseif($mont_news == '07'){
												echo 'Июл';
											}elseif($mont_news == '08'){
												echo 'Авг';
											}elseif($mont_news == '09'){
												echo 'Сен';
											}elseif($mont_news == '10'){
												echo 'Окт';
											}elseif($mont_news == '11'){
												echo 'Ноя';
											}elseif($mont_news == '12'){
												echo 'Дек';
											}
										?>
									</div>
								</div>
							</div>
							<div class="entry-content">
								<div class="item-title">
									<h4><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?echo $arItem["NAME"]?></a></h4>
								</div>
								<div class="readmore">
									<i class="fa fa-caret-right"></i>
									<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="View more">Подробнее</a>
								</div>
							</div>
						</div>
					</div>
				</div>

			<?endforeach;?>
		</div>
	</div>
</div>
