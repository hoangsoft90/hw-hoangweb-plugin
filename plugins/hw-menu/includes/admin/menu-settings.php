<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 20:25
 */
if(class_exists('HW_SKIN') && function_exists('hwskin_load_APF_Fieldtype')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
if(class_exists('AdminPageFramework')):
class HW_NAVMENU_settings extends AdminPageFramework{
    const MENU_ROOT_MENU_PAGE = 'hoangweb-theme-options';
    const page_setting_slug = 'hw_navmenu_settings_page';   //note that: page slug should not match class name

    /**
     * save current skin settings
     * @var
     */
    private $current_skin_setting = null;
    /**
     * tell the framework what page to create
     */
    public function setUp() {
        //$this->setRootMenuPage( 'sdfgfgdfg' );      # set the top-level page, ie add a page to the Settings page
        $this->setRootMenuPageBySlug(self::MENU_ROOT_MENU_PAGE);      //add submenu page under hoangweb theme options
        #add sub-menu pages
        $this->addSubMenuItem(
            array(
                'title'     => 'Menu',
                'page_slug' => self::page_setting_slug,
            )
        );

        //modify apf hwskin fieldtype
        //add_filter('apf_hwskin', array($this, '_apf_hwskin'),10,2);
        add_filter('hwskin_field_output', array($this, '_apf_hwskin_field_output'),10,3);   //hack field output
        add_filter('renderOptionField_APF', array($this, '_apf_renderOptionField'), 10,2);  //before output skin options
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
    public function validation_HW_NAVMENU_settings($aInput, $aOldInput) {
        $enable_filter_menu = HW_NavMenu_Metabox_settings::create_menu_field_setting('enable_filter_menu', true);
        $enable_skin = HW_NavMenu_Metabox_settings::create_menu_field_setting('enable_skin', true);
        $status = '0';    //disable preload stuff for skin
        if(isset($aInput[$enable_filter_menu]) && $aInput[$enable_filter_menu]
            && !empty($aInput[$enable_skin])){
            $status = '1';
        }

        $skin_field = HW_NavMenu_Metabox_settings::create_menu_field_setting('skin', true);

        //save current skin for enqueue
        if(!empty($aInput[$skin_field])) {
            if(isset($aInput[$skin_field]['hash_skin']) && isset($aInput[$skin_field]['hwskin_config'])) {
                $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($aInput[$skin_field]);//
                $skin->instance->save_skin_assets(array(
                    'skin' => $aInput[$skin_field],
                    'object' => 'hw-menu-'.$skin_field,
                    'status' => $status
                ));
            }

        }

        return $aInput;
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    //do in class HW_NavMenu_Metabox_settings
    public function do_hw_navmenu_settings_page() {
        echo "<p>Thiết lập cài đặt cho menu dưới đây. Chú ý:
        <ul>
        <li>- Đặt tên menu và tên đăng ký menu (theme_location) khác nhau, nếu trùng nhau ưu tiên theme_location trước.</li>
        <li>- Có thể sửa menu theo 'theme_location' hoặc tên 'menu', linh hoạt trong TH gọi menu theo cách của bạn.</li>
        </ul>
        </p>";

    }

    /**
     * return setting admin page
     * @return string|void
     */
    public static function get_admin_setting_page() {
        return admin_url('wp-admin/admin.php?page='.self::page_setting_slug);
    }
    /**
     * modify field setting before render skin options fields
     * @param $field
     * @param $aField
     */
    public function _apf_renderOptionField($field, $aField){
        if(empty($this->current_skin_setting)){
            $menu = HW_NavMenu_Metabox_settings::get_active_menu();
            $skin = HW_NavMenu_Metabox_settings::get_menu_setting('skin', $menu);
            if($skin){
                $this->skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($skin);
                $file = $this->skin->instance->get_skin_file($this->skin->hash_skin);
                if(file_exists($file)){
                    $theme = array();
                    include($file);
                    $this->current_skin_setting = $theme;
                }
            }
        }
        if(!empty($this->current_skin_setting)){
            $menu_args = $this->current_skin_setting['args'];
            if(isset($field['name']) && isset($menu_args[$field['name']])) {
                $field['value'] = $menu_args[$field['name']];   //get args from skin for current menu

            }
        }

        if(isset($field['value']) && isset($field['description'])){
            if(isset($field['method']) && $field['method'] == 'append') {
                $field['description'] .= '<br/>Thêm vào sau giá trị mặc định "<em><span style="color:blue">'.htmlspecialchars($field['value'],ENT_QUOTES).'</span></em>" (thuộc về skin hiện tại)';
            }
            elseif(isset($field['method']) && $field['method'] == 'override'){
                $field['description'] .= '<br/>Sẽ thay thế giá trị mặc định "<em><span style="color:blue">'.htmlspecialchars($field['value'],ENT_QUOTES).'</span></em>" (thuộc về skin hiện tại)';
            }
            else{
                $field['description'] .= '<br/>Giá trị mặc định "<em><span style="color:blue">'.htmlspecialchars($field['value'],ENT_QUOTES).'</span></em>" (thuộc về skin hiện tại)';
            }
        }
        return $field;
    }
    /**
     * before output hw_skin apf field
     * @param $skin: HW_SKIN object
     * @param $Output: chunk of string to output
     * @param $field_output: field output in string
     */
    public function _apf_hwskin_field_output( $aOutput= array(),$skin,$field_output = ''){
        if(class_exists('HW_HOANGWEB') && !HW_HOANGWEB::is_current_screen(self::page_setting_slug)) {
            return $aOutput;
        }
        $aOutput[] = '<div>Lưu ý: Những tùy chọn cài đặt này là options của skin bạn đã áp dụng cho menu và sẽ được bổ xung vào tùy các cài đặt mặc định của skin. VD: container_class, menu_class..</div>';

        return $aOutput;
    }
    /**
     * return all registered nav menus
     * @return array
     */
    public static function get_all_registered_navmenus(){
        return get_registered_nav_menus();  //create nav menu by ie: register_nav_menu( 'primary', __( 'Primary Menu', 'hoangweb' ) );
    }

    /**
     * get menus data
     * @return array
     */
    public static function get_all_created_navmenus($key = 'name') {
        $menus = hw_get_all_menus();
        $data = array();
        foreach($menus as $name) {
            if($key=='name') $id = sanitize_title($name->name);
            else $id = $name->term_id;
            $data[$id] = $name->name;
        }
        return $data;
    }
    /**
     * show nav menu settings
     * @param $oAdminPage
     */
    public function load_hw_navmenu_settings_page( $oAdminPage ) {
        //get list of wp_nav_menus
        $menus_list_location = self::get_all_registered_navmenus();
        $menus_list = self::get_all_created_navmenus(); //we want to list all saved menus not just menu location

        //setting fields
        $settings_fields = array();

        //select sidebar
        $settings_fields['select_menu'] = array(
            'field_id' => 'select_menu',
            'type' => 'select',
            'title' => __('Chọn Nav Menu'),
            'description' => __('Chọn Nav Menu muốn sửa cài đặt'),
            'label' => $menus_list,
            /*'attributes'        => array(
                'field' => array(
                    'style' => 'float:right; width:auto;',
                )
            )*/
        );
        //select sidebar
        $settings_fields['select_menu1'] = array(
            'field_id' => 'select_menu1',
            'type' => 'select',
            'title' => __('Chọn Nav Menu (Location)'),
            'description' => __('Chọn Nav Menu Location muốn sửa cài đặt'),
            'label' => $menus_list_location,
            /*'attributes'        => array(
                'field' => array(
                    'style' => 'float:right; width:auto;',
                )
            )*/
        );
        //set current active sidebar in edit screen
        if($settings_fields['select_menu'] && isset($_GET['menu'])) {
            $settings_fields['select_menu']['value'] = $_GET['menu'];
        }
        if($settings_fields['select_menu1'] && isset($_GET['menu'])) {
            $settings_fields['select_menu1']['value'] = $_GET['menu'];
        }


        //register fields
        foreach($settings_fields as $aFieldSetting){
            $this->addSettingField($aFieldSetting);
        }
    }

}
if(is_admin() ){
    //init custom field type
    if(class_exists('APF_hw_skin_Selector_hwskin')) {
        new APF_hw_skin_Selector_hwskin('HW_NAVMENU_settings');    //register new field type to this class
    }
    new HW_NAVMENU_settings;
}
endif;
include_once('hw-menu-metabox-setting.php');