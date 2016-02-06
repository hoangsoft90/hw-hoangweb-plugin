<?php 
/**
 Plugin Name: HW Livechat
 Plugin URI:
 Description: Tiện ích Chat trực tuyến.
 Version: 1.0
 Text Domain: hwchat
 Domain Path: /lang/
 author: http://hoangweb.com
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_LIVECHAT_URL', plugins_url('',__FILE__));
define('HW_LIVECHAT_PATH', plugin_dir_path(__FILE__));

/**
 * require HW_HOANGWEB plugin
 */
register_activation_hook( __FILE__, 'hw_livechat_require_plugins_activate' );
function hw_livechat_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
//load AdminPageFramework lib
//if(class_exists('HW_HOANGWEB')) HW_HOANGWEB::loadlib('AdminPageFramework');   //->entrusted by hw-hoangweb/__autoload
/*if(!class_exists('AdminPageFramework')){
    include('lib/admin-page-framework.min.php');    //this automaticaly load thank to autoload function
}*/

//load livechat functions
include_once('functions.php');
//admin
#both admin & fontend
include_once('includes/hw-livechat-setting.php');

/**
 * Class HW_Livechat
 */
class HW_Livechat{
    /**
     * share this class instance
     * @var
     */
    private static $instance;

    //static $skin;   //hw_skin instance
    /**
     * constructor
     */
    public function __construct(){
        if($this->check_already()) {
            $this->setup_actions();
        }
    }

    /**
     * already ability to run plugin
     * @return bool
     */
    public function check_already(){
        return class_exists('APF_hw_skin_Selector_hwskin');
    }

    /**
     * setup actions
     */
    public function setup_actions(){
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));    //for admin
        add_action('wp_footer',array($this, '_hw_wp_footer'));   //for frontend
        //add_action('init', array($this, '_init'));
    }
    /**
     * return this class instance
     * @return HW_Livechat
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * init something
     */
    public function _init(){
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('livechat', HW_LIVECHAT_PATH.'helps');
            HW_HELP::register_help('livechat');
            HW_HELP::load_module_help('livechat');
        }
    }
    /**
     *
     */
    public function _hw_wp_footer(){
        if(hw_livechat_option('enable_livechat')){
            //load mobile detector library
            if(function_exists('hwlib_load_library')) {
                $mobile_detect = hwlib_load_library('HW_Mobile_Detect');
                if($mobile_detect->object->isMobile() && !hw_livechat_option('active_on_mobile')) {
                    return ;    //do not show chatbox on mobile device
                }
            }
            /*if(class_exists('HW_Mobile_Detect')) {
                HW_PHP_Libraries::get('HW_Mobile_Detect');
            }*/

            //get embed live chat code
            $embed = hw_livechat_option('chat_embed_code');
            if($embed){
                echo $embed;
            }
            /**
             * apply skin
             */
            $skin_data = hw_livechat_option('chat_skin');
            $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($skin_data);

            if($skin && !empty($skin->instance)){
                $file = $skin->instance->get_skin_file($skin->hash_skin);
                if(file_exists($file)) {
                    $theme = array();   //valid
                    include($file);
                    //depricated
                    /*if(!isset($theme['styles'])) $theme['styles'] = array();
                    if(!isset($theme['scripts'])) $theme['scripts'] = array();

                    if(count($theme['styles']) || count($theme['scripts'])) {
                        $skin->instance->enqueue_files_from_skin(null//$theme['styles']//, $theme['scripts']);  //enqueued css before
                    }*/
                    HW_SKIN::enqueue_skin_assets(array_merge(array('skin_file' => $file ), (array)$skin));
                }
            }
        }

    }
    /**
     * hook admin_enqueue_scripts
     * @param $hook_suffix: current page slug
     */
    public function _admin_enqueue_scripts($hook_suffix){
        if ( false === strpos( $hook_suffix, 'hw_livechat_settings' ) )
            return; //only for wpcf7 current page

        wp_enqueue_style('hw-livechat-style',plugins_url('style.css',__FILE__));
    }
}
//get first of all class instance
HW_Livechat::getInstance();
/**
 * @hook init
 */
add_action('init', 'hw_livechat_init');
function hw_livechat_init(){
    if(class_exists('HW_HELP')){
        HW_HELP::set_helps_path('livechat', HW_LIVECHAT_PATH.'helps');
        HW_HELP::register_help('livechat');
        HW_HELP::load_module_help('livechat');
    }

}
/*add_action('admin_menu','register_my_custom_submenu_page');
function register_my_custom_submenu_page(){
    $t=add_submenu_page( 'nhp_hoangweb_theme_opts_hw_livechat_settings', 'My Custom Submenu Page', 'My Custom Submenu Page', 'manage_options', 'my-custom-submenu-page', 'my_custom_submenu_page_callback' );_print($t);

}
function my_custom_submenu_page_callback() {

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>My Custom Submenu Page</h2>';
    echo '</div>';

}
*/
?>