<?php
/**
 * Module Name: HW YARPP
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//get Yet another related plugin
include ('plugin/yarpp/yarpp.php');

/**
 * Class HW_Module_YARPP
 */
class HW_Module_YARPP extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        //main file
        include_once ('hw-yarpp.php');
        add_action('admin_menu', array($this, 'admin_menu'),2000);
    }
    function admin_menu(){
        global $menu;
        global $submenu;
        //_print($menu);_print($submenu);
    }
    /**
     * after module loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $id = $this->add_menu_box(null, '#');
        #$this->add_submenu_box($id, 0 , 'Quản lý galleries');
        #$this->add_submenu_box($id, 0 , 'Thêm mới');
        #$this->other_menus_page('hoangweb-theme-options');
        $this->other_submenus_page('hw_yarpp');
        $this->enable_config_page('cli/admin');
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_YARPP::init');