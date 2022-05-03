<?php
    require_once $_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/main/include/prolog_before.php';
    require_once '../vendor/autoload.php';
    require_once '../config.php';
    require '../listSmile.php';
    require_once '../Bot/Functions.php';
    require 'SendMessage.php';

    // var_dump($_POST);
    
    if($_POST['soonSend'] == 'on'){
        $SaveMessage = new SenderMessage($_POST['keyboard'], $_POST["selected"], $_POST['text'], $_POST['dateSoonSend'], false, null, $_POST['DoButton']);
        echo $SaveMessage->AddHistoryMessage($_POST['TextCommandButton'], ($_FILES['FileAddMessage']['name'] != "")?$_FILES['FileAddMessage']:null);
    } else {
        if($_POST['command'] != 'on'){
            $sender = new SenderMessage($_POST['keyboard'], $_POST["selected"], $_POST['text'], null, false, null, $_POST['DoButton']);
            echo $sender->SendMessage($_POST['TextCommandButton'], ($_FILES['FileAddMessage']['name'] != "")?$_FILES['FileAddMessage']:null);
        } else {
            $sender = new SenderMessage($_POST['keyboard'], $_POST["selected"], $_POST['text'], null, false, $_POST['TextCommand'], $_POST['DoButton']);
            echo $sender->AddDataBase($_POST['TextCommandButton'], ($_FILES['FileAddMessage']['name'] != "")?$_FILES['FileAddMessage']:null);
        }
    }

    
    
    
?>