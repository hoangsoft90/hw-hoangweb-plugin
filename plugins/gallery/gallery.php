<?php
/**
 * Module Name: Gallery
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main entry
include_once ('hw-gallery.php');


/**
 * Class HW_Module_Gallery
 */
class HW_Module_Gallery extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        add_action('wp_head', array($this, '_put_stuff_in_head'),1,1);

        $this->enable_tab_settings();   //enable setting tab

        if( class_exists('Envira_Gallery_Metaboxes_Lite')) {    //for admin
            $this->enable_submit_button();
        }
        else $this->enable_submit_button(false);

    }

    /**
     * after module is complete loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->register_help('gallery' );
        $id = $this->add_menu_box(null, '#');
        #$this->add_submenu_box($id, HW_WP_URL::manage_posttype_url('hw-gallery') , 'Quản lý galleries');
        #$this->add_submenu_box($id, HW_WP_URL::add_new_posttype_url('hw-gallery') , 'Thêm mới');
        $this->other_menus_page('hw-gallery');
        $this->enable_config_page('cli/admin');
    }
    /**
     * Triggered when the tab is loaded.
     * @param $oAdminPage (depricated)
     */
    public function replyToAddFormElements($oAdminPage) {
        /*if(!class_exists('Envira_Gallery_Metaboxes_Lite') && !is_plugin_active('envira-gallery-lite')) {
            $oAdminPage->addSettingFields(
            #$this->sSectionID,  // target section id
                array(
                    'field_id'          => $this->create_field_name('msg'),
                    'type'              => 'label',
                    'title' => '',
                    'description' => 'Xin lỗi bạn cần kích hoạt plugin (<a href="https://wordpress.org/plugins/envira-gallery-lite/" target="_blank">envira-gallery-lite</a>).'
                )
            );
        }*/
        $this->addFields(
            array(
                'field_id' => 'setting-page',
                'type' => 'label',
                'description' => '<a href="'.HW_WP_URL::manage_posttype_url('hw-gallery').'" target="_blank">Quản lý gallery</a>',
                'show_title_column' => false
            )
        );
        $this->after_fields($oAdminPage);
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

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        #$this->enqueue_script('admin-tooltip.js');
        #HW_Libraries::enqueue_jquery_libs('tooltipster');
    }
}
HW_Module_Gallery::register();
//add_action('hw_modules_load', 'HW_Module_Gallery::init');

