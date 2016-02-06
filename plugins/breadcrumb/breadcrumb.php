<?php
/**
 * Module Name: Breadcrumb
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/10/2015
 * Time: 20:42
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main file
include_once ('hw-bcn.php');
/**
 * Class HW_Module_Breadcrumb
 */
class HW_Module_Breadcrumb extends HW_Module {
    public function __construct() {

    }

    /**
     * module loaded
     */
    public function module_loaded() {
        $this->add_menu_box(null, '#');
        $this->other_submenus_page('breadcrumb-navxt');
        $this->add_to_position(array($this, '_display_breadcrumb'));
        $this->enable_config_page('cli/admin');
    }
    //display breadcrumb
    public function _display_breadcrumb() {
        if(function_exists('hw_display_breadcrumb')) {
            echo '<div class="content_breadcrumb breadcrumbs">';
            hw_display_breadcrumb();
            echo '</div>';
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
}
HW_Module_Breadcrumb::register();
#add_action('hw_modules_load', 'HW_Module_Breadcrumb::init');