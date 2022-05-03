<?php

namespace Mcart\Xls\ORM;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Mcart\Xls\ORM\Profile\ColumnTable;
use Mcart\Xls\ORM\Profile\ConstTable;
use Mcart\Xls\ORM\Profile\Column\CustomFieldsTable;

Loc::loadMessages(__FILE__);

/**
 * Class ProfileTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> FILE string(255) mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> QUANTITY_ELEMENTS_IMPORTED_PER_STEP int mandatory
 * <li> ONLY_UPDATE string(1) mandatory
 * <li> DEACTIVATE_IF_NEW string(1) mandatory
 * <li> DEACTIVATE_IF_QUANTITY_0 string(1) mandatory
 * <li> DEACTIVATE_IF_PRICE_0 string(1) mandatory
 * <li> DEACTIVATE_ELEMENT_IF_NOT_IN_FILE string(1) mandatory
 * <li> ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0 string(1) mandatory
 * <li> HEADER_ROW string(255) mandatory
 * <li> START_ROW string(255) mandatory
 * <li> END_ROW string(255) mandatory
 * <li> IBLOCK_SECTION_ID_FOR_NEW int mandatory
 * </ul>
 *
 * @package Mcart\Xls
 **/
final class ProfileTable extends Entity\DataManager {

    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName() {
        return 'mcart_xls_profile';
    }

    /**
     * Returns entity map definition
     *
     * @return array
     */
    public static function getMap() {
        $loc_pref = 'MCART_XLS_PROFILE_';
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage($loc_pref.'ID'),
            ]),
            new Entity\StringField('NAME', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'NAME'),
                'validation' => function() {
                    return [new Entity\Validator\Length(1, 255)];
                }
            ]),
            new Entity\StringField('FILE', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'FILE'),
                'validation' => function() {
                    return [new Entity\Validator\Length(null, 255)];
                }
            ]),
            new Entity\IntegerField('IBLOCK_ID', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'IBLOCK_ID')
            ]),
            new Entity\ReferenceField(
                'IBLOCK',
                'Bitrix\Iblock\Iblock',
                ['=this.IBLOCK_ID' => 'ref.ID']
            ),
            new Entity\IntegerField('QUANTITY_ELEMENTS_IMPORTED_PER_STEP', [
                'required' => true,
                'default_value' => 100,
                'title' => Loc::getMessage($loc_pref.'QUANTITY_ELEMENTS_IMPORTED_PER_STEP'),
                'validation' => function() {
                    return [new Entity\Validator\Range(1)];
                }
            ]),
            new Entity\BooleanField('ONLY_UPDATE', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'ONLY_UPDATE')
            ]),
            new Entity\BooleanField('DEACTIVATE_IF_NEW', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'DEACTIVATE_IF_NEW')
            ]),
            new Entity\BooleanField('DEACTIVATE_IF_QUANTITY_0', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'DEACTIVATE_IF_QUANTITY_0')
            ]),
            new Entity\BooleanField('DEACTIVATE_IF_PRICE_0', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'DEACTIVATE_IF_PRICE_0')
            ]),
            new Entity\BooleanField('ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'ACTIVATE_IF_QUANTITY_AND_PRICE_NOT_0')
            ]),
            new Entity\StringField('HEADER_ROW', [
                'required' => true,
                'default_value' => 1,
                'title' => Loc::getMessage($loc_pref.'HEADER_ROW'),
                'validation' => function() {
                    return [new Entity\Validator\RegExp('/^[0-9A-z]{1,255}$/')];
                }
            ]),
            new Entity\StringField('START_ROW', [
                'required' => true,
                'default_value' => 2,
                'title' => Loc::getMessage($loc_pref.'START_ROW'),
                'validation' => function() {
                    return [new Entity\Validator\RegExp('/^[0-9A-z]{1,255}$/')];
                }
            ]),
            new Entity\StringField('END_ROW', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'END_ROW'),
                'validation' => function() {
                    return [new Entity\Validator\RegExp('/^[0-9A-z]{0,255}$/')];
                }
            ]),
            new Entity\IntegerField('IBLOCK_SECTION_ID_FOR_NEW', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'IBLOCK_SECTION_ID_FOR_NEW')
            ]),
        ];
    }

    public static function onBeforeDelete(Entity\Event $event){
        $result = new Entity\EventResult;
        $id = $event->getParameter("primary")['ID'];

        $conn = \Bitrix\Main\Application::getConnection();
        $conn->startTransaction();

        $dbItems = ConstTable::getList(['filter' => ['PROFILE_ID' => $id]]);
        while ($ar = $dbItems->fetch()) {
            $resultConst = ConstTable::delete($ar['ID']);
            if(!$resultConst->isSuccess()){
                foreach ($resultConst->getErrors() as $ob) {
                    $result->addError($ob);
                }
                $conn->rollbackTransaction();
                return $result;
            }
        }

        $dbItems = ColumnTable::getList(['filter' => ['PROFILE_ID' => $id]]);
        while ($ar = $dbItems->fetch()) {

            $dbItems2 = CustomFieldsTable::getList(['filter' => ['COLUMN_ID' => $ar['ID']]]);
            while ($ar2 = $dbItems2->fetch()) {
                $resultCustomFields = CustomFieldsTable::delete($ar2['ID']);
                if(!$resultCustomFields->isSuccess()){
                    foreach ($resultCustomFields->getErrors() as $ob) {
                        $result->addError($ob);
                    }
                    $conn->rollbackTransaction();
                    return $result;
                }
            }

            $resultColumn = ColumnTable::delete($ar['ID']);
            if(!$resultColumn->isSuccess()){
                foreach ($resultColumn->getErrors() as $ob) {
                    $result->addError($ob);
                }
                $conn->rollbackTransaction();
                return $result;
            }
        }

        return $result;
    }

    public static function onAfterDelete(Entity\Event $event){
        \Bitrix\Main\Application::getConnection()->commitTransaction();
    }

}
