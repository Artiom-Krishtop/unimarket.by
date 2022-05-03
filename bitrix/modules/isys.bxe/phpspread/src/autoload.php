<?php

spl_autoload_register('SpreadsheetAutoload');

//������������ ������� PhpSpreadSheet
function SpreadsheetAutoload($class)
{
    // PhpOffice namespace prefix
    $prefix = 'PhpOffice\\';

    // ���������� �� ����� �������?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    $base_dir = __DIR__ . '/';

    $relative_class = substr($class, $len);

    // �������������� ���� � �����
    $file = $base_dir . '/' . str_replace('\\', '/', $relative_class) . '.php';

    // ���� ���� ���������� - ����������
    if (file_exists($file)) {
        include_once $file;
    }

}