<?php
/**
 * Module Name: Importer
 * Module URI:
 * Description:
 * Version:
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_IE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_IE_PLUGIN_URL', plugins_url('',__FILE__));
define('HW_WIE_PLUGIN_PATH', HW_IE_PLUGIN_PATH. '/plugin/widget-importer-exporter');
define('HW_WIE_PLUGIN_URL', HW_IE_PLUGIN_URL. '/plugin/widget-importer-exporter');

//export feature
include ('hw-export.php');
include ('hw-import.php');


/**
 * Class HW_Module_importer
 */
class HW_Module_Importer extends HW_Module {
    /**
     * main module constructor
     */
    public function __construct() {
        parent::__construct();
        $this->enable_tab_settings();
        $this->enable_submit_button();
        $this->support_fields('hw_upload');
    }
    public function activated_plugin() {

    }
    public function deactivated_plugin() {

    }
    /**
     * module loaded event
     * @return mixed|void
     */
    public function module_loaded() {
        //enable config
        $this->enable_config_page('cli/general-setups', false);
        //register other cli command
        #$this->register_cli('test');   //ex 1, will load class-cli-test.php as new command
        $this->register_cli('hw-importer','hw-module', 'HW_CLI_HW_Module'); //wp hw-module
        $this->register_cli('hw-importer','hoangweb', 'HW_CLI_Hoangweb');   //wp hoangweb
    }
    /**
     * Triggered when the tab is loaded.
     * @param $oAdminPage
     */
    public function replyToAddFormElements($oAdminPage) {
        $this->addFields(
            array(
                'field_id' => 'upload',
                'type' => 'hw_upload',
                //'type' => 'text',
                'title'=> 'Tải file',
                'image_type' => true,
                //'upload' => '',   //action url
                //'upload_file' => ''   //action file
                //'allow_multiple' => true,
                //'uploads_directory_uri'=> '',
                'uploads_path' => plugin_dir_path(__FILE__). '/uploads/',
                'uploads_directory_uri' => plugins_url('uploads/', __FILE__),
                'random_filename' => true,
                'success_callback' => array($this, 'success_upload_callback'),
                'allow_types'=> ['image/png','image/gif','image/jpeg','image/pjpeg','text/plain','text/html','application/x-zip-compressed','application/pdf']
            ),
            array(
                'field_id' => 'upload1',
                'type' => 'hw_upload1', //note: two uploader will be conflict
                'show_title_column'=> false,
                'image_type' => true
            )
        );
    }
    //for testing
    public function success_upload_callback() {

    }
    /**
     * @hook admin_enqueue_scripts
     * @return mixed|void
     */
    public function admin_enqueue_scripts() {
        $handle = $this->enqueue_script('assets/js.js', array('jquery'));
        $this->localize_script($handle, '__hw_module_importer',array('upload_handle' => hw_modules_url('upload.php', __FILE__)));
    }

    public function print_head(){}
    public function print_footer(){}

    /**
     * fields values validation
     * @param $values
     * @return mixed|void
     */
    public function validation_tab_filter($values) {
        //var_dump($values);
        //var_dump($this->create_field_name('upload'));
        APF_hw_upload_field::do_form_action($values, $this->create_field_name('upload'));
        //return array(); //not save any fields value
        exit(); //the way allow ajax upload work, no any output allow here, if you not specific upload handle
        //return $values;   //allow to save fields value
    }


}
HW_Module_Importer::register();