<?php
/**
 * Module Name: Condition
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
 * Class HW_Module_condition
 */
class HW_Module_condition extends HW_Module {
    /**
     * Main class constructor
     */
    public function __construct() {
        require_once('includes/hw-condition.php');
        require_once('includes/dynamic-conditions.php');
        HW_Conditions_Manager::init_metabox();
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
}
add_action('hw_modules_load', 'HW_Module_condition::init');