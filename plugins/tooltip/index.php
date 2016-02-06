<?php
/**
 * Module Name: Tooltip
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
 * Class HW_Module_Tooltip
 */
class HW_Module_Tooltip extends HW_Module {
    public function __construct() {

    }
    public function module_loaded(){
        $this->register_help('tooltip', 'readme.txt');
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
        $this->enqueue_script('admin-tooltip.js');
        HW_Libraries::enqueue_jquery_libs('tooltips/tooltipster');
    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_Tooltip::init');