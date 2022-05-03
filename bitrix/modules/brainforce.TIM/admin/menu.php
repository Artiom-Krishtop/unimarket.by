<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight('conversion') == 'D')
{
	return false;
}
else
{
	$menu = array(
		array(
			'parent_menu' => 'global_menu_marketing',
			'section' => 'TIM-bot',
			'sort' => 100,
			'text' => Loc::getMessage('BRAINFORCE_TIM_TEXT'),
			'title' => Loc::getMessage('BRAINFORCE_TIM_TITLE'),
			'icon' => 'brainforce_tim_menu_icon',
			'page_icon' => 'brainforce_tim_page_icon',
			'items_id' => 'menu_brainforce_tim',
			'url' => 'brainforce.tim_send.php?lang='.LANGUAGE_ID,
//			'items' => array(
//				array(
//					'text' => Loc::getMessage('CONVERSION_MENU_SUMMARY_TEXT'),
//					'title' => Loc::getMessage('CONVERSION_MENU_SUMMARY_TEXT'),
//					'url' => 'conversion_summary.php?lang='.LANGUAGE_ID,
//				),
//			),
		),
	);

	return $menu;
}