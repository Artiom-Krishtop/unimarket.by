<?

/**
 * CControllerClient::GetInstalledOptions($module_id);
 * формат массива, элементы:
 * 1) ID опции (id инпута)(Берется с помощью COption::GetOptionString($module_id, $Option[0], $Option[2]) если есть)
 * 2) Отображаемое имя опции
 * 3) Значение по умолчанию (так же берется если первый элемент равен пустой строке), зависит от типа:
 *      checkbox - Y если выбран
 *      text/password - htmlspecialcharsbx($val)
 *      selectbox - одно из значений, указанных в массиве опций
 *      multiselectbox - значения через запятую, указанные в массиве опций
 * 4) Тип поля (массив)
 *      1) Тип (multiselectbox, textarea, statictext, statichtml, checkbox, text, password, selectbox)
 *      2) Зависит от типа:
 *         text/password - атрибут size
 *         textarea - атрибут rows
 *         selectbox/multiselectbox - массив опций формата ["Значение"=>"Название"]
 *      3) Зависит от типа:
 *         checkbox - доп атрибут для input (просто вставляется строкой в атрибуты input)
 *         textarea - атрибут cols
 *
 *      noautocomplete) для text/password, если true то атрибут autocomplete="new-password"
 *
 * 5) Disabled = 'Y' || 'N';
 * 6) $sup_text - ??? текст маленького красного примечания над названием опции
 * 7) $isChoiceSites - Нужно ли выбрать сайт??? флаг Y или N
 */


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

require_once $_SERVER['DOCUMENT_ROOT'] . '/telegramm/vendor/autoload.php';

use TelegramBot\Api\Client;
CModule::IncludeModule('iblock');

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

$arOptions = Option::getForModule($module_id, 'arOptions');
$token = trim($arOptions['BOT_TOKEN'], " ");
if (!empty($_REQUEST['BOT_TOKEN']) && $_REQUEST['BOT_TOKEN'] !== $token) {
    $bot = new Client($_REQUEST['BOT_TOKEN']);

    $bot->setWebhook(((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/telegramm/BrainBot.php", $_REQUEST['BOT_TOKEN']);
}
//For paid modules only BEGIN
$isExpired = CModule::IncludeModuleEx($module_id);
if ($isExpired != 4) {
//For paid modules only END    

    $aTabs = array(
        array(
            "DIV" => "edit",
            "TAB" => Loc::getMessage("BRAINFORCE_TIM_OPTIONS_TAB_COMMON"),
            "TITLE" => Loc::getMessage("BRAINFORCE_TIM_OPTIONS_TAB_NAME"),
            "OPTIONS" => array(
                Loc::getMessage("BRAINFORCE_TIM_OPTIONS_TAB_COMMON"),
                array(
                    "BOT_TOKEN",
                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_BOT_TOKEN"),
                    '',
                    array("text", 60)
                ),
                array(
                    "BOT_USERNAME",
                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_BOT_USERNAME"),
                    '',
                    array("text", 60)
                ),
//                array(
//                    "CATEGORY",
//                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_CATEGORY"),
//                    '',
//                    array("text", 20)
//                ),
                array(
                    "ID_MODERATOR",
                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_ID_MODERATOR"),
                    '',
                    array("text", 20)
                ),
                Loc::getMessage("BRAINFORCE_TIM_OPTIONS_SETTING_PRICE"),
                array(
                    "CODE_PRICE_ELEMENT",
                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_CODE_PRICE_ELEMENT"),
                    '',
                    array("text", 20)
                ),
                array(
                    "ID_GROUP_CATALOG",
                    Loc::getMessage("BRAINFORCE_TIM_OPTIONS_ID_GROUP_CATALOG"),
                    '',
                    array("text", 20)
                ),
            )
        )
    );

    if ($arOptions['IBLOCK_SELECT']) {
        $arProps = CIBlock::GetProperties($arOptions['IBLOCK_SELECT']);
        $props = [];
        while ($prop = $arProps->Fetch()) {
            $props[$prop['ID']] = $prop['NAME'];
        }

        $aTabs[0]['OPTIONS'][] = array(
            "IBLOCK_PROPS",
            Loc::getMessage("BRAINFORCE_TIM_OPTIONS_IBLOCK_PROPS"),
            '',
            array("multiselectbox", $props));
    }

    if ($request->isPost() && check_bitrix_sessid()) {

        foreach ($aTabs as $aTab) {

            foreach ($aTab["OPTIONS"] as $arOption) {

                if (!is_array($arOption)) {

                    continue;
                }

                if ($arOption["note"]) {

                    continue;
                }

                if ($request["apply"]) {

                    $optionValue = $request->getPost($arOption[0]);

                    if ($arOption[0] == "switch_on") {

                        if ($optionValue == "") {

                            $optionValue = "N";
                        }
                    }

                    Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
                } elseif ($request["default"]) {

                    Option::set($module_id, $arOption[0], $arOption[2]);
                }
            }
        }
        COption::SetOptionString($module_id, 'IBLOCK_TYPE_SELECT', $_POST['IBLOCK_TYPE_SELECT']);
        COption::SetOptionString($module_id, 'IBLOCK_SELECT', $_POST['IBLOCK_SELECT']);

        LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
    }


    $tabControl = new CAdminTabControl(
        "tabControl",
        $aTabs
    );

    $tabControl->Begin();
    ?>
    <form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>"
          method="POST" id="setparams">
        <?

        $IBLOCK_SELECT = COption::GetOptionString($module_id, 'IBLOCK_SELECT');

        foreach ($aTabs as $aTab) {

            if ($aTab["OPTIONS"]) {

                $tabControl->BeginNextTab();

                __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            }
        }?>
        <tr>
            <td><?=Loc::getMessage("BRAINFORCE_TIM_OPTIONS_IBLOCK")?></td>
            <td><? echo GetIBlockDropDownList($IBLOCK_SELECT, 'IBLOCK_TYPE_SELECT', 'IBLOCK_SELECT', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"'); ?></td>
        </tr>
        <?
        $tabControl->Buttons();
        ?>
        <input type="submit" name="apply" value="<? echo(Loc::GetMessage("BRAINFORCE_TIM_OPTIONS_APPLY")); ?>"
               class="adm-btn-save"/>
        <?
        echo(bitrix_sessid_post());
        ?>

    </form>
    <?
    $tabControl->End();
    ?>
    <? //For paid modules only BEGIN ?>
<? } else {
    echo Loc::GetMessage("BRAINFORCE_TIM_EXPIRED");
}
?>
    <script src="/telegramm/js/jquery-3.6.0.min.js"></script>
    <script src="/telegramm/js/custom.js"></script>
<? //For paid modules only END ?>