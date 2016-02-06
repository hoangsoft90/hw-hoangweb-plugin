<?php
# /root> hw-functions.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 25/05/2015
 * Time: 21:20
 */
include_once(HW_HOANGWEB_INCLUDES. '/layout-templates/site.php');

/**
 * get footer, instead of calling get_footer()
 * @param $slug
 */
function hw_get_footer($slug=''){
    HW__Template::get_footer($slug);
}

/**
 * include header.php
 * @param $slug
 */
function hw_get_header($slug='') {
    HW__Template::get_header($slug);
}
/**
 * print all tags from wp blog
 * @param $context: current post or get all tags, accept: 'all','current_post'
 */
function hw_print_tags($context = 'all'){
    HW__Template::print_all_tags($context);
}
/**
 * return template file match any standard file that refer to files theme
 * @param $header_data: file header data in array, if not given get default any template header file in wordpress
 */
function hw_list_active_theme_templates($header_data = array()){
    $result = HW__Template::list_active_theme_templates($header_data);

    return $result;
}


/**
 * display socials icon
 * @param string $skin
 */
function hw_load_socials($skin = '') {
    NHP_Options_socials::do_social_skin($skin);
}
//------------------------------------------------------------------------------
/**
 *
 */
function hw_theme_section() {

}

/**
 * main loop
 */
function hw_theme_get_main() {
    HW__Template::hw_theme_get_main();
}
//print out for every data
if(!function_exists('__print')) {
    function __print($d){
        HW_Logger::out($d);
    }
}
/**
 * register position
 * @param $name
 * @param $text
 */
function register_position($name, $text) {
    if(class_exists('HW_Module_Position',false)) {
        HW_Module_Positions::get_instance()->register_position($name, $text);
    }
}

/**
 * unregister position
 * @param $name
 */
function unregister_position($name) {
    if(class_exists('HW_Module_Positions',0)) HW_Module_Positions::get_instance()->unregister_position($name);
}

/**
 * display or register modules content with position
 * @param $name
 * @param $callback
 */
function __position($name, $callback = null) {
    if($callback ){
        HW_Module_Positions::add_position_hook($name, $callback);
    }
    else HW_Module_Positions::implement_position($name);
}