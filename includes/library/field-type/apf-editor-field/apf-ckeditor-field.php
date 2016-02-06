<?php
/**
 * APF_hw_editor_field
 */
if ( ! class_exists( 'APF_hw_ckeditor_field' ) && class_exists('HW_APF_FormField')) :
class APF_hw_ckeditor_field extends HW_APF_FormField {
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_ckeditor');
    public $aDefaultKeys = array('show_title_column' => true,);
    public $aFields = null;
    /**
     * constructor
     * @param $class
     */
    public function __construct($class){
        parent::__construct($class, null);

        HW_Libraries::enqueue_jquery_libs('ckeditor');  //load ckeditor lib
        $this->enqueue_assets();
    }

    /**
     * enqueue scripts/styles
     */
    public function enqueue_assets() {
        #wp_enqueue_script('ckeditor-config-js' , plugins_url('assets/config.js', __FILE__));
    }
    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return ".admin-page-framework-input-label-container.hw_more_fields { padding-right: 2em; }
            .hw-editor{}

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
        //$this->aField = $aField;
        $this->init($aField);
        return
            $aField['before_label']
            . $aField['before_input']
            . "<div class='repeatable-field-buttons'></div>"	// the repeatable field buttons will be replaced with this element.
            . $this->_getInputs( $aField )
            #.'XX'
            . $aField['after_input']
            . $aField['after_label'];
    }
    /**
     * @param $aField
     * @return string
     */
    private function _getInputs( $aField ) {
        $_aOutput = array();
        //ckeditor config
        $config = array(
            'language' => 'vi', 'uiColor' => '#9AB8F3'
        );
        if(isset($aField['config']) && is_array($aField['config'])) {
            $config = array_merge($config, $aField['config']);
        }

        $_aAttributes = $aField['attributes'];
        //$description = isset( $aField['description'])? $aField['description'] : '';    //field description

        //$_aAttributes = $aAttributes+ $_aAttributes;
        $name = $_aAttributes['name'];
        $id = HW_Validation::valid_apf_slug($name);
        $value = isset($aField['attributes']['value'])? $aField['attributes']['value'] : '';

        $_aOutput[] = '<textarea id="'.$id.'" name="'.$name.'" class="" rows="6" >'.esc_attr($value).'</textarea>';

        $_aOutput[] =
            "<div class='admin-page-framework-input-label-container my_custom_field_type'>"
            . "<span class='admin-page-framework-input-label-string' style=''>"
            . "</span>" . PHP_EOL
//<input ".$this->generateAttributes($aField['attributes'])."/>
            //."<input type='hidden' name='{$_aAttributes['name']}[hwskin_config]' id='{$skin_config_field_id}' value='".($skin_config_value)."'/>"
            #. "</label>"
            . "</div>";
        $_aOutput[] = '<script type="text/javascript">
        jQuery(function(){
                CKEDITOR.config.extraPlugins = "hw_wp_media_button";
                // Replace the <textarea id="editor1"> with a CKEditor
                // instance, using default configuration.
                CKEDITOR.replace( "'.$id.'" ,'.json_encode($config).');
            });
            </script>
        ';

        return implode( PHP_EOL, $_aOutput );
    }
}
endif;