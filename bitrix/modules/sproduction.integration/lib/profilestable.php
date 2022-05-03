<?php
namespace SProduction\Integration;

use Bitrix\Main,
	Bitrix\Main\Entity,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ProfilesTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> sort int mandatory
 * <li> name string(255) mandatory
 * <li> active string(4) mandatory
 * <li> options string mandatory
 * <li> filter string mandatory
 * <li> statuses string mandatory
 * <li> props string mandatory
 * <li> contact string mandatory
 * </ul>
 *
 * @package SProduction\Integration
 **/

class ProfilesTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sprod_integration_profiles';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('id', [
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_ID_FIELD'),
			]),
			new Entity\StringField('sort', [
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_SORT_FIELD'),
			]),
			new Entity\StringField('name', [
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_NAME_FIELD'),
			]),
			new Entity\BooleanField('active', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_ACTIVE_FIELD'),
				'values' => ['', 'N', 'Y'],
			]),
			new Entity\StringField('options', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_OPTIONS_FIELD'),
			]),
			new Entity\StringField('filter', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_FILTER_FIELD'),
			]),
			new Entity\StringField('statuses', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_STATUSES_FIELD'),
			]),
			new Entity\StringField('props', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_PROPS_FIELD'),
			]),
			new Entity\StringField('contact', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_CONTACT_FIELD'),
			]),
			new Entity\StringField('other', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_OTHER_FIELD'),
			]),
			new Entity\DateField('date_create', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_DATE_CREATE_FIELD'),
			]),
			new Entity\DateField('date_update', [
				'title' => Loc::getMessage('SP_CI_PROFILES_ENTITY_DATE_UPDATE_FIELD'),
				'default_value' => function () {
					return new Type\DateTime();
				},
			]),
		);
	}
	/**
	 * Returns validators for name field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	public static function add(array $fields)
	{
		if (!$fields['sort']) {
			$fields['sort'] = 1;
		}
		if (!$fields['date_create']) {
			$fields['date_create'] = new Type\DateTime();
		}
		$res = parent::add($fields);
		return $res;
	}

	public static function update($id, array $fields)
	{
		foreach ($fields as $field => $value) {
			if (is_array($value)) {
				$fields[$field] = serialize($value);
			}
		}
		$res = parent::update($id, $fields);
		return $res;
	}

	public static function getList(array $params=[])
	{
		$list = [];
		$result = parent::getList($params);
		while ($item = $result->fetch()) {
			foreach ($item as $field => $value) {
				switch ($field) {
					case 'options':
					case 'filter':
					case 'statuses':
					case 'props':
					case 'contact':
					case 'other':
						$item[$field] = unserialize($value);
						break;
				}
			}
			$list[] = $item;
		}
		return $list;
	}

	public static function getById($id) {
		$fields = false;
		$result = parent::getById($id);
		if ($result[0]) {
			$fields = $result[0];
		}
		return $fields;
	}

	protected static function checkFilterConformity($has_conformity, $condition, $value) {
		if ($has_conformity && $value) {
			if (!is_array($value)) {
				$values = [$value];
			}
			else {
				$values = $value;
			}
			if ($condition['operation'] == 'equal') {
				$tmp_conf = 0;
				foreach ($values as $value) {
					if (!in_array($value, $condition['value'])) {
						$tmp_conf++;
					}
				}
				if ($tmp_conf == count($values)) {
					$has_conformity = false;
				}
			}
			if ($condition['operation'] == 'not_equal') {
				$tmp_conf = 0;
				foreach ($values as $value) {
					if (in_array($value, $condition['value'])) {
						$tmp_conf++;
					}
				}
				if ($tmp_conf) {
					$has_conformity = false;
				}
			}
			if ($condition['operation'] == 'more') {
				if ($values[0] <= $condition['value'][0]) {
					$has_conformity = false;
				}
			}
			if ($condition['operation'] == 'less') {
				if ($values[0] >= $condition['value'][0]) {
					$has_conformity = false;
				}
			}
		}
		return $has_conformity;
	}

	public static function getByFilter($order_data) {
		$fields = false;
		$list = self::getList([
			'filter' => ['active' => 'Y'],
		]);
		if (!empty($list)) {
			foreach ($list as $profile) {
				$has_conformity = true;
				$filter = (array)$profile['filter']['filter'];
				foreach ($filter as $item) {
					// Order site
					if ($item['field'] == 'site') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['SITE_ID']);
					}
					// Person type
					elseif ($item['field'] == 'person_type') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['PERSON_TYPE_ID']);
					}
					// Payment type
					elseif ($item['field'] == 'pay_type') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['PAY_TYPE']);
					}
					// Delivery type
					elseif ($item['field'] == 'deliv_type') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['DELIVERY_TYPE_ID']);
					}
					// Order status
					elseif ($item['field'] == 'status') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['STATUS_ID']);
					}
					// User group
					elseif ($item['field'] == 'user_group') {
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['USER_GROUPS_ID']);
					}
					// Order type
					elseif (strpos($item['field'], 'prop_') !== false) {
						$prop_id = (int)str_replace('prop_', '', $item['field']);
						$has_conformity = self::checkFilterConformity($has_conformity, $item, $order_data['PROPERTIES'][$prop_id]['VALUE'][0]);
					}
				}
				//var_dump($has_conformity);
				// Return the first suitable profile
				if ($has_conformity) {
					$fields = $profile;
					break;
				}
			}
		}
		return $fields;
	}
}
