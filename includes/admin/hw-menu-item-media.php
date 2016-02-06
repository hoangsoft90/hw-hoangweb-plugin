<?php
#/root>includes/hoangweb-core.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/07/2015
 * Time: 16:31
 */
class HW_Media_Manager {
    /**
     * main class constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, '_custom_add_script'));
        add_filter('media_view_strings', array($this, '_custom_media_string') , 10, 2);
    }

    /**
     * admin enqueue scripts
     * @wp_hook action admin_enqueue_scripts
     */
    public function _custom_add_script(){
        wp_enqueue_script('hw-media-menu', HW_HOANGWEB_URL. ('/js/custom_media_menu.js' ), array('media-views'), false, true);
    }

    /**
     * custom media view
     * @wp_hook filter media_view_strings
     * @param $strings
     * @param $post
     * @return mixed
     */
    public function _custom_media_string($strings,  $post){
        $strings['customMenuTitle'] = __('Custom Menu Title', 'custom');
        $strings['customButton'] = __('Custom Button', 'custom');
        return $strings;
    }
}

new HW_Media_Manager();