<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\PaySystem\IRefund;
use Bitrix\Main\Diag\Debug;
use Bitrix\Sale\Order;

class OplatiHandler
extends PaySystem\ServiceHandler implements IRefund
{
  /**
   * @param Payment $payment
   * @param Request|null $request
   * @return PaySystem\ServiceResult
   */
  public function initiatePay(Payment $payment, Request $request = null)
  {
    $params = array();

    $this->setExtraParams($params);

    if ($this->isMobile()) {
      return $this->showTemplate($payment, "template_mobile");
    } else {
      return $this->showTemplate($payment, "template");
    }
  }

  /**
   * @return string
   */
  private function getPassword()
  {
    return Option::get('oplati.paysystem', 'password');
  }

  /**
   * @return string
   */
  private function getRegNum()
  {
    return Option::get('oplati.paysystem', 'regnum');
  }

  /**
   * @return bool
   */
  private function isMobile()
  {
    return preg_match(
      "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
      $_SERVER["HTTP_USER_AGENT"]
    );
  }

  /**
   * @return array
   */
  public static function getIndicativeFields()
  {
    return array('BX_HANDLER' => 'OPLATI');
  }

  /**
   * @param Payment $payment
   * @param int $refundableSum
   * @return PaySystem\ServiceResult
   */
  public function refund(Payment $payment, $refundableSum)
  {
    $result = new PaySystem\ServiceResult();

    $response = $this->sendRefundRequest($payment, $refundableSum);

    if ($response['status'] === 1) {
      $result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
    }

    return $result;
  }

  /**
   * @return array
   */
  private function sendRefundRequest(Payment $payment, $refundableSum)
  {
    $curl = curl_init();

    $fields = array(
      'sum' => $refundableSum,
      'orderNumber' => $payment->getId(),
    );

    $fields['details'] = $this->getDetailInfo($payment);

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getUrl($payment, 'refund') . '/' . $payment->getField('PS_INVOICE_ID') . '/reversals',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($fields),
      CURLOPT_HTTPHEADER => array(
        "password: " . $this->getPassword(),
        "regNum: " . $this->getRegNum(),
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    $response = json_decode($response, true);

    return $response;
  }

  /**
   * @param PaySystem\ServiceResult $result
   * @param Request $request
   * @return mixed
   */
  public function sendResponse(PaySystem\ServiceResult $result, Request $request)
  {
    $data = $result->getData();

    echo json_encode($data);
  }

  /**
   * @param Request $request
   * @return mixed
   */
  public function getPaymentIdFromRequest(Request $request)
  {
    return $request->get('orderNumber');
  }

  protected function getUrlList()
  {
    return array(
      'demandPayment' => array(
        self::ACTIVE_URL => 'https://cashboxapi.o-plati.by/ms-pay/pos/demandPayment'
      ),
      'consumerStatus' => array(
        self::ACTIVE_URL => 'https://cashboxapi.o-plati.by/ms-pay/pos/consumerStatus',
      ),
      'ready' => array(
        self::ACTIVE_URL => 'https://cashboxapi.o-plati.by/ms-pay/pos/payments',
      ),
      'checkStatus' => array(
        self::ACTIVE_URL => 'https://cashboxapi.o-plati.by/ms-pay/pos/payments',
      ),
      'refund' => array(
        self::ACTIVE_URL => 'https://cashboxapi.o-plati.by/ms-pay/pos/payments',
      ),
    );
  }

  /**
   * @param Payment $payment
   * @param Request $request
   * @return PaySystem\ServiceResult
   */
  public function processRequest(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();
    $action = $request->get('action');

    if ($action === 'demandPayment') {
      return $this->processDemandPaymentAction($payment, $request);
    } else if ($action === 'consumerStatus') {
      return $this->processConsumerStatusAction($payment, $request);
    } else if ($action === 'consumerReady') {
      return $this->processConsumerReadyAction($payment, $request);
    } else if ($action === 'paymentStatusAwait') {
      return $this->processPaymentStatusAwaitAction($payment, $request);
    } else if ($action === 'paymentStatus') {
      return $this->processPaymentStatusAction($payment, $request);
    } else if ($action === 'consumerStatusAwait') {
      return $this->processConsumerStatusAwaitAction($payment, $request);
    }

    return $result;
  }

  private function processConsumerStatusAwaitAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $sessionId = $request->get('sessionId');

    if (!$sessionId) {
      $errorMessage = 'No sessionId was provided';
      $result->addError(new Error($errorMessage));
      PaySystem\Logger::addError($errorMessage);

      return $result;
    }

    $ready = false;

    $timeStart = microtime(true);

    $maxExecTime = Option::get('oplati.paysystem', 'payment_confirm_await_time', 30);

    while (round(microtime(true) - $timeStart) < $maxExecTime) {
      $response = $this->sendConsumerStatusRequest($payment, $sessionId);

      if ($response['isConsumerReady'] === true) {
        $ready = true;
        break;
      }

      sleep(3);
    }

    if ($ready) {
      return $this->processConsumerReadyAction($payment, $request);
    }

    $result->setData($response);

    return $result;
  }

  private function sendConsumerStatusRequest(Payment $payment, $sessionId)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getUrl($payment, 'consumerStatus') . '?sessionId=' . $sessionId,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "regNum: " . $this->getRegNum()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    return $response;
  }

  private function processConsumerStatusAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $curl = curl_init();

    $sessionId = $request->get('sessionId');

    if (!$sessionId) {
      $errorMessage = 'No sessionId was provided';
      $result->addError(new Error($errorMessage));
      PaySystem\Logger::addError($errorMessage);

      return $result;
    }

    $curlOptions = array(
      CURLOPT_URL => $this->getUrl($payment, 'consumerStatus') . '?sessionId=' . $sessionId,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "regNum: " . $this->getRegNum()
      ),
    );

    curl_setopt_array($curl, $curlOptions);

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    $log = array(
      'method' => 'processConsumerStatusAction',
      'url' => $this->getUrl($payment, 'demandPayment'),
      'curl' => array(
        'options' => $curlOptions,
        'response' => $response
      )
    );

    $result->setData($response);

    $this->log($log);

    return $result;
  }

  private function processPaymentStatusAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $data = array('success' => false);
    $psData = array();

    $response = $this->sendCheckStatusRequest($payment, $request);

    $log = array(
      'method' => 'processPaymentStatusAction'
    );

    $psData = array(
      'PS_STATUS' => 'N',
      'PS_STATUS_CODE' => $response['status'],
      'PS_STATUS_DESCRIPTION' => $this->getStatusDescription($response['status']),
      'PS_CURRENCY' => 'BYN',
      'PS_SUM' => number_format($response['sum'], 2, '.', ''),
      'PS_RESPONSE_DATE' => new DateTime(),
      'PS_INVOICE_ID' => $response['paymentId']
    );

    if ($response['status'] === 1) {
      $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
      $data['success'] = true;
      $psData['PS_STATUS'] = 'Y';
    } else {
      $result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
    }

    $result->setPsData($psData);
    $result->setData($data);

    $log['psResult'] = array(
      'psData' => $result->getPsData(),
      'data' => $result->getData()
    );

    $this->log($log);

    return $result;
  }

  private function processDemandPaymentAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $curl = curl_init();

    $fields = array(
      'paymentMode' => 3
    );

    $curlOptions = array(
      CURLOPT_URL => $this->getUrl($payment, 'demandPayment'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($fields),
      CURLOPT_HTTPHEADER => array(
        "regNum: " . $this->getRegNum(),
        "password: " . $this->getPassword(),
        "Content-Type: application/json"
      ),
    );

    curl_setopt_array($curl, $curlOptions);

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    $log = array(
      'method' => 'processDemandPaymentAction',
      'url' => $this->getUrl($payment, 'demandPayment'),
      'curl' => array(
        'options' => $curlOptions,
        'response' => $response
      )
    );

    $this->log($log);

    $result->setData($response);

    return $result;
  }

  private function processPaymentStatusAwaitAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $timeStart = microtime(true);

    $maxExecTime = Option::get('oplati.paysystem', 'payment_confirm_await_time', 30);
    $data = array('success' => false);
    $psData = array();

    $log = array(
      'method' => 'processPaymentStatusAwaitAction',
      'maxExecTime' => $maxExecTime
    );

    while (round(microtime(true) - $timeStart) < $maxExecTime) {
      $response = $this->sendCheckStatusRequest($payment, $request);

      $psData = array(
        'PS_STATUS' => 'N',
        'PS_STATUS_CODE' => $response['status'],
        'PS_STATUS_DESCRIPTION' => $this->getStatusDescription($response['status']),
        'PS_CURRENCY' => 'BYN',
        'PS_SUM' => number_format($response['sum'], 2, '.', ''),
        'PS_RESPONSE_DATE' => new DateTime(),
        'PS_INVOICE_ID' => $response['paymentId']
      );

      if ($response['status'] === 1) {
        $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
        $data['success'] = true;
        $psData['PS_STATUS'] = 'Y';
      } else {
        $result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
      }

      if ($response['status'] !== 0) {
        break;
      }

      sleep(3);
    }

    $result->setData($data);
    $result->setPsData($psData);

    $log['psResult'] = array(
      'psData' => $result->getPsData(),
      'data' => $result->getData()
    );

    $this->log($log);

    return $result;
  }

  private function sendCheckStatusRequest(Payment $payment, Request $request)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getUrl($payment, 'checkStatus') . '/' . $payment->getField('PS_INVOICE_ID'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "regNum: " . $this->getRegNum()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    return $response;
  }

  private function processConsumerReadyAction(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();

    $response = $this->sendPaymentRequest($payment, $request);

    $log = array(
      'method' => 'processConsumerReadyAction',
      'response' => $response
    );

    $data = array();
    $psData = array();

    if (!$response) {
      $data['success'] = 0;
      $psData['PS_STATUS'] = 'N';
      $errorMessage = 'Could not get any response from ' . $this->getUrl($payment, 'ready');
      $result->addError(new Error($errorMessage));
      PaySystem\Logger::addError('Oplati: sendPaymentRequest: ' . $errorMessage);
    } else {
      $data['success'] = 1;
      $psData = array(
        'PS_STATUS' => 'Y',
        "PS_STATUS_CODE" => $response['status'],
        "PS_STATUS_DESCRIPTION" => $this->getStatusDescription($response['status']),
        "PS_SUM" => number_format($response['sum'], 2, '.', ''),
        "PS_CURRENCY" => 'BYN',
        "PS_RESPONSE_DATE" => new DateTime(),
        "PS_INVOICE_ID" => $response['paymentId']
      );
      if ($response['status'] === 1) {
        $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
      }
    }

    $result->setData($data);
    $result->setPsData($psData);

    $log['psResult'] = array(
      'psData' => $result->getPsData(),
      'data' => $result->getData()
    );

    $this->log($log);

    return $result;
  }

  private function getStatusDescription($code)
  {
    $description = array(
      0 => 'Платеж ожидает подтверждения',
      1 => 'Платеж совершен',
      2 => 'Отказ от платежа',
      3 => 'Недостаточно средств',
      4 => 'Клиент не подтвердил платеж',
    );

    return $description[$code];
  }

  private function sendPaymentRequest(Payment $payment, Request $request)
  {
    $curl = curl_init();

    $fields = array(
      'sum' => number_format($payment->getSum(), 2, '.', ''),
      'orderNumber' => $payment->getId(),
      'sessionId' => $request->get('sessionId')
    );


    $fields['details'] = $this->getDetailInfo($payment, $request);

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getUrl($payment, 'ready'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($fields),
      CURLOPT_HTTPHEADER => array(
        "password: " . $this->getPassword(),
        "regNum: " . $this->getRegNum(),
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    return $response;
  }

  /**
   * Информация для чека
   * @return array
   */
  private function getDetailInfo(Payment $payment, Request $request = null)
  {

    $items = array();
    $amountTotal = 0;
    /**
     * @var BasketItem $item
     */
    $orderId = $payment->getOrderId();
    $order = Order::load($orderId);
    foreach ($order->getBasket()->getBasketItems() as $item) {
      $name = $item->getField('NAME');
      $quantity = $item->getQuantity();
      $price = $item->getPrice();
      $sum = $quantity * $price;

      $items[] = array(
        'type' => 1,
        'name' => $name,
        'quantity' => $quantity,
        'price' => $price,
        'cost' => $sum
      );
      $amountTotal += $sum;
    }

    $shipmentPrice = $order->getShipmentCollection()->getPriceDelivery();

    if ($shipmentPrice) {
      $items[] = array(
        'type' => 2,
        'name' => 'Доставка',
        'price' => $shipmentPrice,
        'cost' => $shipmentPrice
      );
    }

    return [
      'items' => $items,
      'amountTotal' => $payment->getSum()
    ];
  }

  /**
   * @return array
   */
  public function getCurrencyList()
  {
    return array('BYN');
  }

  /**
   * @return bool
   */
  public function isTuned()
  {
    return !empty($this->getPassword()) && !empty($this->getRegNum());
  }

  private function log($data)
  {
    return file_put_contents(
      __DIR__ . '/oplati-' . date('d-m-Y-H') . '-log.json',
      json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE),
      FILE_APPEND
    );
  }
}
