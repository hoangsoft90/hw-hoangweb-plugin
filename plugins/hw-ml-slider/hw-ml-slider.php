<?php
/**
Plugin Name: HW custom meta slider
Plugin URI: http://hoangweb.com
Description: extend metaslider plugin
Author: Hoangweb.com
Author URI: http://hoangweb.com
Version: 1.0
for metaslider v3.3 +
*/
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * load custom from ml-slider plugin
 */
include ('plugin/ml-slider/ml-slider.php');

define('HWML_PLUGIN_URL', plugins_url('',__FILE__));
define('HWML_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HWML_PLUGIN_FILE', __FILE__);

require_once('includes/functions.php');  //main function
include_once('includes/hwml-metaslider-settings.php');  //metaslider settings
include_once('includes/hwml-shortcode-manager.php'); //

//hw slider shortcode for displaying slider
include_once('includes/hwml-shortcode.php');
include_once('includes/hwml-widget.php');

//register HW_WP_NOTICES class
/*if(!class_exists('HW_WP_NOTICES') && file_exists(WP_PLUGIN_DIR.'/hw-admin-notices/hw-notices-class.php'))
    require_once(WP_PLUGIN_DIR.'/hw-admin-notices/hw-notices-class.php');*/
//if(class_exists('HW_HOANGWEB')) HW_HOANGWEB::register_class('HW_WP_NOTICES', WP_PLUGIN_DIR.'/hw-admin-notices/hw-notices-class.php'); #moved this file into hw-hoangweb plugin

/*
localize script:
metaslider.url
metaslider.caption
..see: ml-slider.php > in method 'localize_admin_scripts'
*/
#if(is_admin())
if(class_exists('HW_MLSlider')) {
    HW_MLSlider::get_instance();
}