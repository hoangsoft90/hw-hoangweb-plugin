<?php
/**
 * Module Name: Hiển thị nội dung theo chuyên mục
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
include_once ('hw-taxonomy-post-list-widget.php');
/**
 * Class HW_Module_TPL
 */
class HW_Module_TPL extends HW_Module {
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
add_action('hw_modules_load', 'HW_Module_TPL::init');