<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconlistcard.bundle.css',
	'js' => 'dist/iconlistcard.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.card.basecard',
		'landing.loc',
	],
	'skip_core' => false,
];