<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 18/10/2015
 * Time: 19:45
 */
/**
 * Class APF_hw_html_field
 */
if ( ! class_exists( 'APF_hw_html_field' ) && class_exists('HW_APF_FormField')) :
class APF_hw_html_field extends HW_APF_FormField {
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_html' );
    public  $aDefaultKeys = array('show_title_column' => false,);

    /**
     * constructor
     * @param $class
     */
    public function __construct($class){
        parent::__construct($class, null);//$t=($this->getDefinitionArray('help')); _print($t['aDefaultKeys']);
    }
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
        //$this->prepare_actions();   //actions
        return '

        ';
    }
    /**
     * Returns the output of the field type.
     * @param $aField
     */
    protected function getField( $aField ) {
        $output = '';
        if(isset($aField['output_callback']) && is_callable($aField['output_callback'])) {
            ob_start();
            $output .= (string)call_user_func($aField['output_callback'], $aField);
            $output .= ob_get_contents();
            if(ob_get_contents() && ob_get_length()) ob_end_clean();
        }
        return
            $aField['before_label']
            . $output
            . $aField['after_label'];
    }
}
endif;