<?php
	require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
	use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    CModule::IncludeModule('highloadblock');

	//функция создающая класс для раюоты с таблицей
	function GetEntityDataClass($HlBlockId){
		if (empty($HlBlockId) || $HlBlockId < 1) {
			return false;
		}
		$hlblock = HLBT::getById($HlBlockId)->fetch();
		$entity = HLBT::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();
		return $entity_data_class;
	}

	function getParams($command){
		return explode(" ", $command);
	}

	function EditLine($line){
		$CompliteEdit = preg_replace_callback('/&quot;/',
            function ($matches) {
                global $massivEmojiId;
                $matches[0] = '"';
                return $matches[0];
            },
            $line
        );
        return $CompliteEdit;
	}

	function AddHistory($cahtId, $Text, $type){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$UserId = $HlBlock::getList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $cahtId)
		))->Fetch()['ID'];
		if($type == 'click'){
			$HlBlockHistory = GetEntityDataClass(HL_BLOCK_HISTORY_CLICK);
			$res = $HlBlockHistory::add(array(
				'UF_USER'              => $UserId,
				'UF_TEXT_BUTTON'       => $Text,
				'UF_DATE_CLICK_BUTTON' => date('d.m.Y H:i:s', time())
			));
		} else {
			$HlBlockHistory = GetEntityDataClass(HL_BLOCK_HISTORY_SHEARCH);
			$res = $HlBlockHistory::add(array(
				'UF_USER'         => $UserId,
				'UF_SHEARCH_TEXT' => $Text,
				'UF_DATE_SHEARCH' => date('d.m.Y H:i:s', time())
			));
		}
	}

	function getMessageIdUser($chatId){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::getList(array(
			'select' => array('UF_LAST_MESSAGE_ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
		if($User = $result->Fetch()){
			return $User['UF_LAST_MESSAGE_ID'];
		} else {
			return null;
		}
		
	}

	function GetChatIdModerator(){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$ChatIdModerator = $HlBlock::GetList(array(
			'select' => array('UF_CHAT_ID'),
			'filter' => array('ID' => ID_MODERATOR)
		))->Fetch()['UF_CHAT_ID'];
		return $ChatIdModerator;
	}

	function CheckWriteReview($chatId){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::GetList(array(
			'select' => array('UF_WRITE_REVIEW'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
		if($User = $result->Fetch()){
			if($User['UF_WRITE_REVIEW'] == 1){
				return true;
			} else {
				return false;
			}
		}
	}

	function WriteReviewInDataBase($chatId, $message, $type = null){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result['ID_USER'] = $HlBlock::GetList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		))->Fetch()['ID'];
		$HlBlock = GetEntityDataClass(HL_BLOCK_FEEDBACK_LIST);
		$arAdd = array(
			'UF_USER'           => $result['ID_USER'],
			'UF_MESSAGE_REVIEW' => $message,
			'UF_DATE_SEND'      => date('d.m.Y H:i:s', time())
		);
		if($type != null){
			$arAdd['UF_USER_RESPONSE'] = GetIdAnswerUser('Review_ID');
			$result['TEXT_REVIEW'] = $HlBlock::GetList(array(
				'select' => array('UF_MESSAGE_REVIEW'),
				'filter' => array('ID' => $arAdd['UF_USER_RESPONSE'])
			))->Fetch()['UF_MESSAGE_REVIEW'];
			ClearAnswerUserModerator();
		}
		$res = $HlBlock::add($arAdd);
		$result['Review_ID'] = $res->getId();
		ChangeFeedback($chatId, 0);
		return $result;
	}

	function ClearAnswerUserModerator(){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::update(ID_MODERATOR, array(
			'UF_RESPONSE_TO_USER' => NULL
		));
	}

	function GetIdAnswerUser($type){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$line = $HlBlock::GetList(array(
			'select' => array('UF_RESPONSE_TO_USER'),
			'filter' => array('ID' => ID_MODERATOR)
		))->Fetch()['UF_RESPONSE_TO_USER'];
		$Params = explode('~', $line);
		if($type == 'ID'){
			return $Params[0];
		} else {
			return $Params[1];
		}
		
	}

	function CheckChatID($chatId){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$ChatIdUser = $HlBlock::GetList(array(
			'select' => array('UF_CHAT_ID'),
			'filter' => array('ID' => ID_MODERATOR)
		))->Fetch()['UF_CHAT_ID'];
		if($ChatIdUser == $chatId){
			return true;
		} else {
			return false;
		}
	}

	function ChangeFeedback($chatId, $flag){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::GetList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
		if($User = $result->Fetch()){
			$result = $HlBlock::update($User['ID'], array(
                'UF_WRITE_REVIEW' => $flag
            ));
		}
	}

	function WriteIdLastMessage($chatId, $MessageId){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::GetList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
		if($User = $result->Fetch()){
			$result = $HlBlock::update($User['ID'], array(
				'UF_LAST_MESSAGE_ID' => $MessageId
			));
		}
		return true;
	}

	function AddDataBasePhone($chatId, $phone){
		$HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
		$result = $HlBlock::GetList(array(
			'select' => array('ID'),
			'filter' => array('UF_CHAT_ID' => $chatId)
		));
		if($User = $result->Fetch()){
			$result = $HlBlock::update($User['ID'], array(
				'UF_PHONE' => $phone
			));
		}
	}

	function GetMenu($keyboard){
		$HlBlock = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
		$MenuButton = $HlBlock::getList(array(
			'select' => array('UF_LINE_COMMAND'),
			'filter' => array('UF_COMMAND_TRIGGER' => $keyboard)
		))->Fetch()['UF_LINE_COMMAND'];
		$FirstLine = [];
		$SecondLine = [];
		$ThirdLine = [];
		$FourthLine = [];
		foreach($MenuButton as $value){
			$PropertiesList = explode("~", $value);
			$PropertiesList[0] = Get_smile_on_line($PropertiesList[0]);
			switch ($PropertiesList[3]) {
				case 1:
					$FirstLine[] = $PropertiesList[0];
					break;
				case 2:
					$SecondLine[] = $PropertiesList[0];
					break;
				case 3:
					$ThirdLine[] = $PropertiesList[0];
					break;
				case 4:
					$FourthLine[] = $PropertiesList[0];
					break;
				default:
					break;
			}
		}
		$result = [];
		array_push($result, $FirstLine);
		if(count($SecondLine) > 0)
			array_push($result, $SecondLine);
		if(count($ThirdLine) > 0)
			array_push($result, $ThirdLine);
		if(count($FourthLine) > 0)
			array_push($result, $FourthLine);
		return $result;
	} 

	function GetActionButton($keyboard){
		$HlBlock = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
		$MenuButton = $HlBlock::getList(array(
			'select' => array('UF_LINE_COMMAND'),
			'filter' => array('UF_COMMAND_TRIGGER' => $keyboard)
		))->Fetch()['UF_LINE_COMMAND'];
		foreach($MenuButton as $value){
			$PropertiesList = explode("~", $value);
			$Action[$PropertiesList[0]] = $PropertiesList[1];
		}
		return $Action;
	}

	function ShearchEmojiAndChange($LineMessage){
        global $massivEmoji;
        mb_internal_encoding("UTF-8mb4");
        for($i = 0; $i < strlen($LineMessage); $i++){
            $tmp = mb_substr($LineMessage, $i, 1);
            if($res = $massivEmoji[bin2hex($tmp)]){
                $resultLine .= $res;
            } else {
				$tmp2 = mb_substr($LineMessage, $i, 2);
				if($res = $massivEmoji[bin2hex($tmp2)]){
					$resultLine .= $res;
					$i++;
				} else {
					$resultLine .= $tmp;
				}
            }
        }
        return $resultLine;
    }

	function Get_smile_on_line($line){
        $ListSmile = preg_replace_callback('/\[smile\d+\]/',
            function ($matches) {
                global $massivEmojiId;
                $matches[0] = hex2bin($massivEmojiId[$matches[0]]);
                return $matches[0];
            },
            $line
        );
        return $ListSmile;
    }
?>