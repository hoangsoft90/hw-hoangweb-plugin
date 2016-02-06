<?php
/**
 * Module Name: Widget ty gia thoi tiet
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once ('hw-widget-weather-tygia.php');
/**
 * Class HW_Module_Weather
 */
class HW_Module_Weather extends HW_Module {
    public function __construct() {

    }
    public function module_loaded() {
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
add_action('hw_modules_load', 'HW_Module_Weather::init');