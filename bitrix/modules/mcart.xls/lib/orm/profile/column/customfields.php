<?php

namespace Mcart\Xls\ORM\Profile\Column;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CustomFieldsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COLUMN_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> VALUE string mandatory
 * </ul>
 *
 * @package Bitrix\Xls
 **/
final class CustomFieldsTable extends Entity\DataManager {

    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName() {
        return 'mcart_xls_profile_column_custom_fields';
    }

    /**
     * Returns entity map definition
     *
     * @return array
     */
    public static function getMap() {
        $loc_pref = 'MCART_XLS_PROFILE_COLUMN_CUSTOM_FIELDS_';
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\IntegerField('COLUMN_ID', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'COLUMN_ID')
            ]),
            new Entity\ReferenceField(
                'COLUMN',
                'Mcart\Xls\ORM\Profile\Column',
                ['=this.COLUMN_ID' => 'ref.ID']
            ),
            new Entity\StringField('NAME', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'NAME')
            ]),
            new Entity\TextField('VALUE', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'VALUE')
            ]),
        ];
    }

}
