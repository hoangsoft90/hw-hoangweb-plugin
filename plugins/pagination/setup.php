<?php
/**
 * require HW_HOANGWEB plugin
 */
function hwnavig_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin',
            'wp-pagenavi/wp-pagenavi.php' => 'wp-pagenavi'
        ));
    }
    else{
        wp_die('Vui lòng kích hoạt plugin hw-hoangweb');
    }

}

/**
 * re-order list of actived plugins
 */
function _hwpagenav_move_after_yarpp_when_activation(){
    if(!is_admin()) return;

    if(function_exists('hw_reorder_actived_plugins')) {
        hw_reorder_actived_plugins('hw-pagenavi/hw_pagenavi.php','wp-pagenavi/wp-pagenavi.php');
    }
}

/**
 * @param $callback
 */
function hwpagenavi_init($callback){
    if(is_callable($callback)) {
        call_user_func($callback);
    }
    //for standalone wp plugin, since we migrate to module
    #register_activation_hook( HW_PAGENAVI_PLUGIN_FILE, 'hwnavig_require_plugins_activate' );
    #add_action( 'activated_plugin', '_hwpagenav_move_after_yarpp_when_activation');
}
add_action('init','myinit');
function myinit(){
    //echo HW_PAGENAVI_PATH.'/hw_pagenavi.php';
    //hw_reorder_actived_plugins('hw-yarpp/hw-yarpp.php','yet-another-related-posts-plugin/yarpp.php');
    //hw_reorder_actived_plugins('wp-pagenavi/wp-pagenavi.php','hw-pagenavi/hw_pagenavi.php');
    //_print(get_option('active_plugins'));

}
