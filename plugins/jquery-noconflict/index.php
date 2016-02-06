<?php
/**
 * Module Name: Jquery noConflict
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
 * Class HW_Tooltip
 */
class HW_Module_jQueryConflict extends HW_Module {
    public function __construct() {
        add_action('wp_head', array($this, '_put_stuff_in_head'),1,1);

    }

    /**
     * @hook wp_head
     */
    public function _put_stuff_in_head() {
        #echo '<script>jQuery.noConflict();</script>';
    }

    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        $this->enqueue_script('assets/no-conflict.js', array('jquery'));
        $this->enqueue_script('http://code.jquery.com/jquery-migrate-1.2.1.min.js');
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        #$this->enqueue_script('admin-tooltip.js');
        #HW_Libraries::enqueue_jquery_libs('tooltipster');
    }
}
add_action('hw_modules_load', 'HW_Module_jQueryConflict::init');