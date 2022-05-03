<?php
/**
 * Created by Artmix.
 * User: Oleg Maksimenko <oleg.39style@gmail.com>
 * Date: 19.02.2016
 */

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

$formsParams = array(
    'TEXTAREA_FIELDS' => array(
        'USER_DESCRIPTION',
    ),
    'PHONE_FIELDS' => array(
        'PHONE',
    ),
    'PHONE_MASK_FIELDS' => array(
        'PHONE',
    ),
    'EMAIL_FIELDS' => array(
        'EMAIL',
    ),
);

?><!--noindex-->
<div id="aboc-catalog-buyoneclick-wrapper" style="display: none">
    <div class="ax-buyoneclick-inner">
        <h4 class="ax-buyoneclick-form-title">Купить в один клик</h4>
        <form name="aboc-form" class="ax-buyoneclick-form js-ax-buyoneclick-form" method="post">
            <?php echo bitrix_sessid_post() ?>
            <div class="ax-buyoneclick-form-msg-error js-ax-buyoneclick-form-msg-error"></div>
            <div class="ax-buyoneclick-form-msg-success js-ax-buyoneclick-form-msg-success"></div>
            <div class="form-field-wrap">
                <?php
                foreach ($arParams['FIELDS'] as $fieldName) {

                    $dataRequiredParam = in_array($fieldName, $arParams['REQUIRED_FIELDS']) ? ' data-required="1"' : '';

                    ?><p><label class="ax-buyoneclick-label-name" for="aboc-<?php echo ToLower($fieldName) ?>">
                        <?php echo Loc::getMessage('ACBOC_FORM_' . $fieldName) ?>:<?php
                            if (in_array($fieldName, $arParams['REQUIRED_FIELDS'])) {
                                ?> <span class="mark_required_field">*</span><?php
                            }
                        ?></label><?php

                        // TextArea
                        if (in_array($fieldName, $formsParams['TEXTAREA_FIELDS'])) {
                            ?><textarea
                                class="ax-buyoneclick-textarea-s"
                                rows="4"
                                name="<?php echo $fieldName ?>"
                                id="aboc-<?php echo ToLower($fieldName) ?>"
                                data-field-name="<?php echo Loc::getMessage('ACBOC_FORM_' . $fieldName) ?>"<?=$dataRequiredParam ?>
                            ></textarea><?php

                        // Phones fields
                        } else if (in_array($fieldName, $formsParams['PHONE_FIELDS'])) {
                            ?><input
                                class="ax-buyoneclick-input-s"
                                type="tel"
                                autocorrect="off"
                                autocomplete="tel"
                                name="<?php echo $fieldName ?>"
                                id="aboc-<?php echo ToLower($fieldName) ?>"
                                data-field-name="<?php echo Loc::getMessage('ACBOC_FORM_' . $fieldName) ?>"<?=$dataRequiredParam ?>
                            ><?php

                        // Email fields
                        } else if (in_array($fieldName, $formsParams['EMAIL_FIELDS'])) {
                            ?><input
                                class="ax-buyoneclick-input-s"
                                type="email"
                                autocapitalize="off"
                                autocorrect="off"
                                autocomplete="email"
                                name="<?php echo $fieldName ?>"
                                id="aboc-<?php echo ToLower($fieldName) ?>"
                                data-field-name="<?php echo Loc::getMessage('ACBOC_FORM_' . $fieldName) ?>"
                                data-field-type="email"<?=$dataRequiredParam ?>
                            ><?php

                        // Simple inputs
                        } else {
                            ?><input
                                class="ax-buyoneclick-input-s"
                                type="text"
                                name="<?php echo $fieldName ?>"
                                id="aboc-<?php echo ToLower($fieldName) ?>"
                                data-field-name="<?php echo Loc::getMessage('ACBOC_FORM_' . $fieldName) ?>"<?=$dataRequiredParam ?>
                            ><?php
                        }

                    ?>

                    </p><?php

                } ?>
                <?php if ($arParams['SHOW_USER_AGREE_BLOCK']) { ?>
                    <div class="ax-licence-block">
                        <input name="USER_AGREE" type="hidden" value="N" />
                        <input class="ax-licence-block-input js-ax-buyoneclick-form-user-agree-checkbox" id="aboc-user-agree" name="USER_AGREE" type="checkbox" value="Y" />
                        <label class="ax-buyoneclick-label-name" for="aboc-user-agree"><?php
                            echo (
                                isset($arParams['~USER_AGREE_LABEL_TEXT']) && strlen(trim($arParams['~USER_AGREE_LABEL_TEXT']))
                                    ? $arParams['~USER_AGREE_LABEL_TEXT']
                                    : Loc::getMessage('ACBOC_FORM_USER_AGREE_LABEL_TEXT')
                            )
                        ?></label>
                    </div>
                    <p class="ax-buyoneclick-text"><?php
                        echo str_replace(
                                '#USER_AGREE_LINK#',
                                (
                                    isset($arParams['USER_AGREE_LINK']) && strlen(trim($arParams['USER_AGREE_LINK']))
                                        ? trim($arParams['USER_AGREE_LINK'])
                                        : '#'
                                ),
                            isset($arParams['~USER_AGREE_TEXT']) && strlen(trim($arParams['~USER_AGREE_TEXT']))
                                ? $arParams['~USER_AGREE_TEXT']
                                : Loc::getMessage('ACBOC_FORM_USER_AGREE_TEXT')
                            )
                    ?></p>
                <?php } ?>

                <?php $captchaBlockId = sprintf('ax-buyoneclick-captcha-%s', $component->randString()); ?>

                <div id="<?php echo $captchaBlockId ?>"><?php

                $frame = $this->createFrame($captchaBlockId, false)->begin();

                if (!$USER->IsAuthorized() && $arResult['USE_CAPTCHA_USER_REGISTRATION']) {
                    ?><p>
                        <label class="ax-buyoneclick-label-name" for="aboc-captcha"><?php
                            echo Loc::getMessage('ACBOC_FORM_CAPTCHA')
                        ?> <span class="mark_required_field">*</span>
                        </label>
                        <div class="ax-buyoneclick-captcha-block">
                            <input
                                type="hidden"
                                name="captcha_sid"
                                class="js-ax-buyoneclick-form-captcha-sid"
                                value="<?php echo $arResult['CAPTCHA_CODE']?>"
                            />
				            <img
				                src="/bitrix/tools/captcha.php?captcha_sid=<?php echo $arResult['CAPTCHA_CODE']?>"
				                class="ax-buyoneclick-form-captcha-img js-ax-buyoneclick-form-captcha-img"
				                width="180"
				                height="40"
				                alt="CAPTCHA"
				                title="<?php echo Loc::getMessage('ACBOC_FORM_CAPTCHA_RELOAD_MESSAGE') ?>"
				                onclick="!!window.axBuyOnClick && BX.delegate(window.axBuyOnClick.reloadCaptchaCode(event), window.axBuyOnClick);"
				            />
                        </div>
                        <input
                            class="ax-buyoneclick-input-s"
                            type="text"
                            name="captcha_word"
                            id="aboc-captcha"
                            data-field-name="<?php echo Loc::getMessage('ACBOC_FORM_CAPTCHA_SMALL') ?>"
                            data-required="1"
                        >
                    </p><?php
                }

                $frame
                    ->beginStub()
                    ->end();

                ?>

                </div>

                <button
                        type="button"
                        name="aboc-submit"
                        class="ax-buyoneclick-form-submit js-ax-buyoneclick-form-submit<?php
                        if ($arParams['SHOW_USER_AGREE_BLOCK']) {
                            echo ' ax-disabled';
                        }
                        ?>">
                    <span><?php echo Loc::getMessage('ACBOC_FORM_BUTTON') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--/noindex-->

<script>
    <?php if ($arParams['INCLUDE_PRIMARY_JS']) { ?>

        (function () {
            if (!!window.axBuyOnClick) {

                axBuyOnClick.setConfig(<?php
                    echo CUtil::PhpToJSObject(
                        array(
                            'requiredFields' => $arParams['REQUIRED_FIELDS'],
                            'productId' => (int) $arParams['PRODUCT_ID'],
                            'windowType' => is_string($arParams['WINDOW_TYPE'])
                                ? (string) $arParams['WINDOW_TYPE']
                                : (bool) $arParams['WINDOW_TYPE'],
                            'windowContentSelector' => '#aboc-catalog-buyoneclick-wrapper',
                            'scrollIntoViewButton' => true,
                            'focusFirstRequiredField' => true,
                            'showUserAgreeBlock' => (bool) $arParams['SHOW_USER_AGREE_BLOCK'],
                            'tplErrorRow' => Loc::getMessage('ACBOC_JS_ERROR_ROW_TPL'),
                            'tplErrorEmailRow' => Loc::getMessage('ACBOC_JS_ERROR_EMAIL_ROW_TPL'),
                            'tplErrorOrderRow' => Loc::getMessage('ACBOC_JS_ERROR_ORDER_ROW_TPL'),
                            'tplSuccessMessage' => isset($arParams['~SUCCESS_MESSAGE']) && strlen(trim($arParams['~SUCCESS_MESSAGE']))
                                ? $arParams['~SUCCESS_MESSAGE']
                                : Loc::getMessage('ACBOC_JS_SUCCESS_MESSAGE_TPL'),
                            'tplErrorUserAgreeRow' => isset($arParams['~USER_AGREE_ERROR_MESSAGE']) && strlen(trim($arParams['~USER_AGREE_ERROR_MESSAGE']))
                                ? $arParams['~USER_AGREE_ERROR_MESSAGE']
                                : Loc::getMessage('ACBOC_JS_USER_AGREE_ERROR_MESSAGE_TPL')
                        ),
                        false,
                        true
                    );
                ?>);

            }
        })();

    <?php } ?>
</script>
