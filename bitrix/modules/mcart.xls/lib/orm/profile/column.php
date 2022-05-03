<?php

namespace Mcart\Xls\ORM\Profile;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ColumnTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> COLUMN string(255) mandatory
 * <li> SAVE_IN_PREF string(8) mandatory
 * <li> SAVE_IN string(255) mandatory
 * <li> HANDLER string(8) mandatory
 * <li> DO_NOT_IMPORT_ROW_IF_EMPTY string(1) mandatory
 * <li> IS_IDENTIFY_ELEMENT string(1) mandatory
 * </ul>
 *
 * @package Mcart\Xls
 **/
final class ColumnTable extends Entity\DataManager {
    const HANDLER__URL = 'URL';
    const HANDLER__PICTURE = 'PICTURE';
    const HANDLER__DATE = 'DATE';
    const HANDLER__DATETIME = 'DATETIME';
    const SAVE_IN_PREF__FIELD = 'FIELD';
    const SAVE_IN_PREF__PROPERTY = 'PROPERTY';
    const SAVE_IN_PREF__PRODUCT = 'PRODUCT';
    const SAVE_IN_PREF__PRICE = 'PRICE';

    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName() {
        return 'mcart_xls_profile_column';
    }

    /**
     * Returns entity map definition
     *
     * @return array
     */
    public static function getMap() {
        $loc_pref = 'MCART_XLS_PROFILE_COLUMN_';
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\IntegerField('PROFILE_ID', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'PROFILE_ID')
            ]),
            new Entity\ReferenceField(
                'PROFILE',
                'Mcart\Xls\ORM\Profile',
                ['=this.PROFILE_ID' => 'ref.ID']
            ),
            new Entity\StringField('COLUMN', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'COLUMN'),
                'validation' => function() {
                    return [new Entity\Validator\RegExp('/^[0-9A-z]{1,255}$/')];
                }
            ]),
            new Entity\EnumField('SAVE_IN_PREF', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'SAVE_IN_PREF'),
                'values' => [
                    self::SAVE_IN_PREF__FIELD,
                    self::SAVE_IN_PREF__PROPERTY,
                    self::SAVE_IN_PREF__PRODUCT,
                    self::SAVE_IN_PREF__PRICE,
                ],
            ]),
            new Entity\StringField('SAVE_IN', [
                'required' => true,
                'title' => Loc::getMessage($loc_pref.'SAVE_IN'),
                'validation' => function() {
                    return [new Entity\Validator\RegExp('/^[_0-9A-z]{1,255}$/')];
                }
            ]),
            new Entity\EnumField('HANDLER', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'HANDLER'),
                'values' => [
                    Loc::getMessage($loc_pref.'HANDLER__DATE') => self::HANDLER__DATE,
                    Loc::getMessage($loc_pref.'HANDLER__DATETIME') => self::HANDLER__DATETIME,
                    Loc::getMessage($loc_pref.'HANDLER__PICTURE') => self::HANDLER__PICTURE,
                    Loc::getMessage($loc_pref.'HANDLER__URL') => self::HANDLER__URL,
                ],
            ]),
            new Entity\BooleanField('DO_NOT_IMPORT_ROW_IF_EMPTY', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'DO_NOT_IMPORT_ROW_IF_EMPTY')
            ]),
            new Entity\BooleanField('IS_IDENTIFY_ELEMENT', [
                'required' => false,
                'default_value' => 'N',
				'values' => ['N','Y'],
                'title' => Loc::getMessage($loc_pref.'IS_IDENTIFY_ELEMENT')
            ]),
        ];
    }

}
