<?
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\McartXls;
use Mcart\Xls\ORM\ProfileTable;
/* @var $obMcartXls McartXls */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mcart.xls/prolog.php");
Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(Loc::getMessage("MCART_XLS_TITLE"));

if(!($RIGHT = McartXls::checkAccess('R'))){
    return;
}
$obMcartXls = McartXls::getInstance();
if(!$obMcartXls->checkRequirements()){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
    $obMcartXls->showErrors();
    return;
}

$sTableID = ProfileTable::getTableName();
$oSort = new CAdminSorting($sTableID, 'NAME', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

if ($RIGHT >= 'W'){
    // сформируем меню из одного пункта - добавление
    $aContext = [
      [
        "TEXT"=>Loc::getMessage("MCART_XLS_PROFILE_ADD"),
        "LINK"=>"mcart_xls_profile_edit_step_1.php?lang=".LANGUAGE_ID,
        "TITLE"=>Loc::getMessage("MCART_XLS_PROFILE_ADD"),
        "ICON"=>"btn_new",
      ],
    ];
    // и прикрепим его к списку
    $lAdmin->AddAdminContextMenu($aContext);

    // delete action
    $obRequest = Application::getInstance()->getContext()->getRequest();
    if ($obRequest->getQuery('action') === 'delete' && check_bitrix_sessid()){
        $profileId = (int)$obRequest->getQuery('ID');
        if($profileId > 0){
            $result = ProfileTable::delete($profileId);
            if (!$result->isSuccess()) {
                require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
                \CAdminMessage::ShowMessage(implode('<br />', $result->getErrorMessages()));
                echo '<br /><a class="adm-btn" href="mcart_xls_index.php?lang='.LANGUAGE_ID.'">'.Loc::getMessage("MCART_XLS_BACK").'</a>';
                return;
            }
        }
        LocalRedirect('mcart_xls_index.php?lang='.LANGUAGE_ID);
    }
}

//заголовки
$lAdmin->AddHeaders([
    [
        "id" => "ID",
        "content" => "ID",
        "sort" => "id",
        "align" => "right",
        "default" => true,
    ],
    [
        "id" => "NAME",
        "content" => Loc::getMessage("MCART_XLS_COL_NAME"),
        "sort" => "name",
        "default" => true,
    ],
    [
        "id" => "IBLOCK",
        "content" => Loc::getMessage("MCART_XLS_COL_IBLOCK"),
        "sort" => "iblock.name",
        "default" => true,
    ],
]);

// выберем список
$rsData = ProfileTable::getList([
	'order'  => [strtoupper(strip_tags($by)) => strip_tags($order)],
	'select' => ['ID', 'NAME', 'IBLOCK_ID', 'IBLOCK.NAME', 'IBLOCK.IBLOCK_TYPE_ID']
]);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("MCART_XLS_PROFILE_NAV")));

//подготовка списка к выводу
while ($arRes = $rsData->fetch()){

    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($arRes['ID'], $arRes);

    $row->AddViewField(
        "IBLOCK",
        '<a href="iblock_edit.php?type='.$arRes['MCART_XLS_ORM_PROFILE_IBLOCK_IBLOCK_TYPE_ID'].'&ID='.$arRes['IBLOCK_ID'].'&lang='.LANGUAGE_ID.'">'.
            $arRes['MCART_XLS_ORM_PROFILE_IBLOCK_NAME'].
        '</a>'
    );

    if ($RIGHT < 'W'){
        $row->AddViewField("NAME", $arRes['NAME']);
        continue;
    }
    $row->AddViewField("NAME", '<a href="mcart_xls_profile_edit_step_1.php?ID='.$arRes['ID'].'&lang='.LANGUAGE_ID.'">'.$arRes['NAME'].'</a>');

	$arActions = [];
    $arActions[] = [
        'ICON'=>'delete',
        'TEXT' => GetMessage('MCART_XLS_PROFILE_DELETE'),
        'ACTION' => 'if(confirm("'.GetMessageJS('MCART_XLS_PROFILE_DELETE').'?")) '.
            $lAdmin->ActionRedirect('mcart_xls_index.php?action=delete&ID='.$arRes['ID'].'&lang='.LANGUAGE_ID.'&'.bitrix_sessid_get())
    ];
	$row->AddActions($arActions);
}

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
//CAdminMessage::ShowNote(Loc::getMessage("MCART_XLS_TITLE"));
//--------------------------//

// выведем таблицу списка элементов
$lAdmin->DisplayList();

echo BeginNote();
    echo '<h3>'.Loc::getMessage("MCART_XLS_REQUIREMENT_CHECK").'</h3>';
    echo '<ul>';
    foreach ($obMcartXls->getRequirementsList() as $ar) {
        echo '<li>'.$ar['NAME'];
        if(!$ar['isRequired']){
            echo ' (optional)';
        }
        echo ' ... '.($ar['VALUE']? Loc::getMessage("MCART_XLS_REQUIREMENT_CHECK_PASSED") : Loc::getMessage("MCART_XLS_REQUIREMENT_CHECK_FAILED")).'</li>';
    }
    echo '</ul>';
echo EndNote();

//--------------------------//
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");