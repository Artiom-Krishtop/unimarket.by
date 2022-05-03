<?define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("BGPB Payment");

use Bitrix\Sale\Order;
use Bitrix\Main\Loader;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$result = false;
if($request->get('PAYMENT_ID')!='' && Loader::includeModule('sale')){
    list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($request->get('PAYMENT_ID'));
    if ($orderId > 0)
    {
        /** @var \Bitrix\Sale\Order $order */
        $order = \Bitrix\Sale\Order::load($orderId);
        if ($order)
        {
            /** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
            $paymentCollection = $order->getPaymentCollection();
            if ($paymentCollection && $paymentId > 0)
            {
                /** @var \Bitrix\Sale\Payment $payment */
                $payment = $paymentCollection->getItemById($paymentId);
                if ($payment)
                {
                    $service = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                    if ($service)
                        $result = $service->processRequest($request);
                }
            }
        }
    }
}

if(empty($result)){
    echo '<p class="error">Заказ не найден</p>';
}else{
    if($result->isSuccess())
        echo '<p>Статус заказа обновлён</p>';
/*    else
        echo '<p class="error">'.implode('<br/>',$result->getErrorMessages()).'</p>';*/
}
?><?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');