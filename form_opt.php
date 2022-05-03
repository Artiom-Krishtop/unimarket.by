<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_POST) {
  $arEventFields = array(
    "RS_DATE_CREATE" => date("d.m.Y G:i"),
    "name" => htmlspecialchars($_POST["name"]),
    "phone" => htmlspecialchars($_POST["phone"]),
    "email" => htmlspecialchars($_POST["email"]),
    "namecompany" => htmlspecialchars($_POST["namecompany"]),
    "message" => htmlspecialchars($_POST["message"])
  );
  CEvent::Send("OPT_FORM", "s1", $arEventFields);
  CEvent::CheckEvents();
  echo 'ok';
} else {
    echo 'error';
}
?>
