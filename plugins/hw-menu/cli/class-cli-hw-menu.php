<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/11/2015
 * Time: 10:11
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_CLI_HW_Menu
 */
class HW_CLI_HW_Menu extends HW_CLI_Command {
    /**
     * update nav menu location
     * @param $location
     * @param $menu_id
     */
    public static function set_menu_location($location, $menu_id) {
        // Set the menu to primary menu location
        /*$locations = get_theme_mod( 'nav_menu_locations' );
        $locations[$location] = $menu_id;
        set_theme_mod ( 'nav_menu_locations', $locations );
        */
        if(class_exists('HW_NAVMENU', 0)) HW_NAVMENU::set_menu_location($location, $menu_id);
    }
    /**
     * create nav menu
     * @param $args
     * @param $assoc_args
     */
    public function create_nav_menu($args, $assoc_args) {
        $menu_name = $this->get_cmd_arg($assoc_args, 'menu_name', '');
        $menu_location = $this->get_cmd_arg($assoc_args, 'menu_location', '');
        $items = $this->get_cmd_arg($assoc_args, 'items', array());
        $allow_override = $this->get_cmd_arg($assoc_args, 'override_exist', false);

        $menu_exists = wp_get_nav_menu_object( $menu_name );
        // If it doesn't exist, let's create it.
        if( !$menu_exists){
            $menu_id = wp_create_nav_menu($menu_name);
        }
        else $menu_id = $menu_exists->term_id;

        if($allow_override || !$menu_exists) {
            // Set up default menu items
            /*wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' =>  __('Home'),
                'menu-item-classes' => 'home',
                'menu-item-url' => home_url( '/' ),
                'menu-item-status' => 'publish')
            );
            if(is_array($items))
            foreach($items as $menu_item){
                $title = $menu_item['title'];
                $url = !empty($menu_item['url'])? $menu_item['url'] : home_url();
                $status = !empty($menu_item['status'])? $menu_item['status'] : 'publish';

                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' =>  __($title),
                    'menu-item-url' => $url,
                    'menu-item-status' => $status)
                );
            }*/
            //set menu location
            if($menu_location) self::set_menu_location($menu_location, $menu_id);
            //apply skin
            $options = $this->get_cmd_data('options');
            $this->do_import();
            WP_CLI::success( ' create nav menu "'.$menu_name.'" successful.' );
        }

    }

    /**
     * delete nav menu
     * @param $args
     * @param $assoc_args
     */
    public function del_nav_menu($args, $assoc_args) {
        $menu = $this->get_cmd_arg($assoc_args, 'menu_name', '');
        wp_delete_nav_menu($menu);
        WP_CLI::success( ' delete nav menu "'.$menu.'" successful.' );
    }

}