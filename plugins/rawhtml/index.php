<?php
/**
 * Module Name: Raw HTML
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
 * Time: 13:37
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Module_rawhtml
 */
class HW_Module_rawhtml extends HW_Module {
    public function __construct() {
        hwlib_register(
            'HW_Rawhtml',
            'rawhtml'
        );
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
HW_Module_rawhtml::register();