<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/10/2015
 * Time: 11:38
 */
if( !class_exists('HW_Gallery')):
class HW_Gallery extends HW_Envira_Gallery{
    /**
     * singleton
     * @var null
     */
    public static $instance= null;
    /**
     * main constructor method
     */
    public function __construct() {
        parent::__construct();
        //load hooks
        $this->setup_hooks();
    }

    /**
     * init hooks
     */
    public function setup_hooks() {
        add_filter('hw_gallery_custom_gallery_data', array($this, '_gallery_custom_gallery_data'),10, 3);
        add_filter('hw_gallery_pre_data', array($this, '_gallery_pre_data'),10, 2);
        add_filter('hw_gallery_output_start', array($this, '_gallery_output_start'), 10,2);
        add_filter('hw_gallery_output_before_container', array($this, '_gallery_output_before_container'), 10,2);
        add_filter('hw_gallery_output_before_item', array($this, '_gallery_output_before_item'), 10,5 );
        add_filter('hw_gallery_output_before_link', array($this, '_gallery_output_before_link'), 10, 5);
        add_filter('hw_gallery_output_before_image', array($this, '_gallery_output_before_image'), 10, 5);
        add_filter('hw_gallery_output_after_image', array($this, '_gallery_output_after_image'), 10,5);
        add_filter('hw_gallery_output_after_link', array($this, '_gallery_output_after_link'), 10,5);
        add_filter('hw_gallery_output_single_item', array($this, '_gallery_output_single_item'), 10,5);
        add_filter('hw_gallery_output_after_item', array($this, '_gallery_output_after_item'), 10,5);
        add_filter('hw_gallery_output_after_container', array($this, '_gallery_output_after_container'), 10,2);
        add_filter('hw_gallery_output_end', array($this, '_gallery_output_end'), 10,2);
        add_filter('hw_gallery_output', array($this, '_gallery_output'), 10,2);

    }

    /**
     * HW_SKIN::apply_skin_data callback
     * @param $args
     */
    public static function _hw_skin_before_include_skin_file($args) {
        #extract($args);
        return $args;
    }
    /**
     * A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
     * @param $bool
     * @param $atts
     * @param $post
     * @return mixed
     */
    public function _gallery_custom_gallery_data($bool, $atts, $post) {
        return $bool;
    }

    /**
     *  Allow the data to be filtered before it is stored and used to create the gallery output.
     * @param $data
     * @param $gallery_id
     */
    public function _gallery_pre_data($data, $gallery_id) {
        return $data;
    }

    /**
     * Apply a filter before starting the gallery HTML.
     * @param $gallery
     * @param $data
     */
    public function _gallery_output_start($gallery, $data ) {
        return $gallery;
    }

    /**
     * Build out the gallery HTML.
     * @param $gallery
     * @param $data
     */
    public function _gallery_output_before_container($gallery, $data) {
        return $gallery;
    }

    /**
     * @param $gallery
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     * @return mixed
     */
    public function _gallery_output_before_item($gallery, $id, $item, $data, $i) {
        return $gallery;
    }

    /**
     * @param $output
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     */
    public function _gallery_output_before_link($output, $id, $item, $data, $i) {
        return $output;
    }

    /**
     * @param $output
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     * @return mixed
     */
    public function _gallery_output_before_image($output, $id, $item, $data, $i) {
        return $output;
    }

    /**
     * @param $output
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     */
    public function _gallery_output_after_image($output, $id, $item, $data, $i) {
        return $output;
    }

    /**
     * @param $output
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     */
    public function _gallery_output_after_link($output, $id, $item, $data, $i) {
        return $output;
    }

    /**
     * @param $output
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     */
    public function _gallery_output_single_item($output, $id, $item, $data, $i ) {
        return $output;
    }

    /**
     * @param $gallery
     * @param $id
     * @param $item
     * @param $data
     * @param $i
     * @return mixed
     */
    public function _gallery_output_after_item($gallery, $id, $item, $data, $i) {
        return $gallery;
    }

    /**
     * @param $gallery
     * @param $data
     */
    public function _gallery_output_after_container($gallery, $data) {
        return $gallery;
    }

    /**
     * @param $gallery
     * @param $data
     */
    public function _gallery_output_end($gallery, $data){
        return $gallery;
    }

    /**
     * @param $gallery
     * @param $data
     * @return mixed
     */
    public function _gallery_output($gallery, $data) {
        return $gallery;
    }
}

endif;
/**
 * shortcode
 */
include_once ('shortcodes.php');