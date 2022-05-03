<?php

namespace ISYS\BXE;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helpers
{
    static $arBXE_Errors;

    public static function AllNeededPHPExtensionsAvailableCheck()
    {
        if (!class_exists('ZipArchive') || !class_exists('XMLWriter')) {

            static::$arBXE_Errors = Array();

            if (!class_exists('ZipArchive')) {
                static::$arBXE_Errors[] = Loc::getMessage('ISYS_BXE_ZIPARCHIVE_NOT_AVAILABLE');
            }
            if (!class_exists('XMLWriter')) {
                static::$arBXE_Errors[] = Loc::getMessage('ISYS_BXE_XML_WRITER_NOT_AVAILABLE');
            }

            return false;
        }

        return true;
    }

    public static function GetErrors(){
        return static::$arBXE_Errors;
    }

    /**
     * ������� �� Y � N � �� � ���
     *
     * @param $value (Y\N)
     * @return string (��\���)
     */
    public static function FromYToYes($value)
    {
        if ($value == 'Y') {
            $result = Loc::getMessage('ISYS_BXE_Y');
        } else {
            $result = Loc::getMessage('ISYS_BXE_N');
        }

        return $result;
    }

    public static function ExportCurrentAdminPageToExcel($obList)
    {
        //��������� ������ � �����������
        //TODO: ��������� ������� � ������ �������
        $xlsHeaders = array();
        $activeColumns = array();
        foreach ($obList->aVisibleHeaders as $key => $row) {
            array_push($xlsHeaders, $row['content']);
            $activeColumns[] = $key;
        }

        $serverName = $_SERVER['HTTP_HOST'];
        $iPos = strrpos($serverName, ':');
        if ($iPos !== false) {
            $serverName = substr($serverName, 0, $iPos);
        }
        $serverHost = 'http://' . $serverName;

        //��������� ������ � �������
        $xlsRows = array();
        foreach ($obList->aRows as $row) {
            $xlsRow = array();
            foreach ($activeColumns as $columnKey) {
                //�������� ������� ����
                $rawCell = $row->aFields[$columnKey];
                $xlsCell = html_entity_decode(strip_tags($rawCell['view']['value']));
                if (empty($xlsCell)) {
                    $xlsCell = $row->arRes[$columnKey];
                }

                switch ($rawCell['view']['type']) {
                    case 'checkbox':
                        {
                            $xlsCell = Helpers::FromYToYes($xlsCell);
                        }
                        break;
                    case 'file':
                        {
                            //TODO: �������� ��� ������ �� ������
                            $xlsCell = $serverHost . \CFile::GetPath($row->arRes[$columnKey]);
                        };
                        break;
                    default:
                        {
                        };
                        break;
                }
                array_push($xlsRow, $xlsCell);
            }
            array_push($xlsRows, $xlsRow);
        }

        //����� �������� ������ ��� �������� �������
        $xlsData = array_merge(array($xlsHeaders), $xlsRows);
        //���������� ����� � ���� � ����������� ������ �� ������
        $bxf = new BitrixExcelFile();
        $bxf->WriteAndDownload($xlsData);
    }

    public static function isHyperlink($strValue)
    {
        $specPos = strpos($strValue, '://');

        if ($specPos === 4 || $specPos === 5) {
            return true;
        }

        return false;
    }

}