<?php
/**
 * Module Name: Ngrok Share Your localhost
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//include plugin file
include_once ('hw-ngrok-hoang.php');
/**
 * Class HW_Module_NgrokLocalhost
 */
class HW_Module_NgrokLocalhost extends HW_Module {
    public function __construct() {

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
add_action('hw_modules_load', 'HW_Module_NgrokLocalhost::init');