<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    use Bitrix\Main\Config\Option as Option;
    CModule::IncludeModule('highloadblock');
    CModule::IncludeModule("iblock");

    class GenerateAllData{
        protected $SMILE = array(
            'BOOK_GREEN'       => "\xF0\x9F\x97\x82",
            'BOOK_YELLOW'      => "\xf0\x9f\x93\xa6",
            'ARROW_BACK_BLACK' => "\xF0\x9F\x94\x99",
            'ARROW_BACK_BLUE'  => "\xe2\xac\x85\xef\xb8\x8f",
            'ARROW_NEXT_BLUE'  => "\xe2\x9e\xa1\xef\xb8\x8f",
            'GLOBUS_EUROUPE'   => "\xF0\x9F\x8C\x8D",
            'SMILE_SAD'        => "\xF0\x9F\x98\x94",
            'STAR'             => "\xe2\xad\x90\xef\xb8\x8f",
            'CHECK_MARK'       => "\xE2\x9C\x85"
        );
        protected $Flag = false;

        protected function GetEntityDataClass($HlBlockId){
            if (empty($HlBlockId) || $HlBlockId < 1) {
                return false;
            }
            $hlblock = HLBT::getById($HlBlockId)->fetch();
            $entity = HLBT::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            return $entity_data_class;
        }

        /** Метод возвращающий список каталогов
        *    @param int|string|null                  $SECTION_ID
        */
        protected function GetCatalog($SECTION_ID = null, $type){
            if($SECTION_ID !== NULL && !empty($SECTION_ID)){
                $this->Flag = true;
            }
            $arFilter = [
                'IBLOCK_ID'   => CATEGORY,
                'ACTIVE'      => 'Y',
                'SECTION_ID'  => $SECTION_ID
            ];
            return CIBlockSection::GetList([], $arFilter, false, false, []);
        }

        //Генерирует кнопки для каталогов
        protected function GenerateButtonCatalog($catalogList, $method, $SECTION_ID = null){
            $ListButton = [];
            while($catalog = $catalogList->Fetch()){
                $inlineButton = [
                    ["text" => $this->SMILE["BOOK_GREEN"].$catalog['NAME'], "callback_data" => $method . "~" . $catalog['ID']],
                ];
                array_push($ListButton, $inlineButton);
            }
            if($this->Flag != false){
                $IdBackSection = $this->GetIdBackSection($SECTION_ID);
                $inlineButton = [
                    ["text" => $this->SMILE['ARROW_BACK_BLACK']."Назад", "callback_data" => "back" . "~". $IdBackSection],
                ];
                array_push($ListButton, $inlineButton);
            }
            return $ListButton;
        }

        //Возвращает номер предыдущего списка каталогов
        protected function GetIdBackSection($SECTION_ID){
            $arFilter = [
                'IBLOCK_ID'   => CATEGORY,
                'ACTIVE'      => 'Y',
                'ID'          => $SECTION_ID
            ];
            return CIBlockSection::GetList([], $arFilter, false, false, ['IBLOCK_SECTION_ID'])->Fetch()['IBLOCK_SECTION_ID'];
            // $arFilter = [
            //     'IBLOCK_ID'   => CATEGORY,
            //     'ACTIVE'      => 'Y',
            //     'ID'          => $ID
            // ];
            // return CIBlockSection::GetList([], $arFilter, false, false, ['IBLOCK_SECTION_ID'])->Fetch()['IBLOCK_SECTION_ID'];
        }

        //Метод возвращающий максимальное кол-во страниц
        protected function getCountPageCategories($SECTION_ID){
            $countElement = CIBlockSection::GetSectionElementsCount($SECTION_ID, ['CNT_ACTIVE' => 'Y']);
            if($countElement%10 == 0){
                return $countElement/10;
            } else {
                return floor($countElement/10)+1;
            }
        }

        //Метод генерирующий кнопки товаров
        protected function GenerateButtonElement($elementList, $param, $nowPage = null, $type = null, $SECTION_ID){
            $ListButton = [];
            $Schyt = 0;

            if($nowPage === null){
                $numberPage = 1;
            } else{
                if($type != null){
                    $numberPage = ($type == 'previousProduct')? $nowPage-1 : $nowPage+1;
                }
            }
            while($element = $elementList->GetNext()){
                if($Schyt >= 10){
                    break;
                }
                if($Schyt == 0 && $type != 'shearch'){
                    if($type == 'previousProduct'){
                        $IdLastProduct = $element['ID'];
                    } else {
                        $IdFirstProduct = $element['ID'];
                    }
                }
                $inlineButton = [
                    ["text" => $this->SMILE["BOOK_YELLOW"].EditLine($element['NAME']), "callback_data" => $param . "~" . $element['ID']],
                ];
                if($type != 'shearch'){
                    if($type != 'previousProduct'){
                        $IdLastProduct = $element['ID'];
                    } else {
                        $IdFirstProduct = $element['ID'];
                    }
                }

                if($type == 'previousProduct'){
                    array_unshift($ListButton, $inlineButton);
                } else {
                    array_push($ListButton, $inlineButton);
                }
                $Schyt++;
            }
            if(count($ListButton) == 0){
                if($type == 'shearch'){
                    $inlineButton = [
                        ["text" => "К сожалению наш поиск ничего не нашёл".$this->SMILE['SMILE_SAD'], "callback_data" => "none"]
                    ];
                } else {
                    $inlineButton = [
                        ["text" => "К сожалению товаров в этой категории нету".$this->SMILE['SMILE_SAD'], "callback_data" => "none"]
                    ];
                }
                array_push($ListButton, $inlineButton);
            }
            if($type != 'shearch'){
                $countPage = $this->getCountPageCategories($SECTION_ID);
                $commandBack = ($numberPage == 1)? "none":"previousProduct";
                $commandNext = ($countPage != 0 && $countPage != 1)? "nextProduct":"none";
                if($countPage > 1){
                    $inlineButton = [
                        ["text" => $this->SMILE['ARROW_BACK_BLUE']."Назад", "callback_data" => $commandBack. "~" . $SECTION_ID . "~" . $IdFirstProduct . "~" . $numberPage],
                        ["text" => $numberPage." / ".$countPage, "callback_data" => "numberPage"],
                        ["text" => "Далее".$this->SMILE['ARROW_NEXT_BLUE'], "callback_data" => $commandNext. "~" . $SECTION_ID . "~" . $IdLastProduct . "~" . $numberPage],
                    ];
                    array_push($ListButton, $inlineButton);
                }
                if($this->Flag != false){
                    $IdBackSection = $this->GetIdBackSection($SECTION_ID);
                    $inlineButton = [
                        ["text" => $this->SMILE['ARROW_BACK_BLACK']."Вернуться к категориям", "callback_data" => "back" . "~". $IdBackSection],
                    ];
                    array_push($ListButton, $inlineButton);
                }
            }

            return $ListButton;
        }

        protected function GetShearchElement($shearch){
            return CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>CATEGORY, "NAME"=>"%".$shearch."%"), false, ['nPageSize' => 10], Array("ID", 'IBLOCK_ID', "NAME", 'IBLOCK_SECTION_ID'));
        }

        /**
        *    @param int|string                  $SECTION_ID
        *    @param string                      $type
        *    @param int|string                  $IdElement
        */
        protected function GetListProduct($SECTION_ID, $type, $IdElement = null){
            if($type != null){
                $this->Flag = true;
                if($type == 'nextProduct'){
                    $arOrder = [
                        'ID' => 'asc'
                    ];
                    $arFilter = [
                        'SECTION_ID'  => $SECTION_ID,
                        'ACTIVE'      => 'Y',
                        '>ID'         => $IdElement
                    ];
                    return CIBlockElement::GetList($arOrder, $arFilter, false, Array("nTopCount"=>10), ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID']);
                } else {
                    $arOrder = [
                        'ID' => 'desc'
                    ];
                    $arFilter = [
                        'SECTION_ID'  => $SECTION_ID,
                        'ACTIVE'      => 'Y',
                        '<ID'         => $IdElement
                    ];
                    return CIBlockElement::GetList($arOrder, $arFilter, false, Array("nTopCount"=>10), ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID']);
                }
            } else {
                $arOrder = [
                    'ID' => 'asc'
                ];
                $arFilter = [
                    'SECTION_ID'  => $SECTION_ID,
                    'ACTIVE'      => 'Y'
                ];
                return CIBlockElement::GetList($arOrder, $arFilter, false, Array("nTopCount"=>10), ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID']);
            }
        }

        /**
        *    @param int|string                  $ELEMENT_ID
        */
        protected function GenerateMessageProduct($ELEMENT_ID){
            $module_id = 'brainforce.TIM';
            $arOptions = Option::getForModule($module_id,'arOptions');

            $arFilter = [
                'IBLOCK_ID'   => CATEGORY,
                'ID'          => $ELEMENT_ID
            ];
            $arSelect = [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'DETAIL_TEXT',
                'PREVIEW_TEXT'
            ];

            if(ID_PRICE == ""){
                array_push($arSelect, "PROPERTY_".CODE_PRICE);
            } else {
                array_push($arSelect, 'CATALOG_GROUP_'.ID_PRICE);
            }
            $InformationElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if($el = $InformationElement->Getnext()){
                $price = "";
                mb_internal_encoding('UTF-8');

                strlen($el['PREVIEW_TEXT']) ? $text = $el['PREVIEW_TEXT'] : $text = $el['DETAIL_TEXT'];
                $text = strip_tags($text);
                $text = html_entity_decode($text);

                if(strlen($text) > 500) {
                    $text = mb_substr($text, 0, 500);
                    $text = explode(" ", $text);
                    unset($text[count($text) - 1]);
                    $text = implode(' ', $text);
                    $text = trim($text);
                    $text .= "..._";
                } else {
                    $text .= "_";
                }

                /*Проверяют выводить ли цену вообще*/
                if($el["CATALOG_PRICE_".ID_PRICE] != 0 && !empty($el["CATALOG_PRICE_".ID_PRICE])){
                    $price = "*Цена:* ".$el["CATALOG_PRICE_".ID_PRICE]."р.";
                }
                if($el["PROPERTY_".CODE_PRICE."_VALUE"] != 0 && !empty($el["PROPERTY_".CODE_PRICE."_VALUE"])){
                    $price = "*Цена:* ".$el["PROPERTY_".CODE_PRICE."_VALUE"]."р.";
                }
                /*Проверяют выводить ли цену вообще*/

                /* Добавляем свойства */
                $propsText = "\n\n";
                if ($arOptions['IBLOCK_PROPS']) {
                    $arProps = CIBlockElement::GetProperty(
                        $el['IBLOCK_ID'],
                        $ELEMENT_ID,
                        [],
                        ['ID' => explode(",", $arOptions['IBLOCK_PROPS'])]);

                    $props = [];
                    while ($prop = $arProps->Fetch()) {
                        $props[] = $prop;
                    }

                    foreach ($props as $key => $prop) {
                        if ($prop['MULTIPLE'] = 'Y') {
                            $arrProp = CIBlockElement::GetPropertyValues($el['IBLOCK_ID'], ['ID' => $ELEMENT_ID], false, ['ID' => [$prop['ID']]])->Fetch();
                            $props[$key]['VALUE'] = $arrProp[$prop['ID']];
                        }
                    }

                    foreach ($props as $key => $prop) {
                        if ($prop['LINK_IBLOCK_ID']) {
                            if (is_array($prop['VALUE'])) {
                                $values = [];
                                foreach ($prop['VALUE'] as $value) {
                                    $linkEl = CIBlockElement::GetByID($value)->Fetch();
                                    $values[] = $linkEl['NAME'];
                                }
                                $props[$key]['VALUE'] = $values;
                            } else {
                                $linkEl = CIBlockElement::GetByID($prop['VALUE'])->Fetch();
                                $props[$key]['VALUE'] = $linkEl['NAME'];
                            }
                        }
                    }

                    foreach ($props as $key => $prop) {
                        if ($prop['PROPERTY_TYPE'] == "N") {
                            $props[$key]['VALUE'] = $prop['VALUE'] / 10 * 10;
                        }
                    }

                    $propsText = "\n\n";

                    foreach ($props as $prop) {
                        $propsText .= "*" . $prop['NAME'] . "*: ";
                        if (is_array($prop['VALUE'])) {
                            $prop['VALUE'] = implode(", ", $prop['VALUE']);
                        }
                        $propsText .= $prop['VALUE'];
                        $propsText .= "\n\n";
                    }
                }


                $result = "*".EditLine($el['NAME'])."*\n\n_".$text.$propsText.$price;
            }
            return $result;
        }
//        protected function GenerateMessageProduct($ELEMENT_ID){
//            $arFilter = [
//                'IBLOCK_ID'   => CATEGORY,
//                'ID'          => $ELEMENT_ID
//            ];
//            $arSelect = [
//                'NAME',
//                'DETAIL_TEXT'
//            ];
//            if(ID_PRICE == ""){
//                array_push($arSelect, "PROPERTY_".CODE_PRICE);
//            } else {
//                array_push($arSelect, 'CATALOG_GROUP_'.ID_PRICE);
//            }
//            $InformationElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
//            if($el = $InformationElement->Getnext()){
//                $price = "";
//                mb_internal_encoding('UTF-8');
//                if($pos = mb_stripos($el["DETAIL_TEXT"], "<br>", 0, 'UTF-8')){
//                    $el["DETAIL_TEXT"] = mb_substr($el["DETAIL_TEXT"], $pos+4, 200, 'UTF-8');
//                } else {
//                    $el["DETAIL_TEXT"] = mb_substr($el["DETAIL_TEXT"], 3, 200, 'UTF-8');
//                }
//                if($el['DETAIL_TEXT'][0] == " "){
//                    $el["DETAIL_TEXT"] = mb_substr($el["DETAIL_TEXT"], 1, -1, 'UTF-8');
//                }
//
//                /*Проверяют выводить ли цену вообще*/
//                if($el["CATALOG_PRICE_".ID_PRICE] != 0 && !empty($el["CATALOG_PRICE_".ID_PRICE])){
//                    $price = "\n\n*Цена:* ".$el["CATALOG_PRICE_".ID_PRICE]."р.";
//                }
//                if($el["PROPERTY_".CODE_PRICE."_VALUE"] != 0 && !empty($el["PROPERTY_".CODE_PRICE."_VALUE"])){
//                    $price = "\n\n*Цена:* ".$el["PROPERTY_".CODE_PRICE."_VALUE"]."р.";
//                }
//                /*Проверяют выводить ли цену вообще*/
//
//                $el['DETAIL_TEXT'] = strip_tags($el['DETAIL_TEXT']);
//                $el['DETAIL_TEXT'] = preg_replace('/[^ a-zа-яё\d]/ui', '', $el['DETAIL_TEXT']);
//                $result = "*".EditLine($el['NAME'])."*\n\n_".$el["DETAIL_TEXT"]."..._".$price;
//            }
//            return $result;
//        }
        protected function GenerateButtonProduct($ELEMENT_ID){
            $ListButton = array();
            $arFilter = [
                'IBLOCK_ID'   => CATEGORY,
                'ID'          => $ELEMENT_ID
            ];
            $arSelect = [
                'DETAIL_PAGE_URL'
            ];
            $InformationElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if($el = $InformationElement->Getnext()){
                $URL = $this->URL.substr($el['DETAIL_PAGE_URL'], 1);
                $inlineButton = [
                    ["text" => $this->SMILE['STAR']."Избранное", "callback_data" => "AddFavorites~".$ELEMENT_ID],
                    ["text" => $this->SMILE["GLOBUS_EUROUPE"]."На сайт","url" => $URL],
                ];
                array_push($ListButton, $inlineButton);
                $inlineButton = [
                    ["text" => $this->SMILE['CHECK_MARK']."Заказать", "callback_data" => "BuyProduct~".$ELEMENT_ID],
                ];
                array_push($ListButton, $inlineButton);
            }
            return $ListButton;
        }

        protected function GenerateNextMessage($IdMessage, $type){
            $Message = $this->GetEntityDataClass(HL_BLOCK_MESSAGE_LIST)::getList(array(
                'select' => array('*'),
                'filter' => array('ID' => $IdMessage)
            ))->Fetch();
            $result = [];
            if($type == 'button'){
                foreach($Message['UF_BUTTON_NAME'] as $key => $value){
                    if(strpos($Message['UF_LINE_COMMAND'][$key], "NextMessage") !== false || strpos($Message['UF_LINE_COMMAND'][$key], "none") !== false){
                        $inlineButton = [
                            ["text" => Get_smile_on_line($value), "callback_data" => $Message['UF_LINE_COMMAND'][$key]]
                        ];
                    } else {
                        $inlineButton = [
                            ["text" => Get_smile_on_line($value), "url" => $Message['UF_LINE_COMMAND'][$key]]
                        ];
                    }
                    array_push($result, $inlineButton);
                }
            } elseif($type == 'message') {
                $result = Get_smile_on_line($Message['UF_MESSAGE']);
            } else {
                if($path = CFile::GetPath($Message['UF_FILE'])){
                    $result = $this->URL.substr($path, 1);
                } else {
                    $result = NULL;
                }
            }
            return $result;
        }

        //Генрирует сообщение для предыдущего списка каталогов
        protected function GenerateBackMessage($nameCatalog){
            if($this->Flag !== false){
                return "Подкатегории - *".$nameCatalog."*";
            } else {
                return "Список категорий";
            }
        }

        protected function SetInformation($chatId, $ReviewID){
            $line = $chatId."~".$ReviewID;
            $HlBlock = $this->GetEntityDataClass(HL_BLOCK_USER_LIST);
            $result = $HlBlock::update(ID_MODERATOR, array(
                'UF_WRITE_REVIEW'     => "Y",
                'UF_RESPONSE_TO_USER' => $line
            ));
        }

        protected function AddFavorites($ELEMENT_ID, $chatId){
            $HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
            $UserId = $HlBlock::GetList(array(
                'select' => array('ID'),
                'filter' => array('UF_CHAT_ID' => $chatId)
            ))->Fetch()['ID'];

            $HlBlock = GetEntityDataClass(HL_BLOCK_FAVORITES_USERS);
            $result = $HlBlock::getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_PRODUCT_ID' => $ELEMENT_ID,
                    'UF_USER'       => $UserId
                )
            ));
            if($res = $result->Fetch()){
                return "Этот товар уже добавлен в избранное";
            } else {
                $res = $HlBlock::add(array(
                    'UF_USER'       => $UserId,
                    'UF_PRODUCT_ID' => $ELEMENT_ID,
                    'UF_DATE_ADD'   => date('d.m.Y H:i:s', time())
                ));
                return "Товар успешно добавлен в избранное".$this->SMILE['CHECK_MARK'];
            }
        }

        protected function AddOrders($ELEMENT_ID, $chatId, $Payment, $Delivery){
            $HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
            $UserId = $HlBlock::GetList(array(
                'select' => array('ID'),
                'filter' => array('UF_CHAT_ID' => $chatId)
            ))->Fetch()['ID'];

            $HlBlock = GetEntityDataClass(HL_BLOCK_ORDERS_USERS);
            $result = $HlBlock::getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_PRODUCT_ID' => $ELEMENT_ID,
                    'UF_USER'       => $UserId
                )
            ));

            if($res = $result->Fetch()){
                return "Заказ на этот товар уже создан";
            } else {
                $res = $HlBlock::add(array(
                    'UF_USER'           => $UserId,
                    'UF_PRODUCT_ID'     => $ELEMENT_ID,
                    'UF_DATE_ADD'       => date('d.m.Y H:i:s', time()),
                    'UF_DELIVERY'       => $Delivery,
                    'UF_PAYMENT_METHOD' => $Payment
                ));
                $BackInformation['MESSAGE'] = "*Ваш заказ успешно создан и отправлен оператору на обработку*".$this->SMILE['CHECK_MARK'];
                $BackInformation['INFO'] = [
                    'USER_ID'  => $UserId,
                    'ORDER_ID' => $res->getId(),
                    'PAYMENT'  => $paymentName,
                    'DELIVERY' => $nameDelivery
                ];
                return $BackInformation;
            }
        }

        protected function GenerateListDelivery($DeliveryId = null, $chatId = null){
            $result = [];
            $res = \Bitrix\Sale\Delivery\Services\Table::getList(
                array(
                    'order' => array("SORT" => "ASC"),
                    'filter' => array('ACTIVE' => 'Y'),
                    'select' => array('*')
                )
            );
            if($DeliveryId === null){
                while ($deliv = $res->fetch()) {
                    if($deliv['NAME'] == 'Без доставки')
                        continue;
                    $inlineButton = [
                        ["text" => Get_smile_on_line($deliv['NAME']), "callback_data" => "delivery~".$deliv['ID']."~".$this->Params[1]]
                    ];
                    array_push($result, $inlineButton);
                }
                $BackInformation['keyboard'] = $result;
                $BackInformation['MESSAGE'] = "*Выберите способ доставки*";
                $BackInformation['trigger'] = 'NewMessage';
            } else {
                $dbRestr = \Bitrix\Sale\Delivery\Restrictions\Manager::getList(array(
                    'filter' => array('SERVICE_ID' => $DeliveryId) // ID службы доставки
                ));
                while ($arRestr = $dbRestr->fetch()) {
                    if(!$arRestr["PARAMS"]) { // У ограничений по платежной системе нет параметров
                        $arRestr["PARAMS"] = array();
                    }
                    $params = $arRestr["CLASS_NAME"]::prepareParamsValues($arRestr["PARAMS"], $DeliveryId); // Получаем платежные системы
                    if ($params["PAY_SYSTEMS"]) {
                        foreach($params["PAY_SYSTEMS"] as $value){
                            $payment = CSalePaySystem::GetByID($value);
                            $inlineButton = [
                                ["text" => Get_smile_on_line($payment['NAME']), "callback_data" => "payment~".$DeliveryId."~".$payment['ID']."~".$this->Params[2]]
                            ];
                            array_push($result, $inlineButton);
                        }
                    }
                }
                $BackInformation['keyboard'] = $result;
                $BackInformation['MESSAGE'] = "*Выберите способ оплаты*";
                $BackInformation['trigger'] = 'NewMessage';
            }
            return $BackInformation;
        }

        protected function CheckNumberUser($chatId){
            $HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
            $Phone = $HlBlock::GetList(array(
                'select' => array('UF_PHONE'),
                'filter' => array('UF_CHAT_ID' => $chatId)
            ))->Fetch()['UF_PHONE'];
            if($Phone){
                $BackInformation = $this->AddOrders($this->Params[3], $chatId, $this->Params[2], $this->Params[1]);
                if(!is_array($BackInformation)){
                    $BackInformation['MESSAGE'] = $BackInformation;
                }

                $BackInformation['keyboard'] = null;
                $BackInformation['trigger'] = 'AddOrder';

            } else {
                $BackInformation = $this->AddOrders($this->Params[3], $chatId, $this->Params[2], $this->Params[1]);
                $BackInformation['keyboard'] = null;
                $BackInformation['MESSAGE'] = "*Ваш заказ успешно создан и отправлен оператору на обработку*".$this->SMILE['CHECK_MARK']."\nОтправьте свой номер телефона, что бы оператор мог с вами связаться";
                $BackInformation['trigger'] = 'GetPhone';
            }
            return $BackInformation;
        }
    }
?>