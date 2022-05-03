<?php

spl_autoload_register('SpreadsheetAutoload');

//Автозагрузка классов PhpSpreadSheet
function SpreadsheetAutoload($class)
{
    // PhpOffice namespace prefix
    $prefix = 'PhpOffice\\';

    // Использует ли класс префикс?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    $base_dir = __DIR__ . '/';

    $relative_class = substr($class, $len);

    // Предполагаемый путь к файлу
    $file = $base_dir . '/' . str_replace('\\', '/', $relative_class) . '.php';

    // Если файл существует - подключаем
    if (file_exists($file)) {
        include_once $file;
    }

}