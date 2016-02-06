<?php
#/root>
if(class_exists('HW_HOANGWEB')) HW_HOANGWEB::load_fieldtype('APF_hw_condition_rules');  //load query rules apf field

if(class_exists('AdminPageFramework_MetaBox')):
class HW_Conditions_Metabox extends AdminPageFramework_MetaBox {
    static private $instance;   //current class instance

    /**
     * set class instance
     * @param $inst: an object instanceof this class
     */
    static public function setInstance($inst){
        if($inst instanceof HW_Conditions_Metabox){
            self::$instance = $inst;
        }
    }
    /**
     * return once instance for this class
     * @return mixed
     */
    static public function getInstance(){
        return self::$instance;
    }

    /**
     * prepare actions hooks
     */
    protected function setup_actions() {
        add_filter('APF_hw_condition_rules-types', array($this, '_get_rules_types'));
        add_filter('APF_hw_condition_rules-compare-operators', array($this, '_get_compare_operators'));

    }
    /**
     * display something
     */
    public function do_HW_Conditions_Metabox() {
        echo '<p>Cài đặt cấu hình điều kiện động.</p>';

    }
    /**
     * pre-defined validation callback method
     * @param $sInput
     * @param $sOldInput
     * @return mixed
     */
    public function validation_HW_Conditions_Metabox( $sInput, $sOldInput ) {
        //  delete transient when add new one or modify exists post
        delete_transient('hw_dynamic_conditions_settings');
        //if(!isset($_SESSION['skin_options']) && isset($sInput['skin_options'])) unset($sInput['skin_options']);
        return $sInput;
    }

    /**
     * @hook APF_hw_condition_rules: apf_check_fields_condition
     * @param $result
     * @param $params
     * @return mixed
     */
    public static function _apf_check_fields_condition($result, $params) {
        if($params['match_page'] && isset($and_bind['plugins'])) {
            $result['plugins'] = (is_plugin_active($and_bind['plugins']['act_values']) && $and_bind['plugins']['compare'] == 'actived') || (!is_plugin_active($and_bind['plugins']['act_values']) && $and_bind['plugins']['compare'] == 'deactived');
        }
        return $result;
    }
    /**
     * @hook apf_rules_field_get_values
     * @param $field
     * @param $name
     * @return mixed
     */
    public static function _apf_rules_field_get_values($field, $name) {
        return $field;
    }
    /**
     * @hook apf_rules_field_settings
     * @param $args
     * @param $object
     */
    public static function _apf_rules_field_settings($args, $object) {
        //list all actived plugins
        if($object == 'plugins') {
            $active_plugins = get_option('active_plugins');
            $args['field']['options'] = $active_plugins;
        }
        return $args;
    }
    /**
     * @param $types
     * @return mixed
     */
    public function _get_rules_types($types) {
        $types['plugins'] = __('Plugins');
        return $types;
    }

    /**
     * @param $values
     */
    public function _get_compare_operators($values) {
        $values['actived'] = __("Kích hoạt (plugin)");
        $values['deactived'] = __("Không kích hoạt (plugin)");
        return $values;
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        //init hooks
        $this->setup_actions();

        /*$template_files =  get_page_templates();   //get all available templates file
        $templates_field_data = array('' => '--- Chọn ---');  //list all templates select tag

        //prepare fields
        foreach($template_files as $name=>$file) {
            $templates_field_data[$file] = $name;
        }*/

        $this->addSettingFields(
        //'general',
            array(
                'field_id' => 'enable',
                'type' => 'checkbox',
                'title' => 'Kích hoạt',
                'description' => 'Kích hoạt điều kiện.'
            ),
            array(
                'field_id' => 'query_data_and',
                'type' => 'hw_condition_rules',
                'title' => __('Dàng buộc AND'),
                'description' => 'Thêm điều kiện lọc AND.',
                'repeatable' => true,

            ),
            array(
                'field_id' => 'query_data_or',
                'type' => 'hw_condition_rules',
                'title' => __('Dàng buộc OR'),
                'description' => 'Thêm điều kiện lọc OR.',
                'repeatable' => true
            )
            /*array(
                'field_id' => 'template',
                'type' => 'select',
                'title' => "Template",
                'description' => 'Chọn template chuyển hướng. Chú ý: các file template khai báo tên, nằm trong thư mục theme hiện tại và có thể nằm thư mục con.',
                'label' => &$templates_field_data
            )*/
        );
        if(isset($apf_fields) && count($apf_fields)) {
            foreach($apf_fields as $field_setting) {
                $this->addSettingField($field_setting);
            }
        }
        // content_{page slug}_{tab slug}
        //add_filter( 'content_hw_conditions_settings', array( $this, 'replyToInsertContents' ) );
    }
    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );
        $btn ='';
        return $sContent. $btn;
    }
}
endif;

if(is_admin()) {
    //assign new field type to the metabox
    //if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_Conditions_Metabox');
    if(class_exists('APF_hw_condition_rules')) new APF_hw_condition_rules('HW_Conditions_Metabox');

    add_filter('apf_rules_field_get_values', 'HW_Conditions_Metabox::_apf_rules_field_get_values' ,1, 2);
    add_filter('apf_rules_field_settings',  'HW_Conditions_Metabox::_apf_rules_field_settings' ,1, 2);
    add_filter('apf_check_fields_condition',  'HW_Conditions_Metabox::_apf_check_fields_condition' ,1, 2);
}
