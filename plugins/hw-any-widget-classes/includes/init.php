<?php
#/root>includes/functions.php

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 22:32
 */
//require HW_HOANGWEB plugin

function hw_awc_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
//for standalone wp plugin, since we migrate to module
#register_activation_hook( HW_AWC_PLUGIN_FILE, 'hw_awc_require_plugins_activate' );

function _hw_awc_require_plugins_activate() {
    //create table if not exists
    $sql = "
    CREATE TABLE IF EXISTS `hw_widgets_settings` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `widget` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `_group` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `setting` text COLLATE utf8_unicode_ci NOT NULL,
 `description` text COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ";
    global $wpdb;
    $wpdb->query($sql);
}
hw_register_activation_hook(HW_AWC_PLUGIN_FILE, '_hw_awc_require_plugins_activate');
/**
 * @hook init
 * register help for module
 */
function _hw_awc_init(){
    if(class_exists('HW_HELP')){
        HW_HELP::set_helps_path('awc', HW_AWC_PATH.'helps');
        HW_HELP::register_help('awc');
        HW_HELP::load_module_help('awc');
    }
}
add_action('init', '_hw_awc_init');

