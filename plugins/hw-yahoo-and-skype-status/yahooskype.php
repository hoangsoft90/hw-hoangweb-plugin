<?php
/*
Plugin Name: HW Yahoo Skype Status Pro
Plugin URI: http://hoangweb.com
Description: <strong>Display yahoo skype status</strong> on the website by using widget.
Author: Hoangweb.com
Version: 1.0
*/
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_YK_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_YK_PLUGIN_URL', plugins_url('', __FILE__));
define('HW_YK_PLUGIN_FILE', __FILE__);

require_once ('includes/functions.php');

//widget
include_once('includes/hwyk-widget.php');

?>