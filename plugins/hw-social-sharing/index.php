<?php
/**
 * Module Name: Social Sharing
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
include_once ('hw-social-sharing.php');
/**
 * Class HW_Module_SocialSharing
 */
class HW_Module_SocialSharing extends HW_Module {
    public function __construct() {

    }

    /**
     * module loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/admin');
        $this->add_menu_box(null,'#');
        $this->other_menus_page('hw_social_option');
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
add_action('hw_modules_load', 'HW_Module_SocialSharing::init');