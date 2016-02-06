<?php
/**
 * Module Name: Livechat
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
include_once ('livechat.php');
/**
 * Class HW_Module_Livechat
 */
class HW_Module_Livechat extends HW_Module {
    public function __construct() {

    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/config-metabox');
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
     * module data
     * @return array|mixed|void
     */
    public function export() {
        //return get_option();
        return array('a'=>'A');
    }
}
HW_Module_Livechat::register();

