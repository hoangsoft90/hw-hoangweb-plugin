<?php
/**
 * Module Name: Socials
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
 * Class HW_Module_Socials
 */
class HW_Module_Socials extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {

    }
    //register help
    public function module_loaded() {
        $this->register_help('social','help.html', 'help'); //  {CURRENT_MODULE}/help/helps_view/help.html
    }
    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {

    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_Socials::init');