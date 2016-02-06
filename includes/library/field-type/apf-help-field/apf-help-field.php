<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 17/10/2015
 * Time: 09:56
 */
if ( ! class_exists( 'APF_hw_help_fields' ) && class_exists('HW_APF_FormField')) :
class APF_hw_help_fields extends HW_APF_FormField {
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_help' );
    public $aDefaultKeys = array('show_title_column' => false,);
    /**
     * @var array
     */
    private $data = array();

    /**
     * constructor
     * @param $class
     */
    public function __construct($class){
        parent::__construct($class, null);//$t=($this->getDefinitionArray('hw_help')); _print($t['aDefaultKeys']);

        #add_action('wp_ajax_hw_help_popup', array());
        //add_filter('field_types_HW_Module_Settings_page', array($this, '___replyToRegisterInputFieldType'));
    }
    public function ___replyToRegisterInputFieldType($a){
        return $a;
    }
    /**
     * enqueue scripts/stylesheets
     * @param array $aField
     */
    private function enqueue_scripts($module, $aField) {
        $this->data['help_url'] = admin_url('admin-ajax.php?action=hw_help_popup');
        $this->data['nonce'] = wp_create_nonce('hw-module-help-nonce');

        wp_enqueue_script('hw-module-help-script', plugins_url('js.js', __FILE__), array('jquery'));
        wp_localize_script('hw-module-help-script' ,'__hw_modules_help', $this->data);
    }

    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return ".admin-page-framework-input-label-container.hw_more_fields { padding-right: 2em; }
            .hw-module-help{}
            .apf-fieldrow-hw-module-help *{margin:0px !important;padding:0px !important;}
            .admin-page-framework-section-table {position:relative;}
            .apf-fieldrow-hw-module-help {position: absolute;text-align: right;}
        ";
    }
    /**
     * return field type specific inline js
     * @return string
     */
    protected function getScripts() {
        //$this->prepare_actions();   //actions
        return '
            jQuery(document).ready(function(){
                jQuery(".hw-module-help").closest(".admin-page-framework-fieldrow").addClass("apf-fieldrow-hw-module-help");
                jQuery(".hw-module-help").closest("div").css({"float":"right"});
            });
        ';
    }
    /**
     * Returns the output of the field type.
     * @param $aField
     */
    protected function getField( $aField ) {
        $this->init($aField);   //init field
        $module = isset($aField['module'])? $aField['module'] : '';
        if(!$module) return 'Lỗi code.' .__FILE__;

        $this->enqueue_scripts($module, $aField);    //put stuffs
        return
            $aField['before_label']
            . $this->generate_help_icon($module, $aField )
            #.'XX'
            . $aField['after_label'];
    }

    /**
     * display help icon
     * @param $module
     * @param $aField
     */
    private function generate_help_icon($module, $aField) {
        $module_label = isset($aField['module_info']['name'])? $aField['module_info']['name'] : $module->module_name;
        $help = $aField['hw_help'];
        if(empty($help)) return ;

        $help_file = HW_HELP::get_help_popup_file(array($help['class'], $help['file']));

        $_aAttributes = $aField['attributes'];
        $name = $_aAttributes['name'];
        $id = HW_Validation::valid_apf_slug($name);

        $html = '<a href="#" id="'.$id.'" data-hw-module="'.$module->module_name.'" data-hw-help-file="'.urlencode(HW_Encryptor::encrypt($help_file)).'" title="'.$module_label.'"><img src="'.plugins_url('help_icon.png', __FILE__).'" class="module-help-icon hw-module-help"/>Trợ giúp</a>';
        return $html;
    }
}
endif;