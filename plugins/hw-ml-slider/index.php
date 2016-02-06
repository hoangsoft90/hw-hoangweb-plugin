<?php
/**
 * Module Name: HW Slider
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main file
include_once ('hw-ml-slider.php');

/**
 * Class HW_Module_Slider
 */
class HW_Module_Slider extends HW_Module {
    public function __construct() {
        $this->enable_tab_settings();
        $this->enable_submit_button();
        $this->support_fields('hw_html');
        $this->add_to_position(array($this, 'display_slider'));
    }

    /**
     * add slider to position on frontend
     */
    public function display_slider() {
        echo do_shortcode('[hw_metaslider id='.hw_option('main_slider_id',1).']');
    }

    /**
     * module_loaded event
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/config-stats');
        $this->add_menu_box(null);
        $this->other_menus_page(array('hw-metaslider', 'hwml_shortcode' ));

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
add_action('hw_modules_load', 'HW_Module_Slider::init');