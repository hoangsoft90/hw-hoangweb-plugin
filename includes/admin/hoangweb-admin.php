<?php
#hoangweb-settings.php

if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Interface HW_Admin_Options_interface
 */
interface HW_Admin_Options_interface {
    /**
     * @return mixed
     */
    public function load();

}
/**
 * Class HW_Admin_Options
 */
abstract class HW_Admin_Options extends HW_Core implements HW_Admin_Options_interface{
    /**
     * @var array
     */
    private $api_classes = array();
    /**
     * @var
     */
    public static $admin;

    /**
     * class construct method
     */
    public function __construct() {
        $this->api_classes = array(
            'HW_WP_Languages' => HW_HOANGWEB_INCLUDES. '/api-options/class.wp.languages.php',
            'HW_WP_User' => HW_HOANGWEB_INCLUDES. '/api-options/class.wp.user.php',
            'HW_WP_Posts_Manager' => HW_HOANGWEB_INCLUDES. '/api-options/class.wp.posts-manager.php',
            'HW_WP_Media' => HW_HOANGWEB_INCLUDES. '/api-options/class.wp.media.php'
        );

    }

    /**
     * @param $class
     * @param $path
     */
    public function add_api($class, $path) {
        if(!isset($this->api_classes[$class]) && file_exists($path)) {
            $this->api_classes[$class] = $path;
        }
    }

    /**
     * load api class
     * @param $api
     * @return null|api instance if pass 1 name
     */
    public function load_api() {
        $args = func_get_args();
        foreach($args as $api)
        if(isset($this->api_classes[$api])
            && (!is_object($this->api_classes[$api]) || !$this->api_classes[$api] instanceof HW_Admin_Options) )
        {
            HW_HOANGWEB::register_class($api, $this->api_classes[$api]);
            include_once($this->api_classes[$api]);
            $this->api_classes[$api] = new $api();
            $this->api_classes[$api]->load();
        }
        if(count($args)==1) return $this->api_classes[$args[0]];
    }

    /**
     * main setting object
     * @return mixed
     */
    public function setting() {
        return self::$admin ;
    }

    /**
     * get api instance
     * @param $api
     */
    public function get_api($api) {
        return isset($this->api_classes[$api])? $this->api_classes[$api] : null;
    }

    /**
     * init api
     */
    public function load(){}

}
/**
 * Class HW_Admin_page_Implement
 */
class HW_Admin_page_Implement extends HW_Admin_Options{

    /**
     * @var
     */
    public static $instance;
    /**
     * theme configuration
     * @var
     */
    public $theme_config;

    /**
     * constructor
     */
    public function __construct(){
        parent::__construct();
        if(empty(self::$admin)) self::$admin = $this;
        $this->setup_hooks();   //you need to run hooks for first before other settings

        $this->load_api('HW_WP_Languages');
        $this->load_api('HW_WP_User');
        $this->load_api('HW_WP_Media', 'HW_WP_Posts_Manager');
    }

    /**
     * setup the hooks
     */
    private function setup_hooks() {
        add_action( 'admin_menu', array($this, '_modify_exists_menu') );
        add_action('init', array($this, '_init_something'));

        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));

        /**
         * general settings
         */
        add_action('admin_init', array(&$this, '_general_options_section'));
        add_action('after_setup_theme', array($this, '_setup_theme'));
        add_action("wp_ajax_hw_install_static_url_subdomain", array(&$this,"_ajax_install_static_url_subdomain")); //only for admin page
    }
    /**
     * prepare hook
     * @hook after_setup_theme
     */
    public function _setup_theme() {
        //no, because this trigger before init
        if(empty($this->theme_config))
            $this->theme_config = HW__Template::get_theme_config();

    }

    /**
     * general options page callback
     * @hook admin_init
     */
    public function _general_options_section() {
        add_settings_section(
            'hw_settings_section', // Section ID
            'Cài đặt Hoangweb', // Section Title
            array($this,'_general_section_options_callback'), // Callback
            'general' // What Page?  This makes the section show up on the General Settings Page
        );
        //register fields
        add_settings_field( // Option 1
            'languages_upload', // Option ID
            'Đa ngôn ngữ', // Label
            array($this, '_multilanguage_list_field_callback'), // !important - This is where the args go!
            'general', // Page it will be displayed (General Settings)
            'hw_settings_section', // Name of our section
            array( // The $args
                'languages_upload' // Should match Option ID
            )
        );
        add_settings_field( // Option 1
            'active_languages', // Option ID
            'Kích hoạt ngôn ngữ', // Label
            array($this, '_multilanguage_activation_field_callback'), // !important - This is where the args go!
            'general', // Page it will be displayed (General Settings)
            'hw_settings_section', // Name of our section
            array( // The $args
                'active_languages' // Should match Option ID
            )
        );
        add_settings_field( //serve images from subdomain
            'img_subdomain',
            'Thiết lập URL ảnh vào Subdomain',
            array($this, '_img_subdomain_field_callback'),
            'general',
            'hw_settings_section',
            array('img_subdomain')
        );
        /*add_settings_field( // Option 2
            'option_2', // Option ID
            'Option 2', // Label
            'my_textbox_callback', // !important - This is where the args go!
            'general', // Page it will be displayed
            'hw_settings_section', // Name of our section (General Settings)
            array( // The $args
                'option_2' // Should match Option ID
            )
        );*/

        register_setting('general','languages_upload', 'esc_attr');
        register_setting('general','img_subdomain','esc_attr');

    }
    /**
     * install serve image subdomain
     * @ajax hw_install_static_url_subdomain
     */
    public function _ajax_install_static_url_subdomain(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_install_static_url_subdomain_nonce")) {
            exit("hacked !");
        }
        global $wpdb;
        //update option 'upload_url_path'
        if(!isset($_POST['subdomain'])) return;

        //get subdomain
        $schema = 'http://';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            // SSL connection
            $schema = 'https://';
        }
        $subdomain = rtrim($schema.$_POST['subdomain'].'.'.$_SERVER['SERVER_NAME'],'/');

        $main_domain = rtrim(site_url(),'/');   //main domain

        /**
         * scan some table to update absolute url
         */
        //$query[] = 'update wp_options set option_value=REPLACE(option_value,"'.$main_domain.'","'.$subdomain.'")';
        //$query[] = 'update wp_posts set guid=REPLACE(guid,"'.$main_domain.'","'.$subdomain.'")';
        $query[] = 'update wp_posts set post_content=REPLACE(post_content,"'.$main_domain.'","'.$subdomain.'")';
        //$query[] = 'update wp_postmeta set meta_value=REPLACE(meta_value,"'.$main_domain.'","'.$subdomain.'")';

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if(HW_URL::valid_url($subdomain)){
                update_option('upload_url_path', $subdomain);
            }
            foreach($query as $sql) $wpdb->query($sql);
            echo 'Chú ý: Sửa file .htaccess';
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * move image URL to subdomain of this website
     */
    public function _img_subdomain_field_callback(){
        $schema = 'http://';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            // SSL connection
            $schema = 'https://';
        }
        $subdomain = (get_option('img_subdomain'));
        //note that name of form field must match with name that you given in function add_settings_field
        echo $schema.'<input type="text" name="img_subdomain" id="img_subdomain" value="'.$subdomain.'" size="5"/>.'.$_SERVER['SERVER_NAME'].'/';
        echo '<div><a class="button" href="__hw_localize_object.change_static_url_subdomain(this)">Cài đặt</a>';
        echo '<div class="more"></div></div>';
    }
    public function _general_section_options_callback() { // Section Callback
        echo '<p>A little message on editing info</p>';
    }
    /**
     * active selected languages
     * @param $args: argument
     */
    public function _multilanguage_activation_field_callback($args) {  // Textbox Callback
        //$option = get_option($args[0]);
        //echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
        if($this->check_uninstall_lang){
            echo '<a href="javascript:void(0)" class="button" onclick="__hw_localize_object.start_install_wplangs(this,jQuery(\'#hw_langs\'))">Nhấn để bắt đầu Cài đặt</a>';

        }
        else echo '<p>Tất cả các files ngôn ngữ đã tồn tại trong hệ thống</p>';

    }
    /**
     * multilanguages avaiable
     * @param array $args: argument
     */
    public function _multilanguage_list_field_callback($args){

        $this->check_uninstall_lang = HW_WP_Languages::hw_dropdown_languages(array(
            'name'         => 'hw_langs',
            'id'           => 'hw_langs',
            'selected'     => '',
        ));
        if($this->check_uninstall_lang) echo '<div>Chọn một hoặc nhiều ngôn ngữ chưa cài đặt có trong hoangweb mà bạn muốn cài đặt tại đây:</div>';

        else echo '<div>Tất cả các files ngôn ngữ đã tồn tại trong hệ thống.</div>';
    }

    /**
     * @hook admin_enqueue_scripts
     * enqueue scripts/styles
     */
    public function _admin_enqueue_scripts() {
        if(HW_HOANGWEB::is_current_screen('hoangweb-theme-options')) {
            HW_Libraries::enqueue_jquery_libs('jquery-colorbox');    //jquery colorbox lib
        }
    }

    /**
     * @hook init
     * init action callback (testing)
     */
    public function _init_something(){
        //show/hide advanced feature exists in admin menu
        $show_advanced = hw_get_setting('enable_developer_feature');
        if(!$show_advanced) {
            //acf menu, according to http://www.advancedcustomfields.com/resources/how-to-hide-acf-menu-from-clients/
            add_filter('acf/settings/show_admin', '__return_false');

        }

        //register help for the plugin
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('hoangweb', HW_HOANGWEB_PATH.'helps');
            HW_HELP::set_helps_url('hoangweb', HW_HOANGWEB_URL.'/helps');
            HW_HELP::register_help('hoangweb');
            HW_HELP::load_module_help('hoangweb');
        }

    }


    /**
     * change/modify global admin menu label
     * @hook admin_menu
     */
    public function _modify_exists_menu() {
        global $menu;
        global $submenu;

        if(function_exists('remove_menu_page')) {
            remove_menu_page( 'edit.php?post_type=acf' );
            remove_menu_page( 'cpt_main_menu' );
        }
        /*remove_menu_page('edit.php'); // Posts
        remove_menu_page('upload.php'); // Media
        remove_menu_page('link-manager.php'); // Links
        remove_menu_page('edit-comments.php'); // Comments
        remove_menu_page('edit.php?post_type=page'); // Pages
        remove_menu_page('plugins.php'); // Plugins
        remove_menu_page('themes.php'); // Appearance
        remove_menu_page('users.php'); // Users
        remove_menu_page('tools.php'); // Tools
        remove_menu_page('options-general.php'); // Settings
        //page post type, your_post_type should be the name of your actual post type.
        remove_menu_page( 'edit.php?post_type=your_post_type' );

        //For plugins, it seems you only need the page= query var
        if( !current_user_can( 'administrator' ) ): //cat66 plugin
            remove_menu_page('cart66_admin');
        endif;
        */
    }



}
if(is_admin() || is_call_behind()) {
    //HW_Admin_page_Implement::get_instance();
    _hw_global('admin', 'HW_Admin_page_Implement');
}