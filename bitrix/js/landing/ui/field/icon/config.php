<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/icon.bundle.css',
	'js' => 'dist/icon.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.panel.iconpanel',
		'landing.ui.field.image',
	],
	'skip_core' => false,
];