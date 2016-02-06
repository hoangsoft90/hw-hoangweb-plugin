<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/11/2015
 * Time: 10:40
 */
/**
 * Class HW_WP_Media
 */
class HW_WP_Media extends HW_Admin_Options {
    /**
     * @var array
     */
    var $default_images = array('thumbnail', 'medium', 'large');
    public function __construct() {
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function load() {
        //allow upload mimes type
        add_filter('upload_mimes', array($this, '_custom_upload_mimes'));
        add_filter( 'image_size_names_choose', array($this, '_image_size_names_choose') );
        add_action( 'after_setup_theme', array($this, '_add_image_sizes') );
    }

    /**
     * @param $sizes
     */
    function _image_size_names_choose( $sizes ) {
        //$sizes['small_thumb'] = __( 'Small Thumb', 'mytheme' );
        $all_sizes = get_intermediate_image_sizes();

        foreach($all_sizes as $size) {
            if(!isset($sizes[$size])) $sizes[$size] = __($size, 'hoangweb');
        }
        return $sizes;
    }
    /**
     * allow upload mimes type
     * @hook upload_mimes
     * @param array $existing_mimes
     * @return array
     */
    public function _custom_upload_mimes ( $existing_mimes=array() ) {
        $mimes = hw_list_mines_type();
        $allow_uploadfile_type = hw_get_setting('allow_uploadfile_type');

        // add your extension to the array, add as many as you like
        foreach($mimes as $mime_type => $ext) {
            if(isset($allow_uploadfile_type[$mime_type]) && $allow_uploadfile_type[$mime_type]) {
                $existing_mimes[$mime_type] = $ext;
            }
        }

        // removing existing file types
        //unset( $existing_mimes['exe'] );

        // and return the new full result
        return $existing_mimes;
    }

    /**
     * setup theme
     * @hook after_setup_theme
     */
    public function _add_image_sizes() {
        if(!$this->setting()->theme_config) return ;
        $general = $this->setting()->theme_config->item('configuration');
        if(!empty($general['media']))
        foreach ($general['media'] as $name => $size) {
            $width = isset($size['width'])? $size['width'] : '';
            $height = isset($size['height'])? $size['height'] : '';
            $crop = empty($size['crop'])? false : true;

            if(!in_array($name, $this->default_images)) {  //custom image size
                add_image_size( $name, $width, $height ,$crop);
            }
        }
    }
    /**
     * set image size
     * @param $size
     * @param string $thumb
     */
    public function set_image_size($size, $thumb='thumbnail') {
        $width = isset($size['width'])? $size['width'] : '';
        $height = isset($size['height'])? $size['height'] : '';

        if($width) update_option('$thumb'. '_size_w', $width ) ;
        if($height) update_option('$thumb'. '_size_h', $height ) ;
    }
    /**
     * list of all mimes type
     * @return array
     */
    public static function list_mines_type() {
        $mimes = array(
            'x3d' => 'application/vnd.hzn-3d-crossword',
            '3gp' => 'video/3gpp',
            '3g2' => 'video/3gpp2',
            'pdf' => 'application/pdf',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'exe' => 'application/x-msdownload',
            'pub' => 'application/x-mspublisher'
        );
        return $mimes;
    }
}