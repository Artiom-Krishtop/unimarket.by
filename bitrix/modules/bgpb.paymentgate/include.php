<?defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses('bgpb.paymentgate', array(
    'BGPB\Paymentgate' => 'lib/CPaymentGate.php',
));