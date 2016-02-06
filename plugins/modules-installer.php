<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 06/12/2015
 * Time: 09:31
 */
/**
 * Class HW_Modules_Packages
 */
class HW_Modules_Packages extends HW_Settings_AdminPage_MenuItem{
    /**
     * @var
     */
    private $packages;
    /**
     * main class construct method
     */
    public function __construct(){
        parent::__construct();
        $this->setMenuItem(array(
            'title' => 'Cài Modules',
            'page_slug' => 'hw-install-module'
        ));
        //$this->enable_submit_button();
        $this->support_fields(array('hw_html', 'hw_upload'));

        add_action('admin_enqueue_scripts',  array(&$this, 'admin_enqueue_scripts'));
        add_action('hw_upload_file_success', array($this, 'success_upload_callback'));
        add_action('hw_upload_file_error', array($this, 'fail_upload_callback'));
        HW_HOANGWEB::register_ajax('search_module', array(&$this, '_ajax_search_package_modules'));

        if(class_exists('APF_hw_upload_field') && APF_hw_upload_field::is_success()) {
            //do something
        }
    }

    /**
     * @ajax search_module
     */
    public function _ajax_search_package_modules() {
        $module = hw__req('s_module');
        for($i=1;$i<3;$i++) {
            $module = array('slug' => 'module-'.$i, 'name'=> 'module '.$i, 'desc' => "module description ");
            hw_include_template('module-repository', compact('module'));
        }
        #echo $module;
    }
    /**
     * menu title
     * @return string
     */
    public function menu_title() {
        return '';
    }
    /**
     * @param AdminPageFramework $oAdminPage
     */
    public function replyToAddFormElements($oAdminPage=null) {
        $tab = isset($_GET['tab'])? $_GET['tab'] : 'showcase';

        if(!APF_hw_upload_field::is_success() && $tab == 'upload') {
            $this->addFields(
                array(
                    'field_id' => 'uploader',
                    'type' => 'hw_upload',
                    'title'=> 'Tải file',
                    //'image_type' => true,
                    //'upload' => '',   //action url
                    //'upload_file' => ''   //action file
                    //'allow_multiple' => true,
                    //'uploads_directory_uri'=> '',
                    'uploads_path' => HW_HOANGWEB_PATH. 'data/upgrade/',
                    'uploads_directory_uri' => HW_HOANGWEB_URL. '/data/upgrade/',
                    //'random_filename' => true,
                    'allow_types'=> ['application/x-zip-compressed','image/png','image/gif','image/jpeg','image/pjpeg'],
                    //'callbacks' => array('success'=> 'success_upload_callback'), //for js callback    =>wrong
                    'redirect' => true,
                    'success_callback_js' => 'hw_upload_success_callback',
                    'error_callback_js' => 'hw_error_callback'
                )
            );
        }
        //after upload complete
        if(APF_hw_upload_field::is_success() && hw__req('action') == 'upload-module') {

        }
        //install modules
        if(hw__req('action') == 'install-module') {

        }
        //content page
        $this->addHTML( array($this, 'modules_installation_page'));
    }
    /**
     * for testing
     * @hook hw_upload_file_success
     */
    public function success_upload_callback($file) {

    }
    /**
     * failt to upload
     * @hook hw_upload_file_error
     */
    function fail_upload_callback() {

    }
    /**
     * hw_html field callback
     * @callback hw_html field
     */
    public function modules_installation_page() {
        if(!isset($_GET['tab'])) $_GET['tab'] = 'shows';

        if($_GET['tab'] == 'upload') {
            //after upload complete
            if(APF_hw_upload_field::is_success() && hw__req('action') == 'upload-module') {
                $this->do_upload_module();
            }
            else {
                hw_include_template('module-upload');
            }
            return ;
        }
        //shows almost modules
        elseif($_GET['tab'] == 'shows' && hw__req('action') !='install-module') {
            $this->shows();
        }
        //install module page
        else if(hw__req('action') =='install-module'){
            $this->do_install_module();
        }
    }

    /**
     * modules showcase
     */
    private function shows() {
        echo '<a class="button upload add-new-h2" href="'. HW_URL::curPageURL(array('tab' => 'upload')).'">Tải module lên</a>';
        echo '<h2>Các gói mở rộng tính năng thay đổi và mở rộng thêm khả năng làm việc của Hoangweb Plugin.</h2>';

        hw_include_template('modules-shows') ;  //shows popular modules in list
    }
    /**
     * do upload module
     */
    private function do_upload_module() {
        //__print($_POST);
        $_request = array('action' => 'upload-module');

        $_data = $_POST['data'];
        if(isset($_data['data']) && is_string($_data['data'])) $_data['data'] = json_decode($_data['data']);
        $_data['package'] = $zipfile = $_data['data']['path'];

        include (HW_HOANGWEB_INCLUDES .'/hw-update.php');
        /*if(hw_valid_module_zipfile($zipfile)) {
            HW_Unzipper::extract($zipfile, HW_HOANGWEB_PLUGINS );  //extract module to modules directory path
        }*/
    }
    /**
     * install module page
     */
    private function do_install_module() {
        $_request = array(
            //'action' => 'install-module'
        );
        //prepare data
        $_data['data'] = $_POST;
        if(isset($_data['data']) && is_string($_data['data'])) $_data['data'] = json_decode($_data['data']);
        $_data['from'] = 'local';

        include (HW_HOANGWEB_INCLUDES .'/hw-update.php');
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_script('hw-module-installer', HW_HOANGWEB_URL . '/js/module-installer.js');
    }
    /**
     * @return mixed|void
     */
    function print_scripts() {
        echo "<script>
        function hw_upload_success_callback(data, obj) {
            if(obj.get_config('redirect')) {
                if(data.status == 1 || data.code.toLowerCase()=='exists') {
                    jQuery.post_to_self({data: JSON.stringify(data), uploaded: '1'},{upload:'success', action:'upload-module'});	//or 'upload=success'
                }
            }
        }
        jQuery('input[type=text],input[type=search]').keypress(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
            }
        });
        </script>";
    }

    /**
     * @return mixed|void
     */
    function print_styles() {
        echo '<style>
        .wp-filter{padding:10px;text-align: right;}
        .admin-page-framework-field{width:100%;}
        </style>';
    }
    /**
     * fields values validation
     * @param $values
     * @return mixed|void
     */
    public function validation_tab_filter($values) {
        //var_dump($values);
        //var_dump($this->create_field_name('upload'));
        APF_hw_upload_field::do_form_action($values, $this->create_field_name('uploader'));
        //return array(); //not save any fields value
        //exit(); //the way allow ajax upload work, no any output allow here, if you not specific upload handle
        if(hw__post('s')) {
            $_REQUEST['s'] = hw__post('s');
            HW_SESSION::save_session('search_module', hw__post('s'));
        }

        return $values;   //allow to save fields value
    }
    /**
     * fetch modules from repositories
     * @return array
     */
    function get_modules_repositories() {
        //$parser = new HW_WXR_Parser ;
        $this->packages = HW_WXR_Parser::read_simplexml_object(HW_HOANGWEB_PLUGINS. '/modules-package.xml' );
        /*if(!empty($result->xml->module))
        foreach($result->xml->module as $module) {

        }*/
        return !empty($this->packages->xml->module )? $this->packages->xml->module : array();
    }
}
HW_Modules_Packages::register_menu();
//HW_HOANGWEB_Settings::register_submenu('HW_Modules_Packages');