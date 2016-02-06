<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 21:07
 */

/**
 * get awc apf option
 * @param string $opt: give name of option want to getting
 * @param string $default: default value    (optional)
 * @param string $group: group section name (optional)
 */
function hw_navmenu_option($opt='',$default='',$group = ''){
    if(!$opt) return AdminPageFramework::getOption( 'HW_NAVMENU_settings'); //return all fields value in section
    if($group) return AdminPageFramework::getOption( 'HW_NAVMENU_settings', array($group,$opt), $default );
    else return AdminPageFramework::getOption( 'HW_NAVMENU_settings', $opt, $default );
}

/**
 * display wp_nav_menu
 * @param array $args: wp_nav_menu args
 * @param $position: specific menu
 */
function hw_wp_nav_menu( $args = array(), $position = 'main') {
    //get menu name or location
    if($position == 'top') $menu = 'menu-top';
    elseif($position == 'footer') {
        $menu = 'menu-footer';
    }
    else $menu = 'primary';

    $_args = array(
        'theme_location' => $menu
    );
    $args = array_merge((array)$args, $_args);
    //modify arguments
    $args['echo'] = 0;

    echo apply_filters('hw_wp_nav_menu', wp_nav_menu($args));
}

/**
 * return all created menus
 * @return array|WP_Error
 */
function hw_get_all_menus() {
    return get_terms('nav_menu');
}
/**
 * get qtranslate switcher
 */
function hw_get_qtrans_switcher() {
    return class_exists('NHP_Options_mqtranslate_Frontend')? NHP_Options_mqtranslate_Frontend::get_qtrans_switcher() : '';
}
/**
 * theme setup
 * Sets up theme defaults and registers the various WordPress features that
 */
add_action( 'after_setup_theme', 'hw_menu_theme_setup' );
function hw_menu_theme_setup(){
    // This theme uses wp_nav_menu() in one location.
    register_nav_menu( 'primary', __( 'Menu chính' ) );
    register_nav_menu( 'menu-top', __( 'Menu top' ) );
    register_nav_menu( 'menu-footer', __( 'Menu footer' ) );

    //WordPress will render its built-in HTML5 search form:
    add_theme_support( 'html5', array( 'search-form' ) );
}

/**
 * fix home_url
 * @param $url
 * @param $what
 * @return mixed|string
 */
function hw_menu_qtrans_convertHomeURL($url, $what) {
    if(function_exists('qtrans_convertURL') && $what=='/') return qtrans_convertURL($url);
    return $url;
}

add_filter('home_url', 'hw_menu_qtrans_convertHomeURL', 10, 2);

/**
 * init helps module
 */
add_action('init', '_hw_navmenu_init');
function _hw_navmenu_init(){
    if(class_exists('HW_HELP')){
        HW_HELP::set_helps_path('navmenu', HW_MENU_PATH.'helps');
        HW_HELP::set_helps_url('navmenu', HW_MENU_URL .'/helps' );
        HW_HELP::register_help('navmenu');
        HW_HELP::load_module_help('navmenu');
    }
}