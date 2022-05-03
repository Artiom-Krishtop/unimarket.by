<?php

namespace Mcart\Xls\Helpers;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class Html {
    public $css_class_tr;
    public $css_class_required;
    public $css_class_td_title;
    public $css_class_td_value;

    public function __construct($css_class_tr = 'row', $css_class_required = 'input_required', $css_class_td_title = 'input_title', $css_class_td_value = 'input_value') {
        $this->css_class_tr = filter_var((string)$css_class_tr, FILTER_SANITIZE_STRING);
        $this->css_class_required = filter_var((string)$css_class_required, FILTER_SANITIZE_STRING);
        $this->css_class_td_title = filter_var((string)$css_class_td_title, FILTER_SANITIZE_STRING);
        $this->css_class_td_value = filter_var((string)$css_class_td_value, FILTER_SANITIZE_STRING);
    }

    public static function showInputsHidden($pref, $k, $v) {
         if(!is_array($v)){ ?>
            <input type="hidden" name="<?=$pref.$k?>" value="<?=$v?>"><?
             return;
        }
        foreach ($v as $k2 => $v2) {
            self::showInputsHidden($pref, $k.'['.$k2.']', $v2);
        }
    }

    /**
     * Возвращает строку таблицы формы.
     * В первой ячейке название, во второй - поле ввода.
     *
     * @param string $title         Название
     * @param string $tooltip       Описание
     * @param boolean $isRequired   Обязательное ли
     * @param string $type          text | boolean | select | select_iblock
     * @param array $input_options  ['name' => 'field_name', 'value' => 'field_value', 'type' => 'text', 'size' => '50']
     * @param array $td1_options    ['width' => '40%']
     * @param array $td2_options    ['width' => '60%']
     * @return string
     */
    public function getRowInput($title = '', $tooltip = '', $isRequired = false, $type = 'text', $input_options = [], $td1_options = [], $td2_options = [] ) {
        if(
            ($type!='select_iblock' && empty($input_options['name'])) ||
            ($type=='select_iblock' && (empty($input_options['iblock_id']['name']) || empty($input_options['iblock_type_id']['name'])))
        ){
            return '';
        }
        $title = htmlspecialchars((string)$title, ENT_QUOTES | ENT_HTML401);
        $tooltip = htmlspecialchars((string)$tooltip, ENT_QUOTES | ENT_HTML401);
        $isRequired = intval($isRequired);
        $css_class_tr = $this->css_class_tr;
        if($isRequired){
            $css_class_tr .= ' '.$this->css_class_required;
        }

        $html = '<tr';
        if(!empty($css_class_tr)){
            $html .= ' class="'.$css_class_tr.'"';
        }
        $html .= '><td class="'.$this->css_class_td_title.'"';
        foreach ($td1_options as $k => $v) {
            $html .= ' '.$k.'="'.htmlspecialchars($v).'"';
        }
        $html .= '>'.ShowJSHint($tooltip, ['return' => true]).$title.'</td>'
            .'<td class="'.$this->css_class_td_value.'"';
        foreach ($td2_options as $k => $v) {
            $html .= ' '.$k.'="'.htmlspecialchars($v).'"';
        }
        $html .= '>';
        switch ($type) {
            case 'boolean':
                $html .= $this->getInputBoolean($input_options);
                break;
            case 'select';
                $html .= $this->getInputSelect($input_options);
                break;
            case 'select_iblock';
                $html .= $this->getSelectIblock($input_options);
                break;
            default: // text | hidden
                $html .= $this->getInputText($input_options);
                break;
        }
        $html .= '</td></tr>';


        return $html;
    }

    /**
     *
     * @param array $input_options ['name' => 'field_name', 'value' => 'field_value', 'type' => 'text', 'size' => '50']
     * @return string
     */
    public function getInputText($input_options) {
        if(empty($input_options['name'])){
            return '';
        }
        if(empty($input_options['type'])){
            $input_options['type'] = 'text';
        }
        $html .= '<input';
        foreach ($input_options as $k => $v) {
            $html .= ' '.$k.'="'.htmlspecialchars($v).'"';
        }
        $html .= ' />';
        return $html;
    }

    /**
     *
     * @param array $input_options ['name' => 'field_name', 'value' => 'field_value', 'type' => 'text', 'size' => '50']
     * @return string
     */
    public function getInputBoolean($input_options) {
        if(empty($input_options['name'])){
            return '';
        }
        $name = htmlspecialchars($input_options['name']);
        $html = '<input type="hidden" name="'.$name.'" value="N" />'
            .'<input type="checkbox" value="Y" name="'.$name.'" ';
        if($input_options['value']=='Y'){
            $html .= ' checked';
        }
        foreach ($input_options as $k => $v) {
            if($k == 'value' || $k == 'name'){
                continue;
            }
            $html .= ' '.$k.'="'.htmlspecialchars($v).'"';
        }
        $html .= ' />';
        return $html;
    }

    /**
     *
     * @param array $input_options Example: <pre>
     *  [
     *      'name' => 'field_name',
     *      'selected' => ['k2'],
     *      'options' => [ ['ID' => 'k1','NAME' => 'v1'], ['ID' => 'k2','NAME' => 'v2'] ],
     *      'options_as_key_value' => false,
     *      'option_key_for_value' => 'ID',
     *      'option_key_for_name' => 'NAME',
     *      'multiple' => 'multiple'
     *  ]
     * </pre>
     * @return string
     */
    public function getInputSelect($input_options) {
        if(empty($input_options['name'])){
            return '';
        }
        if (!is_array($input_options['selected'])) {
            $input_options['selected'] = empty($input_options['selected'])? [] : [(string)$input_options['selected']];
        }
        if (empty($input_options['option_key_for_value'])) {
            $input_options['option_key_for_value'] = 'ID';
        }
        if (empty($input_options['option_key_for_name'])) {
            $input_options['option_key_for_name'] = 'NAME';
        }
        $input_options['options_as_key_value'] = ($input_options['options_as_key_value']===true);
        $arExclude = ['selected', 'options', 'option_key_for_value', 'option_key_for_name', 'options_as_key_value'];
        $html = '<select ';
        foreach ($input_options as $k => $v) {
            if(in_array($k, $arExclude)){
                continue;
            }
            $html .= ' '.$k.'="'.htmlspecialchars($v).'"';
        }
        $html .= '>';
        foreach ($input_options['options'] as $key =>  $ar) {
            if($input_options['options_as_key_value']){
                $k = htmlspecialchars($key);
                $v = htmlspecialchars($ar);
            }else{
                $k = htmlspecialchars($ar[$input_options['option_key_for_value']]);
                $v = htmlspecialchars($ar[$input_options['option_key_for_name']]);
            }
            $html .= '<option';
            if(in_array($k, $input_options['selected'])){
                $html .= ' selected="selected"';
            }
            $html .= ' value="'.$k.'">'.$v.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     *
     * @param array $input_options Example: <pre>
     *  [
     *      'arIBlocksByTypes' => [
     *          [
     *              'IBLOCK_TYPE_ID' => 'catalog',
     *              'IBLOCK_TYPE_NAME' => 'Catalog',
     *              'IBlocks' => [
     *                  ['ID' => '1','NAME' => 'Products1'],
     *                  ['ID' => '2','NAME' => 'Products2']
     *              ],
     *          ],
     *          [
     *              'IBLOCK_TYPE_ID' => 'news',
     *              'IBLOCK_TYPE_NAME' => 'News',
     *              'IBlocks' => [],
     *          ]
     *      ],
     *      'iblock_type_id' => ['name' => 'IBLOCK_TYPE_ID', 'selected' => 'catalog'],
     *      'iblock_id' => ['name' => 'IBLOCK_ID', 'selected' => 1],
     *      'isDisabled' => false,
     *      'selectorJquery' => '#adm-workarea'
     *  ]
     * </pre>
     * @return string
     */
    public function getSelectIblock($input_options) {
        if (empty($input_options['arIBlocksByTypes']) || !is_array($input_options['arIBlocksByTypes'])) {
            return '';
        }
        $iblock_type_id_name = (string)$input_options['iblock_type_id']['name'];
        if (empty($iblock_type_id_name)) {
            $iblock_type_id_name = 'IBLOCK_TYPE_ID';
        }
        $iblock_id_name = (string)$input_options['iblock_id']['name'];
        if (empty($iblock_id_name)) {
            $iblock_id_name = 'IBLOCK_ID';
        }
        $iblock_type_id_selected = (string)$input_options['iblock_type_id']['selected'];
        $iblock_id_selected = intval($input_options['iblock_id']['selected']);
        $isDisabled = ($input_options['isDisabled']===true);
        $selectorJquery = (string)$input_options['selectorJquery'];

        $disabled = ($isDisabled? ' disabled ' : '');
        $html = '<select class="IBLOCK_TYPE_ID" name="'.$iblock_type_id_name.'" '.$disabled.'>';
        $jsIBlocks = '';
        $optionsIBlock = '';
        foreach ($input_options['arIBlocksByTypes'] as $arIBlockType) {
            if (empty($arIBlockType['IBlocks'])) {
                continue;
            }
            $html .= '<option value="'.$arIBlockType['IBLOCK_TYPE_ID'].'" '
                . ($arIBlockType['IBLOCK_TYPE_ID'] == $iblock_type_id_selected?' selected="selected"':'').'>'
                . '['.$arIBlockType['IBLOCK_TYPE_ID'].'] '.$arIBlockType['IBLOCK_TYPE_NAME'].'</option>';
            $str = '';
            foreach ($arIBlockType['IBlocks'] as $arIBlock) {
                $str .= '<option value="'.$arIBlock['ID'].'">['.$arIBlock['ID'].'] '.$arIBlock['NAME'].'</option>';
                if($arIBlockType['IBLOCK_TYPE_ID'] == $iblock_type_id_selected){
                    $optionsIBlock .= '<option value="'.$arIBlock['ID'].'" '
                        .($arIBlock['ID'] == $iblock_id_selected?' selected="selected"':'')
                        .'>['.$arIBlock['ID'].'] '.$arIBlock['NAME'].'</option>';
                }
            }
            if(!$isDisabled){
                $jsIBlocks .= "arIBlocks['".$arIBlockType['IBLOCK_TYPE_ID']."'] = '<select name=\"".$iblock_id_name."\">$str</select>';\n";
            }
        }
        $html .= '</select><span class="IBLOCK_ID">';
        if (!empty($optionsIBlock)) {
            $html .= '<select name="'.$iblock_id_name.'"'.$disabled.'>'.$optionsIBlock.'</select>';
        }
        $html .=  '</span>';
        if(!$isDisabled){
            $html .=  '<script type="text/javascript">
                $(function() {
                    var arIBlocks = {};
                   '.$jsIBlocks.'
                    $(\''.$selectorJquery.' select[name="'.$iblock_type_id_name.'"]\').change(function(){
                        var val = $(this).val(),
                            str = arIBlocks[val];
                        if(str == undefined){
                            str = "";
                        }
                        $(\''.$selectorJquery.' .IBLOCK_ID\').html(str);
                        $(\''.$selectorJquery.' select[name="'.$iblock_id_name.'"]\').change();
                    });
                });
                </script>';
        }
        return $html;
    }
}
