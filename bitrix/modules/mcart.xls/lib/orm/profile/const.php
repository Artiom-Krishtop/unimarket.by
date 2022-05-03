<?php

namespace Mcart\Xls\ORM\Profile;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ConstTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> SAVE_IN_PREF string(8) mandatory
 * <li> SAVE_IN string(255) mandatory
 * <li> VALUE string(255) mandatory
 * </ul>
 *
 * @package Mcart\Xls
 **/
final class ConstTable extends Entity\DataManager {
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
        return 'mcart_xls_profile_const';
    }

    /**
     * Returns entity map definition
     *
     * @return array
     */
    public static function getMap() {
        $loc_pref = 'MCART_XLS_PROFILE_CONST_';
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
            new Entity\StringField('VALUE', [
                'required' => false,
                'title' => Loc::getMessage($loc_pref.'VALUE'),
                'validation' => function() {
                    return [new Entity\Validator\Length(null, 255)];
                }
            ])
        ];
    }

}
