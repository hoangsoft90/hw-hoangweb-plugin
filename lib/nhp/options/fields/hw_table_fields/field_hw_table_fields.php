<?php
/**
 * Class NHP_hw_table_fields
 * cover from field type APF hw table fields
 */
class NHP_Options_hw_table_fields extends NHP_Options {
    /**
     * default field args
     * @var array
     */
    private $aDefaultKeys = array();

    /**
     * Field Constructor.
     *
     * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
     *
     * @since NHP_Options 1.0
     */
    public function __construct($field = array(), $value ='', $parent){
        parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
        $this->field = $field;
        $this->value = $value;

        $this->aDefaultKeys = array(
            'data_base_field' => '',
            'show_root_field' => true, //ddslick setting

        );
        $aField = $this->field;    //$this->field;

        $this->prepare_actions();   //actions
    }
    /**
     * get field value
     * @param $fname
     * @param $default: default value if not found
     */
    function get_field_value($fname, $default = ''){
        $aField = $this->field;    //$this->field;
        if(isset($aField[$fname]) ) {
            $value = apply_filters('nhp_table_fields_field',$aField[$fname],$fname);
        }
        elseif(isset($this->aDefaultKeys[$fname])) $value = $this->aDefaultKeys[$fname];
        return isset($value)? $value : $default;
    }
    /**
     * add hooks
     */
    private function prepare_actions() {
        add_filter('nhp_table_column_field', array($this, '_nhp_table_column_field'), 10, 3);
    }
    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since NHP_Options 1.0
     */
    function render(){
        //$display = $this->get_field_value('display','select');  //picker style
        $class = (isset($this->field['class']))? $this->field['class'] : '';

        $field_name = $this->args['opt_name'].'['.$this->field['id'].']';   //base field name
        $attributes = array(
            'id' => $this->field['id'],
            'name' => $field_name,
            'class' => $class,

        );
        //$_aAttributes = $aAttributes+ $_aAttributes;
        $_aOutput[] = $this->renderTableRow($this->field, $attributes);
        echo implode("\n", $_aOutput);
        echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>' : '';
    }
    /**
     * filter nhp-table-field column
     * @param $field
     * @param $value
     * @param $field_attrs
     * @param $_aAttributes
     */
    public function _nhp_table_column_field($s, $id, $args ) {
        if($args['aField']['id'] !== $id) return ;    //detect current field id, ignore for other

        //add attributes
        preg_match('/\[(\d+)\]/', $args['aAttributes']['name'], $res);  //find id
        if(count($res)>=2 ) {
            if(!isset($args['field_attrs']['data-id'])) $args['field_attrs']['data-id'] = $args['aField']['id'].'__'.$res[1];
            if(!isset($args['field_attrs']['data-name'])) $args['field_attrs']['data-name'] = $args['field']['name'];
            if(!isset($args['field_attrs']['data-fieldname'])) $args['field_attrs']['data-fieldname'] = $args['aField']['id'];
        }
    }


    /**
     * render table row
     * @param $aField
     * @param $_aAttributes
     */
    private function renderTableRow( $aField, $_aAttributes){
        //valid
        if(!isset($aField['fields']) || !is_array($aField['fields'])) return;
        //whether show root field
        $show_root = !isset($aField['show_root_field'])? false : $aField['show_root_field'];

        //get data of rows
        if(isset($aField['data_base_field']) && isset($aField['fields'][$aField['data_base_field']])) {
            if(isset($aField['fields'][$aField['data_base_field']]['options'])) {
                $rows = $aField['fields'][$aField['data_base_field']]['options'];
            }

        }
        if(empty($rows)) $rows = array(1);
        //get headers
        //if(isset($aField['table_header'])) $headers = $aField['table_header'];
        if(!isset($aField['table_header'])) $aField['table_header'] = array_keys($aField['fields']);

        if(!empty($aField['table_header']) && is_array($aField['table_header'])){
            $header_cols = '<tr>';
            foreach($aField['table_header'] as $label) {
                //remove label if is numeric
                if(is_numeric($label)) $label = '';
                $header_cols .= '<td>'.$label.'</td>';
            };
            $header_cols .= '<tr>';
        }
        //prepare fields row
        $fields_row = array();

        foreach(array_keys($rows) as $key ){
            $fields_row[] = '<tr class="" data-key="'.$key.'">';
            foreach(array_keys($aField['table_header']) as $field_id){
                //row class
                $class = 'hw-nhp-table-fields-'. $aField['id'].'-'.$field_id;
                //foreach($aField['fields'] as $field) {
                //get field setting
                if(is_numeric($field_id)) $field = array_shift($aField['fields']);
                elseif(isset($aField['fields'][$field_id])){
                    $field = $aField['fields'][$field_id];
                }
                //data row base root field
                if(isset($aField['data_base_field'])){
                    //except root field slug
                    if($field_id == $aField['data_base_field'] && $show_root){
                        $fields_row[] = '<td class="'.$class.'">'.$key.'</td>';
                    }
                    elseif(!empty($field)) $fields_row[] = '<td>'.$this->renderField ($field, $key, $aField,$_aAttributes).'</td>';
                }
                else $fields_row[] = '<td class="'.$class.'">'.$this->renderField ($field, false, $aField,$_aAttributes).'</td>';  //non-root peer to peer

            }
            $fields_row[] = '</tr>';
        }

        $output = "
        <table border='1' class='hw-table hw-nhp-table-fields'>
            {$header_cols}
            ".implode('',$fields_row)."
        </table>
        ";
        return $output;
    }

    /**
     * valid field id from their name or any give string
     * @param $str
     */
    private static  function generate_id($str){
        return class_exists('HW_Validation') ? HW_Validation::valid_objname($str) : $str;
    }

    /**
     * extend from APF framework
     * @return array
     */
    public static function uniteArrays() {
        $_aArray = array();
        foreach (array_reverse(func_get_args()) as $_aArg) {
            $_aArray = self::uniteArraysRecursive(self::getAsArray($_aArg), $_aArray);
        }
        return $_aArray;
    }
    static public function getAsArray($asValue) {
        if (is_array($asValue)) {
            return $asValue;
        }
        if (!isset($asValue)) {
            return array();
        }
        return ( array )$asValue;
    }
    public static function uniteArraysRecursive($aPrecedence, $aDefault) {
        if (is_null($aPrecedence)) {
            $aPrecedence = array();
        }
        if (!is_array($aDefault) || !is_array($aPrecedence)) {
            return $aPrecedence;
        }
        foreach ($aDefault as $sKey => $v) {
            if (!array_key_exists($sKey, $aPrecedence) || is_null($aPrecedence[$sKey])) $aPrecedence[$sKey] = $v;
            else {
                if (is_array($aPrecedence[$sKey]) && is_array($v)) {
                    $aPrecedence[$sKey] = self::uniteArraysRecursive($aPrecedence[$sKey], $v);
                }
            }
        }
        return $aPrecedence;
    }
    static public function generateAttributes(array $aAttributes) {
        $_sQuoteCharactor = "'";
        $_aOutput = array();
        foreach ($aAttributes as $_sAttribute => $_vProperty) {
            if (in_array(gettype($_vProperty), array('array', 'object', 'NULL'))) {
                continue;
            }
            $_aOutput[] = "{$_sAttribute}={$_sQuoteCharactor}" . esc_attr($_vProperty) . "{$_sQuoteCharactor}";
        }
        return implode(' ', $_aOutput);
    }

    /**
     * render Field
     * @param $field
     * @param $key
     * @param $_aAttributes
     * @return string
     */
    private function renderField($field, $key,$aField, $_aAttributes) {
        $out = '';
        if($key !== false) $field_base = $_aAttributes['name'].'['.$key.']';
        else $field_base = $_aAttributes['name'];

        $name = $field_base.'['.$field['name'].']';
        $id = self::generate_id($name);

        $class = $field['name'];    //generate field class
        //$_aSelectTagAttributes = $this->uniteArrays($aField['attributes'][$aField['type']], array('id' => $aField['input_id'], 'name' => $aField['_input_name'], 'data-id' => $aField['input_id'],));
        $field_attrs = $this->uniteArrays(isset($field['attributes'])? $field['attributes'] : array(), array('name' => $name, 'id' => $id, 'class' => $class));

        //get field value
        if($key !==false) $value = isset($this->value[$key][$field['name']])? $this->value[$key][$field['name']] : '';
        else $value = isset($this->value[$field['name']])? $this->value[$field['name']] : '';

        //filter field setting
        //$field = apply_filters('nhp_table_column_field', $field, $value,$field_attrs, $_aAttributes);
        $args = array(
            'aField' => &$aField,
            'field' => &$field,
            'field_attrs' => &$field_attrs,
            'aAttributes' => $_aAttributes,
            'value' => $value
        );
        apply_filters('nhp_table_column_field',0, $aField['id'], $args);

        //other field data
        $description = isset($field['description']) ? '<p><em>'.$field['description'].'</em></p>' : '';

        if($field['type'] == 'text') {  //input tag
            $field_attrs['value'] = $value;
            $out .= '<label><input type="text" '.$this->generateAttributes($field_attrs).'/></label>';
            $out .= $description;
        }
        elseif($field['type'] == 'textarea') {  //textarea

            $out .= '<label><textarea '.$this->generateAttributes($field_attrs).'>'.$value.'</textarea></label>';
            $out .= $description;
        }
        elseif($field['type'] == 'checkbox') {  //checkbox field

            if(!empty($field['options']) && is_array($field['options'])) {
                foreach ($field['options'] as $key => $val) {
                    $old_name = $field_attrs['name'];

                    $field_attrs['name'] .= "[{$key}]";
                    if(is_array($value) && in_array($key, array_keys((array)$value) ) && isset($value[$key])) {
                        $field_attrs['checked'] = 'checked';
                    }

                    $out .= '<label class="'.$class.'"><input  type="checkbox" '.$this->generateAttributes($field_attrs).'/> '. $val .'</label>';

                    //reset
                    if(isset($field_attrs['checked'])) unset($field_attrs['checked']);
                    $field_attrs['name'] = $old_name;   //resume old name for next field
                }
            }
            else {
                if($value == 'on') $field_attrs['checked'] = 'checked';
                $out .= '<label class="'.$class.'"><input type="checkbox" '.$this->generateAttributes($field_attrs).' /></label>';
            }
            $out .= $description;
        }
        elseif($field['type'] == 'select') {    //select tag
            if(is_string($field['options'])) $field['options'] = explode(',',$field['options']);

            $out .= '<select '.$this->generateAttributes($field_attrs).'>';
            if(is_array($field['options'])) {
                foreach($field['options'] as $name => $text) {
                    //if(is_numeric($name)) $name = $text;
                    $selected = ($name == $value)? 'selected="selected"' : '';
                    $option_id = $id.'_'.$name;

                    $out .= '<option id="'.$option_id.'" value="'.$name.'" '.$selected.'>'.$text.'</option>';
                }
            }
            $out .= '</select>';
            $out .= $description;   //field desc
        }
        elseif($field['type'] == 'custom') {    //custom
            if(isset($field['callback']) && is_callable($field['callback'])) {
                $args = array(
                    'attributes' => $field_attrs,
                    'field' =>$field,
                    'value' => $value,
                    'field_basename' => $field_base,
                    'values' => isset($aField['attributes']['value'])? $aField['attributes']['value'] : array(),
                    'aField' => $aField
                );
                $out .= call_user_func($field['callback'], $args, $this);
            }
            $out .= $description;   //field description
        }
        else $out .= '';
        return $out;
    }

    /**
     *
     * generate attributes -> you don't need clone from orgin method generateAttributes(..)
     * @param array $atts
     */
    static public function _generateAttributes(array $aAttributes) {
        $_sQuoteCharactor = "'";
        $_aOutput = array();
        foreach ($aAttributes as $sAttribute => $sProperty) {
            if (is_array($sProperty) || is_object($sProperty) ) {
                continue;
            }
            $_aOutput[] = "{$sAttribute}={$_sQuoteCharactor}{$sProperty}{$_sQuoteCharactor}";
        }
        return implode(' ', $_aOutput);
    }
}