<?php
use TelegramBot\Api\Client;
require 'vendor/autoload.php';
require 'listSmile.php';
require 'Bot/Functions.php';
require 'Bot/BotMassage.php';
require 'Bot/Validation.php';
require 'ajax/SendMessage.php';
require 'config.php';

try {
    $bot = new Client(BOT_TOKEN);

    $body = file_get_contents('php://input');
    $str = (array)json_decode($body);
    if($str['message']){
        $ChatText = (array)$str['message'];
        $user = (array)$ChatText['from'];
        $chatId[] = $user['id'];
        if($ChatText['contact']){
            $Contact = (array)$ChatText['contact'];
            $Phone = $Contact['phone_number'];
            AddDataBasePhone($chatId[0], $Phone);
            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(GetMenu('MENU'), false, true);
            $bot->sendMessage($chatId[0], "Ваш номер успешно записан мы свяжемся с вами в ближайшее время", 'Markdown', false, null, $keyboard);
        } else {        
            preg_match('/^(?:@\w+\s)?\/([^\s@]+)(@\S+)?\s?(.*)$/', $ChatText['text'], $matches);
            if(!empty($matches)){
                $command = $matches[1];
                $bot->command($command, function ($message) use ($bot) {
                    global $command;
                    $ListButton = [];
                    $chatId[] = $message->getChat()->getId();
                    $username = $message->getFrom()->getUsername();
                    $name = $message->getFrom()->getFirstName()." ".$message->getFrom()->getLastName();
                    ChangeFeedback($chatId[0], 0);
                    $mes = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST)::GetList(array(
                        'select' => array('*'),
                        'filter' => array('UF_COMMAND_TRIGGER' => $command)
                    ))->Fetch();
                    if(isset($mes['UF_MESSAGE'])){
                        $mes['UF_MESSAGE'] = Get_smile_on_line($mes['UF_MESSAGE']);
                    }
                    if($command == "start"){
                        AddHlBlockUser($chatId[0], $username, ShearchEmojiAndChange($name));
                        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(GetMenu('MENU'), false, true);
                        if(isset($mes['UF_MESSAGE'])){
                            if($mes['UF_FILE'] != NULL && $mes['UF_FILE'] != "0"){
                                $URL = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/";
                                $FileUrl = $URL.CFile::GetPath($mes['UF_FILE']);
                                $bot->sendPhoto($chatId[0], $FileUrl, $mes['UF_MESSAGE'], null, $keyboard, false, 'Markdown');
                            } else {
                                $bot->sendMessage($chatId[0], $mes['UF_MESSAGE'], null, false, null, $keyboard);
                            }
                        } else {
                            $bot->sendMessage($chatId[0], "Доброго времени суток ".$name, null, false, null, $keyboard);
                        }
                        AddHistory($chatId[0], 'start', 'click');
                    } elseif($command == "category") {
                        $BotMessage = new BotMessage("", $chatId[0]);
                        $res = $BotMessage->GetCatalogButton('category');
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($res['keyboard']);
                        $messageResult = $bot->sendMessage($chatId[0], $res["MESSAGE"], null, false, null, $keyboard);
                        WriteIdLastMessage($chatId[0], $messageResult->getMessageId());
                        AddHistory($chatId[0], 'category', 'click');
                    } elseif($command == "feedback") {
                        ChangeFeedback($chatId[0], 'Y');
                        $bot->sendMessage($chatId[0], "Введите ваш отзыв", 'Markdown');
                        AddHistory($chatId[0], 'feedback', 'click');
                    } else {
                        $sender = new SenderMessage($mes['UF_BUTTON_NAME'], $chatId, $mes['UF_MESSAGE'], null, true, null, $mes['UF_LINE_COMMAND']);
                        $FileId['ID'] = $mes['UF_FILE'];
                        $sender->SendMessage(null, ($mes['UF_FILE'] != NULL && $mes['UF_FILE'] != "0")? $FileId:NULL);
                        AddHistory($chatId[0], $command, 'click');
                    }
                }); 
            } else {
                $ListAction = GetActionButton('MENU');
                $Message = $ChatText['text'];
                $Message = ShearchEmojiAndChange($Message);
                $mes = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST)::GetList(array(
                    'select' => array('*'),
                    'filter' => array('UF_COMMAND_TRIGGER' => $Message)
                ))->Fetch();
                if(!$mes){
                    if($ListAction[$Message] == 'about'){
                        $Message = "about";
                        $mes = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST)::GetList(array(
                            'select' => array('*'),
                            'filter' => array('UF_COMMAND_TRIGGER' => $Message)
                        ))->Fetch();
                    }
                }
                AddMessage2Log($Message);
                if($mes){
                    $sender = new SenderMessage($mes['UF_BUTTON_NAME'], $chatId, Get_smile_on_line($mes['UF_MESSAGE']), null, true, null, $mes['UF_LINE_COMMAND']);
                    $FileId['ID'] = $mes['UF_FILE'];
                    $sender->SendMessage(null, ($mes['UF_FILE'] != NULL && $mes['UF_FILE'] != "0")? $FileId:NULL);
                    ChangeFeedback($chatId[0], 0);
                    AddHistory($chatId[0], $Message, 'click');
                } elseif($Action = $ListAction[$Message]) {
                    switch ($Action) {
                        case 'catalog':
                            $BotMessage = new BotMessage("", $chatId[0]);
                            $res = $BotMessage->GetCatalogButton('category');
                            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($res['keyboard']);
                            $messageResult = $bot->sendMessage($chatId[0], $res["MESSAGE"], null, false, null, $keyboard);
                            WriteIdLastMessage($chatId[0], $messageResult->getMessageId());
                            ChangeFeedback($chatId[0], 0);
                            AddHistory($chatId[0], $Message, 'click');
                            break;
                        case 'about':
                            $sender = new SenderMessage($mes['UF_BUTTON_NAME'], $chatId, Get_smile_on_line($mes['UF_MESSAGE']), null, true, null, $mes['UF_LINE_COMMAND']);
                            $FileId['ID'] = $mes['UF_FILE'];
                            $sender->SendMessage(null, ($mes['UF_FILE'] != NULL && $mes['UF_FILE'] != "0")? $FileId:NULL);
                            ChangeFeedback($chatId[0], 0);
                            AddHistory($chatId[0], $Message, 'click');
                            break;
                        case 'repost':
                            $botInformation = $bot->api->getMe();
                            $botUsername = $botInformation->getUsername();
                            $botUsername = str_ireplace("_", "\_", $botUsername);
                            $botUrl = "t.me/".$botUsername;
                            $bot->sendMessage($chatId[0], $botUrl, 'Markdown');
                            ChangeFeedback($chatId[0], 0);
                            AddHistory($chatId[0], $Message, 'click');
                            break;
                        case 'feedback':
                            ChangeFeedback($chatId[0], 'Y');
                            $bot->sendMessage($chatId[0], "Введите ваше сообщение", 'Markdown');
                            AddHistory($chatId[0], $Message, 'click');
                            break;
                        default:
                            break;
                    }
                } elseif(CheckWriteReview($chatId[0])){
                    if(CheckChatID($chatId[0])){
                        if($IdAnswer = GetIdAnswerUser('ID')){
                            $CallBack = WriteReviewInDataBase($chatId[0], $Message, 'Answer');
                            $Text = $CallBack['TEXT_REVIEW'];
                            $bot->sendMessage($IdAnswer, "*Ответ на сообщение* - '_".Get_smile_on_line($Text)."_':\n".Get_smile_on_line($Message), 'Markdown');
                            $bot->sendMessage($chatId[0], "Ответ успешно отправлен", 'Markdown');
                        }
                    } else {
                        $CallBack = WriteReviewInDataBase($chatId[0], $Message);
                        $UserId = $CallBack['ID_USER'];
                        $ReviewID = $CallBack['Review_ID'];
                        $bot->sendMessage($chatId[0], "Ваш отзыв принят", 'Markdown');
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
                            [
                                [
                                    ["text" => "\xF0\x9F\x93\xAC Ответить на отзыв \xF0\x9F\x93\xAC", "callback_data" => "answerReview~".$chatId[0]."~".$ReviewID]
                                ]
                            ]
                        );
                        $bot->sendMessage(GetChatIdModerator(), 'Новый отзыв от пользователя '.$username.'('.$UserId.'): '.Get_smile_on_line($Message), 'Markdown', false, null, $keyboard);
                    }
                } else {
                    if($Message != ""){
                        $BotMessage = new BotMessage("shearch~".$Message, $chatId[0]);
                        $res = $BotMessage->OnCallback();
                        if ($res["MESSAGE"]) {
                            if($res['keyboard'])
                                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($res['keyboard']);
                            else
                                $keyboard = null;
                            $bot->sendMessage($chatId[0], $res["MESSAGE"], 'Markdown', false, null, $keyboard);
                        }
                        AddHistory($chatId[0], $Message, 'shearch');
                    }
                }
            }
        }
    } else {
        $bot->callbackQuery(function ($callbackQuery) use ($bot) {
            $chatId[0] = $callbackQuery->getMessage()->getChat()->getId();
            $paramsStr = $callbackQuery->getData();
            $messageId = getMessageIdUser($chatId[0]);
            $Message = new BotMessage($paramsStr, $chatId[0]);
            $res = $Message->OnCallback();
            AddMessage2Log($res);
            if ($res["MESSAGE"]) {
                if($res['FILE']){
                    if($res['keyboard'])
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($res['keyboard']);
                    else
                        $keyboard = null;
                    $res = $bot->sendPhoto($chatId[0], $res['FILE'], $res["MESSAGE"], null, $keyboard, false, 'Markdown');
                } else {
                    if($res['keyboard'])
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($res['keyboard']);
                    else
                        $keyboard = null;
                    switch ($res['trigger']) {
                        case 'NewMessage':
                            $bot->sendMessage($chatId[0], $res["MESSAGE"], 'Markdown', false, null, $keyboard);
                            break;
                        case 'AddOrder':                        
                            $bot->sendMessage($chatId[0], $res["MESSAGE"], 'Markdown', false, null, $keyboard);
                            if($res['INFO']){
                                $username = $callbackQuery->getMessage()->getChat()->getUsername();
                                $UserId = $res['INFO']['USER_ID'];
                                $OrderId = $res['INFO']['ORDER_ID'];
                                $Delivery = $res['INFO']['DELIVERY'];
                                $Payment = $res['INFO']['PAYMENT'];
                                $bot->sendMessage(GetChatIdModerator(), "*Новый заказ от ".$username."(".$UserId."):* Номер заказа: ".$OrderId."\n*Способ доставки:* _".$Delivery."_\n*Способ оплаты:* _".$Payment."_", 'Markdown', false, null, $keyboard);
                            }
                            break;
                        case 'AddFavorites':
                            $bot->sendMessage($chatId[0], $res["MESSAGE"], 'Markdown', false, null, $keyboard);
                            break;
                        case 'GetPhone':
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(array(array(array('text' => 'Отправить свой контакт', 'request_contact' => true))), false, true);
                            $bot->sendMessage($chatId[0], $res['MESSAGE'], 'Markdown', false, null, $keyboard);
                            if($res['INFO']){
                                $username = $callbackQuery->getMessage()->getChat()->getUsername();
                                $UserId = $res['INFO']['USER_ID'];
                                $OrderId = $res['INFO']['ORDER_ID'];
                                $Delivery = $res['INFO']['DELIVERY'];
                                $Payment = $res['INFO']['PAYMENT'];
                                $bot->sendMessage(GetChatIdModerator(), "*Новый заказ от ".$username."(".$UserId."):* Номер заказа: ".$OrderId."\n*Способ доставки:* _".$Delivery."_\n*Способ оплаты:* _".$Payment."_", 'Markdown', false, null, $keyboard);
                            }
                            break;
                        default:
                            $res = $bot->editMessageText($chatId[0], $messageId, $res["MESSAGE"], 'Markdown', false, $keyboard);
                            break;
                    }
                }
            }
            AddHistory($chatId[0], $paramsStr, 'click');
        });
    }
    $bot->run();
    
} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
    file_put_contents("error.txt", $e);
    echo $e;
}

?> 