<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

<?
	$frame = $this->createFrame()->begin("");
?>


	<div class="rev_slider owl-carousel">
		<?foreach($arResult["BANNERS"] as $k => $banner):?>
		<div class='item_big_ban'>
			<a href=''>
				
			</a>
			<div class='content_item_big_ban'>
				<?=$banner?>
			</div>
		</div>
		<?endforeach;?>
	</div>

<?$frame->end();?>

<?endif;?>
