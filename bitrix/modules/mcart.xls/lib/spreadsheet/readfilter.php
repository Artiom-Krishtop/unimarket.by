<?php

namespace Mcart\Xls\Spreadsheet;

use Bitrix\Main\Localization\Loc;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

Loc::loadMessages(__FILE__);

final class ReadFilter implements IReadFilter{
    private $startRow   = 1;
    private $stopRow    = 10;
    private $endRow;
    private $headerRow;
    private $columns;
    private $lastRow;

    public function __construct($startRow = 0, $limit = 10, $headerRow = null, $endRow = null, $columns = null) {
        $this->startRow = intval($startRow);
        if($this->startRow <= 0){
            $this->startRow = 1;
        }
        $this->stopRow = $this->startRow+intval($limit)-1;
        if($endRow > 0 && $this->stopRow > $endRow){
            $this->stopRow = $endRow;
        }
        $this->headerRow = intval($headerRow);
        if(!is_array($columns)){
            $columns = null;
        }
        $this->endRow = $endRow;
        $this->columns = $columns;
    }

    public function readCell($column, $row, $worksheetName = '') {
        //  Only read the rows and columns that were configured
        if(($this->headerRow && $this->headerRow == $row) || ($row >= $this->startRow && $row <= $this->stopRow))  {
            if (!$this->columns || in_array($column,$this->columns)) {
                return true;
            }
        }
        $this->lastRow = $row;
        return false;
    }

    public function isContinue($row) {
        return ($row < $this->startRow && ((!$this->headerRow || $this->headerRow != $row)));
    }

    public function isComplete() {
        return (($this->endRow && $this->stopRow >= $this->endRow) || $this->stopRow >= $this->lastRow);
    }

    public function getProperties() {
        return get_object_vars($this);
    }

}