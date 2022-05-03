<?php
    file_put_contents('debug.txt', "Да, я запускаюсь");
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    require_once '../telegramm/vendor/autoload.php';
    require_once '../telegramm/config.php';
    require_once '../bitrix/templates/aspro_next/ajax/SendMessage.php';
    CModule::IncludeModule('highloadblock');

    

    function GetEntityDataClass($HlBlockId){
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = HLBT::getById($HlBlockId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    $HlBlockMessage = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
    $HlBlockUser = GetEntityDataClass(HL_BLOCK_USER_LIST);

    $Message = $HlBlockMessage::GetList(array(
        'select' => array('*')
    ));

    while($mes = $Message->Fetch()){
        if($mes['UF_DATE_SEND'] != NULL){
            if(time() >= strtotime($mes['UF_DATE_SEND'])){
                foreach($mes['UF_LIST_USER'] as $value){
                    $UserChatId = $HlBlockUser::GetList(array(
                        'select' => array('UF_CHAT_ID'),
                        'filter' => array('ID' => $value)
                    ))->Fetch()['UF_CHAT_ID'];
                    $listUsersChatId[] = $UserChatId;
                }
                $sender = new SenderMessage($mes['UF_BUTTON_NAME'], $listUsersChatId, $mes['UF_MESSAGE'], null, true, null, $mes['UF_LINE_COMMAND']);
                $FileId['ID'] =  $mes['UF_FILE'];
                $sender->SendMessage(null, ($mes['UF_FILE'] != NULL && $mes['UF_FILE'] != "0")? $FileId:NULL);
                $res = $HlBlockMessage::update($mes['ID'], array(
                    'UF_DATE_SEND' => NULL
                ));
            }
        }
    }

    
?>