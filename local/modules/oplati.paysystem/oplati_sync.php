<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager;

if (!Loader::includeModule('oplati.paysystem') || !Loader::includeModule('sale')) {
  die();
}

$paySystems = Manager::getList(array(
  'filter' => ['ACTION_FILE' => 'oplati', 'ACTIVE' => 'Y'],
  'select' => ['*']
))->fetchAll();


if (empty($paySystems)) {
  die();
}

foreach ($paySystems as $ps) {
  $psIds[$ps['ID']] = $ps['ID'];
}

$payments = PaymentCollection::getList(array(
  'select' => array('*'),
  'filter' => array('=PAY_SYSTEM_ID' => $psIds, 'PAID' => 'N', 'PS_STATUS_CODE' => 0)
))->fetchAll();

if (empty($payments)) {
  die();
}

foreach ($payments as $payment) {
  $server = Application::getInstance()->getContext()->getServer();

  $request = new HttpRequest(
    $server,
    array('action' => 'paymentStatus', 'orderNumber' => $payment['ID'], 'BX_HANDLER' => 'OPLATI'),
    array(),
    array(),
    array()
  );

  $service = Manager::getObjectById($payment['PAY_SYSTEM_ID']);

  if ($service) {
    $result = $service->processRequest($request);
  }
}
