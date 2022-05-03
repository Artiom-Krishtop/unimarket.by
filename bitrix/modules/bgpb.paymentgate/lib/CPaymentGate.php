<?
namespace BGPB;

class PaymentGate
{
    private $apiUrl = false;
    private $apiPort = false;
    private $userName = false;
    private $password = false;

    private $requestMethod = 'POST'; 
    private $testModeApiUrl = 'https://test.paymentgate.ru/testpayment/rest/';

    private $sslMode = false;
    private $sslCert = false;
    private $sslKey = false;
    private $sslPasswd = false;

    /*
     * @param string $userName  PaymentGate user userName
     * @param string $password  PaymentGate user password
     * @param string $apiUrl  base url for curl requests
     */
    function __construct($userName,$password,$apiUrl,$apiPort=false){
        $this->userName = $userName;
        $this->password = $password;
        $this->apiUrl = $apiUrl;
        if(!empty($apiPort))
            $this->apiPort = $apiPort;
    }

    /*
     * Enables/Disables test mode
     *
     * @param bool $testMode
     */
    public function setTestMode($testMode){
        /*$this->testMode = $testMode;*/
        $this->testMode = false;
    }

    /*
     * Enables/Disables SSL mode
     *
     * @param bool $ssl
     */
    public function setSslMode($ssl){
        $this->sslMode = $ssl;
    }

    /*
     * Sets request method for curl requests
     *
     * @param string $requestMethod  POST|GET|DELETE
     */
    public function setRequestMethod($requestMethod){
        if(in_array($requestMethod, array('GET','POST','DELETE')))
            $this->requestMethod = $requestMethod;
    }


    /*
     * Sets path to SSL certificate
     *
     * @param string $setSslCert  path to SSL certificate
     */
    public function setSslCert($sslCert){
        $this->sslCert = $sslCert;
    }


    /*
     * Sets path to SSL key
     *
     * @param string $setSslKey  path to SSL key
     */
    public function setSslKey($sslKey){
        $this->sslKey = $sslKey;
    }


    /*
     * Sets SSL password
     *
     * @param string $sslPasswd  SSL password
     */
    public function setSslPasswd($sslPasswd){
        $this->sslPasswd = $sslPasswd;
    }

    /*
     * Register order in paymentgate
     *
     * @param array $params
         * orderNumber required AN..32
         * amount required N..20
         * currency N3
         * returnUrl required AN..512
         * failUrl AN..512
         * description AN..1024
         * language A2
         * pageView A..7
         * clientId AN..255
         * jsonParams AN..1024
     *
     * @return array
        * orderId AN..64
        * formUrl AN..512
        * errorCode N3 0,1,3,4,5,7
        * errorMessage AN..512
     */
    public function registerOrder($params){
        return $this->sendRequest('register.do',$params);
    }

    /*
     * Gets order status from paymentgate
     *
     * @param array $params
        * orderId required AN..64
        * language A2
     *
     * @return array
         * OrderStatus N2 0,2,3,4,5,6
         * ErrorCode N3 0,2,5,6
         * ErrorMessage AN..512
         * OrderNumber AN..32
         * Pan N..19
         * expiration N6
         * cardholderName A..64
         * Amount N..20
         * currency N3
         * approvalCode AN6
         * authCode N3
         * ip AN..20
         * BindingInfo:
            * clientId AN..255
            * bindingId AN..255
     */
    public function getOrderStatus($params){
        return $this->sendRequest('getOrderStatus.do',$params);
    }

    /*
     * Gets bindings for client
     *
     * @param string $clientId required AN..255   shop client id
     *
     * @return array
     * errorCode N1 0,1,2,5,7
     * errorMessage AN..512
     * binding
        * bindingId AN..255
        * maskedPan N..19
        * expiryDate N6
     */
    public function getBindings($clientId){
        return $this->sendRequest('getBindings.do',array('clientId'=>$clientId));
    }

    /*
     * Creates a request to api
     *
     * @param string $method  method string for curl requests
     * @param array $params  method parameters
     * @return array
     */
    private function sendRequest($method, $params = ''){
        if($this->testMode)
            $url = $this->testModeApiUrl;
        else
            $url = $this->apiUrl;

        $url .= $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if($this->sslMode){
/*            \Bitrix\Main\Diag\Debug::writeToFile('ssl ON');
            \Bitrix\Main\Diag\Debug::writeToFile($this->sslCert);
            \Bitrix\Main\Diag\Debug::writeToFile(\CFile::GetPath($busValues['SSL_CERTIFICATE']));
            \Bitrix\Main\Diag\Debug::writeToFile($this->sslKey);
            \Bitrix\Main\Diag\Debug::writeToFile(''.$_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($busValues['SSL_KEY']));
            \Bitrix\Main\Diag\Debug::writeToFile($this->sslPasswd);*/

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLCERT, $this->sslCert);
            curl_setopt($ch, CURLOPT_SSLKEY, $this->sslKey);
            curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->sslPasswd);
        }else{
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if(!empty($this->apiPort))
            curl_setopt($ch, CURLOPT_PORT, $this->apiPort);

        if(!is_array($params))
            $params = array();
        if(empty($params['userName']))
            $params['userName']=$this->userName;
        if(empty($params['password']))
            $params['password']=$this->password;

        if ($this->requestMethod == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }elseif($this->requestMethod == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }else{
            $url .= '?'.http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL, $url);

$fp = fopen(dirname(__FILE__).'/errorlog.txt', 'w');
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_STDERR, $fp);

        $result=curl_exec($ch);





/*        \Bitrix\Main\Diag\Debug::writeToFile($params);
        \Bitrix\Main\Diag\Debug::writeToFile(curl_getinfo($ch));
        \Bitrix\Main\Diag\Debug::writeToFile(curl_error($ch));
        \Bitrix\Main\Diag\Debug::writeToFile($result);*/

        curl_close($ch);

        return json_decode($result,true);
    }
}
