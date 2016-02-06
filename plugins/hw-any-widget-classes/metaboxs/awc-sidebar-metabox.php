<?php
#/root>includes/awc-sidebar-settings.php

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/05/2015
 * Time: 21:10
 */

if(class_exists('AdminPageFramework_MetaBox_Page') && !class_exists('HW_AWC_Sidebar_Settings')):
class HW_AWC_Sidebar_Settings extends AdminPageFramework_MetaBox_Page {
    /**
     * fields skin for sidebar widgets
     * @var array
     */
    static $widget_skins;

    /**
     * prepare widget skins fields
     */
    static public function available_widget_skins($pure = false){
        self::$widget_skins = array(
            'skin_default' => array('title'=>'Chọn skin mặc định.', 'description' => __('Chọn skin cho widget box')),
            'skin1'=> array('title' => 'Widget style 1','description'=>'Chọn skin cho widget box'),
            'skin2' => array('title'=>'Widget style 2'),
            'skin3' => array('title' => 'Widget style 3'),
        );
        //return purely data for select options
        if($pure) {
            $widget_skins_data = array();
            foreach(self::$widget_skins as $theme_name => $opt) {
                $widget_skins_data[$theme_name] = $opt['title'];

            }
            return $widget_skins_data;
        }
        return self::$widget_skins;
    }
    /**
     * generate field name base current editing sidebar
     * @param $name: field name
     * @param bool $valid_apf_field
     */
    static public function create_fieldname4sidebar($name, $valid_apf_field=false){
        $sidebar = self::get_active_sidebar();
        //return $name.'_'.$sidebar;
        $field = $sidebar."_".$name;
        if($valid_apf_field) $field = self::valid_sidebar_name($field);
        return $field;
    }

    /**
     * return current working sidebar in setting page
     */
    static public function get_active_sidebar(){
        global $wp_registered_sidebars;
        if(isset($_GET['sidebar'])) $sidebar = $_GET['sidebar'];    //get active sidebar from url param for firstly
        else $sidebar = hwawc_get_option('select_sidebars');
        if(!$sidebar || !isset($wp_registered_sidebars[$sidebar])){  //if not exists, otherwise get first sidebar to edit
            list($sidebar,$p) = each($wp_registered_sidebars);
        }
        return $sidebar;
    }
    /**
     * get specific sidebar setting if exists
     * @param $sidebar
     * @return mixed
     */
    static public function get_sidebars($sidebar = ''){
        global $wp_registered_sidebars;
        if(is_string($sidebar) && isset($wp_registered_sidebars[$sidebar])) return $wp_registered_sidebars[$sidebar];
    }

    /**
     * get sidebar field setting value
     * @param $field: option
     * @param string $sidebar
     */
    static public function get_sidebar_setting($field,$sidebar = ''){
        global $wp_customize;
        static $settings = array();
        if(!$sidebar) $sidebar = self::get_active_sidebar();    //if null get current sidebar
        if(!isset($settings[$sidebar])) $settings[$sidebar] = hwawc_get_option();
        //valid sidebar slug
        $sidebar_name = self::valid_sidebar_name($sidebar);

        if($field && isset($settings[$sidebar][$sidebar_name.'_'.$field])) {
            //return hwawc_get_option($sidebar_name.'_'.$field);
            $value = $settings[$sidebar][$sidebar_name.'_'.$field];
            if(is_array($value) && !empty($wp_customize)) return;   //if whether current is customize page
            return $value;
        }
    }

    /**
     * return valid field name from string
     * @param $str:
     * @return mixed
     */
    static public function valid_sidebar_name($str){
        $str = preg_replace('#[\s@\#\$\!%\^\&\*\(\)\-\+\=\~]#','_',$str);
        //$str = preg_replace('#-{2,}#','_',$str);
        return $str;
    }

    /**
     * check if sidebar name valid
     * @param $name: given sidebar name
     */
    static public function check_valid_sidebar_name($name){
        return !(preg_match('/\-{2,}|[\!\\/@#\$%\^&\*\(\)\[\]+=~]/',$name));
        //return !(preg_match('/[\!\\/@#\$%\^&\*\(\)\[\]\-+=~]/',$name));
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        //$data = self::get_sidebar_setting('color','sidebar---1');
        $sidebar = self::get_active_sidebar(); //current sidebar
        //top save button
        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );

        /**
         * Adds setting fields in the meta box.
         */
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('enable_override_sidebar'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'title' => 'Kích hoạt tùy chỉnh sidebar',
            'label' => 'Kích hoạt cho phép tùy chỉnh sidebar với các cài đặt dưới.',

        );
        if(!self::check_valid_sidebar_name($sidebar)){
            //auto fix invalid sidebar name
            $settings_fields[] = array(
                'field_id' => self::create_fieldname4sidebar('autofix_sidebar_name'),
                'type'=>'checkbox',
                #'value' => '#fff',
                'title' => 'Sửa tên sidebar không hợp lệ',
                'label' => 'Phát hiện sidebar này có tên không hợp lệ được quy định bởi hoangweb. Tên sidebar không được chứa ký tự đặc biệt và dấu -. Chọn vào đây để tự động sửa lỗi tên sidebar không hợp lệ.',
                'attributes' => array(
                    'class' => 'invalid-sidebar-name'
                )
            );
        }

        //alphabe each widgets in sidebar
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('alphabe_widgets'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'label' => 'Đánh số thứ tự cho mỗi widgets trong sidebar, VD:<br/> <img src="'.HW_AWC_URL.'/images/index-widget-id.png"/>',
            'title' => 'Đánh số thứ tự cho mỗi widgets'
        );
        //color
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('bgcolor_title'),
            'type'=>'color',
            #'value' => '#fff',
            'title' => 'Mầu thanh widget',
        );
        //background image widget title
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('bgimg_title'),
            'type'=>'image',
            #'value' => '#fff',
            'title' => 'Ảnh nền thanh widget',
        );

        //background color widget
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('bgcolor_box'),
            'type'=>'color',
            #'value' => '#fff',
            'title' => 'Mầu nền widget',
        );
        //widget bg image
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('bgimg_box'),
            'type'=>'image',
            #'value' => '#fff',
            'title' => 'Ảnh nền widget',
        );
        $settings_fields[] = array(
            'field_id' => self::create_fieldname4sidebar('tip'),
            'type' =>'text',
            'title' => 'Chú ý: để box giao diện widget hoạt động đúng, vào widget lưu lại thay đổi, bạn cũng có thể thiết lập từng box cho widget.'
        );
        //more skins
        self::available_widget_skins();
        foreach(self::$widget_skins as $name => $field){
            $settings_fields[] = array(
                'field_id' => self::create_fieldname4sidebar($name),
                'type' => 'hw_skin',
                'title' => isset($field['title'])? $field['title'] : 'Widget '.$name,
                'description' => isset($field['description'])? $field['description'] : 'Chọn skin cho widget box',
                'enable_skin_condition' => true,
                'external_skins_folder' => 'hw_awc_skins',
                'skin_filename' => 'hwawc-skin.php',
                'enable_external_callback' => false,
                'skins_folder' => 'skins',
                'apply_current_path' => HW_AWC_PATH,
                'plugin_url' => HW_AWC_URL,
                #'group' => 'olark' //dynamic
            );
        }

        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button_bottom',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );
        //register fields
        foreach($settings_fields as $aFieldSetting){
            $this->addSettingField($aFieldSetting);
        }
        // content_{page slug}_{tab slug}
        add_filter( 'content_'. HW_Sidebar_Settings::SETTING_PAGE_SLUG, array( $this, 'replyToInsertContents' ) );

    }

    /**
     * return sidebar setting page url
     * @param string $sidebar: sidebar name
     */
    static public function get_edit_sidebar_setting_page_link($sidebar = ''){
        $url = 'admin.php?page=' . HW_Sidebar_Settings::SETTING_PAGE_SLUG;
        if($sidebar) $url .= '&sidebar='.$sidebar;
        return admin_url($url);
    }
    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );

        $edit_sidebar_page = self::get_edit_sidebar_setting_page_link();
        $btn = '<a href="javascript:void(0)" onclick="location.href=\''.$edit_sidebar_page.'&sidebar=\'+jQuery(select_sidebars__0).val();" class="button button-primary">Sửa sidebar</a>';
        return $sContent. $btn;
    }
}

if(is_admin() ){
    /**
     * register metabox to page slug
     */
    add_action('init','_hw_awc_sidebar_setting_init');
    function _hw_awc_sidebar_setting_init(){
        $sidebar = HW_AWC_Sidebar_Settings::get_active_sidebar();

        $sidebar = HW_AWC_Sidebar_Settings::get_sidebars($sidebar);
        if(isset($sidebar['name']) ) {
            if(!HW_AWC_Sidebar_Settings::check_valid_sidebar_name($sidebar['id'])) $sidebar_id = HW_AWC_Sidebar_Settings::valid_sidebar_name($sidebar['id']);
            else $sidebar_id = $sidebar['id'];

            if($sidebar_id == $sidebar['id']) $sidebar_id = 'ID: '.$sidebar_id;
            else $sidebar_id = 'ID: <span>('.$sidebar['id'].')</span> -> Đổi thành: ('.$sidebar_id.')';

            //$sidebar_name = '('.$sidebar['name'].');
             $sidebar_name = ' - '.$sidebar_id;
        }
        else $sidebar_name = '';

        if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_AWC_Sidebar_Settings');
        new HW_AWC_Sidebar_Settings(
            null,                                           // meta box id - passing null will make it auto generate
            __( 'Cài đặt cho sidebar '.$sidebar_name, 'hwawc' ), // title
            //array( 'hw_sidebar_widgets_settings' =>  array( 'hw_sidebar_widgets_settings' ) ),    //syntax: {page slug}=>{tab slug}
            HW_Sidebar_Settings::SETTING_PAGE_SLUG,  //apply for this page slug
            'normal',                                         // context
            'default'                                       // priority
        );
    }

}
endif;