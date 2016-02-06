<?php
/**
 * Module Name: Customize Menu
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main file
include_once ('hw-menu.php');   //note: do not use menu.php this name same as wp core file, so i renamed to hw-menu.php
/**
 * Class HW_Module_NavMenu
 */
class HW_Module_NavMenu extends HW_Module {
    public function __construct() {

    }
    public function module_loaded() {
        $this->enable_tab_settings();   //enable setting tab
        $this->enable_config_page('cli/box-installer');
        $this->enable_submit_button();
    }
    /**
     * Triggered when the tab is loaded.
     * @Param $oAdminPage
     */
    public function replyToAddFormElements($oAdminPage) {
        $locations = get_registered_nav_menus();
        $menus = HW_NAVMENU_settings::get_all_created_navmenus('id');

        $this->addFields(
            array(
                'field_id' => '__primary_menu__',
                'type' => 'select',
                'title' => 'Menu chính',
                'label' => $locations
            ),
            array(
                'field_id' => '__secondary_menu__',
                'type' => 'select',
                'title' => 'Menu phụ',
                'label' => $locations
            )

        );
        $this->addBreakLine();
        foreach($locations as $location => $desc) {
            $field = array(
                'field_id' => $location,
                'type' => 'select',
                'title'=> $desc,
                'label' => $menus
            );
            $this->addField($field);
        }

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
    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        $locations = get_registered_nav_menus();
        foreach($values as $field => $menu) {
            foreach(array_keys($locations) as $location) {
                if($this->real_field_name($field) == HW_Validation::valid_apf_slug($location)) {
                    HW_NAVMENU::set_menu_location($location, $menu);
                    break;
                }
            }

        }

        return $values;
    }
}
HW_Module_NavMenu::register();