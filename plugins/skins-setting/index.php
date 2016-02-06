<?php
/**
 * Module Name: Skin Settings
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

require_once (dirname(__FILE__) . '/skins-settings-table.php');
/**
 * Class HW_Tooltip
 */
class HW_Module_skins_settings extends HW_Module {
    public function __construct() {

        //enable tab settings
        $this->enable_tab_settings();
        $this->enable_submit_button();

        //access module setting from outside
        #_Print(HW_Module_skins_settings::get()->get_field_value('field1'));  -->don't get field in constructor
    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        ////access module setting from outside
        #_Print(HW_Module_skins_settings::get()->get_field_value('field1'));
        // Section
        /*$oAdminPage->addSettingSections(
            $this->sPageSlug, // the target page slug
            array(
                'tab_slug'          => $this->sTabSlug,
                'section_id'        => $this->sSectionID,
                'title'             => __( 'Using Callbacks', 'admin-page-framework-demo' ),
                'description'       => __( 'These fields are (re)defined with callbacks.', 'admin-page-framework-demo' ),
            )
        );
*/
        /**
         * Fields to be defined with callback methods - pass only the required keys: 'field_id', 'section_id', and the 'type'.
         */

        $oAdminPage->addSettingFields(
            #$this->sSectionID,  // target section id
            array(
                'field_id' => $this->create_field_name('table_settings'),
                'type' => 'hw_admin_table',
                'title' => '',
                'show_title_column' => false,
                'WP_List_Table' => 'HW_List_Table_Skins_settings',
                //params
                'columns' => array(
                    'id' => __('ID'),
                    'blog_id'=>__('Blog'),
                    'object'=>__('Tên'),
                    #'widget' => __('skin'),
                    'setting' => __('Config'),
                    'status' => __('Status')
                ),
                'sortable_columns' => array(
                    'id' => array('id', false),     #true the column is assumed to be ordered ascending,
                    'blog_id' => array('blog_id' ,false),     #if the value is false the column is assumed descending or unordered.
                    'object' => array('object', false)
                )
            ),

            array()
        );
        #$this->after_fields($oAdminPage);
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
        if($this->is_module_setting_page()) {
            HW_Libraries::enqueue_jquery_libs('jquery-colorbox');
            $this->enqueue_script('assets/scripts.js');
            $this->enqueue_style('assets/style.css');
        }

    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_skins_settings::init');