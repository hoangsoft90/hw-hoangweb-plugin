<?php
/**
 * Module Name: Yahoo Skype Status
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
include_once ('yahooskype.php');
/**
 * Class HW_Module_YahooSkype
 */
class HW_Module_YahooSkype extends HW_Module {
    public function __construct() {

    }

    /**
     * after module loaded
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
    public function print_head(){}
    public function print_footer(){}
}
HW_Module_YahooSkype::register();