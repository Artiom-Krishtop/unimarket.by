<?
include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/lib/orm/profile.php";
include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/lib/orm/profile/const.php";
include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/lib/orm/profile/column.php";
include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/lib/orm/profile/column/customfields.php";

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Application;
use Mcart\Xls\ORM\ProfileTable;
use Mcart\Xls\ORM\Profile\ConstTable;
use Mcart\Xls\ORM\Profile\ColumnTable;
use Mcart\Xls\ORM\Profile\Column\CustomFieldsTable;

Loc::loadMessages(__FILE__);

if (class_exists("mcart_xls")) {
    return;
}

class mcart_xls extends CModule {

    public $MODULE_ID = "mcart.xls";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "Y";
	public $PARTNER_NAME;
	public $PARTNER_URI;
    private $config_debug = false;
    private $connection;
    private $sqlHelper;

    public function mcart_xls() {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = TASKFROMEMAIL_MODULE_VERSION;
            $this->MODULE_VERSION_DATE = TASKFROMEMAIL_MODULE_VERSION_DATE;
        }

        $this->MODULE_NAME = Loc::getMessage("MCART_XLS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MCART_XLS_MODULE_DESCRIPTION");

        $this->PARTNER_NAME = "MCArt";
        $this->PARTNER_NAME = Loc::getMessage("MCART_XLS_PARTNER_NAME");
        $this->PARTNER_URI = "http://mcart.ru/";

        $arConfig = Configuration::getValue('exception_handling');
        $this->config_debug = $arConfig['debug'];

        $this->connection = Application::getConnection();
        $this->sqlHelper = $this->connection->getSqlHelper();
    }

    public function DoInstall() {
        $result = true;
        if (!IsModuleInstalled($this->MODULE_ID)) {
            $result = $this->InstallDB();
            if(!$result){
                return $result;
            }
            $result = $this->InstallEvents();
            if(!$result){
                return $result;
            }
            $result = $this->InstallFiles();
        }
        return $result;
    }

    public function DoUninstall() {
        $result = $this->UnInstallDB();
        if(!$result){
            return $result;
        }
        $result = $this->UnInstallEvents();
        if(!$result){
            return $result;
        }
        $result = $this->UnInstallFiles();
        return $result;
    }

    public function InstallDB() {
        try {
            $ob = ProfileTable::getEntity();
            if(!$this->isExistsTable($ob->getDBTableName())){
                $ob->createDbTable();
            }
            $ob = ConstTable::getEntity();
            if(!$this->isExistsTable($ob->getDBTableName())){
                $ob->createDbTable();
            }
            $ob = ColumnTable::getEntity();
            if(!$this->isExistsTable($ob->getDBTableName())){
                $ob->createDbTable();
            }
            $ob = CustomFieldsTable::getEntity();
            if(!$this->isExistsTable($ob->getDBTableName())){
                $ob->createDbTable();
            }
        } catch (\Exception $e) {
            if(!$this->config_debug){
                CAdminMessage::ShowMessage('Error');
            }else{
                CAdminMessage::ShowMessage($e->getMessage().":\n".$e->getTraceAsString());
            }
            return false;
        }
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    public function UnInstallDB() {
        try {
            foreach ([
                ProfileTable::getTableName(),
                ConstTable::getTableName(),
                ColumnTable::getTableName(),
                CustomFieldsTable::getTableName()
            ] as $tbl) {
                if (empty($tbl)) {
                    CAdminMessage::ShowMessage('Error');
                    return false;
                }
                if(!$this->isExistsTable($tbl)){
                    continue;
                }
                $this->connection->queryExecute('DROP TABLE `'.$tbl.'`;');
            }
        } catch (\Exception $e) {
            if(!$this->config_debug){
                CAdminMessage::ShowMessage('Error');
            }else{
                CAdminMessage::ShowMessage($e->getMessage().":\n".$e->getTraceAsString());
            }
            return false;
        }
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallEvents() {
        return true;
    }

    public function UnInstallEvents() {
        return true;
    }

    public function InstallFiles() {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/panel",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
//		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/include",
//          $_SERVER["DOCUMENT_ROOT"]."/bitrix/include", true, true);
        return true;
    }

    public function UnInstallFiles() {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/panel/main/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel/main");
        DeleteDirFilesEx("/bitrix/images/".$this->MODULE_ID);
//		DeleteDirFilesEx("/bitrix/include/".$this->MODULE_ID);

        $dbFile = CFile::GetList(array(), array('MODULE_ID' => $this->MODULE_ID));
        while($arDbFile = $dbFile->GetNext()){
            CFile::Delete($arDbFile['ID']);
        }

        return true;
    }

    private function isExistsTable($dbTableName) {
        $arConfig = $this->connection->getConfiguration();
        $ob = $this->connection->query('SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES`
            WHERE `TABLE_TYPE`="BASE TABLE" AND
                `TABLE_SCHEMA`="'.$this->sqlHelper->forSql($arConfig['database']).'" AND
                `TABLE_NAME`="'.$this->sqlHelper->forSql($dbTableName).'";');
        $ar = $ob->fetch();
        return (!empty($ar));
    }

}