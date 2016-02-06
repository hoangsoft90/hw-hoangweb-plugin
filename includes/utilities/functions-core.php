<?php
# /root>

/**
 * print inline message
 * @param $str
 * @param string $type
 * @param $out
 */
function hw_inline_msg($str, $type= 'error', $out=true) {
    $class = array('notice');
    if(strpos($type, 'error') !== false) $class[] = 'error';
    else $class[] = 'updated';

    echo '<div id="message" class="'.join(' ',$class).'">';
    echo '<p>';print_r($str);echo '</p>';
    echo '</div>';
}

/**
 * re-order actived plugins list
 * @param $plugin: plugin path
 * @param $pos: specific position or other plugin path to find position of that plugin (default 0)
 */
function hw_reorder_actived_plugins($plugin, $pos = 0){
    if(!is_admin()) return;
    // ensure path to this file is via main wp plugin path
    $active_plugins = get_option('active_plugins');
    $first_plugin_key = array_search($plugin, $active_plugins);
    if(!is_numeric($pos)) $second_plugin_key = array_search($pos, $active_plugins);
    else $second_plugin_key = $pos;
    if(!is_numeric($pos) && $second_plugin_key == 0) $second_plugin_key++; //don't you should learn index start from 0
    array_splice($active_plugins, $first_plugin_key, 1);    //remove first plugin from list
    if(is_numeric($second_plugin_key))
    {
        $active_plugins = array_merge(array_slice($active_plugins, 0, $second_plugin_key, true)
            , array($plugin)
            , array_slice($active_plugins, $second_plugin_key, count($active_plugins) , true)); //tobe sure get all plugin num ->don't -1

    }

    update_option('active_plugins', $active_plugins);
    /*if ($first_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
        array_splice($active_plugins, $this_plugin_key, 1);
        array_unshift($active_plugins, $this_plugin);
        update_option('active_plugins', $active_plugins);
    }*/
}

/**
 * require list of pre-plugins while active new plugin
 * @param array $required_plugins: list of require plugins
 */
function hw_require_plugins_list_before_active($required_plugins = array()){
    $message = 'Xin lỗi, yêu cầu cài đặt & kích hoạt plugin "%s". <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>';
    //list required plugin before you can use this plugin correctly
    if(!is_array($required_plugins)) $required_plugins = array();
    $required_plugins ['hw-hoangweb/hoangweb.php'] = 'hw-hoangweb';

    foreach($required_plugins as $plugin_path => $name){//__save_session($plugin_path,is_plugin_active( $plugin_path ),'hx');
        // Require parent plugin
        if ( ! is_plugin_active( $plugin_path ) and current_user_can( 'activate_plugins' ) ) {
            // Stop activation redirect and show error
            wp_die(sprintf($message,$name));
        }
    }
}
/**
 * get install plugin link
 * @param $plugin
 * @param $check_exists: check plugin for exists if not return plugin activation link
 * @param array $query_args
 * @return string
 */
function hw_install_plugin_link($plugin,$title = '',$check_exists = false,$query_args = array()){
    $action = 'install-plugin';
    $slug = $plugin;
    $args = array(
        //'action' => $action,
        'plugin' => $slug,
        'tab' => 'plugin-information',
        'TB_iframe' => true,
        'width' => 640,
        'height' => 500
    );
    if(is_array($query_args)) $args = array_merge($args, $query_args);
    //method 1
    /*$url = wp_nonce_url(
        add_query_arg(
            $args,
            //admin_url( 'update.php' )
            admin_url('plugin-install.php')
        ),
        $action.'_'.$slug
    );*/
    //method 2
    $url = add_query_arg(
        $args,
        network_admin_url( 'plugin-install.php' )
    );
    if(!$title) $title = $plugin;
    if($check_exists && function_exists('is_plugin_active') && is_plugin_active($plugin)){
        return ;
    }
    return '<a href="'.$url.'" class="thickbox" title="Cài đặt '.$plugin.'">'.$title.'</a>';
}
/***
 * add wp option
 * @param $name
 * @param $value
 * @param bool $merge_array
 */
function hw_add_wp_option($name, $value, $merge_array = false) {

    $data = get_option($name);
    if(!$data && $merge_array) {
        $data = array();
        add_option($name, $data);
    }
    if( $merge_array) {
        if(!is_array($value) ) $value = (array) $value;
        $data = array_merge($data,$value);
    }
    else $data = $value;

    update_option($name, $data);
}
/**
 * hoangweb get common theme option
 * @param string $name
 * @param string $default default value
 * @return mixed|string
 */
function hw_option($name = '', $default=''){
    //we will get group of option by nhp
    $opts =  get_option('nhp_hoangweb_theme_opts');
    if(isset($opts[$name]) && $opts[$name] !== "") return $opts[$name];else return $default;
}

/**
 * return one of setting
 * @param $name
 * @param string $default
 * @param string $group
 * @return mixed|null|void
 */
function hw_get_setting($name, $default = '',$group = '') {
    if(empty($group)) $group = 'general';

    if(is_array($name)) $field = $name;
    else $field = array($group, $name);

    return AdminPageFramework::getOption( 'HW_HOANGWEB_Settings', $field, $default );
}

/**
 * register module activation hook
 * @param $module_file
 * @param $callback
 */
function hw_register_activation_hook($module_file, $callback) {
    HW_HOANGWEB::register_activation_hook($module_file, $callback);
}

/**
 * register module deactivation hook
 * @param $module_file
 * @param $callback
 */
function hw_register_deactivation_hook($module_file, $callback) {
    HW_HOANGWEB::register_deactivation_hook($module_file, $callback);
}

/**
 * register theme deactivation hook
 * @param $code
 * @param $function
 */
function hw_register_theme_deactivation_hook($code, $function) {
    HW__Template::register_theme_deactivation_hook($code, $function);
}

/**
 * register theme activation hook
 * @param $code
 * @param $function
 */
function hw_register_theme_activation_hook($code, $function) {
    HW__Template::register_theme_activation_hook($code, $function);
}
/**
 * list of all mimes type
 * @return array
 */
function hw_list_mines_type() {
    $mimes = array(
        'x3d' => 'application/vnd.hzn-3d-crossword',
        '3gp' => 'video/3gpp',
        '3g2' => 'video/3gpp2',
        'pdf' => 'application/pdf',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'exe' => 'application/x-msdownload',
        'pub' => 'application/x-mspublisher'
    );
    return $mimes;
}
/**
 * checks is WP is at least a certain version (makes sure it has sufficient comparison decimals
 */
function is_wp_version( $is_ver ) {
    $wp_ver = explode( '.', get_bloginfo( 'version' ) );
    $is_ver = explode( '.', $is_ver );
    for( $i=0; $i<=count( $is_ver ); $i++ )
        if( !isset( $wp_ver[$i] ) ) array_push( $wp_ver, 0 );

    foreach( $is_ver as $i => $is_val )
        if( $wp_ver[$i] < $is_val ) return false;
    return true;
}

/**
 * detect ajax request in wp
 * @return bool
 */
if(!function_exists('is_ajax')):
function is_ajax() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return true;
    }
    return false;
}
endif;
/**
 * @return bool
 */
if(!function_exists('is_xmlrpc')):
function is_xmlrpc() {
    return defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
}
endif;
/**
 * detect whether content created by behind calling
 * @return bool
 */
function is_call_behind() {
    return is_ajax() || is_xmlrpc() || class_exists('WP_CLI_Command',0);
}
/**
 * get search query
 * @return string|void
 */
function hw_admin_search_query($name='s') {
    return esc_attr( wp_unslash(hw__req( $name)));
}

/**
 * return ajax handle name
 * @return string
 */
function hw_get_ajax_handle() {
    return HW_HOANGWEB::get_active_ajax_handle();
}
/**
 * get POST data
 * @param $key
 * @param string $value default value
 * @return string
 */
function hw__post($key, $value='') {
    return isset($_POST[$key])? $_POST[$key] : $value;
}
/**
 * get GET data
 * @param $key
 * @param string $value default value
 * @return string
 */
function hw__get($key, $value='') {
    return isset($_GET[$key])? esc_attr($_GET[$key]) : $value;
}
/**
 * get $_REQUEST data
 * @param $key
 * @param string $value default value
 * @return string
 */
function hw__req($key, $value='') {
    return isset($_REQUEST[$key])? esc_attr($_REQUEST[$key]) : $value;
}

/**
 * include template file
 * @param $file
 * @param array $data
 */
function hw_include_template($file, $data = array()) {
    if(!preg_match('%\.phtml$%', $file)) $file .= '.phtml'; //valid extension
    if(file_exists(HW_HOANGWEB_PATH. '/includes/phtml/'. $file)) {
        if(is_array($data)) extract($data);
        include(HW_HOANGWEB_PATH. '/includes/phtml/'. $file);
    }
}
/**
 * return autoload config
 * @param $config
 */
function hw_get_autoload($config) {
    return HW_Autoload/*::get_instance()->*/::get_autoload($config);
}
/**
 * autoload class
 * @param $lib
 */
function __autoload($lib)
{
    if(class_exists('HW_Autoload', false)) HW_Autoload::hw_load_class($lib);
}
/**
 * load your class
 * @param $lib
 */
function hw_load_class($lib) {
    if(class_exists('HW_Autoload', false)) HW_Autoload::hw_load_class($lib);
}

/**
 * @param $name
 * @param $class
 * @param bool $autoload
 * @return HW_Admin_Options singleton for class
 */
function _hw_global($name, $class='', $autoload=false) {
    if(!isset($GLOBALS['hoangweb'])) $GLOBALS['hoangweb'] = array();
    if(!isset($GLOBALS['hoangweb'][$name]) && $class ) {
        if( is_string($class) && class_exists($class ,$autoload)  ) {
            if(method_exists($class, 'get_instance')) $GLOBALS['hoangweb'][$name] = call_user_func(array($class, 'get_instance'));
            else $GLOBALS['hoangweb'][$name] =  new $class;
        }
        elseif(is_object($class)) {
            $GLOBALS['hoangweb'][$name] = $class;
        }
    }

    return isset($GLOBALS['hoangweb'][$name])? $GLOBALS['hoangweb'][$name] : null;
}
//modules utilities
include_once ('modules.php');