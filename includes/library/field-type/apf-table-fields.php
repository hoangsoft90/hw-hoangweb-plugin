<?php
/**
 * Class APF_hw_table_fields
 */
if ( ! class_exists( 'APF_hw_table_fields' ) && class_exists('HW_APF_FormField')) :
class APF_hw_table_fields extends HW_APF_FormField {
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_table_fields', 'hw_more_fields');
    /**
     * default params
     * @var array
     */
    public $aDefaultKeys = array('show_root_field' => true,);

    /**
     * constructor
     * @param $class
     */
    public function __construct($class){
        parent::__construct($class, null);
        $this->prepare_actions();   //actions
    }
    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return ".admin-page-framework-input-label-container.hw_more_fields { padding-right: 2em; }
            .hw-table.hw-apf-table-fields {max-width:650px;}
            .hw-table.hw-apf-table-fields,.hw-table.hw-apf-table-fields th ,.hw-table.hw-apf-table-fields td {
                border: 1px solid grey;
                border-collapse: collapse;
                padding: 5px;
            }
            .hw-table.hw-apf-table-fields tr:nth-child(odd) {
                background-color: #f1f1f1;
            }
            .hw-table.hw-apf-table-fields tr:nth-child(even) {
                background-color: #ffffff;
            }
            .hw-apf-table-fields select{max-width:200px;}
        ";
    }

    /**
     * return field type specific inline js
     * @return string
     */
    protected function getScripts() {
        //$this->prepare_actions();   //actions
        return "";
    }

    /**
     * add hooks
     */
    private function prepare_actions() {
        add_filter('apf_table_column_field', array($this, '_apf_table_column_field'), 10, 3);
    }
    /**
     * Returns the output of the field type.
     * @param $aField
     */
    protected function getField( $aField ) {
        //$this->__aField = $aField;
        return
            $aField['before_label']
            . $aField['before_input']
            . "<div class='repeatable-field-buttons'></div>"	// the repeatable field buttons will be replaced with this element.
            . $this->_getInputs( $aField )
            //.'XX'
            . $aField['after_input']
            . $aField['after_label'];
    }

    /**
     * filter apf-table-field column
     * @param $field
     * @param $value
     * @param $field_attrs
     * @param $_aAttributes
     */
    public function _apf_table_column_field($s, $id, $args ) {
        if($args['aField']['field_id'] !== $id) return ;    //detect current field id, ignore for other

        //add attributes
        preg_match('/\[(\d+)\]/', $args['aAttributes']['name'], $res);  //find id
        if(count($res)>=2 ) {
            if(!isset($args['field_attrs']['data-id'])) $args['field_attrs']['data-id'] = $args['aField']['field_id'].'__'.$res[1];
            if(!isset($args['field_attrs']['data-name'])) $args['field_attrs']['data-name'] = $args['field']['name'];
            if(!isset($args['field_attrs']['data-fieldname'])) $args['field_attrs']['data-fieldname'] = $args['aField']['field_id'];
        }
    }
    /**
     * @param $aField
     * @return string
     */
    private function _getInputs( $aField ) {
        $_aOutput = array();

        $_aAttributes = $aField['attributes'];
        //$description = isset( $aField['description'])? $aField['description'] : '';    //field description

        //$_aAttributes = $aAttributes+ $_aAttributes;
        $_aOutput[] = $this->renderTableRow($aField, $_aAttributes);

        /*$_aOutput[] =
            "<div class='admin-page-framework-input-label-container my_custom_field_type'>"
            . "<span class='admin-page-framework-input-label-string' style=''>"
            . "</span>" . PHP_EOL
//<input ".$this->generateAttributes($aField['attributes'])."/>
            //."<input type='hidden' name='{$_aAttributes['name']}[hwskin_config]' id='{$skin_config_field_id}' value='".($skin_config_value)."'/>"
            . "</label>"
            . "</div>";*/

        return implode( PHP_EOL, $_aOutput );
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
                $class = 'hw-apf-table-fields-'. $aField['field_id'].'-'.$field_id;
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
        <table border='1' class='hw-table hw-apf-table-fields'>
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
        $field_attrs = $this->uniteArrays(isset($field['attributes'])? $field['attributes'] : array(), array('name'=>$name, 'id'=>$id, 'class'=>$class));

        //get field value
        if($key !==false) $value = isset($aField['attributes']['value'][$key][$field['name']])? $aField['attributes']['value'][$key][$field['name']] : '';
        else $value = isset($aField['attributes']['value'][$field['name']])? $aField['attributes']['value'][$field['name']] : '';

        //filter field setting
        //$field = apply_filters('apf_table_column_field', $field, $value,$field_attrs, $_aAttributes);
        $args = array(
            'aField' => &$aField,
            'field' => &$field,
            'field_attrs' => &$field_attrs,
            'aAttributes' => $_aAttributes,
            'value' => $value
        );
        apply_filters('apf_table_column_field',0, $aField['field_id'], $args);

        //other field data
        $description = isset($field['description']) ? '<p><em>'.$field['description'].'</em></p>' : '';

        if($field['type'] == 'text') {  //input tag
            $field_attrs['value'] = $value;
            $out .= '<label><input type="text" '.$this->generateAttributes($field_attrs).'/></label>';
            $out .= $description;
        }
        elseif($field['type'] == 'textarea') {  //textarea tag
            //$field_attrs['value'] = $value;
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
endif;