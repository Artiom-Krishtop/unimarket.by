<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/telegramm/config.php';

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Config\Option;
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

$APPLICATION->SetTitle(Loc::getMessage("BRAINFORCE_TIM_TITLE_ADMIN_MENU"));
?>
<style>
    /* 
    #FormMessageTelegramm{
        display: flex;
        align-items: center;
        flex-direction: column;
    }

    .MessageSettings{
        background-color: #fffe90;
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 15px 15px 15px 15px;
        border-radius: 10px;
    } */

    .input_select{
        margin-top: 5px;
    }

    .LineButton{
        margin-top: 5px;
    }

    .AddNewLineButtonMenu,
    .AddNewButtonInLineMenu,
    .AddNewLineButton {
        display: inline-block;
        font-family: arial,sans-serif;
        font-size: 11px;
        font-weight: bold;
        color: rgb(68,68,68);
        text-decoration: none;
        user-select: none;
        padding: .2em 1.2em;
        outline: none;
        border: 1px solid rgba(0,0,0,.1);
        border-radius: 2px;
        background: rgb(245,245,245) linear-gradient(#f4f4f4, #f1f1f1);
        transition: all .218s ease 0s;
    }
    .AddNewLineButtonMenu,
    .AddNewButtonInLineMenu, 
    .AddNewLineButton:hover {
        color: rgb(24,24,24);
        border: 1px solid rgb(198,198,198);
        background: #f7f7f7 linear-gradient(#f7f7f7, #f1f1f1);
        box-shadow: 0 1px 2px rgba(0,0,0,.1);
        cursor: pointer;
    }
    .AddNewLineButtonMenu,
    .AddNewButtonInLineMenu, 
    .AddNewLineButton:active {
        color: rgb(51,51,51);
        border: 1px solid rgb(204,204,204);
        background: rgb(238,238,238) linear-gradient(rgb(238,238,238), rgb(224,224,224));
        box-shadow: 0 1px 2px rgba(0,0,0,.1) inset;
    }
    /* #SendTelegramm {
        transition: 0.5s;
        background-color: #3aa5db;
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
    }
    #SendTelegramm:hover {
        cursor: pointer;
        background-color: #0d86c2;
    }*/
</style>
<div class="adm-detail-block">
    <div class="adm-detail-tabs-block">
        <span class="adm-detail-tab adm-detail-tab-active" id="message">
            <?=Loc::getMessage("BRAINFORCE_TIM_INDEX_TAB_MSG");?>
        </span>
        <span class="adm-detail-tab" id="menu">
            <?=Loc::getMessage("BRAINFORCE_TIM_INDEX_EDIT_MENU_TAB");?>
        </span>
    </div>  
    <div class="tab-message">
        <div class="adm-detail-content-wrap">
            <div class="adm-detail-content">
                <div class="adm-detail-title">
                    <?=Loc::getMessage("BRAINFORCE_TIM_INDEX_TITLE_MSG");?>
                </div>
                <div class="adm-detail-content-item-block">
                    <div class="adm-detail-content-table edit-table">
                        <!-- ФОРМА ВВОДА ДАННЫХ -->
                        <form enctype="multipart/form-data" action="" method="POST" id="FormMessageTelegramm">
                        <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
                            <tbody>
                                <tr class="adm-detail-required-field">
                                    <td width="40%" class="adm-detail-content-cell-l">
                                        <?=Loc::getMessage("BRAINFORCE_TIM_UPLOAD_FILE");?>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <div class="adm-input-file-control adm-input-file-top-shift" id="bx_file_FileAddMessage_cont">
                                            <div class="adm-input-file-new">
                                                <span class="adm-input-file">
                                                    <span>Добавить файл</span>
                                                    <input type="file" name="FileAddMessage" class="adm-designed-file">
                                                </span> 
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="adm-detail-required-field">
                                    <td valign="top" width="40%" class="adm-detail-content-cell-l">
                                        <label for="text"><?=Loc::getMessage("BRAINFORCE_TIM_TEXT_MESSAGE");?></label>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <textarea name="text" id="text" rows="10" cols="90" placeholder="Введите текст сообщения" style="border-radius: 6px;"></textarea>
                                    </td>
                                </tr>
                                <tr class="adm-detail-required-field">
                                    <td valign="top" width="40%" class="adm-detail-content-cell-l">
                                        <label for="text"><?=Loc::getMessage("BRAINFORCE_TIM_BUTTON_ADD");?></label>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <div style="text-align: center;">
                                            <div class="LineButton">
                                                <input type="text" name="keyboard[]" placeholder="Введите текст кнопки">
                                                <select name="TextCommandButton[]" class="typeselect DoButton" data-number="1">
                                                    <option value="none">Нет действий</option>
                                                    <option value="NextMessage">Ссылка на другое сообщение</option>
                                                    <option value="OpenPage">Ссылка на сайт</option>
                                                </select>
                                            </div>
                                            <div class="AddNewLineButton">
                                                +
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="adm-detail-required-field">
                                    <td valign="top" width="40%" class="adm-detail-content-cell-l">
                                        <?=Loc::getMessage("BRAINFORCE_TIM_LIST_USERS");?>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <select name="selected[]" size multiple>
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
                                    </td>
                                </tr>
                                <tr class="adm-detail-required-field">
                                    <td valign="top" width="40%" class="adm-detail-content-cell-l">
                                        <label for="soonSend"><?=Loc::getMessage("BRAINFORCE_TIM_SOON_SEND");?></label>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <div>
                                            <input type="checkbox" name="soonSend" id="soonSend">
                                            <div id="SetTimeSoonSend" style="display: none; margin-top: 15px;">
                                                <div class="adm-input-wrap adm-input-wrap-calendar">
                                                    <input class="adm-input adm-input-calendar" type="text" name="dateSoonSend" size="23" value="">
                                                    <span class="adm-calendar-icon" title="Нажмите для выбора даты" onclick="BX.calendar({node:this, field:'dateSoonSend', form: '', bTime: true, bHideTime: false});"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="adm-detail-required-field">
                                    <td valign="top" width="40%" class="adm-detail-content-cell-l">
                                        <label for="command"><?=Loc::getMessage("BRAINFORCE_TIM_SET_COMMAND");?></label>
                                    </td>
                                    <td width="60%" class="adm-detail-content-cell-r">
                                        <div>
                                            <input type="checkbox" name="command" id="command">
                                            <div id="SetCommand" style="display: none; margin-top: 15px;">
                                                <input type="text" name="TextCommand" placeholder="Введите комманду">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="left: 0px;">
                <div class="adm-detail-content-btns">
                    <input type="submit" value="Отправить" id="SendTelegramm">
                </div>
            </div>  
        </form>
        </div>
    </div>
    <div class="tab-menu">
        <div class="adm-detail-content-wrap">
            <div class="adm-detail-content">
                <div class="adm-detail-title">
                    <?=Loc::getMessage("BRAINFORCE_TIM_INDEX_TITLE_EDIT_MENU_MSG");?>
                </div>
                <div class="adm-detail-content-item-block">
                    <div class="adm-detail-content-table edit-table">
                        <!-- ФОРМА ВВОДА ДАННЫХ -->
                        <form enctype="multipart/form-data" action="" method="POST" id="EditMenuTelegramm">
                        <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
                            <tbody>
                                <tr class="adm-detail-required-field">
                                    <td width="100%" class="adm-detail-content-cell-r">
                                        <table>
                                            <tbody>
                                                <?php
                                                    $HlBlock = GetEntityDataClass(HL_BLOCK_MESSAGE_LIST);
                                                    $ButtonList = $HlBlock::getList(array(
                                                        'select' => array('UF_LINE_COMMAND'),
                                                        'filter' => array('UF_COMMAND_TRIGGER' => 'MENU')
                                                    ));
                                                    $Actions = [
                                                        '0' => [
                                                            'command' => 'none',
                                                            'text'    => 'Нет действий',
                                                        ],
                                                        '1' => [
                                                            'command' => 'catalog',
                                                            'text'    => 'Каталог',
                                                        ],
                                                        '2' => [
                                                            'command' => 'about',
                                                            'text'    => 'О компании',
                                                        ],
                                                        '3' => [
                                                            'command' => 'feedback',
                                                            'text'    => 'Обратная связь',
                                                        ],
                                                        '4' => [
                                                            'command' => 'repost',
                                                            'text'    => 'Поделиться ботом',
                                                        ]
                                                    ];
                                                    $FirstLine[] = '<tr>';
                                                    $SecondLine[] = '<tr>';
                                                    $ThirdLine[] = '<tr>';
                                                    $FourthLine[] = '<tr>';
                                                    if ($Button = $ButtonList->Fetch()) {
                                                        foreach($Button['UF_LINE_COMMAND'] as $value){
                                                            $PropertiesList = explode("~", $value);
                                                            $Options = '';
                                                            switch ($PropertiesList[3]) {
                                                                case 1: 
                                                                    foreach($Actions as $value){
                                                                        $status = '';
                                                                        if($value["command"] == $PropertiesList[1]){
                                                                            $status = 'selected';
                                                                        }
                                                                        $Options .= '<option '.$status.' value="'.$value["command"].'">'.$value["text"].'</option>';
                                                                    }
                                                                    $FirstLine[$PropertiesList[2]][] = '<td>';
                                                                    $FirstLine[$PropertiesList[2]][] = '<div class="LineButton">';
                                                                    $FirstLine[$PropertiesList[2]][] = '<input type="text" name="keyboard[]" placeholder="Введите текст кнопки" value="'.$PropertiesList[0].'"><input type="text" name="PositionY[]" style="display: none;" value="'.$PropertiesList[3].'"><input type="text" name="PositionX[]" style="display: none;" value="'.$PropertiesList[2].'"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'.$PropertiesList[3].'" data-width="'.$PropertiesList[2].'">'.$Options.'</select>';
                                                                    $FirstLine[$PropertiesList[2]][] = '<div class="AddNewButtonInLineMenu">+</div>';
                                                                    $FirstLine[$PropertiesList[2]][] = '</div>';
                                                                    $FirstLine[$PropertiesList[2]][] = '<div class="AddNewLineButtonMenu">+</div>';
                                                                    $FirstLine[$PropertiesList[2]][] = '</td>';
                                                                    break;
                                                                case 2:
                                                                    foreach($Actions as $value){
                                                                        $status = '';
                                                                        if($value["command"] == $PropertiesList[1]){
                                                                            $status = 'selected';
                                                                        }
                                                                        $Options .= '<option '.$status.' value="'.$value["command"].'">'.$value["text"].'</option>';
                                                                    }
                                                                    $SecondLine[$PropertiesList[2]][] = '<td>';
                                                                    $SecondLine[$PropertiesList[2]][] = '<div class="LineButton">';
                                                                    $SecondLine[$PropertiesList[2]][] = '<input type="text" name="keyboard[]" placeholder="Введите текст кнопки" value="'.$PropertiesList[0].'"><input type="text" name="PositionY[]" style="display: none;" value="'.$PropertiesList[3].'"><input type="text" name="PositionX[]" style="display: none;" value="'.$PropertiesList[2].'"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'.$PropertiesList[3].'" data-width="'.$PropertiesList[2].'">'.$Options.'</select>';
                                                                    $SecondLine[$PropertiesList[2]][] = '<div class="AddNewButtonInLineMenu">+</div>';
                                                                    $SecondLine[$PropertiesList[2]][] = '</div>';
                                                                    $SecondLine[$PropertiesList[2]][] = '<div class="AddNewLineButtonMenu">+</div>';
                                                                    $SecondLine[$PropertiesList[2]][] = '</td>';
                                                                    break;
                                                                case 3:
                                                                    foreach($Actions as $value){
                                                                        $status = '';
                                                                        if($value["command"] == $PropertiesList[1]){
                                                                            $status = 'selected';
                                                                        }
                                                                        $Options .= '<option '.$status.' value="'.$value["command"].'">'.$value["text"].'</option>';
                                                                    }
                                                                    $ThirdLine[$PropertiesList[2]][] = '<td>';
                                                                    $ThirdLine[$PropertiesList[2]][] = '<div class="LineButton">';
                                                                    $ThirdLine[$PropertiesList[2]][] = '<input type="text" name="keyboard[]" placeholder="Введите текст кнопки" value="'.$PropertiesList[0].'"><input type="text" name="PositionY[]" style="display: none;" value="'.$PropertiesList[3].'"><input type="text" name="PositionX[]" style="display: none;" value="'.$PropertiesList[2].'"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'.$PropertiesList[3].'" data-width="'.$PropertiesList[2].'">'.$Options.'</select>';
                                                                    $ThirdLine[$PropertiesList[2]][] = '<div class="AddNewButtonInLineMenu">+</div>';
                                                                    $ThirdLine[$PropertiesList[2]][] = '</div>';
                                                                    $ThirdLine[$PropertiesList[2]][] = '<div class="AddNewLineButtonMenu">+</div>';
                                                                    $ThirdLine[$PropertiesList[2]][] = '</td>';
                                                                    break;
                                                                case 4:
                                                                    foreach($Actions as $value){
                                                                        $status = '';
                                                                        if($value["command"] == $PropertiesList[1]){
                                                                            $status = 'selected';
                                                                        }
                                                                        $Options .= '<option '.$status.' value="'.$value["command"].'">'.$value["text"].'</option>';
                                                                    }
                                                                    $FourthLine[$PropertiesList[2]][] = '<td>';
                                                                    $FourthLine[$PropertiesList[2]][] = '<div class="LineButton">';
                                                                    $FourthLine[$PropertiesList[2]][] = '<input type="text" name="keyboard[]" placeholder="Введите текст кнопки" value="'.$PropertiesList[0].'"><input type="text" name="PositionY[]" style="display: none;" value="'.$PropertiesList[3].'"><input type="text" name="PositionX[]" style="display: none;" value="'.$PropertiesList[2].'"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'.$PropertiesList[3].'" data-width="'.$PropertiesList[2].'">'.$Options.'</select>';
                                                                    $FourthLine[$PropertiesList[2]][] = '<div class="AddNewButtonInLineMenu">+</div>';
                                                                    $FourthLine[$PropertiesList[2]][] = '</div>';
                                                                    $FourthLine[$PropertiesList[2]][] = '<div class="AddNewLineButtonMenu">+</div>';
                                                                    $FourthLine[$PropertiesList[2]][] = '</td>';
                                                                    break;
                                                                default:
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                    $FirstLine[] = '</tr>';
                                                    $SecondLine[] = '</tr>';
                                                    $ThirdLine[] = '</tr>';
                                                    $FourthLine[] = '</tr>';
                                                    $result = [];
                                                    array_push($result, $FirstLine);
                                                    if(count($SecondLine) > 2)
                                                        array_push($result, $SecondLine);
                                                    if(count($ThirdLine) > 2)
                                                        array_push($result, $ThirdLine);
                                                    if(count($FourthLine) > 2)
                                                        array_push($result, $FourthLine);
                                                ?>


                                                <?php
                                                    $Line = '';
                                                    function AddLine($massiv, $flagLineAdd = false, $flagRowAdd = false){
                                                        global $Line;
                                                        foreach($massiv as $key => $value){
                                                            if($flagLineAdd == false && $key == 3){
                                                                continue;
                                                            }
                                                            if($flagRowAdd == false && $key == 5){
                                                                continue;
                                                            }
                                                            $Line .= $value;
                                                        }
                                                    }
                                                    
                                                    foreach($result as $key => $value){
                                                        foreach($value as $key2 => $value2){
                                                            if($key2 == 0){
                                                                $Line .= $value2;
                                                                continue;
                                                            } else {
                                                                if(count($value)-1 == $key2){
                                                                    $Line .= $value2;
                                                                    continue;
                                                                }
                                                                if(count($value)-2 == $key && $key == 1){
                                                                    AddLine($value2, true, true);
                                                                    continue;
                                                                } else {
                                                                    if(count($value)-2 == $key2 && $key2 < 3) {
                                                                        AddLine($value2, true);
                                                                        continue;
                                                                    }
                                                                    if(count($result)-1 == $key && $key < 4){
                                                                        AddLine($value2, false, true);
                                                                        continue;
                                                                    }
                                                                    AddLine($value2);
                                                                    continue;
                                                                }
                                                                
                                                            }
                                                        }
                                                    }
                                                    echo $Line;
                                                ?>
                                                <!-- <tr>
                                                    <td>
                                                        <div class="LineButton">
                                                            <input type="text" name="keyboard[]" placeholder="Введите текст кнопки">
                                                            <input type="text" name="PositionY[]" style="display: none;" value="1">
                                                            <input type="text" name="PositionX[]" style="display: none;" value="1">
                                                            <select name="TextCommandButton[]" class="typeselect DoButton" data-number="1" data-width="1">
                                                                <option value="none">Нет действий</option>
                                                                <option value="Catalog">Каталог</option>
                                                                <option value="about">О компании</option>
                                                                <option value="feedback">Обратная связь</option>
                                                                <option value="repost">Поделиться с другом</option>
                                                            </select>
                                                            <div class="AddNewButtonInLineMenu">
                                                                +
                                                            </div>
                                                        </div>
                                                        <div class="AddNewLineButtonMenu">
                                                            +
                                                        </div>
                                                    </td>
                                                </tr> -->
                                            </tbody>
                                        </table>
                                            
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="left: 0px;">
                <div class="adm-detail-content-btns">
                    <input type="submit" value="Отправить" id="SendTelegrammMenu">
                </div>
            </div>  
                        </form>
        </div>
    </div>
</div>
<div class="adm-info-message-wrap">
    <div class="adm-info-message" id="ResultSendTelegramm">	
        Тут выводиться результат выполнения формы
    </div>
</div>

<script src="/telegramm/js/jquery-3.6.0.min.js"></script>
<script>
    if($('#message').hasClass('adm-detail-tab-active')){
        $('.tab-menu').css({'display':'none'});
    }
    else if($('#menu').hasClass('adm-detail-tab-active')){
        $('.tab-message').css({'display':'none'});
    }
    $('#message').on('click',function(){
        $('#menu').removeClass('adm-detail-tab-active');
        $(this).addClass('adm-detail-tab-active');

        $('.tab-message').css({'display':'block'});
        $('.tab-menu').css({'display':'none'});
    });
    $('#menu').on('click',function(){
        $('#message').removeClass('adm-detail-tab-active')
        $(this).addClass('adm-detail-tab-active');

        $('.tab-menu').css({'display':'block'});
        $('.tab-message').css({'display':'none'});
    });
</script>
<script src="/telegramm/js/custom.js"></script>
<?require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php'; ?>