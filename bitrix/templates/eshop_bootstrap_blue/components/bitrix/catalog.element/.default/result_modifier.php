<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arElement = CIblockElement::GetById($arResult["ID"])->GetNext();
$arResult['DETAIL_PAGE_URL'] = $arElement['DETAIL_PAGE_URL'];
$cp = $this->__component;
if (is_object($cp))
    $cp->SetResultCacheKeys(array('DETAIL_PAGE_URL'));