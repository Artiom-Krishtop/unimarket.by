<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/telegramm/config.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/telegramm/Bot/Functions.php';
    require '../listSmile.php';

    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    CModule::IncludeModule('highloadblock');

    foreach($_POST['keyboard'] as $key => $value){
        $value = ShearchEmojiAndChange($value);
        $Line[] = $value."~".$_POST['TextCommandButton'][$key]."~".$_POST['PositionX'][$key]."~".$_POST['PositionY'][$key];
    }

    $HlBlock = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
    $MenuId = $HlBlock::getList(array(
        'select' => array('ID'),
        'filter' => array('UF_COMMAND_TRIGGER' => 'MENU')
    ))->Fetch()['ID'];
    $result = $HlBlock::update($MenuId['ID'], array(
        'UF_LINE_COMMAND' => $Line 
    ));
    $resultMessage[] = "Результат: <br>";
    $resultMessage[] = 'Меню успешно сохранено';
    echo json_encode($resultMessage);
?>