<?php
/**
Plugin Name: HW Breadcrumb
Plugin URI: http://hoangweb.com
Description: hoangweb breacrumb NavXT
Author: Hoangweb.com
Version: 1.0
Text Domain: breadcrumb-navxt
Domain Path: /languages
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//define path
define('HW_BREADCRUMB_URL', plugins_url('',__FILE__));
define('HW_BREADCRUMB_PATH', plugin_dir_path(__FILE__));
define('HW_BREADCRUMB_PLUGIN_FILE', __FILE__);

include_once('includes/functions.php');
/**
 * breadcrumb settings
 */
include_once('includes/hw_breadcrumb_setting.php'); //admin
/**
 * //design use in frontend
 */
include_once('includes/hw_breadcrumb.php');
/**
 * settings
 */
include_once('includes/settings.php');


if(is_admin()){
    HW_Breadcrumb_Setting::getInstance();
}
