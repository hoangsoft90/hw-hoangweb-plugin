<?php
/**
 * Module Name: Tabs UI
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
 * Class HW_Module_Tabs
 */
class HW_Module_Tabs extends HW_Module {
    /**
     * main constructor
     */
    public function __construct() {
        HW_HOANGWEB::register_class('HW_Tabs', dirname(__FILE__) . '/class-ui-tabs.php');
        #HW_HOANGWEB::load_class('HW_Tabs');

        $this->enable_tab_settings();
        $this->enable_submit_button();
    }

    /**
     * when module complete loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/admin', false);
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {

    }
    public function admin_enqueue_scripts(){

    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        $oAdminPage->addSettingFields(
        #$this->sSectionID,  // target section id
            array(
                'field_id'          => $this->create_field_name('container_id'),
                'type'              => 'text',
                'title' => 'container_id'
            ),
            array(
                'field_id' => $this->create_field_name('container_class'),
                'type' => 'text',
                'title' => 'container_class'
            ),
            array(
                'field_id' => $this->create_field_name('tabs_menu_class'),
                'type' => 'text',
                'title' => 'tabs_menu_class'
            ),
            array(
                'field_id' => $this->create_field_name('current_tab_class'),
                'type' => 'text',
                'title' => 'current_tab_class'
            ),
            array(
                'field_id' => $this->create_field_name('tab_content_class'),
                'type' => 'text',
                'title' => 'tab_content_class'
            )
        );
    }
}
HW_Module_Tabs::register();