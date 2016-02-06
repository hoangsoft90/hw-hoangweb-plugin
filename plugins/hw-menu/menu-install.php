<?php
//require HW_HOANGWEB plugin
#register_activation_hook( HW_MENU_PLUGIN_FILE, 'hw_menu_require_plugins_activate' ); //for standalone wp plugin, since we migrate to module
function hw_menu_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
/**
 * re-order list of actived plugins
 */
function _hwmenu_move_after_yarpp_when_activation(){
    if(!is_admin()) return;

    if(function_exists('hw_reorder_actived_plugins')) {
        hw_reorder_actived_plugins('hw-menu/menu.php','hw-skin/hw-skin.php');
    }
}
#add_action( 'activated_plugin', '_hwmenu_move_after_yarpp_when_activation');   //for standalone wp plugin, since we migrate to module