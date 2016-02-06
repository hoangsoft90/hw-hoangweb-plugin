<?php
/*
Plugin Name: HW Menu
Version: 1.0
Plugin URI: http://hoangweb.com
Author: hoangweb.com
License: GPL v3
*/
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_MENU_URL', plugins_url('',__FILE__));
define('HW_MENU_PATH', plugin_dir_path(__FILE__));
define('HW_MENU_PLUGIN_FILE', __FILE__);

//activation hook
include_once('menu-install.php');

/**
 * load common functions
 */
include_once('includes/functions.php');

/**
 * wp_nav_menu settings
 */
include_once('includes/admin/menu-settings.php');

/**
 * add custom metabox to page of menus manager
 */
include_once('includes/metabox-navmenu-page.php');      //beta (un-complete)

/**
 * menu item custom fields
 */
include_once('includes/admin/menu-item-custom-fields.php');

/**
 * show all menu setting in action
 */
include_once('includes/navmenu.php');
//run
HW_NAVMENU::getInstance();