<?php

namespace ISYS\BXE;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class EventHandlers
{
    function AdminContextMenuShow(&$CAdminUiListContext)
    {
        //����� �� ����������� �������� � ����
        if (strpos($_SERVER['QUERY_STRING'], '&mode=BXE')) {
            $buttonLink = $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'];
        } else {
            $buttonLink = $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'] . '&mode=BXE';
        }

        if (!Helpers::AllNeededPHPExtensionsAvailableCheck()) {
            $title = Loc::getMessage("ISYS_BXE_NEED_REINSTALL");

        } else {
            $title = Loc::getMessage("ISYS_BXE_EXCEL_EXPORT_BUTTON_TITLE");
        }
        $newItem = Array(
            'ICON' => 'btn_new',
            'TEXT' => $title,
            'TITLE' => $title,
            'LINK' => $buttonLink,
            'SHOW_TITLE' => true
        );

        $CAdminUiListContext = array_merge($CAdminUiListContext, Array($newItem));
    }

    /**
     * @param \CAdminUiList $obList
     */
    function AdminListDisplay(&$obList)
    {
        //����� �� ����������� �������� � ����
        if (strpos($_SERVER['QUERY_STRING'], '&mode=BXE')) {
            $buttonLink = $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'];
        } else {
            $buttonLink = $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'] . '&mode=BXE';
        }

        //����� ������� ������ ����

        /** @var \CAdminContextMenuList $adminListContext */
        $adminListContext = $obList->context;

        if ($adminListContext != null) {
            $arCurrentAdminMenuItems = $adminListContext->items;

            if (!Helpers::AllNeededPHPExtensionsAvailableCheck()) {
                //� ����� ������ ����
                $newAdminMenuItems = array(
                    array(
                        'TEXT' => Loc::getMessage("ISYS_BXE_NEED_REINSTALL"),
                        'ICON' => 'btn_green',
                        'LINK' => '/bitrix/admin/partner_modules.php?lang=ru',
                        'TITLE' => Loc::getMessage("ISYS_BXE_NEED_REINSTALL")
                    )
                );
            } else {
                //� ����� ������ ����
                $newAdminMenuItems = array(
                    array(
                        'TEXT' => Loc::getMessage("ISYS_BXE_EXCEL_EXPORT_BUTTON_TITLE"),
                        'ICON' => 'btn_green',
                        'LINK' => $buttonLink,
                        'TITLE' => Loc::getMessage("ISYS_BXE_EXCEL_EXPORT_BUTTON_TITLE")
                    )
                );
            }


            //������� 18.0.4 +
            if (get_class($obList) == \CAdminList::class) {
                //���������� � ����������
                $arExtendedContextMenu = array_merge($arCurrentAdminMenuItems, $newAdminMenuItems);
                $obList->AddAdminContextMenu($arExtendedContextMenu, true);
            }
        }

        //�� ������� ������
        if ($_GET['mode'] == 'BXE') {
            Helpers::ExportCurrentAdminPageToExcel($obList);
        }
    }
}


