<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_67_2_NAME'),
		'section' => ['text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-text-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT_2'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT'),
			'type' => ['animation'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT'),
			'type' => ['typo'],
		],
		'.landing-block-node-text-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_TEXT_2'),
			'type' => ['typo'],
		],
		'.landing-block-node-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_BLOCK'),
			'type' => 'align-items',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_2_ELEMENTS'),
			'type' => ['container', 'padding-top', 'padding-bottom'],
		],
	],
];