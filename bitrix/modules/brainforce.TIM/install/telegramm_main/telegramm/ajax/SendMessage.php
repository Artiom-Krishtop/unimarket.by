
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    use TelegramBot\Api\Client;
    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    CModule::IncludeModule('highloadblock');

    class SenderMessage{
        protected $TextKeyboard;
        protected $ListUsers;
        protected $message;
        protected $dateCreate;
        protected $dateSend;
        protected $HlBlockUser;
        protected $HlBlockMessage;
        protected $Cron;
        protected $command;
        protected $DoButton;
        protected $URL;

        public function __construct($TextKeyboard, $ListUsers, $message, $dateSend = null, $cron = false, $command = null, $DoButton = null){
            $this->TextKeyboard = $TextKeyboard;
            $this->ListUsers = $ListUsers;
            $this->message = $message;
            $this->dateCreate = date('d.m.Y H:i:s', time());
            $this->dateSend = $dateSend;
            $this->HlBlockUser = $this->GetEntityDataClass(HL_BLOCK_USER_LIST);
            $this->HlBlockMessage = $this->GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
            $this->cron = $cron;
            $this->command = $command;
            $this->DoButton = $DoButton;
            $this->URL = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/";
        } 

        protected function GetEntityDataClass($HlBlockId){
            if (empty($HlBlockId) || $HlBlockId < 1) {
                return false;
            }
            $hlblock = HLBT::getById($HlBlockId)->fetch();
            $entity = HLBT::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            return $entity_data_class;
        }

        protected function GenerateListUser(){
            foreach($this->ListUsers as $key => $value){
                $UserID = $this->HlBlockUser::GetList(array(
                    'select' => array('ID'),
                    'filter' => array('UF_CHAT_ID' => $value)
                ));
                if($User = $UserID->Fetch()){
                    $listUser[] = $User['ID'];
                }
            }
            return $listUser;
        }

        public function GenerateListUserChatId(){
            foreach($this->ListUsers as $key => $value){
                $UserID = $this->HlBlockUser::GetList(array(
                    'select' => array('UF_CHAT_ID'),
                    'filter' => array('ID' => $value)
                ));
                if($User = $UserID->Fetch()){
                    $listUser[] = $User['UF_CHAT_ID'];
                }
            }
            $this->ListUsers = $listUser;
        }

        protected function EditLineCommand($CommandButton){
            $Number = 0;
            foreach($CommandButton as $key => $value){
                if($value != "none"){
                    if($value == 'OpenPage'){
                        $NewCommand[] = $this->DoButton[$Number];
                    } else {
                        $NewCommand[] = $value."~".$this->DoButton[$Number];
                    }
                    
                    $Number++;
                } else {
                    $NewCommand[] = $value;
                }
            }
            return $NewCommand;
        }

        protected function AddFileInDataBase($File){
            $arFields = [
                'name'      => $File['name'],
                'size'      => $File['size'],
                'tmp_name'  => $File['tmp_name'],
                'type'      => $File['type'],
                'MODULE_ID' => "highloadblock"
            ];
            return CFile::SaveFile($arFields, "highloadblock");
        }

        protected function ConvertTextButton($Button){
            foreach($Button as $key => $value){
                $Button[$key] = ShearchEmojiAndChange($value);
            }
            return $Button;
        }

        protected function ConvertTextButtonInSmile($Button){
            foreach($Button as $key => $value){
                $Button[$key] = Get_smile_on_line($value);
            }
            return $Button;
        }

        public function AddDataBase($CommandButton, $File){
            $this->TextKeyboard = $this->ConvertTextButton($this->TextKeyboard);
            $this->DoButton = $this->EditLineCommand($CommandButton);
            $resultAddList = $this->HlBlockMessage::add(array(
                'UF_COMMAND_TRIGGER'  => ShearchEmojiAndChange($this->command),
                'UF_MESSAGE'          => ShearchEmojiAndChange($this->message),
                'UF_BUTTON_NAME'      => (isset($this->TextKeyboard))?$this->TextKeyboard:"",
                'UF_LINE_COMMAND'     => $this->DoButton,
                'UF_LIST_USER'        => $this->GenerateListUser(),
                'UF_DATE_CREATE'      => $this->dateCreate,
                'UF_DATE_SEND'        => $this->dateSend,
                'UF_FILE'             => CFile::MakeFileArray($this->AddFileInDataBase($File))              
            ));
            $result[] = "Результат: <br>";
            $result[] = 'Сообщение для комманды усппешно записано)))';
            return json_encode($result);
        }

        public function SendMessage($CommandButton = null, $File = null){
            try{
                $repeat = 0;
                if($this->TextKeyboard){
                    $ListButton = array();
                    $number = 0;
                    if($CommandButton != null)
                        $this->DoButton = $this->EditLineCommand($CommandButton);
                    else 
                        $this->TextKeyboard = $this->ConvertTextButtonInSmile($this->TextKeyboard);
                    foreach($this->TextKeyboard as $key => $value){
                        if(strpos($this->DoButton[$key], "NextMessage") !== false || strpos($this->DoButton[$key], "none") !== false){
                            $inlineButton = [
                                ["text" => $value, "callback_data" => $this->DoButton[$key]]
                            ];
                        } else {
                            $inlineButton = [
                                ["text" => $value, "url" => $this->DoButton[$key]]
                            ];
                        }
                        array_push($ListButton, $inlineButton);
                    }
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($ListButton);
                } else{
                    $keyboard = null;
                }
                $result[] = "Результат: <br>";
                $bot = new Client(BOT_TOKEN);
                if($File != null){
                    if(isset($File['ID'])){
                        $FileId = $File['ID'];
                    } else {
                        $FileId = $this->AddFileInDataBase($File);
                    }
                    $FileUrl = $this->URL.CFile::GetPath($FileId);
                }
                $this->message = Get_smile_on_line($this->message);
                
                foreach($this->ListUsers as $key => $value){
                    if($repeat == 30){
                        sleep(1);
                        $repeat = 0;
                    }
                    if($File != null){
                        $bot->sendPhoto($value, $FileUrl, $this->message, null, $keyboard, false, 'Markdown');
                    } else {
                        $bot->sendMessage($value, $this->message, 'Markdown', false, null, $keyboard);
                    }
                    
                    $result[] = $value." : Успешно отправленно!<br>".$this->message;
                    $repeat++;
                }
                if($this->cron == false){
                    $backInformation = $this->AddHistoryMessage(null, $File);
                }
                return json_encode($result);
            }catch (Exception $e){
                return print_r($e);
            }
        }

        public function AddHistoryMessage($CommandButton = null, $File = null){
            if($this->dateSend != null){
                $this->dateSend = strtotime($this->dateSend);
                $this->dateSend = date('d.m.Y H:i:s', $this->dateSend);
                $this->DoButton = $this->EditLineCommand($CommandButton);
            }
            $this->TextKeyboard = $this->ConvertTextButton($this->TextKeyboard);
            $resultAddList = $this->HlBlockMessage::add(array(
                'UF_MESSAGE'          => ShearchEmojiAndChange($this->message),
                'UF_BUTTON_NAME'      => (isset($this->TextKeyboard))?$this->TextKeyboard:"",
                'UF_LINE_COMMAND'     => $this->DoButton,
                'UF_LIST_USER'        => $this->GenerateListUser(),
                'UF_DATE_CREATE'      => $this->dateCreate,
                'UF_DATE_SEND'        => $this->dateSend,
                'UF_COMMAND_TRIGGER'  => ShearchEmojiAndChange($this->command),
                'UF_FILE'             => CFile::MakeFileArray($this->AddFileInDataBase($File))
            ));
            $result[] = "Результат: <br>";
            $result[] = 'Отложенное сообщение успешно записано и будет отправленно в указанное время)))';
            return json_encode($result);
        }
    }
?>