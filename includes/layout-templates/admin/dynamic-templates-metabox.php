<?php
#/root>
if(class_exists('HW_HOANGWEB')) HW_HOANGWEB::load_fieldtype('APF_hw_condition_rules');  //load query rules apf field

if(class_exists('AdminPageFramework_MetaBox')):
class HW_Templates_Metabox extends AdminPageFramework_MetaBox {
    static private $instance;   //current class instance

    /**
     * set class instance
     * @param $inst: an object instanceof this class
     */
    static public function setInstance($inst){
        if($inst instanceof HW_Templates_Metabox){
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
     * display something
     */
    public function do_HW_Templates_Metabox() {
        echo '<p>Cài đặt cấu hình templates động.</p>';

    }
    /**
     * pre-defined validation callback method
     * @param $sInput
     * @param $sOldInput
     * @return mixed
     */
    public function validation_HW_Templates_Metabox( $sInput, $sOldInput ) {
        //  delete transient when add new one or modify exists post
        delete_transient('hw_dynamic_templates_settings');
        //if(!isset($_SESSION['skin_options']) && isset($sInput['skin_options'])) unset($sInput['skin_options']);
        return $sInput;
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        $template_files =  get_page_templates();   //get all available templates file
        $templates_field_data = array('' => '--- Chọn ---');  //list all templates select tag

        //prepare fields
        foreach($template_files as $name=>$file) {
            $templates_field_data[$file] = $name;
        }
        $this->addSettingFields(
        //'general',
            array(
                'field_id' => 'enable',
                'type' => 'checkbox',
                'title' => 'Kích hoạt',
                'description' => 'Kích hoạt thay đổi template.'
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
            ),
            array(
                'field_id' => 'template',
                'type' => 'select',
                'title' => "Template",
                'description' => 'Chọn template chuyển hướng. Chú ý: các file template khai báo tên, nằm trong thư mục theme hiện tại và có thể nằm thư mục con.',
                'label' => &$templates_field_data
            )
        );
        if(isset($apf_fields) && count($apf_fields)) {
            foreach($apf_fields as $field_setting) {
                $this->addSettingField($field_setting);
            }
        }
        // content_{page slug}_{tab slug}
        //add_filter( 'content_hw_templates_settings', array( $this, 'replyToInsertContents' ) );
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
/**
 * Class KIUHG
 */
if(class_exists('AdminPageFramework_MetaBox', false) ):
class HW_DynamicTemplate_MetaBox extends AdminPageFramework_MetaBox {
    public function setUp() {
        global $wp_query;

        $this->addSettingFields(
        //'general',
            array(
                'field_id'      => 'template_tag',
                'type'          => 'label',
                'title'         => __('Template Tag'),
                'description'   => __(''),     #description that will be placed below the input field
                'label' => ' '

            )
        );
    }
}
endif;
if(is_admin()) {
    //assign new field type to the metabox
    //if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_Templates_Metabox');
    if(class_exists('APF_hw_condition_rules')) new APF_hw_condition_rules('HW_Templates_Metabox');
}
