<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager;

if (!Loader::includeModule('oplati.paysystem') || !Loader::includeModule('sale')) {
  echo 'Could not load required modules' . PHP_EOL;
  die();
}

$curl = curl_init();

$date = new DateTime();

$shift = $date->format('d-m-Y');

$url = "https://cashboxapi.o-plati.by/ms-pay/pos/paymentReports?shift=" . $shift;

$curlOptions = array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "regNum: " . Option::get('oplati.paysystem', 'regnum'),
    "password: " . Option::get('oplati.paysystem', 'password')
  ),
);

curl_setopt_array($curl, $curlOptions);

echo 'Set curl options: ' . PHP_EOL . json_encode($curlOptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo 'Fetching url: ' . $url . PHP_EOL;

$response = curl_exec($curl);

echo 'Response: ' . PHP_EOL . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

curl_close($curl);

$response = json_decode($response, true);

$paySystems = Manager::getList(array(
  'filter' => ['ACTION_FILE' => 'oplati', 'ACTIVE' => 'Y'],
  'select' => ['*']
))->fetchAll();

if (!empty($paySystems)) {
  echo 'Fetched pay systems from db: ' . json_encode($paySystems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
  echo 'Could not get pay system with oplati handler' . PHP_EOL;
  die();
}

foreach ($paySystems as $ps) {
  $psIds[$ps['ID']] = $ps['ID'];
}

$orderIds = array();

foreach ($response as $paymentReport) {
  $orderIds[$paymentReport['orderNumber']] = $paymentReport['orderNumber'];
}

$dbPayments = PaymentCollection::getList(array(
  'select' => array('*'),
  'filter' => array('=PAY_SYSTEM_ID' => $psIds, 'ORDER_ID' => $orderIds)
))->fetchAll();

if (!empty($dbPayments)) {
  echo 'Fetched payments from db: ' . json_encode($dbPayments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
  echo 'Could not get any payments' . PHP_EOL;
  die();
}

$payments = array();

foreach ($dbPayments as $payment) {
  $payments[$payment['PS_INVOICE_ID']] = $payment;
}

foreach ($response as &$paymentReport) {
  $sitePayment = $payments[$paymentReport['paymentId']];

  if ($sitePayment['PS_STATUS_CODE'] == $paymentReport['status']) {
    if (round($sitePayment['SUM'], 2) == round($paymentReport['sum'], 2)) {
      $paymentReport['passed'] = true;
    }
  }
}

$emails = explode(',', Option::get('oplati.paysystem', 'emails'));

foreach ($response as $paymentReport) {
  if ($paymentReport['passed'] !== true) {
    $eventName = 'OPLATI_RECONCILIATION_FAIL';

    foreach ($emails as $email) {
      $result = Event::send(array(
        "EVENT_NAME" => $eventName,
        "LID" => SITE_ID,
        "C_FIELDS" => array(
          'EMAIL' => $email
        ),
      ));
    }
    die();
  }
}
echo 'Everything is ok!.' . PHP_EOL;
