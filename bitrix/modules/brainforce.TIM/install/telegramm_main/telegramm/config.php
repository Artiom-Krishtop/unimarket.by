<?php
    use Bitrix\Main\Config\Option as Option;
    CModule::IncludeModule('highloadblock');
    $module_id = 'brainforce.TIM';
    $arOptions = Option::getForModule($module_id,'arOptions');
    $token= trim($arOptions['BOT_TOKEN'], " ");
    $BotName = trim($arOptions['BOT_USERNAME'], " ");
    $Category = trim($arOptions['IBLOCK_SELECT'], " ");
    $Props = trim($arOptions['IBLOCK_PROPS']);
    $Props = explode(',', $Props);
    $IdModerator = trim($arOptions['ID_MODERATOR'], " ");
    
    define('CODE_PRICE', trim($arOptions['CODE_PRICE_ELEMENT'], " "));
    define('ID_PRICE', trim($arOptions['ID_GROUP_CATALOG'], " "));

    
    define('BOT_TOKEN', $token);
    define('BOT_USERNAME', $BotName);
    define('CATEGORY', $Category);
    define('ID_MODERATOR', $IdModerator);


    $result = \Bitrix\Highloadblock\HighloadBlockTable::getList(
        array(
            'filter'=>array('=TABLE_NAME'=>["telegramm_message", "users_telegramm", "feedback_users", "history_click_users", "history_shearch_users", "favorites_users", "orders_users"])
        )
    );
    while($row = $result->fetch()){
        switch ($row['TABLE_NAME']) {
            case 'users_telegramm':
                $USER_HLBLOCK_ID = $row["ID"];
                break;
            case 'telegramm_message':
                $MESSAGE_HLBLOCK_ID = $row["ID"];
                break;
            case 'feedback_users':
                $FEEDBACK_USERS_HLBLOCK_ID = $row["ID"];
                break; 
            case 'history_click_users':
                $HISTORY_CLICK_USERS_HLBLOCK_ID = $row["ID"];
                break;
            case 'history_shearch_users':
                $HISTORY_SHEARCH_USERS_HLBLOCK_ID = $row["ID"];
                break; 
            case 'favorites_users':
                $FAVORITES_USERS_HLBLOCK_ID = $row["ID"];
                break; 
            case 'orders_users':
                $ORDERS_USERS_HLBLOCK_ID = $row["ID"];
                break; 
            default:
                break;
        }
    }
    define('HL_BLOCK_USER_LIST', $USER_HLBLOCK_ID);
    define('HL_BLOCK_MESSAGE_LIST', $MESSAGE_HLBLOCK_ID);
    define('HL_BLOCK_FEEDBACK_LIST', $FEEDBACK_USERS_HLBLOCK_ID);
    define('HL_BLOCK_HISTORY_CLICK', $HISTORY_CLICK_USERS_HLBLOCK_ID);
    define('HL_BLOCK_HISTORY_SHEARCH', $HISTORY_SHEARCH_USERS_HLBLOCK_ID);
    define('HL_BLOCK_FAVORITES_USERS', $FAVORITES_USERS_HLBLOCK_ID);
    define('HL_BLOCK_ORDERS_USERS', $ORDERS_USERS_HLBLOCK_ID);
    
?> 