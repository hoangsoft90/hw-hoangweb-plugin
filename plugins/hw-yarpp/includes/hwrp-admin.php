<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 25/05/2015
 * Time: 20:43
 */
include_once(HWRP_PLUGIN_PATH.'/classes/HWRP_Meta_Box_Settings.php');
/**
 * Class HW_RelatedPosts
 */
class HW_RelatedPosts{
    /**
     * localize data
     * @var array
     */
    private $localize_data = array();

    /**
     * main class constructor
     */
    function __construct(){
        $this->setup_actions(); //init hooks
        //override plugin textdomain path for yarpp plugin
        load_plugin_textdomain('hw-yarpp', false, HWRP_PLUGIN_PATH.'/langs');

        //register help
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('relatedpost', HWRP_PLUGIN_PATH.'helps');
            HW_HELP::set_helps_url('relatedpost', HWRP_PLUGIN_URL.'helps');
            HW_HELP::register_help('relatedpost');
            HW_HELP::load_module_help('relatedpost');
            //HW_HELP::$relatedpost;
        }
    }

    /**
     * setup the actions
     */
    private function setup_actions(){
        //add_action();
        add_action( 'admin_init', array(&$this, '_hwrp_add_custom_meta_box' ));
        add_action('add_meta_boxes_settings_page_hw_yarpp', array(&$this, '_hwrp_add_meta_boxes_settings_page_yarpp'));
        add_action('admin_enqueue_scripts', array(&$this, '_hwrp_admin_enqueue_scripts'));

        add_action( 'registered_post_type', array(&$this, '_registered_post_type'), 10, 2 );
    }

    /**
     * init yarpp object
     * @return YARPP
     */
    public static function init_yarpp(){
        global $hw_yarpp;
        if(!empty($hw_yarpp)) return $hw_yarpp;   //already exists data

        if( function_exists('hw_yarpp_init')) {    //grab from YARPP plugin
            hw_yarpp_init();
        }
        else{
            $hw_yarpp = new HW_YARPP;
        }
        return $hw_yarpp;
    }


    /**
     * modify post types definition
     * @param string $post_type Registered post type name.
     * @param array $args Array of post type parameters.
     */
    public static function _registered_post_type($post_type, $args ){
        static $hwrp_allow_post_types;
        if(!$hwrp_allow_post_types){
            //global $yarpp;    #sory :( this callback run before yarpp
            $hwrp_options = (get_option('hwrp_options'));
            if(!empty($hwrp_options) && isset($hwrp_options['hwrp_allow_post_types'])){
                $hwrp_allow_post_types = (array)$hwrp_options['hwrp_allow_post_types'];
            }
            else $hwrp_allow_post_types = array();
        }

        if (in_array($post_type, $hwrp_allow_post_types) ) {
            global $wp_post_types;
            $args->yarpp_support =true;
            $wp_post_types[ $post_type ] = $args;
        }
    }

    /**
     * add meta box to yarpp setting page
     */

    function _hwrp_add_custom_meta_box(){

        if(class_exists('HWRP_Meta_Box_Settings')){
            add_meta_box(
                'hw_yarpp_setting',
                __( 'Hoangweb cài đặt bài viết liên quan', 'hwrp' ),
                array( HWRP_Meta_Box_Settings::getInstance(), 'display'),
                'settings_page_hw_yarpp',
                'normal',
                'core'
            );
            add_meta_box('hwrp_shortcode',
                __('Hiển thị bài viết liên quan'),
                array(HWRP_Meta_Box_Settings::getInstance(), 'display_how_to_use'),
                'settings_page_hw_yarpp',
                'normal',
                'core'
            );
        }

    }
    //no longer use
    function _hwrp_add_meta_boxes_settings_page_yarpp(){

    }

    /**
     * admin enqueue scripts hook
     */
    function _hwrp_admin_enqueue_scripts(){
        if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen('hw_yarpp')){
            //ajax url
            $nonce = wp_create_nonce("hwrp_load_more_skins_nonce");
            $load_skins_ajax_link = admin_url('admin-ajax.php?action=hwrp_load_more_skins&nonce='.$nonce);
            $this->localize_data['load_skins_ajax'] = $load_skins_ajax_link;

            wp_enqueue_script('hwrp-admin-js', HWRP_PLUGIN_URL.'/js/hwrp-admin-js.js', array('jquery'));
            wp_enqueue_style('hwrp-style', HWRP_PLUGIN_URL. '/css/hwrp-style.css');

            wp_localize_script('hwrp-admin-js', '__hwrp_object', $this->localize_data);
        }
    }

}
