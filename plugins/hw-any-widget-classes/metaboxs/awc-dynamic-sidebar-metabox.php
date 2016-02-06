<?php
#/root>includes/awc-dynamic-sidebars.php
if(class_exists('HW_HOANGWEB')) HW_HOANGWEB::load_fieldtype('APF_hw_condition_rules');  //load query rules apf field

if(class_exists('AdminPageFramework_MetaBox')):
class HWAWC_ModifySidebar_Metabox extends AdminPageFramework_MetaBox {
    static private $instance;   //current class instance

    /**
     * set class instance
     * @param $inst: an object instanceof this class
     */
    static public function setInstance($inst){
        if($inst instanceof HWAWC_ModifySidebar_Metabox){
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
    public function do_HWAWC_ModifySidebar_Metabox() {
        echo '<p>Cài đặt sidebar động.</p>';

    }
    /**
     * pre-defined validation callback method
     * @param $sInput
     * @param $sOldInput
     * @return mixed
     */
    public function validation_HWAWC_ModifySidebar_Metabox( $sInput, $sOldInput ) {
        //  delete transient when add new one or modify exists post
        delete_transient('hw_dynamic_sidebars_settings');
        //if(!isset($_SESSION['skin_options']) && isset($sInput['skin_options'])) unset($sInput['skin_options']);

        return $sInput;
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        $sidebars =  hwawc_get_all_active_sidebars();   //get all registered sidebars
        $sidebar_field_data = array(''=>'--- Chọn ---');  //sidebars select tag

        //prepare fields
        $apf_fields = array();
        foreach($sidebars as $sidebar) {
            $sidebar_field_data[$sidebar['id']] = $sidebar['name'];

            $apf_fields[] = array(
                'field_id' => $sidebar['id'],
                'type' => 'select',
                'title' => "Đổi sidebar ({$sidebar['id']})",
                'description' => 'Thay thế sidebar ('.$sidebar['name'].') với sidebar mới.',
                'label' => &$sidebar_field_data
            );

        }
        $this->addSettingFields(
        //'general',
            array(
                'field_id' => 'enable',
                'type' => 'checkbox',
                'title' => 'Kích hoạt',
                'description' => 'Kích hoạt thay đổi sidebar.'
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
                'field_id' => 'txtx',
                'type' => 'text',
                //'value' => '0'
            )
        );
        if( count($apf_fields)) {
            foreach($apf_fields as $field_setting) {
                $this->addSettingField($field_setting);
            }
        }
        // content_{page slug}_{tab slug}
        //add_filter( 'content_hw_sidebar_widgets_settings', array( $this, 'replyToInsertContents' ) );
    }
    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );
        $btn ='sdfdgdfg';
        return $sContent. $btn;
    }
}
endif;
/**
 * Class KIUHG
 */
if(class_exists('AdminPageFramework_MetaBox', false) ):
class HWAWC_DynamicSidebar_Template_MetaBox extends AdminPageFramework_MetaBox {
    public function setUp() {
        global $wp_query;
        $template_tag = '<code>hw_dynamic_sidebar("sidebar-1")</code>';

        $this->addSettingFields(
        //'general',
            array(
                'field_id'      => 'template_tag',
                'type'          => 'label',
                'title'         => __('Template Tag'),
                'description'   => __('Chèn đoạn code sau vào sidebar template để hiển thị sidebar động trên website.'),     #description that will be placed below the input field
                'label' => 'Thay vì gọi hàm <em>dynamic_sidebar()</em> chúng ta gọi hàm, vd: ' . $template_tag
                    . '<br/>Dựa vào điều kiện trên để thay đổi sidebar gọi ban đầu chuyển hướng sang sidebar khác. Xem thêm trợ giúp từ nút help ở góc phải trên cùng.'

            )
            //preview
            /*,array(
                'field_id' => 'preview',
                'type' => 'label',
                'title' => 'Xem trước',
                'description' => 'Xem trước'
            )*/
        );
    }
}
endif;
if(is_admin()) {
    //assign new field type to the metabox
    //if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HWAWC_ModifySidebar_Metabox');
    if(class_exists('APF_hw_condition_rules')) new APF_hw_condition_rules('HWAWC_ModifySidebar_Metabox');
}
