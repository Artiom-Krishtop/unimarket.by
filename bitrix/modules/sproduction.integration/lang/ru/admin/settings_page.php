<?
$MESS["SP_CI_PAGE_SETTINGS_TITLE"] = "Настройки подключения";
$MESS["SP_CI_SETTINGS_CONNECT_TITLE"] = "Настройки подключения";
$MESS["SP_CI_SETTINGS_CONNECT_SUBTITLE"] = "Данные для подключения модуля к порталу";
$MESS["SP_CI_SETTINGS_CONNECT_SITE"] = "Адрес интернет-магазина";
$MESS["SP_CI_SETTINGS_CONNECT_SITE_HINT"] = "Без слеша в конце";
$MESS["SP_CI_SETTINGS_CONNECT_PORTAL"] = "Адрес портала";
$MESS["SP_CI_SETTINGS_CONNECT_PORTAL_HINT"] = "Без слеша в конце";
$MESS["SP_CI_SETTINGS_CONNECT_APP_LINK"] = "Ссылка для приложения";
$MESS["SP_CI_SETTINGS_CONNECT_APP_LINK_HINT"] = "Создайте приложение на портале и скопируйте в него эту ссылку";
$MESS["SP_CI_SETTINGS_CONNECT_APP_CREATE_LINK"] = "Создать приложение";
$MESS["SP_CI_SETTINGS_CONNECT_APP_ID"] = "Код приложения";
$MESS["SP_CI_SETTINGS_CONNECT_SECRET"] = "Ключ приложения";
$MESS["SP_CI_SETTINGS_SAVE"] = "Сохранить";
$MESS["SP_CI_SETTINGS_RESET_CONN"] = "Сбросить подключение";
$MESS["SP_CI_SETTINGS_RESET_CONN_HINT"] = "Параметры подключения, заданные выше, затронуты не будут";
$MESS["SP_CI_SETTINGS_RESET_CONN_WARNING"] = "Сбросить подключение к порталу?";
$MESS["SP_CI_SETTINGS_CONNECT_AUTH_LINK"] = "Перейдите по ссылке, чтобы модуль подключился к порталу:";
$MESS["SP_CI_SETTINGS_CONNECT_STATUS_LINK"] = "Чтобы проверить корректность настроек и работы системы, перейдите в раздел <a href=\"/bitrix/admin/sprod_integr_status.php?lang=ru\" target=\"_blank\">\"Состояние системы\"</a>";
$MESS["SP_CI_SETTINGS_INFO"] = "Информация";
$MESS["SP_CI_SETTINGS_CONNECT_INFO_TITLE"] = "Порядок настройки модуля";
$MESS["SP_CI_SETTINGS_CONNECT_INFO_TEXT"] = "<p>Шаг 1. Заполните поля \"Адрес интернет-магазина\" и \"Адрес портала\".</p>
    <p>Шаг 2. Скопируйте сгенерированную ссылку для приложения.</p>
    <p>Шаг 3. Перейдите в раздел Приложения / Разработчикам / Другое (https://АДРЕС_ПОРТАЛА/devops/section/standard/).</p>
    <p>Шаг 4. Выберите \"Локальное приложение\".</p>
    <p>Шаг 5. Заполните поля следующим образом:
        <ul>
            <li>Название приложения: \"Интеграция заказов\" (либо другое, удобное вам).</li>
            <li>Тип: Серверное</li>
            <li>\"Путь вашего обработчика\": Вставьте скопированный в модуле текст из поля \"Ссылка для приложения\".</li>
            <li>\"Использует только API\": Да.</li>
            <li>\"Настройка прав\": CRM, Пользователи, Встраивание приложений, Интранет.</li>
        </ul>
        Созданное приложение в дальнейшем будет доступно в разделе Приложения / Разработчикам / Интеграции (https://АДРЕС_ПОРТАЛА/devops/list/).
    </p>
    <p>Шаг 6. После сохранения вы получите \"Код приложения\" и \"Ключ приложения\". Скопируйте их в соответствующие поля в настройках модуля и нажмите \"Сохранить\".</p>
    <p>Шаг 7. Отобразится ссылка для подключения к порталу &ndash; перейдите по ней. Страница обновится и станут доступны другие блоки настроек.</p>
    <p>Шаг 8. В блоке \"Параметры синхронизации\" укажите направление синхронизации и, если необходимо, дату, начиная с которой заказы будут обрабатываться модулем.</p>
    <p>Шаг 9. Когда данные настройки произведены, можно активировать синхронизацию.</p>
    <p>Шаг 10. Чтобы загрузить на портал данные по уже существующим заказам, запустите ручную синхронизацию.</p>";
$MESS["SP_CI_SETTINGS_SYNC_TITLE"] = "Параметры синхронизации";
$MESS["SP_CI_SETTINGS_SYNC_SUBTITLE"] = "Общие настройки работы модуля";
$MESS["SP_CI_SETTINGS_SYNC_SOURCE_ID"] = "Код источника сделок";
$MESS["SP_CI_SETTINGS_SYNC_SOURCE_ID_TOOLTIP"] = "Цифры и латинские символы. Необязательное поле. Задаётся, чтобы модуль отличить заказы, выгруженные в CRM из данного сайта от заказов из других источников.";
$MESS["SP_CI_SETTINGS_SYNC_SOURCE_NAME"] = "Название для вкладок правки и добавления";
$MESS["SP_CI_SETTINGS_SYNC_SOURCE_NAME_TOOLTIP"] = "Необязательный символьный идентификатор сайта, чтобы отличить вкладку правки заказа данного сайта от вкладок правки заказов других сайтов.";
$MESS["SP_CI_SETTINGS_SYNC_DIRECTION"] = "Направление синхронизации";
$MESS["SP_CI_SETTINGS_SYNC_DIRECTION_STOC"] = "Только из интернет-магазина в CRM";
$MESS["SP_CI_SETTINGS_SYNC_DIRECTION_FULL"] = "Двусторонняя";
$MESS["SP_CI_SETTINGS_SYNC_START_DATE"] = "Дата начала синхронизации";
$MESS["SP_CI_SETTINGS_ACTIVE_LABEL"] = "Синхронизация активна";
$MESS["SP_CI_SETTINGS_PROFILES_WARNING"] = "Необходимо настроить и активировать хотя бы один <a href=\"/bitrix/admin/sprod_integr_profiles.php?lang=ru\" target=\"_parent\">профиль</a>";
$MESS["SP_CI_SETTINGS_ADD_SYNC_TITLE"] = "Дополнительная синхронизация";
$MESS["SP_CI_SETTINGS_ADD_SYNC_SUBTITLE"] = "Подстраховочный периодический запуск массовой синхронизации";
$MESS["SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_DISABLED"] = "Не запускать";
$MESS["SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_1H"] = "Запускать каждый час";
$MESS["SP_CI_SETTINGS_ADD_SYNC_SCHEDULE_1D"] = "Запускать каждый день";
$MESS["SP_CI_SETTINGS_ADD_SYNC_INFO_TITLE"] = "Дополнительная и ручная синхронизация";
$MESS["SP_CI_SETTINGS_ADD_SYNC_INFO_TEXT"] = "Включать дополнительную или запускать ручную синхронизацию не обязательно. Основной механизм синхронизации работает по событию: если изменится заказ, данные сразу перекинутся в сделку, и наоборот. Но, <strong>если на сайте или на портале произойдёт какой-либо временный сбой</strong>, и часть данных окажутся не синхронизированы, поможет дополнительная регулярная синхронизация, которая, с заданной периодичностью, будет сверять данные магазина и CRM. А ручная синхронизация полезна <strong>после первоначальной настройки модуля</strong> &mdash; чтобы синхронизировать уже существующие заказы.";
$MESS["SP_CI_SETTINGS_MAN_SYNC_TITLE"] = "Ручная синхронизация";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SUBTITLE"] = "Разовый запуск массовой синхронизации заказов";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD"] = "Обработать заказы, созданные";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_ALL"] = "За всё время";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1D"] = "За последние сутки";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1W"] = "За последние 7 дней";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_1M"] = "За последний месяц";
$MESS["SP_CI_SETTINGS_MAN_SYNC_SYNC_PERIOD_3M"] = "За последние три месяца";
$MESS["SP_CI_SETTINGS_MAN_SYNC_ONLY_NEW"] = "Выгружать только ещё не выгруженные в CRM заказы";
$MESS["SP_CI_SETTINGS_MAN_SYNC_RUN"] = "Запустить";
$MESS["SP_CI_SETTINGS_MAN_SYNC_TITLE"] = "Ручная синхронизация";
$MESS["SP_CI_SETTINGS_MAN_SYNC_TEXT"] = "<p>Ручная синхронизация запускает отправку данных из интернет-магазина в CRM за выбранный период (по дате создания заказа).</p>
<p>Ручная синхронизация учитывает поле \"Дата начала синхронизации\".</p>";
?>