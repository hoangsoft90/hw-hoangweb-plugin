<?php
#/root
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/*if(class_exists('HW_HOANGWEB')) {  //require hw-hoangweb plugin
    //HW_HOANGWEB::loadlib('AdminPageFramework'); //load admin page framework   ->entrusted by hw-hoangweb/__autoload
}
*/
if(class_exists('HW_SKIN') && function_exists('hwskin_load_APF_Fieldtype')) {
    hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
}
include_once(HW_AWC_PATH.'/metaboxs/awc-sidebar-metabox.php');

if(class_exists('AdminPageFramework')):
/**
 * Class HW_Sidebar_Settings
 */
class HW_Sidebar_Settings extends AdminPageFramework{
    /**
     * root menu page
     */
    const HW_WIDGET_SETTING_ROOT_MENU_PAGE = 'hoangweb-theme-options';

    /**
     * setting page slug
     */
    const SETTING_PAGE_SLUG = 'hw_sidebar_widgets_settings';
    /**
     * note: construct no required
     */
    /*function __construct(){

    }*/
    //tell the framework what page to create
    public function setUp() {
        //$this->setRootMenuPage( 'sdfgfgdfg' );      # set the top-level page, ie add a page to the Settings page
        $this->setRootMenuPageBySlug(self::HW_WIDGET_SETTING_ROOT_MENU_PAGE);      //add submenu page under hoangweb theme options
        #add sub-menu pages
        $this->addSubMenuItem(
            array(
                'title'     => 'Cài đặt Sidebar/Widget',
                'page_slug' => self::SETTING_PAGE_SLUG,
            )
        );

        //modify apf hwskin fieldtype
        //add_filter('apf_hwskin', array($this, '_apf_hwskin'),10,2);
        //add_filter('hwskin_field_output', array($this, '_apf_hwskin_field_output'),10,3);   //hack field output
    }

    /**
     * get all registered sidebars
     * @return array
     */
    static public function get_all_sidebars(){
        global $wp_registered_sidebars;
        $structure = array();
        foreach($wp_registered_sidebars as $sidebar => $param){
            $structure[$sidebar] = $param['name'];
        }
        return $structure;
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_sidebar_widgets_settings() {


    }
    /**
     * show widgets settings
     * @param $oAdminPage
     */
    public function load_hw_sidebar_widgets_settings( $oAdminPage ) {

        /*register section
        $this->addSettingSections(
                'hw_livechat_settings',    //must match with page slug
                array(
                        'section_id' => 'general',
                        'title' => 'Cài đặt',
                        'description' => 'Cài đặt chung.',
                ),
                array(
                        'section_id' => 'webhook',
                        'title' => 'Web hook',
                        'description' => 'Truy xuất dữ liệu ra bên ngoài.',
                )
        );*/
        //list registered sidebars
        $sidebars_list = self::get_all_sidebars();
        //setting fields
        $settings_fields = array();

        //select sidebar
        $settings_fields['select_sidebars'] = array(
            'field_id' => 'select_sidebars',
            'type' => 'select',
            'title' => __('Chọn Sidebar'),
            'description' => __('Chọn Sidebar muốn thay giao diện'),
            'label' => $sidebars_list,
            /*'attributes'        => array(
                'field' => array(
                    'style' => 'float:right; width:auto;',
                )
            )*/
           );
        //set current active sidebar in edit screen
        if($settings_fields['select_sidebars'] && isset($_GET['sidebar'])) {
            $settings_fields['select_sidebars']['value'] = $_GET['sidebar'];
        }


        //submit button
        /*$settings_fields[] = array(
                'type' => 'submit',
                'field_id'      => 'submit_button',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
                'label' => 'Lưu lại'
            );
        */
        /*$this->addSettingFields(
        'general',    ->no need specific group

        );*/
        //register fields
        foreach($settings_fields as $aFieldSetting){
            $this->addSettingField($aFieldSetting);
        }

    }
    /**
     * The pre-defined validation callback method.
     *
     * The following hooks are available:
     *	- validation_{instantiated class name}_{field id} – [3.0.0+] receives the form submission value of the field that does not have a section. The first parameter: ( string|array ) submitted input value. The second parameter: ( string|array ) the old value stored in the database.
     *	- validation_{instantiated class name}_{section_id}_{field id} – [3.0.0+] receives the form submission value of the field that has a section. The first parameter: ( string|array ) submitted input value. The second parameter: ( string|array ) the old value stored in the database.
     *	- validation_{instantiated class name}_{section id} – [3.0.0+] receives the form submission values that belongs to the section.. The first parameter: ( array ) the array of submitted input values that belong to the section. The second parameter: ( array ) the array of the old values stored in the database.
     *	- validation_{page slug}_{tab slug} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     *	- validation_{page slug} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     *	- validation_{instantiated class name} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     */
    public function validation_HW_Sidebar_Settings( $aInput, $aOldInput ) {
        $enable_override_sidebar = HW_AWC_Sidebar_Settings::create_fieldname4sidebar('enable_override_sidebar', true);
        $status = (isset($aInput[$enable_override_sidebar]) && $aInput[$enable_override_sidebar])? '1': '0';   //disable preload stuff for skin

        //save current skin for enqueue
        //more skins
        $widget_skins = HW_AWC_Sidebar_Settings::available_widget_skins();
        foreach( $widget_skins as $name => $field){
            $skin_field = HW_AWC_Sidebar_Settings::create_fieldname4sidebar($name, true);   //also fixed sidebar name

            if(!empty($aInput[$skin_field])) {
                if(isset($aInput[$skin_field]['hash_skin']) && isset($aInput[$skin_field]['hwskin_config'])) {
                    $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($aInput[$skin_field]);//
                    $skin->instance->save_skin_assets(array(
                        'skin' => $aInput[$skin_field],
                        'object' => 'sidebar-setting-'. $skin_field,
                        'status' => $status
                    ));
                }

            }
        }
        return $aInput;
    }
}

if(is_admin() || class_exists('HW_CLI_Command', false)){
    //init custom field type
    if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_Sidebar_Settings');
    new HW_Sidebar_Settings;


}
endif;
if(!is_admin()){

}
?>