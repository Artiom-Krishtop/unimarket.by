<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    require 'config.php';
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

    global $USER;
    $URL = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/";
    if(!$USER->IsAuthorized()){
        LocalRedirect($URL);
    } elseif(!$USER->IsAdmin()) {
        LocalRedirect($URL);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?=$APPLICATION->SetTitle("Отправить всем сообщения"); ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href='css/custom.css'>
</head>
<body style="background-image: url(images/BackgroundPageSend.jpg);">
    <form enctype="multipart/form-data" action="" method="POST" id="FormMessageTelegramm">
        <div class="MessageSettings">
            <div>
                <input type="file" name="FileAddMessage" id="file">
            </div>
            <div style="text-align: center;">
                <p style="font-family: Roboto; font-weight: 900; font-size: 20px;">
                    Текст сообщения
                </p>
                <textarea name="text" id="text" rows="15" cols="80" placeholder="Введите текст сообщения" style="border-radius: 6px;"></textarea>
            </div>
            <div style="text-align: center;">
                <div class="LineButton">
                    <input type="text" name="keyboard[]" placeholder="Введите текст кнопки">
                    <select name="TextCommandButton[]" class="DoButton" data-number="1">
                        <option value="none">Нет действий</option>
                        <option value="NextMessage">Ссылка на другое сообщение</option>
                        <option value="OpenPage">Ссылка на сайт</option>
                    </select>
                </div>
                <div class="AddNewLineButton">
                    +
                </div>
            </div>
            
            <div style="display: flex; align-items: center; flex-direction: column;">
                <p style="font-family: Roboto; font-weight: 900; font-size: 20px;">
                    Список пользователей
                </p>
                <select name="selected[]" size="10" multiple="">
                    <?php
                        $HlBlock = GetEntityDataClass(HL_BLOCK_USER_LIST);
                        $listUsers = $HlBlock::getList(array(
                            'select' => array('*')
                        ));
                        while($user = $listUsers->Fetch()){
                            if($user['UF_CHAT_ID'] != ""):
                                if($user['UF_USER_NAME'] != ""){
                                    $name = $user['UF_USER_NAME'];
                                } else {
                                    $name = $user['UF_NAME'];
                                }
                    ?>
                        <option name="<?=$name ?>" value="<?=$user['UF_CHAT_ID']; ?>"><?=$name; ?>(<?=$user['ID'] ?>)</option>
                    <?php 
                            endif;
                        } 
                    ?>
                </select>
            </div>
            <div style="display: flex; flex-direction: row;">   
                <div style="margin-top: 10px; background-color: #cacccd; width: 200px; padding: 10px; box-shadow: inset 0px 0px 5px 2px rgba(0, 0, 0, 0.6);">
                    <label for="soonSend">Отложенная отправка:</label>
                    <input type="checkbox" name="soonSend" id="soonSend">
                    <div id="SetTimeSoonSend" style="display: none; margin-top: 15px;">
                        <input type="datetime-local" name="dateSoonSend">
                    </div>
                </div>
                <div style="margin-top: 10px; margin-left: 5px; background-color: #cacccd; width: 270px; padding: 10px; box-shadow: inset 0px 0px 5px 2px rgba(0, 0, 0, 0.6);">
                    <label for="command">Задать сообщение для команды:</label>
                    <input type="checkbox" name="command" id="command">
                    <div id="SetCommand" style="display: none; margin-top: 15px;">
                        <input type="text" name="TextCommand" placeholder="Введите комманду">
                    </div>
                </div>
            </div>
            <br>
            <input type="submit" value="Отправить" id="SendTelegramm">
        </div>
    </form>
    <div id="ResultSendTelegramm">

    </div>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/custom.js"></script>
</body>
</html>
