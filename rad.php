<?
//header('Content-Type: application/json');
//ini_set('display_errors',true);
//error_reporting(E_ALL);

//получаем данные
$data = file_get_contents('php://input');
$data = json_decode($data,1);
//var_dump($data);

function setError($str) {
    return array('error'=> $str);
}

function generate_string($strength = 16) {
    $random_string = '';
    for($i = 0; $i < $strength/100; $i++) {
        $random_string .= '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
    } 
    return $random_string;
}

function getPHPversion ($token) {
    return phpversion();
}

function checkMySqlConnection($token,$cms) {
    if ($cms = 'Bitrix') {
        include('bitrix/php_interface/dbconn.php');
    }
    $mysql = mysqli_connect($DBHost,$DBLogin,$DBPassword,$DBName);
    if ($mysql) {
        $res = 'OK';
    } else {
        $res = 'ERROR';
    }
    return $res;
}

function checkBitrixLicense() {
    include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $update = \Bitrix\Main\Config\Option::get("main", "update_system_update");
    $objDateTime = new Bitrix\Main\Type\DateTime($update);
    $update = $objDateTime->format("d.m.Y");
    return $update;
}

function checkEmail($token,$email) {
    $res = mail($email,'RAD test message','RAD test message OK');
    if ($res) {
        $result = 'OK';    
    } else {
        $result = 'ERROR';
    }    
    return $result;
}

function checkGzip() {
    if(ini_get("zlib.output_compression")) {
        $result = 'YES';  
    } else {
        $result = 'NO';
    }   
    return $result;
}

function checkSpace() {
    $filename = 'data-rad.txt';
    //пороги файлов
    //500Mb
    for ($i=0;$i<5;$i++) {
        if (file_put_contents($filename, generate_string(100000000),FILE_APPEND)) {
            $result = "OK";
        } else {
            $result = "ERROR";
        }
    }
    unlink($filename);
    return $result;
}

////////////////////////////// METHODS //////////////////////////////////////

if ($data['method'] == 'getPHPversion') {
    if ($data['token']) {
        $version = array('PHPversion'=>phpversion());
    } else {
        $version = setError('NO TOKEN');
    }
    echo json_encode($version);
}

if ($data['method'] == 'checkGzip') {
    if ($data['token']) {
        $version = array('isGZip'=>checkGzip());
    } else {
        $version = setError('NO TOKEN');
    }
    echo json_encode($version);
}

if ($data['method'] == 'checkMySqlConnection') {
    if ($data['token']) {
        $version = array('checkMySqlConnection'=>checkMySqlConnection($data['token'],$data['params']['cms']));
    } else {
        $version = setError('NO TOKEN');
    }
    echo json_encode($version);
}

if ($data['method'] == 'checkEmail') {
    if ($data['token']) {
        $isMail = array('CHECK_MAIL' => checkEmail($data['token'],$data['params']['email']));
    } else {
        $isMail = setError('NO TOKEN');
    }
    echo json_encode($isMail);
}

if ($data['method'] == 'checkBitrixLicense') {
    if ($data['token']) {
        $update = array('LastUpdated'=>checkBitrixLicense());
    } else {
        $update = setError('NO TOKEN');
    }
    echo json_encode($update);
}

if ($data['method'] == 'checkSpace') {
    if ($data['token']) {
        $isSpace = array('CHECK_SPACE' => checkSpace());
    } else {
        $isSpace = setError('NO TOKEN');
    }
    echo json_encode($isSpace);
}