<?php
/**
 * Module Name: Map
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once(dirname(__FILE__). '/widget-map.php');  //map widget

/**
 * Class HW_Module_Tooltip
 */
class HW_Module_Gmap extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        HW_HOANGWEB::register_class('HW_Gmap', dirname(__FILE__). '/includes/class-ui-gmap.php');
        //enable setting tab for this module
        $this->enable_tab_settings();
        $this->enable_submit_button();

        //register shortcode for display map
        add_shortcode('hw_googlemap', array($this, '_hw_render_google_map'));
    }

    /**
     * after module loaded
     */
    public function module_loaded() {

        $this->enable_config_page('cli/admin', false);
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        $data = array();
        //parse geocode from address
        $address = $this->get_field_value('address');
        $data['address_text'] = $address;
        if(class_exists('HW_Gmap',true)) {
            $address_location = HW_Gmap::getLocationFromAddress($address);
            if($address_location) $data['address_location'] =  $address_location;
        }

        $handle = $this->enqueue_script('assets/map.js', array('jquery'));
        $this->localize_script($handle, '__hw_module_map', $data);

    }

    /**
     * render map on frontend
     * @param $atts shortcode attributes
     * @param $content
     * @shortcode hw_googlemap
     */
    public function _hw_render_google_map($atts = array(), $content='') {
        if(class_exists('HW_Gmap')) {
            $map = HW_Gmap::get_instance();
            $map->_option('module', $this); //reference module to class instance

            $this->enqueue_script('https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places',null, 'maps.googleapis.com');
            $this->enqueue_style('assets/map.css');
            return $map->render_googlemap($atts);
        }

    }
    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        #$this->enqueue_script('admin-tooltip.js');
        #HW_Libraries::enqueue_jquery_libs('tooltipster');
        $this->enqueue_scripts();
    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        $field_addr = $this->create_field_name('address');  //address field
        $field_location = $this->create_field_name('location');

        $oAdminPage->addSettingFields(
        #$this->sSectionID,  // target section id
            array(
                'field_id'          => $field_addr,
                'type'              => 'text',
                'title' => 'Địa chỉ',
                'description' => '<a href="javascript:void(0)" onclick="__hw_module_map.get_location(this, jQuery(\'#'.$field_addr.'__0\').val(),\'#'.$field_location.'__0\')">Lấy tọa độ</a>'
            ),
            array(
                'field_id' => $field_location, //to store address location
                'type' => 'hidden'
            ),
            array(
                'field_id' => $this->create_field_name('show_searchbox'),
                'type' => 'checkbox',
                'title' => 'Hiển thị hộp tìm kiếm.'
            ),
            array(
                'field_id' => $this->create_field_name('width'),
                'type' => 'text',
                'title' => 'Width (px/%)',
                'description' => '(mặc định px)'
            ),
            array(
                'field_id' => $this->create_field_name('height'),
                'type' => 'text',
                'title' => 'Height (px/%)',
                'description' => '(mặc định px)'
            ),
            array()
        );
    }
}
HW_Module_Gmap::register();

function hw_module_map_register_activation_hook() {
    #_print('activation hook for gmap');
}
hw_register_activation_hook(__FILE__, 'hw_module_map_register_activation_hook');

function hw_module_map_register_deactivation_hook(){
    #_print('deactivation hook for gmap');
}
hw_register_deactivation_hook(__FILE__, 'hw_module_map_register_deactivation_hook');