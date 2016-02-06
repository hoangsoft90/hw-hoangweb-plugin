<?php
/*
Plugin Name: HW Social Share
Plugin URI: http://hoangweb.com
Description: Social sharing
Version: 1.0
Author: Hoangweb.COM
Author URI: http://hoangweb.com
*/
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_SOCIALSHARE_URL', plugins_url('', __FILE__));
define('HW_SOCIALSHARE_PATH' , plugin_dir_path(__FILE__));
define('HW_SOCIALSHARE_PLUGIN_FILE' , (__FILE__));


//load options page
if(!class_exists('AdminPageFramework')){
	require_once( dirname( __FILE__ ) . '/libs/admin-page-framework.min.php' );
}

/**
 * functions
 */
require_once('includes/functions.php');

/**
 * widget
 */
require_once('hwss-widget.php');

/**
 * shortcode
 */
require_once('includes/hwss-shortcode.php');

/**
 * settings
 */
include_once ('includes/hwss-settings.php');

/**
 * implements settings
 */
include_once ('includes/social-sharing.php');


