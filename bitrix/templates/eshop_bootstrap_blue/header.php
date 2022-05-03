<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".SITE_TEMPLATE_ID."/header.php");
$APPLICATION->SetTitle("");
CJSCore::Init(array("fx"));
$curPage = $APPLICATION->GetCurPage(true);
$theme = COption::GetOptionString("main", "wizard_eshop_bootstrap_theme_id", "blue", SITE_ID);
?><!DOCTYPE html>
<html xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<meta name="yandex-verification" content="bc4e778bba0942a2" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_DIR?>favicon.ico" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<link rel="canonical" href="<?='https://'.$_SERVER['HTTP_HOST'].$GLOBALS["APPLICATION"]->GetCurPage()?>"/>
	<?$APPLICATION->ShowHead();?>
	<?
	$APPLICATION->SetAdditionalCSS("/bitrix/css/main/bootstrap.css", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/owl.carousel.min.css", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/slick.css", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/jquery.fancybox.min.css", true);
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/font-awesome.min.css", true);
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/bootstrap.min.js');
	//$APPLICATION->AddHeadScript('https://uguide.ru/js/script/ok4.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.fancybox.min.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.lazy.min.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/owl.carousel.min.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/slick.min.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/maskinput.js');
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/main.js');
	?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
	<title><?$APPLICATION->ShowTitle()?></title>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter49710649 = new Ya.Metrika2({
                    id:49710649,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/tag.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks2");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/49710649" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-122746692-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-122746692-1');
</script>
</head>
<body class="archive category category-blog category-30 wpb-js-composer js-comp-ver-5.4.1 vc_responsive dokan-theme-revo">

	<? global $USER;
	if (!$USER->IsAdmin()){ ?>
		<style>
			#panel{
				height: 0 !important;
				overflow: hidden !important;
			}
		</style>
	<? } ?>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>

<div class="body-wrapper theme-clearfix">
    <div class="body-wrapper-inner"id="bx_eshop_wrap">

        <header id="header" class="header header-style1">
            <div class="header-top">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 col-sm-4 mb-xs-1">
                            <div class="text-xs-center font-xxs-smaller">
                                <i class="fa fa-clock-o"></i>
                                <?$APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    Array(
                                        "AREA_FILE_SHOW" => "file",
                                        "AREA_FILE_SUFFIX" => "inc",
                                        "EDIT_TEMPLATE" => "",
                                        "PATH" => "/include/inc_header_schedule.php"
                                    )
                                );?>

                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-8">
                            <div class="text-right text-xs-center font-xxs-smaller">
                                <i class="fa fa-truck"></i>
                                <?$APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    Array(
                                        "AREA_FILE_SHOW" => "file",
                                        "AREA_FILE_SUFFIX" => "inc",
                                        "EDIT_TEMPLATE" => "",
                                        "PATH" => "/include/inc_header_delivery.php"
                                    )
                                );?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-mid">
                <div class="container">
                    <div class="row">
                        <!-- Logo -->
                        <div class="top-header col-lg-2 col-md-2 pull-left">
                            <div class="revo-logo">
                                <a <? if($GLOBALS["APPLICATION"]->GetCurPage() != '/'){ echo 'href="/"'; }?>>
                                    <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_logo.php"), false);?>
                                </a>
                            </div>
                        </div>
                        <!-- Primary navbar -->
                        <div id="main-menu" class="main-menu clearfix col-lg-8 col-md-8 pull-left">
                            <nav id="primary-menu" class="primary-menu">
                                <div class="mid-header clearfix">
                                    <div class="navbar-inner navbar-inverse">
                                        <div class="resmenu-container">
                                            <button class="navbar-toggle mobile_menu_button" type="button">
                                                <span class="sr-only">Categories</span>
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                            </button>

                                            <div class="menu-responsive-wrapper mobile_menu">
                                                                                        <div class="close_list_cat close_mob_menu"></div>
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:menu",
                                                    "mobil_menu",
                                                    array(
                                                        "ALLOW_MULTI_SELECT" => "N",
                                                        "CHILD_MENU_TYPE" => "left",
                                                        "DELAY" => "N",
                                                        "MAX_LEVEL" => "1",
                                                        "MENU_CACHE_GET_VARS" => array(""),
                                                        "MENU_CACHE_TIME" => "3600",
                                                        "MENU_CACHE_TYPE" => "N",
                                                        "MENU_CACHE_USE_GROUPS" => "Y",
                                                        "ROOT_MENU_TYPE" => "mobil",
                                                        "USE_EXT" => "N",
                                                        "COMPONENT_TEMPLATE" => "top_menu"
                                                    ),
                                                    false
                                                );?>

                                            </div>
                                            <div class='overflow_mobile_menu'></div>

                                        </div>
                                        <?$APPLICATION->IncludeComponent(
                                            "bitrix:menu",
                                            "top_menu",
                                            array(
                                                "ALLOW_MULTI_SELECT" => "N",
                                                "CHILD_MENU_TYPE" => "left",
                                                "DELAY" => "N",
                                                "MAX_LEVEL" => "1",
                                                "MENU_CACHE_GET_VARS" => array(
                                                ),
                                                "MENU_CACHE_TIME" => "3600",
                                                "MENU_CACHE_TYPE" => "A",
                                                "MENU_CACHE_USE_GROUPS" => "Y",
                                                "ROOT_MENU_TYPE" => "top",
                                                "USE_EXT" => "N",
                                                "COMPONENT_TEMPLATE" => "top_menu",
                                                "COMPOSITE_FRAME_MODE" => "A",
                                                "COMPOSITE_FRAME_TYPE" => "AUTO"
                                            ),
                                            false
                                        );?>
                                    </div>
                                </div>
                            </nav>
                        </div>
                        <!-- /Primary navbar -->
                        <!-- Sidebar Top Menu -->
                        <div class="contact-us-header pull-right">
                            <div class="widget-1 widget-first widget text-4 widget_text">
                                <div class="widget-inner">
                                    <div class="textwidget">
                                        <div class="contact-us">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                Array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "AREA_FILE_SUFFIX" => "inc",
                                                    "EDIT_TEMPLATE" => "",
                                                    "PATH" => "/include/inc_header_contact.php"
                                                )
                                            );?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sticky-cart pull-right">
                            <div class="top-form top-form-minicart revo-minicart pull-right">
                                <div class="top-minicart-icon pull-right">
                                    <a class="cart-contents" href="/themes/sw_revo/cart/" title="View your shopping cart"><span class="minicart-number">0</span></a>
                                </div>
                                <div class="wrapp-minicart">
                                    <div class="minicart-padding">
                                        <div class="number-item">There are <span class="item">0 item(s)</span> in your cart</div>
                                        <ul class="minicart-content">
                                        </ul>
                                        <div class="cart-checkout">
                                            <div class="price-total">
                                                <span class="label-price-total">Subtotal:</span>
                                                <span class="price-total-w"><span class="price"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">&#36;</span>0.00</span>
                                                </span>
                                                </span>
                                            </div>
                                            <div class="cart-links clearfix">
                                                <div class="cart-link"><a href="/themes/sw_revo/cart/" title="Cart">View Cart</a></div>
                                                <div class="checkout-link"><a href="/themes/sw_revo/checkout/" title="Check Out">Check Out</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sticky-search pull-right">
                            <i class="fa fa-search"></i>
                            <div class="sticky-search-content">
                                <div class="top-form top-search">
                                    <div class="topsearch-entry">
                                        <form method="get" id="searchform_special" action="/themes/sw_revo/">
                                            <div>
                                                <input type="text" value="" name="s" id="s" placeholder="Enter your keyword..." />
                                                <button type="submit" title="Search" class="fa fa-search button-search-pro form-button"></button>
                                                <input type="hidden" name="search_posttype" value="product" />
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-bottom">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-2 col-xs-2 vertical_megamenu vertical_megamenu-header pull-left <? if($APPLICATION->GetCurPage(false) === '/'){ echo 'active'; } ?>">
                            <div class="mega-left-title"><strong>Каталог</strong></div>
                            <div class="vc_wp_custommenu wpb_content_element">
                                <div class="wrapper_vertical_menu vertical_megamenu" data-number="9" data-moretext="See More" data-lesstext="See Less">
                                    <?$APPLICATION->IncludeComponent(
                                        "bitrix:catalog.section.list",
                                        ".default",
                                        Array(
                                            "ADD_SECTIONS_CHAIN" => "Y",
                                            "CACHE_GROUPS" => "Y",
                                            "CACHE_TIME" => "36000000",
                                            "CACHE_TYPE" => "A",
                                            "COMPONENT_TEMPLATE" => ".default",
                                            "COMPOSITE_FRAME_MODE" => "A",
                                            "COMPOSITE_FRAME_TYPE" => "AUTO",
                                            "COUNT_ELEMENTS" => "N",
                                            "IBLOCK_ID" => "2",
                                            "IBLOCK_TYPE" => "catalog",
                                            "SECTION_CODE" => "",
                                            "SECTION_FIELDS" => array(0=>"",1=>"",),
                                            "SECTION_ID" => $_REQUEST["SECTION_ID"],
                                            "SECTION_URL" => "",
                                            "SECTION_USER_FIELDS" => array(0=>"",1=>"",),
                                            "SHOW_PARENT_NAME" => "Y",
                                            "TOP_DEPTH" => "3",
                                            "VIEW_MODE" => "LIST"
                                        )
                                    );?>
                                </div>
                            </div>
                        </div>
                        <div class="search-cate col-lg-7 col-md-6 col-sm-7 col-xs-6">
                            <div class="widget sw_ajax_woocommerce_search-3 sw_ajax_woocommerce_search pull-left">
                                <div class="widget-inner">
                                    <div class="revo_top swsearch-wrapper clearfix">
                                        <div class="top-form top-search ">
                                            <div class="topsearch-entry">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:search.form",
                                                    ".default",
                                                    array(
                                                        "PAGE" => "#SITE_DIR#search/index.php",
                                                        "USE_SUGGEST" => "N",
                                                        "COMPONENT_TEMPLATE" => ".default"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-right col-lg-2 col-md-2 col-sm-3 col-xs-4 pull-right">
                            <div class="widget sw_top-6 sw_top pull-right">
                                <div class="widget-inner">
                                    <?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", "cart_header", Array(
                                        "PATH_TO_BASKET" => SITE_DIR."personal/cart/",	// Страница корзины
                                        "PATH_TO_PERSONAL" => SITE_DIR."personal/",	// Страница персонального раздела
                                        "SHOW_PERSONAL_LINK" => "N",	// Отображать персональный раздел
                                        "SHOW_NUM_PRODUCTS" => "Y",	// Показывать количество товаров
                                        "SHOW_TOTAL_PRICE" => "Y",	// Показывать общую сумму по товарам
                                        "SHOW_PRODUCTS" => "N",	// Показывать список товаров
                                        "POSITION_FIXED" => "N",	// Отображать корзину поверх шаблона
                                        "SHOW_AUTHOR" => "Y",	// Добавить возможность авторизации
                                        "PATH_TO_REGISTER" => SITE_DIR."login/",	// Страница регистрации
                                        "PATH_TO_PROFILE" => SITE_DIR."personal/",	// Страница профиля
                                    ),
                                        false
                                    );?>
                                </div>
                            </div>
                            <div class='lk_user'>
                                <a href='/personal/'><i class="fa fa-user-o" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <? if ($APPLICATION->GetCurPage(false) !== '/'){ ?>
                <? $APPLICATION->IncludeComponent(
                "bitrix:breadcrumb",
                "new_bread",
                array(
                    "PATH" => "",
                    "SITE_ID" => "s1",
                    "START_FROM" => "0",
                    "COMPONENT_TEMPLATE" => "new_bread"
                ),
                false
            ); ?>
        <? } ?>
        <div class="container">
            <?
            $title_page = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            if ($APPLICATION->GetCurPage(false) !== '/' && strripos($title_page, '/search') === false){ ?>
                <h1 class='title_page'><?=$APPLICATION->ShowTitle(false);?></h1>
            <? } ?>

<?$needSidebar = preg_match("~^".SITE_DIR."(catalog|personal\/cart|personal\/order\/make)/~", $curPage);?>
