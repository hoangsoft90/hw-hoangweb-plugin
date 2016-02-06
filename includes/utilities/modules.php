<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 22:04
 */
/**
 * fetch module info from file
 * @param $file
 */
function get_module_data($file) {
    $header = array(
        'name'          => 'Module Name',
        'description'   => 'Description',
        'version' => 'Version',
        'author'        => 'Author',
        'uri'           => 'Author URI',
    );
    if(file_exists($file)) {
        return get_file_data($file, $header);
    }
}
/**
 * return list of installed hw modules
 */
function hw_get_modules($refresh=false) {#update_option('hw_install_modules', array());
    static $modules;
    if(!$modules || $refresh) {
        $modules = get_option('hw_install_modules');#delete_option('hw_install_modules');
        if(empty($modules)) { //first init option database
            $modules =array();
            add_option('hw_install_modules', $modules);
        }
    }
    return $modules;
}

/**
 * check whether module is actived
 * @param $name
 * @return bool
 */
function hw_is_active_module($name) {
    $modules = hw_get_modules();
    foreach($modules as $module) {
        if(isset($module[0]) && $name == $module[0]) return true;
    }
    return false;
}

/**
 * check whether module is inactive
 * @param $name
 * @return bool
 */
function hw_is_inactive_module($name) {
    return hw_is_active_module($name);
}
/**
 * active module by slug
 * @param $module one or more module data
 * @param int|string $pos
 */
function hw_activate_modules($module, $pos = '') {
    static $actived_modules;
    $modules = array();
    if(!$actived_modules) $actived_modules = hw_get_modules(true);
    $count= count($actived_modules);

    if(isset($module[0]) && !is_array($module[0])) $modules[] = $module;    //add single module to array
    else $modules = $module;

    foreach($modules as $_module) {
        if(!isset($actived_modules[$_module[0]]) && HW_TGM_Module_Activation::get_modules($_module[0])) {
            //get register module meta data
            $info = HW_TGM_Module_Activation::get_modules($_module[0]);
            if(empty($info['position'])) $info['position']= count($actived_modules);

            //$actived_modules[$_module[0]] = $_module;
            $actived_modules = hwArray::add_item_keyval($actived_modules, $_module[0], $_module, $info['position']);
            //load new module file for initialize
            HW_HOANGWEB::load_module($_module[0]);

            //activation hook for module
            $activation_hooks = HW_HOANGWEB::register_activation_hook(realpath(HW_HOANGWEB_PLUGINS .'/'.$_module[0]));
            if(is_array($activation_hooks))
                foreach($activation_hooks as $activation_hook) {
                    if(is_callable($activation_hook)) {
                        call_user_func($activation_hook);
                        usleep(200);    //sleep in miliseconds
                    }
                }
        }
    }
    //save option to db
    if($count !== count($actived_modules)) {
        update_option('hw_install_modules', $actived_modules);
        HW_Cache::reset_wp_menus_caches();
        return true;
    }
    return false;
}

/**
 * deactive one or more module
 * @param $module one or more module names
 */
function hw_deactivate_modules($module) {
    static $actived_modules;
    $modules = array();
    if(!$actived_modules) $actived_modules = hw_get_modules(true);
    $count= count($actived_modules);

    if(isset($module[0]) && !is_array($module[0])) $modules[] = $module;    //add single module to array
    elseif(is_string($module)) $modules[] = array($module);
    else $modules = $module;

    foreach((array)$modules as $_module) {
        if(isset($_module[0]) && is_string($_module[0]) && isset($actived_modules[$_module[0]])) {
            unset($actived_modules[$_module[0]]);
            //deactivation hook for module
            $hooks = HW_HOANGWEB::register_deactivation_hook(realpath(HW_HOANGWEB_PLUGINS .'/'.$_module[0]));
            if(is_array($hooks))
                foreach($hooks as $hook) {
                    if(is_callable($hook)) {
                        call_user_func($hook);
                        usleep(200);    //sleep in miliseconds
                    }
                }

        }
    }
    //save option to db
    if($count != count($actived_modules)) {
        update_option('hw_install_modules', $actived_modules);
        HW_Cache::reset_wp_menus_caches();
    }#__save_session('a',$actived_modules);
}
/**
 * Retrieve a URL within the modules
 * @param string $path
 * @param string $plugin
 * @return mixed|void
 */
function hw_modules_url( $path = '', $plugin = '' ) {

    $path = wp_normalize_path( $path );
    $plugin = wp_normalize_path( $plugin );
    $url = HW_HOANGWEB_PLUGINS_URL;


    $url = set_url_scheme( $url );

    if ( !empty($plugin) && is_string($plugin) ) {
        $folder = dirname(plugin_basename($plugin));
        if ( '.' != $folder )
            $url .= '/' . ltrim($folder, '/');
    }

    if ( $path && is_string( $path ) )
        $url .= '/' . ltrim($path, '/');

    /**
     * Filter the URL to the plugins directory.
     *
     * @since 2.8.0
     *
     * @param string $url    The complete URL to the plugins directory including scheme and path.
     * @param string $path   Path relative to the URL to the plugins directory. Blank string
     *                       if no path is specified.
     * @param string $plugin The plugin file path to be relative to. Blank string if no plugin
     *                       is specified.
     */
    return apply_filters( 'hw_modules_url', $url, $path, $plugin );
}
/**
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 * copy from: wp-includes/plugin.php > plugin_dir_path function
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the filesystem path of the directory that contains the plugin.
 */
function hw_module_dir_path( $file ) {
    return trailingslashit( dirname( $file ) );
}