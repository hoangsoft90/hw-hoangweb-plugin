<?php

/**
 * require list of pre-plugins while active new plugin
 * @param array $required_plugins: list of require plugins
 */
function hwyk_require_plugins_list_before_active($required_plugins = array()){
    $message = 'Sory, you need to active plugin "%s". <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>';
    //list required plugin before you can use this plugin correctly
    if(!is_array($required_plugins)) $required_plugins = array();
    #$required_plugins ['hw-hoangweb/hoangweb.php'] = 'hw-hoangweb';

    foreach($required_plugins as $plugin_path => $name){//__save_session($plugin_path,is_plugin_active( $plugin_path ),'hx');
        // Require parent plugin
        if ( ! is_plugin_active( $plugin_path ) and current_user_can( 'activate_plugins' ) ) {
            // Stop activation redirect and show error
            wp_die(sprintf($message,$name));
        }
    }
}

//require HW SKIN plugin
register_activation_hook( HW_YK_PLUGIN_FILE, 'hwyk_require_plugins_activate' );
function hwyk_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hwyk_require_plugins_list_before_active(array(
            #'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else wp_die(__('Sory, please install HW SKIN plugin first before active this plugin.','hwyk'));

}
//load plugin text domain
function hwyk_wnb_load_textdomain() {
    load_plugin_textdomain( 'hwyk', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'hwyk_wnb_load_textdomain');