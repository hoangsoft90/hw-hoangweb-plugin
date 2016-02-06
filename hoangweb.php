<?php 
/**
Plugin Name: HW Hoangweb
Plugin URI: http://hoangweb.com
Description: Hoangweb plugin.
Author: Hoangweb.com
Version: 1.0
Text Domain: hoangweb
Domain Path: /languages
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


define( 'HW_HOANGWEB_REL_PATH', dirname( plugin_basename( __FILE__ ) ) . '/' );

/**
 * includes path
 */
define( 'HW_HOANGWEB_INCLUDES', plugin_dir_path( __FILE__ ). 'includes');
/**
 * plugin path & url
 */
define( 'HW_HOANGWEB_URL', plugins_url( '', __FILE__ ) );
define( 'HW_HOANGWEB_PATH', plugin_dir_path( __FILE__ ) );
define( 'HW_HOANGWEB_IMAGES', HW_HOANGWEB_URL. '/images');
define( 'HW_ADMIN_URL', HW_HOANGWEB_URL. '/admin');
define( 'HW_ADMIN_INCLUDES', HW_HOANGWEB_INCLUDES. '/utilities/admin');

/**
 * js+PHP libraries
 */
define( 'HW_HOANGWEB_PHP_LIBS', plugin_dir_path( __FILE__ ). 'plugins/PHP-libraries');
define( 'HW_HOANGWEB_JQUERY_LIBS', HW_HOANGWEB_URL. '/js/libraries');
define( 'HW_HOANGWEB_JQUERY_LIBS_PATH', HW_HOANGWEB_PATH. '/js/libraries');

/**
 * Plugins/modules path
 */
define( 'HW_HOANGWEB_PLUGINS', plugin_dir_path( __FILE__ ). 'plugins');
define( 'HW_HOANGWEB_PLUGINS_URL', HW_HOANGWEB_URL. '/plugins');

define( 'HW_HOANGWEB_PLUGIN_FILE',   __FILE__ );
define('HW_HOANGWEB_PLUGIN_SLUG', plugin_basename(HW_HOANGWEB_PLUGIN_FILE));
define('HW_HOANGWEB_PLUGIN_NAME', basename(dirname(HW_HOANGWEB_PLUGIN_FILE)));

/**
 * Utilities
 */
define('HW_HOANGWEB_UTILITIES', HW_HOANGWEB_INCLUDES . '/utilities');
define('HW_HOANGWEB_CLASSES_PATH', HW_HOANGWEB_PATH . 'classes');
define('HW_HOANGWEB_AJAX', plugins_url('ajax.php', __FILE__) );
/**
 * hoangweb settings
 */
require_once('includes/hoangweb-core.php');


/**
 * theme header customizer
 */
require_once('lib/customizer-custom-controls/hw-theme-customizer.php');

/**
 * initialize
 */
require_once('hw-install.php');

/**
 * register class
 */
//set autoload admin notices class
HW_HOANGWEB::load_class('HW_WP_NOTICES');

//feature button toggle class
HW_HOANGWEB::load_class('HW_ButtonToggle_widget');

//set autoload HW_POST class
HW_HOANGWEB::load_class('HW_POST');

/**
 * acf untilities
 */
//include_once('classes/plugins/hw_acf_api.php');
HW_HOANGWEB::load_class('HW_ACF_API');

/**
 * featured
 */
//include_once('plugins/video/video.php');

add_action('admin_enqueue_scripts', 'HW_ButtonToggle_widget::_hwbtw_admin_enqueue_scripts',10);  //admin enqueue scripts
add_filter('widget_update_callback', 'HW_ButtonToggle_widget::_hwbtw_in_widget_form_update',10,3);  //update widget instance

//start instance
new HW_HOANGWEB();

#delete_option('hw_install_modules');exit();
#do_action('hw_hoangweb_loaded');    //load after all masterial loaded in this plugin
?>