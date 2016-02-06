<?php
/**
 * Module Name: Widget Marquee
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/10/2015
 * Time: 10:23
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_MARQUEE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_MARQUEE_PLUGIN_URL', plugins_url('', __FILE__));

/**
 * functions
 */
include_once (dirname(__FILE__). '/includes/functions.php');
/**
 * marquee text widget
 */
include_once(dirname(__FILE__). '/widget/hw-widget-marquee.php');

/**
 * Class HW_Module_marquee
 */
class HW_Module_marquee extends HW_Module {
    public function __construct() {
        HW_Marquee_Widget::init();
    }
    /**
     * when module complete loaded
     * @return mixed|void
     */
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
}
HW_Module_marquee::register();
