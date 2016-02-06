<?php
/**
 * require plugin while active this plugin
 */
function hwcf7_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else wp_die('Vui lòng kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');
}
register_activation_hook( HW_WPCF7_PLUGIN_FILE, 'hwcf7_require_plugins_activate' );