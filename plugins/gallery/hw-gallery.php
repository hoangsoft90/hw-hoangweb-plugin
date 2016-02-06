<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
//constants
define('HW_GALLERY_PLUGIN_URL', plugins_url('',__FILE__));
define('HW_GALLERY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_GALLERY_PLUGIN_FILE', (__FILE__));
define('HW_GALLERY_ENVIRA_PLUGIN_URL', HW_GALLERY_PLUGIN_URL. '/plugin/gallery');
define('HW_GALLERY_ENVIRA_PLUGIN_PATH', HW_GALLERY_PLUGIN_PATH. '/plugin/gallery');

//require HW_HOANGWEB plugin
/*function hwgl_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin',
            'envira-gallery-lite/envira-gallery-lite.php'
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
register_activation_hook( HW_GALLERY_PLUGIN_FILE, 'hwgl_require_plugins_activate' );   //not support for multisite
*/
/**
 * Free Envira gallery
 */
include ('plugin/gallery/hw-gallery-lite.php');
/**
 * functions
 */
require ('includes/functions.php');

/**
 * core class
 */
require ('includes/envira-gallery.php');

/**
 * for admin
 */
if(is_admin()) {
    require ('includes/admin/gallery-settings.php');
}
else {
    //frontend
    require ('includes/gallery-frontend.php');
}
/**
 * widget
 */
include_once ('includes/gallery-widget.php');

//init class instance
if(!is_admin()) {
    HW_Gallery::get_instance();
}
else HW__Gallery_Lite::get_instance();