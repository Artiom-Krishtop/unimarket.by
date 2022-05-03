<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    require 'generateAllData.php';

    // $str = '{"update_id":289385769,
    //     "callback_query":{"id":"4253940502126176688","from":{"id":990447705,"is_bot":false,"first_name":"Frog\ud83d\ude43","last_name":"Dappi","username":"Keliorw","language_code":"ru"},"message":{"message_id":1503,"from":{"id":1854690114,"is_bot":true,"first_name":"Booksir","username":"ShopShearchBot"},"chat":{"id":990447705,"first_name":"Frog\ud83d\ude43","last_name":"Dappi","username":"Keliorw","type":"private"},"date":1628006069,"text":"\u0421\u043f\u0438\u0441\u043e\u043a \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u0439","reply_markup":{"inline_keyboard":[[{"text":"\ud83d\udcd7\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u043a\u043d\u0438\u0433\u0438","callback_data":"category~230"}],[{"text":"\ud83d\udcd7\u0423\u0447\u0435\u0431\u043d\u0430\u044f \u043b\u0438\u0442\u0435\u0440\u0430\u0442\u0443\u0440\u0430","callback_data":"category~400"}],[{"text":"\ud83d\udcd7\u0425\u0443\u0434\u043e\u0436\u0435\u0441\u0442\u0432\u0435\u043d\u043d\u0430\u044f \u043b\u0438\u0442\u0435\u0440\u0430\u0442\u0443\u0440\u0430","callback_data":"category~401"}],[{"text":"\ud83d\udcd7\u0411\u0438\u0437\u043d\u0435\u0441 \u043b\u0438\u0442\u0435\u0440\u0430\u0442\u0443\u0440\u0430","callback_data":"category~403"}],[{"text":"\ud83d\udcd7\u041d\u0435\u0445\u0443\u0434\u043e\u0436\u0435\u0441\u0442\u0432\u0435\u043d\u043d\u0430\u044f \u043b\u0438\u0442\u0435\u0440\u0430\u0442\u0443\u0440\u0430","callback_data":"category~404"}]]}},"chat_instance":"6789582165807870123","data":"category~230"}}';
    // $str = (array)json_decode($str);
    // $str = (array)$str['message'];
    // echo "<pre>";
    // var_dump($str);
    // echo "</pre>";

    // echo hex2bin();
    //===============================================================================================

    // $res = \Bitrix\Sale\Delivery\Services\Table::getList(
    //     array(
    //         'order' => array("SORT" => "ASC"),
    //         'filter' => array('ACTIVE' => 'Y'),
    //         'select' => array('*')
    //     ) 
    // );
    // if ($dev = $res->fetch()) {
    //     // echo "<pre>";
    //     // var_dump($dev);
    //     // echo "</pre>";
    //     $dbRestr = \Bitrix\Sale\Delivery\Restrictions\Manager::getList(array(
    //         'filter' => array('SERVICE_ID' => $dev['ID']) // ID службы доставки 
    //     ));
    //     while ($arRestr = $dbRestr->fetch()) {
    //         if(!$arRestr["PARAMS"]) { // У ограничений по платежной системе нет параметров
    //             $arRestr["PARAMS"] = array(); 
    //         }
    //         $params = $arRestr["CLASS_NAME"]::prepareParamsValues($arRestr["PARAMS"], $dev['ID']); // Получаем платежные системы
    //         if ($params["PAY_SYSTEMS"]) {
    //             // echo "<pre>";
    //             // var_dump($params["PAY_SYSTEMS"]);
    //             // echo "</pre>";
    //             foreach($params["PAY_SYSTEMS"] as $value){
    //                 $payment = CSalePaySystem::GetByID($value);
    //                 echo "<pre>";
    //                 var_dump($payment);
    //                 echo "</pre>";
    //             }
    //         }
    //     }
    // }
    

    class BotMessage extends GenerateAllData{
        protected $Params;
        protected $chatId;
        protected $URL;

        public function __construct($line, $chatId){
            $this->Params = explode('~', $line);
            file_put_contents('debug.txt', $line);
            $this->chatId = $chatId;
            $this->URL = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/";
        }

        public function OnCallback(){
            $method = $this->Params[0];
            switch($method){
                case 'category':
                    $result = $this->GetSubcatalogueButton($method);
                    if(count($result['keyboard']) < 2){
                        $result = $this->GetListButtonElement("element");   
                    }
                    break;
                case 'element':
                    $result = $this->GenerateDescriptionElement();
                    break;
                case 'back':
                    $result = $this->GetSubcatalogueButton($method);
                    break;
                case 'previousProduct':
                    $result = $this->GetListButtonElement("element", $method);
                    break;
                case 'nextProduct':
                    $result = $this->GetListButtonElement("element", $method);
                    break;
                case 'NextMessage':
                    $result = $this->GetNextMessage();
                    break;
                case 'shearch':
                    $result = $this->GetListButtonElement("element", $method);
                    break;
                case 'answerReview':
                    $result = $this->SetInformationOnModerator();
                    break;
                case 'AddFavorites':
                    $result = $this->AddDataBaseFavorites();
                    break;
                case 'BuyProduct':
                    $result = $this->ChoiceDelivery();
                    break;
                case 'delivery':
                    $result = $this->ChoicePayment();
                    break;
                case 'payment':
                    $result = $this->CheckNumber();
                    break;
                default:
                    break;
            }
            return $result;
        }

        /** Метод генерирующий сообщение с кнопками подкаталога
        *    @param string                      $method
        */
        protected function GetSubcatalogueButton(
            $method
        ){
            if($method == 'back'){
                $catalogList = parent::GetCatalog((isset($this->Params[1]))?$this->Params[1]:NULL, 'SubCatalog');
                $result['keyboard'] = parent::GenerateButtonCatalog($catalogList, 'category', $this->Params[1]);
                if($this->Params[1]){
                    $nameSubCatalog = CIBlockSection::GetByID($this->Params[1])->GetNext()["NAME"];
                    $result["MESSAGE"] = parent::GenerateBackMessage($nameSubCatalog);
                } else {
                    $result["MESSAGE"] = parent::GenerateBackMessage(NULL);
                }
            } else {
                $catalogList = parent::GetCatalog($this->Params[1], 'SubCatalog');
                $result['keyboard'] = parent::GenerateButtonCatalog($catalogList, $method, $this->Params[1]);
                $nameSubCatalog = CIBlockSection::GetByID($this->Params[1])->GetNext()["NAME"]; 
                $result["MESSAGE"] = "Подкатегории - *".$nameSubCatalog."*";
            }
            return $result;
        }

        /** Метод генерирующий сообщение с кнопками товаров
        *    @param string                      $method
        *    @param string                      $type
        */
        protected function GetListButtonElement(
            $method,
            $type = null
        ){
            if($type == 'shearch'){
                $elementList = parent::GetShearchElement($this->Params[1]);
                $result['keyboard'] = parent::GenerateButtonElement($elementList, $method, null, $type, null);
                $result["MESSAGE"] = "Результат по запросу: *".$this->Params[1]."*";
            } else {
                $elementList = parent::GetListProduct($this->Params[1], $type, $this->Params[2]);
                $result['keyboard'] = parent::GenerateButtonElement($elementList, $method, $this->Params[3], $type, $this->Params[1]);
                $nameCatalog = CIBlockSection::GetByID($this->Params[1])->GetNext()["NAME"];
                $result["MESSAGE"] = "Список товаров - *".$nameCatalog."*";
            }
            return $result;
        }

        protected function GenerateDescriptionElement(){ 
            $result['keyboard'] = parent::GenerateButtonProduct($this->Params[1]);
            $result['MESSAGE'] = parent::GenerateMessageProduct($this->Params[1]);
            $result['FILE'] = $this->URL.CFile::GetPath(CIBlockElement::GetByID($this->Params[1])->GetNext()["PREVIEW_PICTURE"]);
            return $result;
        }

        protected function GetNextMessage(){
            $result['keyboard'] = parent::GenerateNextMessage($this->Params[1], 'button');
            $result['MESSAGE'] = parent::GenerateNextMessage($this->Params[1], 'message');
            $result['FILE'] = parent::GenerateNextMessage($this->Params[1], 'file');
            $result['trigger'] = "NewMessage";
            return $result;
        }

        /** Метод для генерации сообщения, которое выводит корень каталога
        *    @param string                      $method
        */
        public function GetCatalogButton(
            $method
        ){
            $catalogList = parent::GetCatalog(null, 'catalog');//Возвращает список всех каталогов
            $result['keyboard'] = parent::GenerateButtonCatalog($catalogList, $method);//Генерирует кнопки для вывода в сообщение
            $result["MESSAGE"] = "Список категорий";//текст сообщения
            return $result;
        }

        protected function SetInformationOnModerator(){
            $result["MESSAGE"] = "Напишите ответ пользователю";
            $result['trigger'] = "NewMessage";
            parent::SetInformation($this->Params[1], $this->Params[2]);
            return $result;
        }

        protected function AddDataBaseFavorites(){
            $result["MESSAGE"] = parent::AddFavorites($this->Params[1], $this->chatId);
            $result['trigger'] = 'AddFavorites';
            return $result;
        }

        // protected function AddDataBaseOrders(){
        //     $BackInformation = parent::AddOrders($this->Params[1], $this->chatId);
        //     $result["MESSAGE"] = $BackInformation['MESSAGE'];
        //     $result['trigger'] = $BackInformation['trigger'];
        //     if($BackInformation['USER']){
        //         $result['INFO'] = $BackInformation['USER'];
        //     }
        //     return $result;
        // }

        protected function ChoiceDelivery(){
            return parent::GenerateListDelivery();
        }

        protected function ChoicePayment(){
            return parent::GenerateListDelivery($this->Params[1], $this->chatId);
        }
        
        protected function CheckNumber(){
            return parent::CheckNumberUser($this->chatId);
        }
    }
?> 