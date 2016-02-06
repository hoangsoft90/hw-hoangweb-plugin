<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 20:59
 */
if(class_exists('AdminPageFramework_MetaBox_Page')):
/**
 * Class HW_NavMenu_Metabox_settings
 */
class HW_NavMenu_Metabox_settings extends AdminPageFramework_MetaBox_Page {
    //const menu_setting_page_slug = 'hw_navmenu_settings';
    /**
     * return current menu in editing page
     */
    public static function get_active_menu(){
        $registered_navmenus = HW_NAVMENU_settings::get_all_registered_navmenus(); //get all registered nav menus
        $created_navmenus = HW_NAVMENU_settings::get_all_created_navmenus(); //get all created nav menus, this way can be used more menus

        if(hw__get('menu')) {
            $menu = hw__get('menu'); //get active menu from url param for firstly
        }
        else {
            if(hw__get('menu_type') =='name' ){  //because i set menu name option firstly
                $menu = hw_navmenu_option('select_menu');
            }
            elseif(hw__get('menu_type') =='location' || !hw__get('menu_type')) {  //for default to get menu location
                $menu = hw_navmenu_option('select_menu1');
            }
        }
        if(!$menu || (!isset($registered_navmenus[$menu]) && !isset($created_navmenus[$menu]) )){  //if not exists, otherwise get first menu to edit
            list($menu,$p) = each($registered_navmenus);    //check registered navmenus for first
        }
        return is_string($menu)? $menu : '';
    }
    /**
     * generate field name base current editing menu
     * @param $name: field name
     * @param bool $valid_apf_field
     * @return string
     */
    static public function create_menu_field_setting($name, $valid_apf_field=false){
        $menu = self::get_active_menu();
        //return $name.'_'.$menu;
        $field= $menu."_".$name;
        if($valid_apf_field) $field = self::valid_apf_field_name($field);
        return $field;
    }
    /**
     * get menu field setting value
     * @param $field: option
     * @param string $menu
     * @return array
     */
    static public function get_menu_setting($field = '',$menu = ''){
        static $settings = array();
        if(!$menu) $menu = self::get_active_menu();    //if null get current menu
        if($menu && !isset($settings[$menu])) $settings[$menu] = hw_navmenu_option();
        //valid menu slug for apf field
        $menu_name = self::valid_apf_field_name($menu);

        if($field ) {
            return isset($settings[$menu][$menu_name.'_'.$field])? $settings[$menu][$menu_name.'_'.$field] : '';
        }
        return $settings[$menu];
    }
    /**
     * return valid field name from string
     * @param $str
     * @return mixed
     */
    static public function valid_apf_field_name($str){
        HW_HOANGWEB::load_class('HW_Validation');
        return HW_Validation::valid_apf_slug($str);
    }
    /**
     * return sidebar setting page url
     * @param string $menu  menu slug
     */
    static public function get_edit_menu_setting_page_link($menu = ''){
        $url = 'admin.php?page='.HW_NAVMENU_settings::page_setting_slug;
        if($menu) $url .= '&menu='.$menu;
        return admin_url($url);
    }
    /**
     * get specific menu setting if exists
     * @param $sidebar
     * @return mixed
     */
    public static function get_navmenu($menu){
        $created_menus_data = HW_NAVMENU_settings::get_all_registered_navmenus();
        $registered_menus_data = HW_NAVMENU_settings::get_all_created_navmenus();   //change to this

        if(is_string($menu) && isset($created_menus_data[$menu])) return $created_menus_data[$menu];
        elseif(is_string($menu) && isset($registered_menus_data[$menu])) return $registered_menus_data[$menu];
        else{
            return $menu;   //return back
        }
    }
    /**
     * display something
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_navmenu_settings(){
        //$data = hw_navmenu_option();
        echo '<p>Thiết lập cài đặt cho menu.</p>';
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        $menu = self::get_active_menu(); //current menu

        /**
         * Adds setting fields in the meta box.
         */
        /*$settings_fields[] = array(
            'field_id' => 'test',
            'type'=>'text',
            'title' => 'sdfsf'
        );*/
        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button1',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );

        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('enable_filter_menu'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'title' => 'Kích hoạt tùy chỉnh menu',
            'label' => 'Kích hoạt cho phép tùy chỉnh menu với các cài đặt dưới.',

        );
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('show_searchbox'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'title' => 'Hiển thị searchbox vào menu',
            'label' => 'Hiển thị thêm searchbox vào menu hiện tại.',

        );
        //mqtranslate integration
        if(!is_plugin_active('mqtranslate/mqtranslate.php') || !is_plugin_active('qtranslate-x/mqtranslate.php')) {
            $tip = 'Chú ý: <br/>- Yêu cầu cài đặt plugin '.hw_install_plugin_link('mqtranslate/mqtranslate.php', 'mqtranslate'). ' hoặc '. hw_install_plugin_link('qtranslate-x/mqtranslate.php', 'qtranslate-x');
            $tip .= '<br/>- Với qtranslate-x bạn cần kích hoạt <a target="_blank" href="'.admin_url('options-general.php?page=qtranslate-x#integration').'">chế độ tương thích</a>.';
            $tip .= '<br/>- Kích hoạt module translate.';
        }
        else $tip = 'Plugin "mqtranslate/qtranslate-x" đã sẵn sàng.';
        $tip .= '<br/>Chọn giao diện hiển thị nút thay đổi ngôn ngữ <a href="'.admin_url('admin.php?page=hoangweb-theme-options&tab=multilang').'" target="_blank">tại đây</a>.';

        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('show_langs_switcher'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'title' => 'Thêm chọn đa ngôn ngữ vào menu',
            'label' => 'Hiển thị thêm chọn đa ngôn ngữ vào menu hiện tại.',
            'description' => $tip

        );
        //remove ul wrap
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('remove_ul_wrap'),
            'type' => 'checkbox',
            'title' => 'Xóa ul wrap',
            'label' => 'Xóa thẻ ul bao quanh nội dung menu.'
        );
        //remove ul,li around wp_nav_menu output
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('only_anchor_tag_nav_menu'),
            'type' => 'checkbox',
            'title' => 'Xóa ul,li bao quanh menu',
            'label' => 'Xóa ul,li bao quanh menu, chỉ hiển thị thẻ liên kết '.htmlspecialchars('<a')
        );
        /*$settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('allow_tags_nav_menu'),
            'type' => 'checkbox',
            'title' => 'HTML cho phép của nav menu',
            'label' => 'HTML cho phép trong nội dung wp_nav_menu'
        );*/
        //add home menu to first of nav menu
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('show_home_menu'),
            'type' => 'checkbox',
            'title' => 'Hiển thị menu home',
            'label' => 'Hiển thị menu home vào trước nav menu'
        );
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('show_icon'),
            'type' => 'checkbox',
            'title' => 'Hiển thị biểu tượng',
            'label' => 'Hiển thị biểu tượng vào mỗi nav menu item đã thiết lập.'
        );
        //enable menu skin
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('enable_skin'),
            'type' => 'checkbox',
            'title' => 'Kích hoạt giao diện',
            'label' => 'Kích hoạt cho phép sử dụng giao diện.'
        );
        //hw_skin
        $settings_fields[] = array(
            'field_id' => self::create_menu_field_setting('skin'),
            'type' => 'hw_skin',
            'title' => 'Giao diện',
            'label' => 'Chọn giao diện cho menu',
            'external_skins_folder' => 'hw_navmenu_skins',
            'skin_filename' => 'navmenu-skin.php',
            'enable_external_callback' => false,
            'skins_folder' => 'skins',
            'apply_current_path' => HW_MENU_PATH,
            'plugin_url' => HW_MENU_URL,
            'template_header_data' => array(
                'name' => 'HW Template',
            )
        );
        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button2',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );
        //register fields
        foreach($settings_fields as $aFieldSetting){
            $this->addSettingField($aFieldSetting);
        }
        // content_{page slug}_{tab slug}
        add_filter( 'content_'.HW_NAVMENU_settings::page_setting_slug, array( $this, 'replyToInsertContents' ) );
    }
    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );

        $edit_menu_page = self::get_edit_menu_setting_page_link();
        $class_navname = hw__get('menu_type')=='name'? 'button-primary': 'button';
        $class_navloca = hw__get('menu_type')=='location'? 'button-primary': 'button';

        $btn = '<a href="javascript:void(0)" onclick="location.href=\''.$edit_menu_page.'&menu_type=name&menu=\'+jQuery(select_menu__0).val();" class="button '.$class_navname.'">Sửa menu (name)</a>';
        $btn .= '<a href="javascript:void(0)" onclick="location.href=\''.$edit_menu_page.'&menu_type=location&menu=\'+jQuery(select_menu1__0).val();" class="button '.$class_navloca.'">Sửa menu (Location)</a>';
        return $sContent. $btn;
    }
}
    //boot class
    if(is_admin()){
        /**
         * register metabox to page slug
         */
        add_action('init','_hw_navmenu_setting_init');
        function _hw_navmenu_setting_init(){
            $menu_slug = HW_NavMenu_Metabox_settings::get_active_menu();
            $menu = HW_NavMenu_Metabox_settings::get_navmenu($menu_slug);
            if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_NavMenu_Metabox_settings');
            new HW_NavMenu_Metabox_settings(
                null,                                           // meta box id - passing null will make it auto generate
                __( 'Cài đặt cho menu ('.$menu.' - ID:&nbsp'.$menu_slug.')', 'hwawc' ), // title
                //array( 'hw_sidebar_widgets_settings' =>  array( 'hw_sidebar_widgets_settings' ) ),    //syntax: {page slug}=>{tab slug}
                HW_NAVMENU_settings::page_setting_slug,  //apply for this page slug
                'normal',                                         // context
                'default'                                       // priority
            );
        }

    }
endif;