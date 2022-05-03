<?php

namespace ISYS\BXE;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

use PhpOffice\PhpSpreadsheet\Cell;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Helper;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet;

Loc::loadMessages(__FILE__);

final class BitrixExcelFile
{
    public $helper = '';
    public $spreadsheet = '';
    public $source = '';

    // Конструктор класса, открывающий файл для работы
    public function __construct()
    {

        $this->helper = new Helper\Sample();
        $this->spreadsheet = new Spreadsheet();
    }

    // Построчное чтение/запись
    public function WriteAndDownload($source)
    {
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $currentTime = new \DateTime();

        //$filename = $this->helper->getTemporaryFilename(); //Генерируем случайное имя файла
        $filename = $request->get('type') . '_' . $request->get('IBLOCK_ID') . '_' . $currentTime->format('y-d-m_h-i-s') . '.xlsx';

        //Преобразуем объект
        $sheet = $this->spreadsheet->getActiveSheet();
        //Переносим значения в файл построчно, чтобы не потреблять много памяти
        $this->WriteMultipleRows($sheet, $source);
        //Доп. свойства файла
        $this->spreadsheet->getProperties()
            ->setCreator('BetterXlsxExport')
            ->setLastModifiedBy('BetterXlsxExport')
            ->setTitle('BetterXlsxExport Document')
            ->setSubject('BetterXlsxExport Document')
            ->setDescription('BetterXlsxExport Document')
            ->setKeywords('BetterXlsxExport Document Xlsx Bitrix');


        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $saveSuccess = false;
        $xlsxContent = '';

        try {
            ob_start();
            $writer->save('php://output');
            $xlsxContent = ob_get_contents();
            ob_end_clean();
            $saveSuccess = true;

        } catch (\Exception $exception) {
            $saveSuccess = false;
        }


        if (!$saveSuccess) {
            //Альтернативный метод с использованием файла
            try {
                $moduleRoot = realpath(dirname(__FILE__) . '/../');
                $tmpfilename = tempnam($moduleRoot, "xlsx_tmp_");

                $writer->save($tmpfilename);

                $handle = fopen($tmpfilename, "r");
                $xlsxContent = fread($handle, filesize($tmpfilename));
                fclose($handle);
                $saveSuccess = true;
                unlink($tmpfilename);
            } catch (\Exception $exception) {
                $saveSuccess = false;
            }
        }

        if ($saveSuccess) {
            //Очищаем буфер вывода, чтобы в файл не попал HTML страницы
            global $APPLICATION;
            $APPLICATION->RestartBuffer();

            // Redirect output to a client’s web browser (Xls)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            echo $xlsxContent;

            exit;
        } else {
            ShowError(Loc::getMessage('ISYS_BXE_EXCEL_EXPORT_ERROR'));
        }
    }

    function WriteMultipleRows(Worksheet $sheet, $source)
    {
        $startColumn = 0;
        $CurrentRow = 1;

        foreach ($source as $rowData) {
            $currentColumn = $startColumn;
            foreach ($rowData as $cellValue) {

                if (LANG_CHARSET === 'windows-1251') {
                    $cellValue = iconv("Windows-1251", 'UTF-8', $cellValue);
                }

                if (!empty($cellValue)) {
                    $cell = $sheet->getCellByColumnAndRow($currentColumn, $CurrentRow);
                    $cell->setValue($cellValue);


                    if (Helpers::isHyperlink($cellValue)) {
                        $cell->getHyperlink()->setUrl($cellValue);

                        $styleArray = array(
                            'font' => array(
                                'color' => Array('rgb' => '0000FF'),
                                'underline' => Font::UNDERLINE_SINGLE,
                            )
                        );

                        $sheet->getStyle($cell->getCoordinate())->applyFromArray($styleArray);

                    }
                }

                ++$currentColumn;
            }
            ++$CurrentRow;
        }
    }
}