<?
//use Bitrix\Main\Application;
//use Bitrix\Main\Loader;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

if(class_exists('bgpb_paymentgate')) return;

Class bgpb_paymentgate extends CModule
{
    var $MODULE_ID = 'bgpb.paymentgate';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = 'Y';
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function bgpb_paymentgate()
    {
        $arModuleVersion = array();

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('IM_PG_MODULE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('IM_PG_MODULE_MODULE_DESCRIPTION');

        $this->PARTNER_NAME = Loc::getMessage('IM_PG_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'http://www.bgpb.by';
    }
    
    function GetModuleRightList()
    {
        return array(
            'reference_id' => array('D','R','W'),
            'reference' => array(
                '[D] '.Loc::getMessage('IM_PG_MODULE_DENIED'),
                '[R] '.Loc::getMessage('IM_PG_MODULE_VIEW'),
                '[W] '.Loc::getMessage('IM_PG_MODULE_ADMIN')
            )
        );
    }

    function DoInstall()
    {
        if($GLOBALS['APPLICATION']->GetGroupRight($this->MODULE_ID) >= 'W'){
            $this->InstallFiles();
            if (Loader::includeModule('sale')) {
                CSalePaySystemAction::Add(array(
                    'PAY_SYSTEM_ID' => '',
                    'PERSON_TYPE_ID' => '',
                    'NAME' => 'PaymentGate',
                    'ACTION_FILE' => 'paymentgate',
                    'NEW_WINDOW'=>'N',
                    'IS_CASH'=>'N',
                    'ENCODING'=>'utf-8'
                ));
            }
            
            ModuleManager::registerModule($this->MODULE_ID);
        }
    }

    function DoUninstall()
    {
        if($GLOBALS['APPLICATION']->GetGroupRight($this->MODULE_ID) == 'W'){
            $this->UnInstallFiles();
            if (Loader::includeModule('sale')) {
                $db = CSalePaySystemAction::GetList(
                    array('ID' => 'ASC'),
                    array('ACTION_FILE' => 'paymentgate'),
                    false,false,
                    array('ID')
                );
                while ($row = $db->Fetch())
                    CSalePaySystemAction::Delete($row['ID']);
            }
            ModuleManager::unregisterModule($this->MODULE_ID);
        }

    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bgpb.paymentgate/install/paymentgate/', $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/sale_payment/paymentgate/',true,true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bgpb.paymentgate/install/paymentgate_pages/', $_SERVER['DOCUMENT_ROOT'].'/paymentgate/',true,true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('/local/php_interface/include/sale_payment/paymentgate');
        DeleteDirFilesEx('/paymentgate');
        return true;
    }
}