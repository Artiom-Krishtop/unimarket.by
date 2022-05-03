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
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
$this->addExternalCss("/bitrix/css/main/font-awesome.css");
$this->addExternalCss($this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css');
CUtil::InitJSCore(array('fx'));
?>

	<div class="row sidebar-row">
	<div class="bx-newsdetail-block" id="<?echo $this->GetEditAreaId($arResult['ID'])?>">

		<div class="entry-wrap detail_page_news">
			<div class="entry-thumb single-thumb">
				<img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" class="attachment-revo_detail_thumb size-revo_detail_thumb wp-post-image">
				<span class="entry-date latest_post_date">
					<span class="day-time"><?=substr($arResult['TIMESTAMP_X'], 0, 2)?></span>
					<span class="month-time">
						<?
							$mont_news = substr($arResult['TIMESTAMP_X'], 3, 2);
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
					</span>
				</span>
				<? if(empty($arResult["DETAIL_PICTURE"]["SRC"])){ ?>
					<style>
						.detail_page_news .entry-thumb.single-thumb{
							padding-bottom: 57px;
						}
					</style>
				<? } ?>
			</div>
			<div class="entry-content clearfix">
				<div class="entry-summary single-content ">
					<?if($arResult["NAV_RESULT"]):?>
						<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>
						<?echo $arResult["NAV_TEXT"];?>
						<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
					<?elseif(strlen($arResult["DETAIL_TEXT"])>0):?>
						<?echo $arResult["DETAIL_TEXT"];?>
					<?else:?>
						<?echo $arResult["PREVIEW_TEXT"];?>
					<?endif?>
				</div>
				<div class="clear"></div>
				<div class="single-content-bottom clearfix">
					<div class="social-share">
						<div class="title-share">Поделиться</div>
						<div class="wrap-content">
							<script type="text/javascript">(function() {
							  if (window.pluso)if (typeof window.pluso.start == "function") return;
							  if (window.ifpluso==undefined) { window.ifpluso = 1;
							    var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
							    s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
							    s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
							    var h=d[g]('body')[0];
							    h.appendChild(s);
							  }})();</script>
							<div class="pluso" data-background="transparent" data-options="medium,round,line,horizontal,nocounter,theme=04" data-services="vkontakte,odnoklassniki,facebook,twitter,moimir,email,print"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	</div>
</div>
<script type="text/javascript">
	BX.ready(function() {
		var slider = new JCNewsSlider('<?=CUtil::JSEscape($this->GetEditAreaId($arResult['ID']));?>', {
			imagesContainerClassName: 'bx-newsdetail-slider-container',
			leftArrowClassName: 'bx-newsdetail-slider-arrow-container-left',
			rightArrowClassName: 'bx-newsdetail-slider-arrow-container-right',
			controlContainerClassName: 'bx-newsdetail-slider-control'
		});
	});
</script>