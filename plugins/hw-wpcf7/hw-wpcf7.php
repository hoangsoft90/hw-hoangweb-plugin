<?php 
/**
Plugin Name: HW WPCF7
Plugin URI: 
Description: Thêm chức năng cho contact form 7. Hỗ trợ Contact Form 7
Version: 1.0
Text Domain: hwcf
Domain Path: /lang/
author: http://hoangweb.com
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_WPCF7_URL', plugins_url('',__FILE__));
define('HW_WPCF7_PATH', plugin_dir_path(__FILE__));
define('HW_WPCF7_PLUGIN_FILE', __FILE__);

include_once('functions.php');

//require HW_HOANGWEB plugin
include('activation.php');

include_once('includes/fields.php');

/*if(!class_exists('AdminPageFramework')){  //autoload from plugin hw-hoangweb
    include_once('lib/admin-page-framework.min.php');   //load AdminPageFramework lib
}*/

/**
 * hw-wpcf7 setting
 */
//include_once('includes/meta-boxes.php');   //draft

/**
 * build each wpcf7 options
 */
include_once('includes/wpcf7-settings.php');
/**
 * do on frontend
 */
include_once ('includes/hw-wpcf7-frontend.php');
/**
 *
 * wpcf7 common setting
 */
if(is_admin()){ #only admin
    include_once('includes/hw-cf-admin.php');
}

/**
 * wpcf7 action hook
 */
include_once('includes/hw-wpcf7-action.php');

/**
 * contact form templates
 */
include_once('includes/hw-wpcf7-template.php');
/**
 * setup
 */
HW_WPCF7::get_instance();

if(!is_admin()) {
    new HW_WPCF7_Frontend();
}
