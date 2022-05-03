<?php

namespace Mcart\Xls\Spreadsheet;

use Bitrix\Main\Localization\Loc;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use Mcart\Xls\McartXls;
use CFile;
use Exception;
use Memcache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use function ConvertTimeStamp;

Loc::loadMessages(__FILE__);

final class Reader {

    const ERROR_PREF = 'Error spreadsheet reader';
    const ERROR_CODE_PREF = 'SPREADSHEET_READER';

    /**
     * @var Mcart\Xls\Spreadsheet\ReadFilter
     */
    public $obReadFilter;

    /**
     * @var PhpOffice\PhpSpreadsheet\Reader\IReader
     */
    public $obReader;

    /**
     * @var PhpOffice\PhpSpreadsheet\Worksheet
     */
    public $obWorksheet;

    private $obMcartXls;
    private $arFile;

    public function __construct($file, $readDataOnly = true) {
        $this->obMcartXls = McartXls::getInstance();
        if (is_array($file)) {
            $this->arFile = $file;
        } else {
            $this->arFile = CFile::MakeFileArray($file);
        }
        if (!$this->arFile) {
            $this->obMcartXls->addError(self::ERROR_PREF.'. File not found.', self::ERROR_CODE_PREF.'#01');
            return;
        }
        try {
            $this->setSettings();
            $inputFileType = IOFactory::identify($this->arFile['tmp_name']);
            $this->obReader = IOFactory::createReader($inputFileType);
            if(intval($readDataOnly)){
                $this->obReader->setReadDataOnly(true);
            }
            $worksheetNames = $this->obReader->listWorksheetNames($this->arFile['tmp_name']);
            $this->obReader->setLoadSheetsOnly($worksheetNames[0]);
        } catch (Exception $e) {
            $this->addError($e, self::ERROR_PREF, self::ERROR_CODE_PREF.'#02');
            return;
        }
    }

    public function getPathToFile() {
        return $this->arFile['tmp_name'];
    }

    public function getFile() {
        return $this->arFile;
    }

    public function getWorksheet($startRow = 0, $limit = 10, $headerRow = null, $endRow = null) {
        if ($this->obMcartXls->hasErrors()) {
            return false;
        }
        try {
            $this->obReadFilter = new ReadFilter($startRow, $limit, $headerRow, $endRow);
            $this->obReader->setReadFilter($this->obReadFilter);
            $spreadsheet = $this->obReader->load($this->arFile['tmp_name']);
            $this->obWorksheet = $spreadsheet->getWorksheetIterator()->current();
        } catch (Exception $e) {
            $this->addError($e, self::ERROR_PREF, self::ERROR_CODE_PREF.'#03');
            return false;
        }
        return $this->obWorksheet;
    }

    public function read($startRow = 0, $limit = 10, $headerRow = null, $endRow = null) {
        if (!$this->getWorksheet($startRow, $limit, $headerRow, $endRow) || $this->obMcartXls->hasErrors()) {
            return false;
        }
        $arr = [];
        foreach ($this->obWorksheet->getRowIterator() as $row) {
            $rowI = $row->getRowIndex();
            if ($this->obReadFilter->isContinue($rowI)) {
                continue;
            }
            $ar = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
            foreach ($cellIterator as $cell) {
                if ($cell === null) {
                    $ar[] = ['value' => ''];
                    continue;
                }
                $arCell = [
                    'value' => $cell->getCalculatedValue(),
                    'coordinate' => $cell->getCoordinate(),
                    'column' => $cell->getColumn(),
                ];
                $arCell['value_format'] = $arCell['value'];
                if ($cell->hasHyperlink()) {
                    $arCell['isHyperlink'] = true;
                    $arCell['value_format'] = $cell->getHyperlink()->getUrl();
                }
                $arCell['isDateTime'] = Date::isDateTime($cell);
                if ($arCell['isDateTime']) {
                    $arCell['timestamp'] = Date::excelToTimestamp($arCell['value']);
                    $arCell['value_format'] = ConvertTimeStamp($arCell['timestamp'], "FULL");
                }
                $arCell['value_format'] = str_replace(["\r", "\n"], '', $arCell['value_format']);
                $ar[] = $arCell;
            }
            $arr[$rowI] = $ar;
        }
        return $arr;
    }

    private function setSettings() {
        if ($this->obMcartXls->isExtensionLoaded('memcache')) {
            $client = new Memcache();
            if ($client->connect('localhost', 11211)) {
                $pool = new MemcacheCachePool($client);
                $simpleCache = new SimpleCacheBridge($pool);
                Settings::setCache($simpleCache);
            }
        }
        $options = LIBXML_DTDATTR;
        if (defined('LIBXML_DTDLOAD')) {
            $options |= LIBXML_DTDLOAD;
        }
        if (defined('LIBXML_COMPACT')) {
            $options |= LIBXML_COMPACT;
        }
        if (defined('LIBXML_BIGLINES')) {
            $options |= LIBXML_BIGLINES;
        }
        if (defined('LIBXML_PARSEHUGE')) {
            $options |= LIBXML_PARSEHUGE;
        }
        if (defined('LIBXML_HTML_NOIMPLIED')) {
            $options |= LIBXML_HTML_NOIMPLIED;
        }
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $options |= LIBXML_HTML_NODEFDTD;
        }
        if (defined('LIBXML_NOBLANKS')) {
            $options |= LIBXML_NOBLANKS;
        }
        Settings::setLibXmlLoaderOptions($options);
        Date::setDefaultTimezone(date_default_timezone_get());
    }

    private function addError($e, $message) {
        $message = (string)$message;
        if(!$this->obMcartXls->isDebug()){
            if ($message == '') {
                $message = 'Error';
            }
            return $this->obMcartXls->addError($message);
        }
        if ($message == '' || $message = 'Error') {
            $message = $e->getMessage();
        }else{
            $message .= "\n".$e->getMessage();
        }
        $message .= ":\n".$e->getTraceAsString();
        return $this->obMcartXls->addError($message);
    }

}
