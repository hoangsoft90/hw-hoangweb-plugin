<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/05/2015
 * Time: 14:05
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//require HW_HOANGWEB plugin
function hw_bcn_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin',
            'breadcrumb-navxt/breadcrumb-navxt.php'
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
//for standalone wp plugin, since we migrate to module
#register_activation_hook( HW_BREADCRUMB_PLUGIN_FILE, 'hw_bcn_require_plugins_activate' );   //not support for multisite