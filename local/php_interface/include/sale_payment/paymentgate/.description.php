<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => 'BGPB Payment',
	'SORT' => 500,
	'CODES' => array(
		'PS_IS_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_TEST'),
			'SORT' => 100,
			'GROUP' => 'PAYSYSTEM',
			'INPUT' => array(
				'TYPE' => 'Y/N'
			)
		),
		'PS_IS_BINDING' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_BINDING'),
			'SORT' => 110,
			'GROUP' => 'PAYSYSTEM',
			'INPUT' => array(
				'TYPE' => 'Y/N'
			)
		),
		'SITE_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_SITENAME'),
			'SORT' => 200,
			'GROUP' => 'PAYSYSTEM'
		),
		'URL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_URL'),
			'SORT' => 210,
			'GROUP' => 'PAYSYSTEM'
		),
		'PORT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_PORT'),
			'SORT' => 250,
			'GROUP' => 'PAYSYSTEM'
		),
		'USER_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_USER_NAME'),
			'SORT' => 300,
			'GROUP' => 'PAYSYSTEM'
		),
		'PASSWORD' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_PASSWORD'),
			'SORT' => 400,
			'GROUP' => 'PAYSYSTEM'
		),
		'USE_SSL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_USE_SSL'),
			'SORT' => 410,
			'GROUP' => 'PAYSYSTEM',
			'INPUT' => array(
				'TYPE' => 'Y/N'
			)
		),
		'SSL_KEY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_SSL_KEY'),
			'SORT' => 420,
			'GROUP' => 'PAYSYSTEM',
			'INPUT' => array('TYPE' => 'FILE')
		),
		'SSL_KEY_PASSWORD' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_SSL_KEY_PASSWORD'),
			'SORT' => 430,
			'GROUP' => 'PAYSYSTEM'
		),
		'SSL_CERTIFICATE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_SSL_CERTIFICATE'),
			'SORT' => 440,
			'GROUP' => 'PAYSYSTEM',
			'INPUT' => array('TYPE' => 'FILE')
		),
		'RETURN_URL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_RETURN_URL'),
			'SORT' => 500,
			'GROUP' => 'PAYSYSTEM',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/paymentgate/',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'FAIL_URL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_FAIL_URL'),
			'SORT' => 600,
			'GROUP' => 'PAYSYSTEM',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'http'. (isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/paymentgate/',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'CLIENT_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_CLIENT_ID'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'USER'
			)
		),
		'PAYMENT_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_PAYMENT_ID'),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'CURRENCY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_CURRENCY'),
			'SORT' => 900,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '933',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SUM' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_SUM'),
			'SORT' => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'ORDER_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_ORDER_ID'),
			'SORT' => 1100,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'XML_ID',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'FORM_URL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_FORM_URL'),
			'SORT' => 1150,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'COMMENTS',
				'PROVIDER_KEY' => 'ORDER'
			)
		),
		'DESCRIPTION' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYMENTGATE_DESCRIPTION'),
			'SORT' => 1200,
			'GROUP' => 'PAYMENT'
		)
	)
);