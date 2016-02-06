<?php
/**
 * Module Name: Contact Form 7
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_Module_Contactform
 */
class HW_Module_Contactform extends HW_Module {
    public function __construct() {
        parent::__construct();
        //main file
        include_once ('hw-wpcf7.php');
        add_filter('hw_valid_custom_submenu', array($this, 'valid_custom_submenu'));
    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {
        $id=$this->add_menu_box(null, '#');
        //$this->add_submenu_box($id,'#', "item 1");
        $this->other_menus_page('wpcf7' );
        $this->enable_config_page('cli/admin');
        //$this->set_exporter('cli/class-export'); old way
    }

    /**
     * @param $menu
     * @return mixed
     */
    public function valid_custom_submenu($menu) {
        if($menu[1] == 'wpcf7_edit_contact_forms' && $menu[2]== 'wpcf7-new') {
            $menu[2] = 'admin.php?page='. $menu[2];
        }
        if($menu[2] == 'hw_wpcf7_settings') {
            $menu[2] = 'admin.php?page='. $menu[2];
        }
        if($menu[2] == 'wpcf7-integration') {
            $menu[2] = 'admin.php?page='. $menu[2];
        }
        return $menu;
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
HW_Module_Contactform::register();
//add_action('hw_modules_load', 'HW_Module_Contactform::init');