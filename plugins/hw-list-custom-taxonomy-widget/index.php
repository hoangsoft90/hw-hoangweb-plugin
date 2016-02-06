<?php
/**
 * Module Name: List Custom Taxonomy Widget
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//include main file
include_once ('list-custom-taxonomy-widget.php');
/**
 * Class HW_Module_LCT
 */
class HW_Module_LCT extends HW_Module {
    public function __construct() {

    }
    public function module_loaded() {
        $this->enable_config_page('cli/admin', false);
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
HW_Module_LCT::register();