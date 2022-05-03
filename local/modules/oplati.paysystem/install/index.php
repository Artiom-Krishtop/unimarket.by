<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Mail\Internal\EventTypeTable;

class oplati_paysystem extends CModule
{
  public $MODULE_ID;
  public $MODULE_NAME;
  public $MODULE_DESCRIPTION;
  public $PARTNER_NAME;
  public $PARTNER_URI;
  var $MODULE_VERSION;
  var $MODULE_VERSION_DATE;

  function __construct()
  {
    include(__DIR__ . '/version.php');
    $this->MODULE_VERSION = $arModuleVersion['VERSION'];
    $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
  
    $this->MODULE_ID = "oplati.paysystem";
    $this->MODULE_NAME = "Оплати!";
    $this->MODULE_DESCRIPTION = "Добавляет платежную систему \"Оплати!\"";
    $this->PARTNER_NAME = 'oplati';
    $this->PARTNER_URI = 'https://www.o-plati.by/';
  }

  function doInstall()
  {
    ModuleManager::registerModule($this->MODULE_ID);

    $app = Application::getInstance();
    $documentRoot = $app->getContext()->getServer()->getDocumentRoot();

    CopyDirFiles(__DIR__ . '/../oplati', $documentRoot . "/local/php_interface/include/sale_payment/oplati", true, true);

    $mailEvent = array(
      'LID' => SITE_ID,
      'EVENT_NAME' => 'OPLATI_RECONCILIATION_FAIL',
      'NAME' => 'Ошибка при сверке',
      'DESCRIPTION' => 'Ошибка при сверке',
      'EVENT_TYPE' => 'email'
    );

    $mailEventExists = EventTypeTable::getList(array(
      'select' => array('*'),
      'filter' => array('EVENT_NAME' => $mailEvent['EVENT_NAME']),
      'limit' => 1
    ))->fetch();

    if (!$mailEventExists) {
      $addResult = EventTypeTable::add($mailEvent);

      print_r($addResult->isSuccess());
    }
  }

  function doUninstall()
  {
    ModuleManager::unRegisterModule($this->MODULE_ID);

    $app = Application::getInstance();
    $documentRoot = $app->getContext()->getServer()->getDocumentRoot();

    Directory::deleteDirectory($documentRoot . '/local/php_interface/include/sale_payment/oplati');
  }
}
