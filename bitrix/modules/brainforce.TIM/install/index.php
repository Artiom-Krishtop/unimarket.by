<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;


Loc::loadMessages(__FILE__);

class brainforce_TIM extends CModule
{
    var $MODULE_ID = 'brainforce.TIM';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    public function __construct()
    {
        if(file_exists(__DIR__."/version.php")){

            $arModuleVersion = [];
            include __DIR__ . '/version.php';

            $this->MODULE_ID 		   = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION 	   = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

            $this->MODULE_NAME =  Loc::getMessage('BRAINFORCE_TIM_NAME');
            $this->MODULE_DESCRIPTION =  Loc::getMessage('BRAINFORCE_TIM_MODULE_DESCRIPTION');
            $this->PARTNER_NAME =  Loc::getMessage("BRAINFORCE_TIM_PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("BRAINFORCE_TIM_PARTNER_URI");
        }
        return false;
    }
    public function AddHighloadBlock(){
        //HL UsersTelegramm
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsusers = Array(
            'ru' => '[TIM] Пользователи',
            'en' => '[TIM] Users'
        );
            $result_users = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimUsersTelegramm',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'users_telegramm',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_users->isSuccess()) {
            $errors_users = $result_users->getErrorMessages();
        } else {
            $id_users = $result_users->getId();
            $userTypeEntity_users = new CUserTypeEntity();
            foreach($arLangsusers as $lang_key_users => $lang_val_users) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_users,
                    'LID' => $lang_key_users,
                    'NAME' => $lang_val_users
                ));
            }
            $userTypeData_users = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_USER_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_CHAT_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_DATE_REGISTATION',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users4 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_LAST_MESSAGE_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users5 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users6 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_WRITE_REVIEW',
                'USER_TYPE_ID' => 'boolean',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => 0,
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users7 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_RESPONSE_TO_USER',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_users8 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_users,
                'FIELD_NAME' => 'UF_PHONE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_users = $userTypeEntity_users->Add($userTypeData_users);
            $userTypeId_users2 = $userTypeEntity_users->Add($userTypeData_users2);
            $userTypeId_users3 = $userTypeEntity_users->Add($userTypeData_users3);
            $userTypeId_users4 = $userTypeEntity_users->Add($userTypeData_users4);
            $userTypeId_users5 = $userTypeEntity_users->Add($userTypeData_users5);
            $userTypeId_users6 = $userTypeEntity_users->Add($userTypeData_users6);
            $userTypeId_users7 = $userTypeEntity_users->Add($userTypeData_users7);
            $userTypeId_users8 = $userTypeEntity_users->Add($userTypeData_users8);
            
        }

        //HL ListSendMessageTelegramm
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangs_mes = Array(
            'ru' => '[TIM] Рассылка',
            'en' => '[TIM] Mailing'
        );
            $result_mes = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimListSendMessageTelegramm',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'telegramm_message',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_mes->isSuccess()) {
            $errors_mes = $result_mes->getErrorMessages();
        } else {
            $id_mes = $result_mes->getId();
            $userTypeEntity_mes = new CUserTypeEntity();
            foreach($arLangs_mes as $lang_key_mes => $lang_val_mes) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_mes,
                    'LID' => $lang_key_mes,
                    'NAME' => $lang_val_mes
                ));
            }
            $userTypeData_mes = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_MESSAGE',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '120',
                    'ROWS' => '50',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_DATE_SEND',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_LIST_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'Y',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes4 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_DATE_CREATE',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes5 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_BUTTON_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'Y',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes6 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_LINE_COMMAND',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'Y',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes7 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_COMMAND_TRIGGER',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_mes8 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_mes,
                'FIELD_NAME' => 'UF_FILE',
                'USER_TYPE_ID' => 'file',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'SIZE' => '20',
                    'LIST_WIDTH'=>'200',
                    'LIST_HEIGHT'=>'200',
                    'MAX_SHOW_SIZE'=>'0',
                    'MAX_ALLOWED_SIZE'=>'0',
                    'TARGET_BLANK'=>'Y',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_mes = $userTypeEntity_mes->Add($userTypeData_mes);
            $userTypeId_mes2 = $userTypeEntity_mes->Add($userTypeData_mes2);
            $userTypeId_mes3 = $userTypeEntity_mes->Add($userTypeData_mes3);
            $userTypeId_mes4 = $userTypeEntity_mes->Add($userTypeData_mes4);
            $userTypeId_mes5 = $userTypeEntity_mes->Add($userTypeData_mes5);
            $userTypeId_mes6 = $userTypeEntity_mes->Add($userTypeData_mes6);
            $userTypeId_mes7 = $userTypeEntity_mes->Add($userTypeData_mes7);
            $userTypeId_mes8 = $userTypeEntity_mes->Add($userTypeData_mes8);
        }

        //HL FeedbackUsers
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsReview = Array(
            'ru' => '[TIM] Сообщения от пользователей',
            'en' => '[TIM] Messages from users'
        );
            $result_review = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimFeedbackUsers',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'feedback_users',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_review->isSuccess()) {
            $errors_review = $result_review->getErrorMessages();
        } else {
            $id_review = $result_review->getId();
            $userTypeEntity_review = new CUserTypeEntity();
            foreach($arLangsReview as $lang_key_review => $lang_val_review) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_review,
                    'LID' => $lang_key_review,
                    'NAME' => $lang_val_review
                ));
            }
            $userTypeData_review = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_review,
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_review2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_review,
                'FIELD_NAME' => 'UF_MESSAGE_REVIEW',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '75',
                    'ROWS' => '25',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_review3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_review,
                'FIELD_NAME' => 'UF_DATE_SEND',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            
            $userTypeId_review = $userTypeEntity_review->Add($userTypeData_review);
            $userTypeId_review2 = $userTypeEntity_review->Add($userTypeData_review2);
            $userTypeId_review3 = $userTypeEntity_review->Add($userTypeData_review3);
            $userTypeData_review4 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_review,
                'FIELD_NAME' => 'UF_USER_RESPONSE',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_review,
                    'HLFIELD_ID'=>$userTypeId_review2,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_review4 = $userTypeEntity_review->Add($userTypeData_review4);
        }
        //HL HistoryShearchUsers
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsRecords = Array(
            'ru' => '[TIM] История поиска',
            'en' => '[TIM] Search history'
        );
            $result_records = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimHistoryShearchUsers',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'history_shearch_users',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_records->isSuccess()) {
            $errors_records = $result_records->getErrorMessages();
        } else {
            $id_records = $result_records->getId();
            $userTypeEntity_records = new CUserTypeEntity();
            foreach($arLangsRecords as $lang_key_records => $lang_val_records) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_records,
                    'LID' => $lang_key_records,
                    'NAME' => $lang_val_records
                ));
            }
            $userTypeData_records = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_records,
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_records2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_records,
                'FIELD_NAME' => 'UF_SHEARCH_TEXT',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '75',
                    'ROWS' => '25',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_records3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_records,
                'FIELD_NAME' => 'UF_DATE_SHEARCH',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_records = $userTypeEntity_records->Add($userTypeData_records);
            $userTypeId_records2 = $userTypeEntity_records->Add($userTypeData_records2);
            $userTypeId_records3 = $userTypeEntity_records->Add($userTypeData_records3);
        }
        //HL HistoryShearchUsers
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsClick = Array(
            'ru' => '[TIM] История кликов',
            'en' => '[TIM] Click history'
        );
            $result_click = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimHistoryClickUsers',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'history_click_users',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_click->isSuccess()) {
            $errors_click = $result_click->getErrorMessages();
        } else {
            $id_click = $result_click->getId();
            $userTypeEntity_click = new CUserTypeEntity();
            foreach($arLangsClick as $lang_key_click => $lang_val_click) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_click,
                    'LID' => $lang_key_click,
                    'NAME' => $lang_val_click
                ));
            }
            $userTypeData_click = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_click,
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_click2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_click,
                'FIELD_NAME' => 'UF_TEXT_BUTTON',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '75',
                    'ROWS' => '25',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_click3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_click,
                'FIELD_NAME' => 'UF_DATE_CLICK_BUTTON',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_click = $userTypeEntity_click->Add($userTypeData_click);
            $userTypeId_click2 = $userTypeEntity_click->Add($userTypeData_click2);
            $userTypeId_click3 = $userTypeEntity_click->Add($userTypeData_click3);
        }
        $userTypeId_users = $userTypeId_users;
        $id_users = $id_users;
        //HL TimFavorites
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsFavorites = Array(
            'ru' => '[TIM] Избранное',
            'en' => '[TIM] Favorites'
        );
            $result_favorites = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimFavorites',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'favorites_users',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_favorites->isSuccess()) {
            $errors_favorites = $result_favorites->getErrorMessages();
        } else {
            $id_favorites = $result_favorites->getId();
            $userTypeEntity_favorites = new CUserTypeEntity();
            foreach($arLangsFavorites as $lang_key_favorites => $lang_val_favorites) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_favorites,
                    'LID' => $lang_key_favorites,
                    'NAME' => $lang_val_favorites
                ));
            }
            $userTypeData_favorites = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_favorites,
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_favorites2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_favorites,
                'FIELD_NAME' => 'UF_PRODUCT_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_favorites3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_favorites,
                'FIELD_NAME' => 'UF_DATE_ADD',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_favorites = $userTypeEntity_favorites->Add($userTypeData_favorites);
            $userTypeId_favorites2 = $userTypeEntity_favorites->Add($userTypeData_favorites2);
            $userTypeId_favorites3 = $userTypeEntity_favorites->Add($userTypeData_favorites3);
        }

        //HL TimOrders
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $arLangsOrders = Array(
            'ru' => '[TIM] Заказы',
            'en' => '[TIM] Orders'
        );
            $result_orders = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'TimOrders',//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
                'TABLE_NAME' => 'orders_users',//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
            ));
        if (!$result_orders->isSuccess()) {
            $errors_orders = $result_orders->getErrorMessages();
        } else {
            $id_orders = $result_orders->getId();
            $userTypeEntity_orders = new CUserTypeEntity();
            foreach($arLangsOrders as $lang_key_orders => $lang_val_orders) {
                Bitrix\Highloadblock\HighloadBlockLangTable::add(array(
                    'ID' => $id_orders,
                    'LID' => $lang_key_orders,
                    'NAME' => $lang_val_orders
                ));
            }
            $userTypeData_orders = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_orders,
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'hlblock',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'HLBLOCK_ID'=>$id_users,
                    'HLFIELD_ID'=>$userTypeId_users,
                    'DISPLAY'=>'LIST',
                    'LIST_HEIGHT'=>'1'
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_orders2 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_orders,
                'FIELD_NAME' => 'UF_PRODUCT_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_orders3 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_orders,
                'FIELD_NAME' => 'UF_DATE_ADD',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    "USE_SECOND" => "Y"
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_orders4 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_orders,
                'FIELD_NAME' => 'UF_PAYMENT_METHOD',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeData_orders5 = array(
                'ENTITY_ID' => 'HLBLOCK_' . $id_orders,
                'FIELD_NAME' => 'UF_DELIVERY',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => array(
                    'DEFAULT_VALUE' => '',
                    'SIZE' => '20',
                    'ROWS' => '1',
                    'MIN_LENGTH' => '0',
                    'MAX_LENGTH' => '0',
                    'REGEXP' => '',
                ),
                'EDIT_FORM_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'ERROR_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
                'HELP_MESSAGE' => array(
                    'ru' => '',
                    'en' => '',
                ),
            );
            $userTypeId_orders = $userTypeEntity_orders->Add($userTypeData_orders);
            $userTypeId_orders2 = $userTypeEntity_orders->Add($userTypeData_orders2);
            $userTypeId_orders3 = $userTypeEntity_orders->Add($userTypeData_orders3);
            $userTypeId_orders4 = $userTypeEntity_orders->Add($userTypeData_orders4);
            $userTypeId_orders5 = $userTypeEntity_orders->Add($userTypeData_orders5);
            return $id_orders;
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){

            $this->InstallFiles();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
            $this->AddHighloadBlock();

        }else{

            $APPLICATION->ThrowException(Loc::getMessage("BRAINFORCE_TIM_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("BRAINFORCE_TIM_INSTALL_TITLE")." \"".Loc::getMessage("BRAINFORCE_TIM_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
    }

    public function InstallFiles(){
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/telegramm_main", $_SERVER["DOCUMENT_ROOT"]."/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return false;
    }

    public function InstallDB(){

        return false;
    }

    public function InstallEvents(){

        return false;
    }

    public function DoUninstall(){

        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        // $this->UnInstallDB();
        // $this->DelHighloadBlock();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("BRAINFORCE_TIM_UNINSTALL_TITLE")." \"".Loc::getMessage("BRAINFORCE_TIM_NAME")."\"",
            __DIR__."/unstep.php"
        );

        return false;
    }
    public function DelHighloadBlock(){
        \Bitrix\Main\Loader::IncludeModule("highloadblock");
        $result_mes = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TimListSendMessageTelegramm")));
        if($row_mes = $result_mes->fetch())
        {
            $HLBLOCK_ID_mes = $row_mes["ID"];
        }
        if(!empty($HLBLOCK_ID_mes))
            Bitrix\Highloadblock\HighloadBlockTable::delete($HLBLOCK_ID_mes);

        $result_users = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TimUsersTelegramm")));
        if($row_users = $result_users->fetch())
        {
            $HLBLOCK_ID_users = $row_users["ID"];
        }
        if(!empty($HLBLOCK_ID_users))
            Bitrix\Highloadblock\HighloadBlockTable::delete($HLBLOCK_ID_users);
        $result_review = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TimFeedbackUsers")));
        if($row_review = $result_review->fetch())
        {
            $HLBLOCK_ID_review = $row_review["ID"];
        }
        if(!empty($HLBLOCK_ID_review))
            Bitrix\Highloadblock\HighloadBlockTable::delete($HLBLOCK_ID_review);

        $result_click = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TimHistoryClickUsers")));
        if($row_click = $result_click->fetch())
        {
            $HLBLOCK_ID_click = $row_click["ID"];
        }
        if(!empty($HLBLOCK_ID_click))
            Bitrix\Highloadblock\HighloadBlockTable::delete($HLBLOCK_ID_click);

        $result_records = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TimHistoryShearchUsers")));
        if($row_records = $result_records->fetch())
        {
            $HLBLOCK_ID_records = $row_records["ID"];
        }
        if(!empty($HLBLOCK_ID_records))
            Bitrix\Highloadblock\HighloadBlockTable::delete($HLBLOCK_ID_records);
}
    public function UnInstallFiles(){
        DeleteDirFilesEx("/telegramm");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/brainforce.TIM/");//icons
//        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/brainforce.TIM/install/telegramm_main", $_SERVER["DOCUMENT_ROOT"]."/");
        return false;
    }

    public function UnInstallDB(){

        return false;
    }

    public function UnInstallEvents(){

        return false;
    }
}
?>