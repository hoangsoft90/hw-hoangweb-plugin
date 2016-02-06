<?php
/**
 * Module Name: Translation
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
 * Class HW_Module_translation
 */
class HW_Module_translation extends HW_Module {
    /**
     * Main class constructor
     */
    public function __construct() {
        include_once(dirname(__FILE__). '/widget/hw-widget-multitranslate.php');  //translate selector widget
        //load HW_mqtranslate class
        HW_HOANGWEB::register_class('HW_mqtranslate', dirname(__FILE__) . '/class-hw_mqtranslate.php');
        HW_HOANGWEB::load_class('HW_mqtranslate');

        add_action('wp_footer', array($this, '_hw_wp_footer'));
    }

    /**
     * @hook wp_footer
     */
    public function _hw_wp_footer() {
        //mqtranslate flags
        if(class_exists('HW_mqtranslate')) HW_mqtranslate::valid_flags_css();
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
add_action('hw_modules_load', 'HW_Module_translation::init');