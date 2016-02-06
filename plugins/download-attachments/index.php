<?php
/**
 * Module Name: Download Attachments
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main plugin file
include ('hw-download-attachments.php');
/**
 * Class HW_Module_downloadattachment
 */
class HW_Module_downloadattachment extends HW_Module {
    /**
     * @var null
     */
    public $skin = null;

    public function __construct() {
        parent::__construct();
        //enable tab settings
        $this->enable_tab_settings();
        $this->enable_submit_button();

        $this->support_fields(array('hw_skin','hw_ckeditor'));
        //getting start
        $obj = HW_Downloadattachment::get_instance($this);
        $obj->_option('module', $this);
    }

    /**
     * module loaded event
     * @return mixed|void
     */
    public function module_loaded() {
        $this->register_help('da', 'help.html','help');
        //set module menu
        $this->add_menu_box(null);
        $this->other_submenus_page('download-attachments-options');
        $this->enable_config_page('cli/admin');
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        #$this->enqueue_scripts();
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {

        return $values;
    }
    public function print_head(){}
    public function print_footer(){}

    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        $this->addFields(

            array(
                'field_id' => 'msg',
                'type' => 'label',
                'description' => 'Cấu hình dữ liệu <a href="'.admin_url('options-general.php?page=download-attachments-options').'" target="_blank">tại đây</a>.',
                'show_title_column' => false,
            ),
            array(
                'field_id' => 'skin',
                'type' => 'hw_skin',
                'title'=> __('Giao diện'),
                'description' => __('Chọn template hiển thị attachments.')  ,
                'external_skins_folder' => 'hw_da_skins',
                'skin_filename' => 'hw-da-skin.php',
                'enable_external_callback' => false,
                'skins_folder' => 'skins',
                'apply_current_path' => HW_DA_PLUGIN_PATH,
                'plugin_url' => HW_DA_PLUGIN_URL,
                'hwskin_field_output_callback' => array($this,'_hwskin_field_output'),
                'set_dropdown_ddslick_setting' => array('width'=>300)
            ),
            array(
                'field_id' => 'download_box_display',
                'type' => 'select',
                'title' => 'Hiển thị',
                'label' => array(
                    'after_content' => __('Chèn cuối bài viết'),
                    'before_content' => __('Chèn trước bài viết'),
                    'manually' => __('Tắt tự động hiển thị')
                )
            ),
            array(
                'field_id' => 'content_before',
                'type' => 'hw_ckeditor',
                'title' => 'content_before',
                'description' => 'Chèn vào trước nội dung'
            ),
            array(
                'field_id' => 'content_after',
                'type' => 'hw_ckeditor',
                'title' => 'content_after',
                'description' => 'Chèn vào sau nội dung'
            )
        );
    }

    /**
     * @param $_aOutput
     * @param $skin
     * @param $field_output
     * @return mixed
     */
    public function _hwskin_field_output($_aOutput,$skin, $field_output) {
        return $_aOutput;
    }
}
HW_Module_downloadattachment::register();
//add_action('hw_modules_load', 'HW_Module_downloadattachment::init');