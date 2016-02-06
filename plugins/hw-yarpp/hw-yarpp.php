<?php 
/**
 Plugin Name: HW Yarpp
 Plugin URI: http://hoangweb.com
 Description: Hoangweb cấu hình plugin tạo bài viết liên quan YARPP.
 Author: Hoangweb.com
 Version: 1.0
 Text Domain: hoangweb
 Domain Path: /languages
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HWRP_PLUGIN_URL' , plugins_url('',__FILE__).'/');
define('HWRP_PLUGIN_PATH' , plugin_dir_path(__FILE__));
define('HWRP_TEMPLATES_PATH', HWRP_PLUGIN_PATH.'includes/templates');
define('HWRP_PLUGIN_FILE', __FILE__);

include_once('hwrp-install.php');   //prevent use file install.php
include_once('includes/functions.php');
include_once('includes/hw-related-yarpp-settings.php');

include_once('includes/hwrp-admin.php');
include_once('includes/hwrp-website.php');

hwrp_init();
if(!is_admin()) {
    HW_RelatedPosts_Frontend::getInstance();
}
?>