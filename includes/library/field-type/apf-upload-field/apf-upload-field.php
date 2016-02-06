<?php

/**
 * Class APF_hw_upload_field
 */
if ( ! class_exists( 'APF_hw_upload_field' ) && class_exists('HW_APF_FormField')) :
class APF_hw_upload_field extends HW_APF_FormField {
    /**
     * script handle name
     * @var null
     */
    protected static $script_handle = null;
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'hw_upload');
    /**
     * @var array
     */
    protected $config = array();
    /**
     * default params
     * @var array
     */
    public $aDefaultKeys = array(
        'show_root_field' => true,
        'image_type' => false,
        'random_filename' => false,
        'maxsize' => 5242880
    );
    /**
     * constructor
     * @param $class
     */
    public function __construct($class){
        parent::__construct($class, __FILE__);
        //$this->prepare_actions();   //actions
        $this->aDefaultKeys['upload'] = plugins_url('upload.php', __FILE__);    //default upload procession
        $this->aDefaultKeys['upload_file'] = plugin_dir_path(__FILE__). '/upload.php';
        $this->aDefaultKeys['uploads_path'] = plugin_dir_path(__FILE__). '/uploads';    //uploads folder
        $this->aDefaultKeys['uploads_directory_uri'] = plugins_url('uploads', __FILE__);    //uploads folder
        $this->aDefaultKeys['no_image'] = plugins_url('assets/images/no-image.jpg', __FILE__);  //no image placeholder
        $this->aDefaultKeys['allow_types'] = array(
            'image/png','image/gif','image/jpeg','image/pjpeg','text/plain','text/html','application/x-zip-compressed','application/pdf'
        );
    }

    /**
     * @wp_hook admin_enqueue_scripts
     * @return mixed|void
     */
    public function admin_enqueue_scripts() {
        global $wp_scripts;
        $aField = $this->getFieldData();
        $data = array(
            'no_image' => $this->get_property('no_image'),
        );
        //form action
        //if($this->get_property('internal_form_action')) {
        $this->config = array(
            'action_url' => $this->get_property('upload'),
            'action_path' => $this->get_property('upload_file'),
            'uploads_folder' => $this->get_property('uploads_path'),
            'uploads_url' => $this->get_property('uploads_directory_uri'),
            'image_type' => $this->get_property('image_type'),
            'random_filename' => $this->get_property('random_filename'),
            'allow_types' => $this->get_property('allow_types'),
            'maxsize' => $this->get_property('maxsize'),
            'redirect' => $this->get_property('redirect'),
            //'upload_handle' => ''       //alway get form action from current page, use action_url param
            'success_callback_js' => $this->get_property('success_callback_js'),
            'error_callback_js' => $this->get_property('error_callback_js'),
        );
        //}
        //else $data['upload_handle'] = $this->get_property('upload');
        $id = md5($this->get_field_name());
        $data[$id] = $this->config;

        $handle = $this->enqueue_script('assets/upload.js', array('jquery'), 'hw-apf-field-upload');
        if($handle) {
            self::$script_handle = $handle;
            $wp_scripts->add_data($handle, $id, $data[$id]);
        }

        if(!$handle ) {
            //$wp_scripts->add_data(self::$script_handle, $id, $data[$id] );  //echo '<textarea>';print_r($wp_scripts);echo '</textarea>';
            $this->add_data(self::$script_handle, '__hw_apf_field_upload', $id, $data[$id]) ;
        }
        elseif($handle){
            $this->localize_script($handle, '__hw_apf_field_upload', $data);
        }

        $this->enqueue_style('assets/style.css');
        //jquery form lib
        HW_Libraries::enqueue_jquery_libs('jquery-libs/form');
    }
    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return ".admin-page-framework-input-label-container.hw_more_fields { padding-right: 2em; }

        ";
    }

    /**
     * return field type specific inline js
     * @return string
     */
    protected function getScripts() {
        //$this->prepare_actions();   //actions
        return "";
    }
    /**
     * Returns the output of the field type.
     * @param $aField
     */
    protected function getField( $aField ) {
        $this->init($aField);
        //$this->__aField = $aField;
        $_aAttributes = $aField['attributes'];
        $name = $this->get_field_name();
        $id = $this->get_field_id();
        $value = $this->get_field_value();

        $field_file = $this->get_field_name('file');
        if($this->get_property('allow_multiple')) {
            $field_file .= '[]';
        }

        //upload configuration
        $configuration = $this->get_field_value('config');
        if(!$configuration) {
            $configuration = HW_Encryptor::encrypt($this->config);
        }

        //action url for upload file
        $html = '<div id="'.$this->get_field_id('hw-apf-uploader-container').'" class="">';
        $html .= '<input type="hidden" name="'.$this->get_field_name('save_file').'" id="'.$this->get_field_id('save_file').'" value="'.$this->get_field_value('save_file').'"/>';

        $html .= '<input type="hidden" name="'.$this->get_field_name('config').'" value="'.$configuration.'"/>';

        $html .= '<input type="file" name="'. $field_file .'" id="'.$this->get_field_id('_file').'"/>';
        $html .= '<div id="'.$this->get_field_id('_message').'"></div>';

        if($this->get_property('image_type') ) {
            $html .= '<div id="'.$this->get_field_id('_image_preview').'" class="hw-apf-upload-image-preview"><img id="'.$this->get_field_id('_previewing').'" class="" src="'.plugins_url('assets/images/no-image.jpg', __FILE__).'" /></div><hr id="line">';
        }

        $html .= '<input type="submit" class="button hw-apf-upload-btn" name="submit" value="'.__('Upload').'"/>';
        //message
        $html .= '<div id="'.$this->get_field_id('messages').'"></div>';

        $args = array(
            'container' => '#'. $this->get_field_id('hw-apf-uploader-container'),
            'message' => '#'. $this->get_field_id('_message'),
            'preview' => '#' . $this->get_field_id('_previewing'),
            'file_input' => '#'. $this->get_field_id('_file'),
            'save_file' => '#' . $this->get_field_id('save_file'),
            'id' => md5($this->get_field_name())
        );
        $html .= '<script>
        jQuery(document).ready(function(){
            var uploader = new HW_Uploader("#hw-apf-upload-field-container-'.$id.'");
            uploader.setup('.json_encode($args).');
            uploader.callbacks({success: function(){}});
        });
        </script>';
        $html .= '</div>';
        return
            $aField['before_label']
            . $aField['before_input']
            . "<div class='repeatable-field-buttons'></div>"	// the repeatable field buttons will be replaced with this element.
            . "<div id='hw-apf-upload-field-container-{$id}'>"
            . $html//$this->_getInputs( $aField )
            .'</div>'
            . $aField['after_input']
            . $aField['after_label'];
    }

    /**
     * get action form
     * @param $values
     * @param $field
     */
    public static function get_action_form($values, $field) {
        if(isset($values[$field])) {

            $config = self::config($values, $field );
            return isset($config->action_path)? $config->action_path : '';
        }
    }

    /**
     * process form action
     * @param $values
     * @param $field
     */
    public static function do_form_action($values, $field) {
        if(empty($_FILES['file-0'])) return ;
        //$action_file = self::get_action_form($values, $field);
        $config = self::config($values, $field );
        //extract($config);
        if(!empty($config->action_path) && file_exists($config->action_path)) {
            include($config->action_path);
            exit();
        }
    }

    /**
     * check whether upload successful
     * @return bool
     */
    public static function is_success() {
        return hw__post('uploaded') == '1' ;
    }

    /**
     * config data
     * @param $values
     * @param $fieldname
     */
    public static function config($values, $fieldname) {

        if(!isset($values[$fieldname])) return ;
        //valid form data
        self::valid_form_data();

        if(!empty($values[$fieldname]['config'])) {
            $configuration = HW_Encryptor::decrypt($values[$fieldname]['config']);
        }
        else $configuration = array();

        return (object)$configuration;
    }
    /**
     * valid form data
     */
    public static function valid_form_data() {
        //limit only for 10 files
        for($i=0;$i<10;$i++) {
            if(!isset($_FILES['file-'.$i])) {
                $_POST[__CLASS__.'_files_num'] = $i;
                break;
            }

        }
        if(!isset($_POST[__CLASS__.'_files_num'])) $_POST[__CLASS__.'_files_num'] = 0;
    }

    /**
     * return num of files to upload
     * @return string
     */
    public static function get_files_num() {
        return hw__post(__CLASS__.'_files_num', 0);
    }
}
endif;