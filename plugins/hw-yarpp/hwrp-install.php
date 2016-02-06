<?php
/**
 * require HW_HOANGWEB plugin
 */
function hwyarpp_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin',
            'yet-another-related-posts-plugin/yarpp.php' => 'yet-another-related-posts-plugin'  //required yarpp plugin
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
//for standalone wp plugin, since we migrate to module
#register_activation_hook( HWRP_PLUGIN_FILE, 'hwyarpp_require_plugins_activate' );
/**
 * re-order list of actived plugins
 */
function _hwrp_move_after_yarpp_when_activation(){
    if(!is_admin()) return;

    if(function_exists('hw_reorder_actived_plugins')) {
        hw_reorder_actived_plugins('hw-yarpp/hw-yarpp.php','yet-another-related-posts-plugin/yarpp.php');
    }
}
//for standalone wp plugin, since we migrate to module
#add_action( 'activated_plugin', '_hwrp_move_after_yarpp_when_activation');

?>