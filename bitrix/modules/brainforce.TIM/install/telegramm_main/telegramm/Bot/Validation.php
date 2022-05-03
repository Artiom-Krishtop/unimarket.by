<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';

    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

    CModule::IncludeModule('highloadblock');

    //функция создающая класс для раюоты с таблицей
    function GetEntityDataClassValidation($HlBlockId){
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = HLBT::getById($HlBlockId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }
    
    function AddHlBlockUser($chatId, $userName, $name){
        $HlBlock = GetEntityDataClassValidation(HL_BLOCK_USER_LIST);
		$result = $HlBlock::GetList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
        if(!$User = $result->Fetch()){
            $result = $HlBlock::add(array(
                'UF_NAME'             => $name,
                'UF_USER_NAME'        => $userName,
                'UF_CHAT_ID'          => $chatId,
                'UF_DATE_REGISTATION' => date('d.m.Y H:i:s', time()),
                'UF_WRITE_REVIEW'     => 0
            ));
        }
    }
?> 