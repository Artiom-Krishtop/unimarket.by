<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'oplati.paysystem');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");

$tabControl = new CAdminTabControl(
  "tabControl",
  array(
    array(
      "DIV" => "edit1",
      "TAB" => 'Настройки',
      "TITLE" => 'Настройки',
    ),
  )
);

if (!empty($save) || !(empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
  if (!empty($restore)) {
    Option::delete(ADMIN_MODULE_NAME);
    CAdminMessage::showMessage(array(
      "MESSAGE" => 'Настройки по умолчанию восстановлены!',
      "TYPE" => "OK",
    ));
  } else {
    if ($request->getPost('payment_confirm_await_time') && intval($request->getPost('payment_confirm_await_time')) > 0) {
      Option::set(
        ADMIN_MODULE_NAME,
        "payment_confirm_await_time",
        $request->getPost('payment_confirm_await_time')
      );
    }
    if ($request->getPost('regnum') && !empty($request->getPost('regnum'))) {
      Option::set(
        ADMIN_MODULE_NAME,
        "regnum",
        $request->getPost('regnum')
      );
    }
    if ($request->getPost('password') && !empty($request->getPost('password'))) {
      Option::set(
        ADMIN_MODULE_NAME,
        "password",
        $request->getPost('password')
      );
    }
    if ($request->getPost('emails') && !empty($request->getPost('emails'))) {
      Option::set(
        ADMIN_MODULE_NAME,
        "emails",
        $request->getPost('emails')
      );
    }
    CAdminMessage::showMessage(array(
      "MESSAGE" => 'Настройки сохранены',
      "TYPE" => "OK",
    ));
  }
}

$tabControl->begin();
?>

<form method="post" action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
  <?php
  echo bitrix_sessid_post();
  $tabControl->beginNextTab();
  ?>
  <tr>
    <td width="50%">
      <label for="payment_confirm_await_time">Время ожидания подтверждения платежа (сек):</label>
    <td width="50%">
      <input type="number" name="payment_confirm_await_time" value="<?= Option::get(ADMIN_MODULE_NAME, 'payment_confirm_await_time') ?>" />
    </td>
  </tr>
  <tr>
    <td>
      <label for="regnum">Регистрационный номер кассы:</label>
    </td>
    <td>
      <input type="text" name="regnum" value="<?= Option::get(ADMIN_MODULE_NAME, 'regnum') ?>">
    </td>
  </tr>
  <tr>
    <td>
      <label for="password">Пароль:</label>
    </td>
    <td>
      <input type="text" name="password" value="<?= Option::get(ADMIN_MODULE_NAME, 'password') ?>">
    </td>
  </tr>
  <tr>
    <td>
      <label for="emails">Список почтовых ящиков для отправки уведомления об ошибке сверки с онлайн кассой (через запятую):</label>
    </td>
    <td>
      <input type="text" name="emails" value="<?= Option::get(ADMIN_MODULE_NAME, 'emails') ?>">
    </td>
  </tr>

  <?php
  $tabControl->buttons();
  ?>
  <input type="submit" name="save" value="<?= Loc::getMessage("MAIN_SAVE") ?>" title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save" />
  <input type="submit" name="restore" title="<?= Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>" onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')" value="<?= Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>" />
  <?php
  $tabControl->end();
  ?>
</form>
<?= BeginNote(); ?>
<p>Для синхронизации статусов платежей с платежной системой "Оплати!" необходимо установить скрипт oplati_sync.php из папки модуля на cron (каждые 2 минуты).</p>
<code>*/2 * * * * /usr/bin/php -f /home/bitrix/www/bitrix/modules/oplati.payment/oplati_sync.php > /dev/null/</code>
<p>Для сверки итогов по смене с платежной системой "Оплати!" необходимо установить скрипт oplati_sync.php из папки модуля на cron (каждый день в 23:59).</p>
<code>59 23 * * * /usr/bin/php -f /home/bitrix/www/bitrix/modules/oplati.payment/oplati_reconciliation.php > /dev/null/</code>
<?= EndNote(); ?>