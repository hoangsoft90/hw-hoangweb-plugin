<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 11:35
 */
class NHP_Options_features extends HW_NHP_Options{
    /***
     * get nhp fields
     * @return array
     */
    public function get_fields (&$sections) {
        //effects
        $effects = array(
            '0' => 'Không hiệu ứng',
            'snow' => 'Tuyết rơi',
            'fireworks' => 'Pháo hoa'
        );
        if(class_exists('AWC_WidgetFeature_fancybox')) {
            $fancybox_options = AWC_WidgetFeature_fancybox::get_options_definition();
        }
        else $fancybox_options = array();

        //fancybox help
        if(class_exists('HW_HELP')) {
            //$fancybox_help = HW_HELP_HOANGWEB::current()->help_static_link('fancybox.html');
            $fancybox_help = HW_HELP::generate_help_popup(array('HW_HELP_HOANGWEB','fancybox.html'), 'Hướng dẫn', 'Hướng dẫn fancybox');
            //HW_HELP::generate_help_popup(array('HW_HELP_HOANGWEB','fancybox.html'), 'Hướng dẫn');     //other approach
        }
        else $fancybox_help = '';

        //features
        $sections['features'] =  array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_280_settings.png',
            'title' => 'Chức năng',
            'fields' => array(
                'debug' => array(
                    'id' =>'debug',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt debug',
                    'desc' => 'Kích hoạt debug lỗi.'
                ),
                //scroll to top
                'scroll2top' => array(
                    'id' =>'scroll2top',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt scroll to top',
                    'desc' => 'Kích hoạt tính năng nút cuộn trang.'
                ),
                'scroll2top_skin' => array(
                    'id' =>'scroll2top_skin',
                    //'type' => 'hw_scroll2top',    //moved to hw_skin_link
                    'type' => 'hw_skin_link',
                    'title' => 'Chọn giao diện nút cuộn nội dung website.',
                    'desc' => 'Chọn giao diện nút cuộn nội dung website.',
                    //use by hw_skin
                    'external_skins_folder' => 'hw_scroll2top_skins',
                    'skin_filename' => 'hw-scroll2top.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => plugin_dir_path(dirname(dirname(__FILE__))),
                    'plugin_url' => plugins_url('',dirname(dirname(__FILE__))),
                    'files_skin_folder' => 'images',
                    'display' => 'ddslick', //accept: ddslick, list,select
                    'group' => 'scroll2top' //dynamic/
                ),

                //scrollbar
                'nice_scrollbar'=>array(  //nice scrollbar
                    'id'=>'nice_scrollbar',
                    'type' => 'select',
                    'title' => 'Giao diện thanh cuộn',
                    'desc' => 'Chọn thanh cuộn đẹp cho website.',
                    'options' => array()
                ),
                'effect' => array(  //javascript's effects
                    'id' => 'effect',
                    'type'=>'select',
                    'title' => 'Hiệu ứng nền',
                    'desc' => 'Sử dụng hiệu ứng nền',
                    'options' =>$effects
                ),
                'fancybox' => array(
                    'id' => 'fancybox',
                    'type' => 'checkbox',
                    'title' => 'Bật tính năng fancybox',
                    'desc' => 'Bật tính năng fancybox',
                    'sub_desc' => $fancybox_help
                ),
                'fancybox_settings' => array(
                    'id' => 'fancybox_settings',
                    'type' => 'hw_options',
                    'title' => 'Thiết lập cấu hình fancybox',
                    'desc' => 'Thiết lập cấu hình fancybox',
                    'settings' => $fancybox_options
                )
            )
        );
    }

    /**
     * scroll to top feature
     */
    public static function do_scroll2top() {
        //display scroll to top button (both admin & frontend)
        $scroll2top = hw_option('scroll2top_skin');
        if(hw_option('scroll2top')
            && isset($scroll2top['hwskin_link_default_skin_file']) )
        {
            $skin = HW_NHP_Field::get_skin_link($scroll2top);
            $file = $skin['template'];
            $image_url = $skin['file_url'];
            /*$file = base64_decode($scroll2top['hwskin_link_default_skin_file']);
            $theme = array();   //init theme config
            $theme['styles']= array();
            $theme['scripts']= array();*/

            if(!is_dir($file) && file_exists($file)){
                //$image_url = isset($scroll2top['hwskin_link_file_url'])? $scroll2top['hwskin_link_file_url']:'';   //scroll to top image
                include($file);
            }
        }
    }

    /**
     * background effect
     */
    public static  function do_bacground_effect() {
        //only frontend
        if(!is_admin() && hw_option('effect')){
            $effect = hw_option('effect');
            //snow effect
            if($effect == 'snow'){
                HW_Libraries::get('bg-effects')->enqueue_scripts('snow.js');
            }
            if($effect == 'fireworks'){
                //wp_enqueue_script('FireWorksNewYear',plugins_url('js/effects/FireWorksNewYear.js',dirname(__FILE__)),array('jquery'));
                HW_Libraries::get('bg-effects')->enqueue_scripts('FireWorksNewYear.js');
            }
        }
    }

    /**
     * do debug mode
     */
    public static function do_debug_mode() {
        //turn off/on debug mode
        if(hw_option('debug') &&  (!defined('WP_DEBUG') || !WP_DEBUG) ){
            if(function_exists('runkit_constant_remove') && defined('WP_DEBUG')) runkit_constant_remove("WP_DEBUG");
            if(!defined('WP_DEBUG')) define("WP_DEBUG", true);   //turn debug
        }
        if(defined('WP_DEBUG') && WP_DEBUG) {
            if(function_exists('runkit_constant_remove') && defined('WP_DEBUG')) runkit_constant_remove("WP_DEBUG");
            if(!defined('WP_DEBUG')) define("WP_DEBUG", false);
        };
    }
}