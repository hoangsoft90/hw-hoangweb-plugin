<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 21:23
 */
//include admin table ui
HW_HOANGWEB::load_class('HW_List_Table');

if ( ! class_exists( 'APF_hw_admin_table' ) && class_exists('HW_APF_FormField')) :
class APF_hw_admin_table extends HW_APF_FormField {
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_admin_table', 'hw_admin_tables');
    public $aDefaultKeys = array();

    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return ".admin-page-framework-input-label-container.hw_more_fields { padding-right: 2em; }

        ";
    }
    /**
     * return field type specific inline js
     * @return string
     */
    protected function getScripts() {
        return "";
    }
    /**
     * Returns the output of the field type.
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
     * @param $aField
     * @return string
     */
    private function _getInputs( $aField ) {
        $_aOutput = array();

        $_aAttributes = $aField['attributes'];

        if(!empty($aField['WP_List_Table']) && class_exists($aField['WP_List_Table'])) {
            $List_Table_class = $aField['WP_List_Table'];
        }
        else $List_Table_class = 'HW_List_Table';

        //Prepare Table of elements
        $wp_list_table = new $List_Table_class();

        //set columns
        if(isset($aField['columns']) && is_array($aField['columns'])) {
            $wp_list_table->_set_columns($aField['columns']);
        }
        //set sortable columns
        if(isset($aField['sortable_columns']) && is_array($aField['sortable_columns'])) {
            $wp_list_table->_set_sortable_columns($aField['sortable_columns']);
        }

        $wp_list_table->prepare_items();    //fetch the data to your table:

        ob_start();
        //display Table of elements
        $wp_list_table->display();

        $content = ob_get_contents();
        if($content && ob_get_length()) ob_end_clean();

        //$_aAttributes = $aAttributes+ $_aAttributes;
        $_aOutput[] = $content;
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
}
endif;