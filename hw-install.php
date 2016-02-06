<?php
#/root>
/**
 * plugins manager
 */
include_once (HW_HOANGWEB_PLUGINS. '/tgmpa-require-plugins.php');

/**
 * return this plugin file
 */
/*
function hw_get_plugin_slug(){
    $wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
    $this_plugin = plugin_basename(trim($wp_path_to_this_file));
    return $this_plugin;
}*/
/**
 * activation hook
 */
function hw_hoangweb_activation_hook() {
    global $wpdb;
    $wpdb->query('-- DROP TABLE IF EXISTS `hw_widgets_settings`;
CREATE TABLE IF EXISTS `hw_widgets_settings` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `widget` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `_group` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `setting` text COLLATE utf8_unicode_ci NOT NULL,
 `description` text COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

}
register_activation_hook( HW_HOANGWEB_PLUGIN_FILE, 'hw_hoangweb_activation_hook' );
/**
 * deactivation hook
 */
function hw_hoangweb_deactivation_hook() {
    delete_option('hw_install_modules');
}
register_deactivation_hook( HW_HOANGWEB_PLUGIN_FILE, 'hw_hoangweb_deactivation_hook' );

/**
 *  runs when any plugin is activated
 * @param $plugin
 * @param $network_activation
 * @hook activated_plugin
 */
function hw_detect_plugin_activation(  $plugin, $network_activation ) {
    // do stuff
    //$t=HW_Setup::_static_option('hooks_detected', 'activated_plugin', true);

}
add_action( 'activated_plugin', 'hw_detect_plugin_activation', 10, 2 );

/**
 *  runs when any plugin is deactivated
 * @param $plugin
 * @param $network_activation
 * @hook deactivated_plugin
 */
function hw_detect_plugin_deactivation(  $plugin, $network_activation ) {
    // do stuff
    //$t=HW_Setup::_static_option('hooks_detected', 'deactivated_plugin', true);

}
add_action( 'deactivated_plugin', 'hw_detect_plugin_deactivation', 10, 2 );

/**
 * order plugins to be loaded before all other plugins
 */
function hw_move_at_first_when_activation(){
    if(!is_admin()) return;
    // ensure path to this file is via main wp plugin path
    hw_reorder_actived_plugins(/*$this_plugin*/HW_HOANGWEB_PLUGIN_SLUG,0);
    /*$active_plugins = get_option('active_plugins');
    $this_plugin_key = array_search($this_plugin, $active_plugins);
    if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
        array_splice($active_plugins, $this_plugin_key, 1);
        array_unshift($active_plugins, $this_plugin);
        update_option('active_plugins', $active_plugins);
    }*/
}

#add_action( 'activated_plugin', 'hw_move_at_first_when_activation');

/**
 * Class HW_Setup
 */
class HW_Setup extends HW_Core{
    public function __construct() {

        add_action( 'admin_notices', array($this, 'hw_getting_start_notices') );

    }

    /**
     * getting start & help user how to begin install hoangweb plugins by show notice in admin
     */
    function hw_getting_start_notices() {
        if(!is_plugin_active('wpfavs/wpfavs.php')){
            ?>
            <div class="updated">
                <p>
                    <?php
                    if(!is_plugin_active('wpfavs/wpfavs.php')){
                        echo 'Để cài đặt và quản lý các plugins cần thiết cho các tính năng của website này, nhấn '.hw_install_plugin_link('wpfavs','vào đây');
                    }
                    ?>
                </p>
            </div>
        <?php
        }
    }

    /**
     * init
     */
    public static function init() {
        new HW_Setup();
    }
}
HW_Setup::init();