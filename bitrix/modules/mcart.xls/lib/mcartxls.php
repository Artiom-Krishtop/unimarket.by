<?php

namespace Mcart\Xls;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CAdminMessage;
use Exception;

Loc::loadMessages(__FILE__);

final class McartXls {
    const ERROR_REQUIREMENTS = 'REQUIREMENTS';
    private static $instance;
    private $arRequirements = [];
    private $isRequirementsValid;
    private $obErrorCollection;
    private $config_debug = false;

    public function __construct() {}

    public static function getModuleID() {
        return 'mcart.xls';
    }

    public function isDebug() {
        return $this->config_debug;
    }

    public static function checkAccess($level = 'W', $showAuthForm = true) {
        global $APPLICATION;
        $RIGHT = $APPLICATION->GetGroupRight(self::getModuleID());
        if ($RIGHT < $level) {
            if ($showAuthForm) {
                $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
            }
            return false;
        }
        return $RIGHT;
    }

    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new McartXls();
            static::$instance->init();
        }
        return static::$instance;
    }

    private function init() {
        $this->obErrorCollection = new ErrorCollection();
        $this->arRequirements['php_version'] = [
            'NAME' => 'PHP >= 5.6.0',
            'VALUE' => version_compare(PHP_VERSION, '5.6.0', '>='),
            'isRequired' => true
        ];
        $this->arRequirements['xml'] = [
            'NAME' => 'PHP extension XML',
            'VALUE' => extension_loaded('xml'),
            'isRequired' => true
        ];
        $this->arRequirements['xmlwriter'] = [
            'NAME' => 'PHP extension XMLWriter',
            'VALUE' => extension_loaded('xmlwriter'),
            'isRequired' => false
        ];
        $this->arRequirements['xmlreader'] = [
            'NAME' => 'PHP extension XMLReader',
            'VALUE' => extension_loaded('xmlreader'),
            'isRequired' => false
        ];
        $this->arRequirements['mbstring'] = [
            'NAME' => 'PHP extension mbstring',
            'VALUE' => extension_loaded('mbstring'),
            'isRequired' => true
        ];
        $this->arRequirements['zip'] = [
            'NAME' => 'PHP extension Zip',
            'VALUE' => extension_loaded('zip'),
            'isRequired' => true
        ];
        $this->arRequirements['gd'] = [
            'NAME' => 'PHP extension GD',
            'VALUE' => extension_loaded('gd'),
            'isRequired' => false
        ];
        $this->arRequirements['dom'] = [
            'NAME' => 'PHP extension DOM',
            'VALUE' => extension_loaded('dom'),
            'isRequired' => false
        ];
        $this->arRequirements['memcache'] = [
            'NAME' => 'PHP extension Memcache',
            'VALUE' => extension_loaded('memcache'),
            'isRequired' => false
        ];
        $this->arRequirements['bitrix_module_fileman'] = [
            'NAME' => 'Bitrix module Fileman',
            'VALUE' => Loader::includeModule('fileman'),
            'isRequired' => true
        ];
        $this->arRequirements['bitrix_module_iblock'] = [
            'NAME' => 'Bitrix module IBlock',
            'VALUE' => Loader::includeModule('iblock'),
            'isRequired' => true
        ];
        $this->arRequirements['bitrix_module_catalog'] = [
            'NAME' => 'Bitrix module Catalog',
            'VALUE' => Loader::includeModule('catalog'),
            'isRequired' => false
        ];
        $this->arRequirements['bitrix_module_search'] = [
            'NAME' => 'Bitrix module Search',
            'VALUE' => Loader::includeModule('search'),
            'isRequired' => false
        ];
        $arConfig = Configuration::getValue('exception_handling');
        $this->config_debug = $arConfig['debug'];
    }

    public function isRequirementsValid() {
        if (!is_null($this->isRequirementsValid)) {
            return $this->isRequirementsValid;
        }
        foreach ($this->arRequirements as $ar) {
            if ($ar['isRequired'] && !$ar['VALUE']) {
                $this->isRequirementsValid = false;
                return $this->isRequirementsValid;
            }
        }
        $this->isRequirementsValid = true;
        return $this->isRequirementsValid;
    }

    public function isExtensionLoaded($key) {
        return $this->arRequirements[strip_tags($key)]['VALUE'];
    }

    public function getRequirementsList() {
        return $this->arRequirements;
    }

    public function checkRequirements() {
        if ($this->isRequirementsValid()) {
            return true;
        }
        $requirements = '';
        $isFirst = true;
        foreach ($this->arRequirements as $ar) {
            if (!$ar['isRequired'] || $ar['VALUE']) {
                continue;
            }
            if ($isFirst) {
                $isFirst = false;
            } else {
                $requirements .= ', ';
            }
            $requirements .= $ar['NAME'];
        }
        $this->addError(
            Loc::getMessage("MCART_XLS_REQUIREMENTS_ERROR", ['#MODULE_ID#' => self::getModuleID(), '#REQUIREMENTS#' => $requirements]),
            self::ERROR_REQUIREMENTS);
        return false;
    }

    public function addError($message, $code = 0) {
        return $this->obErrorCollection->add([new Error($message, $code)]);
    }

    public function addErrors(array $errors) {
        return $this->obErrorCollection->add($errors);
    }

	public function hasErrors() {
		return (bool)count($this->obErrorCollection);
	}

    public function getErrors() {
        return $this->obErrorCollection->toArray();
    }

    public function showErrors() {
        if ($this->hasErrors()) {
            foreach ($this->getErrors() as $obError) {
                CAdminMessage::ShowMessage('['.$obError->getCode().'] '.$obError->getMessage());
            }
        }
    }

    /**
     * @param Exception|Error|Throwable $e
     * @param string $message
     * @param bool $addTraceToDebugMessage
     * @return string
     */
    public function getErrorMessage($e, $message, $addTraceToDebugMessage = true) {
        $message = (string)$message;
        if(!is_object($e)){
            if ($message == '') {
                $message = 'Error';
            }
            return $message;
        }
        if(!$this->isDebug()){
            if ($message == '') {
                $message = $e->getMessage();
            }
            return $message;
        }
        if ($message == '' || $message = 'Error') {
            $message = $e->getMessage();
        }else{
            $message .= "\n".$e->getMessage();
        }
        if(intval($addTraceToDebugMessage)){
            $message .= ":<pre>\n".$e->getTraceAsString().'</pre>';
        }
        return $message;
    }

}
