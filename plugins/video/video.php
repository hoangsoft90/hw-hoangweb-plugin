<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/05/2015
 * Time: 18:20
 */
class HW_Video_Manager extends HW_Module{
    function __construct(){
        $this->setup_hooks();
    }
    function setup_hooks(){
        //init nhp options
        add_action('init', array($this, 'hw_nhp_theme_options'));
    }
    public function hw_nhp_theme_options(){
        $args = array();
        $args['share_icons']['twitter'] = array(
            'link' => 'http://twitter.com/hoangweb',
            'title' => 'Folow me on Twitter',
            'img' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_322_twitter.png'
        );

        $args['share_icons']['linked_in'] = array(
            'link' => 'http://www.linkedin.com/in/hoangweb',
            'title' => 'Find me on LinkedIn',
            'img' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_337_linked_in.png'
        );

        $args['page_slug'] = 'hoangweb-theme-options';
        $args['show_import_export'] = true;    //enable import/export settings
        $args['page_position'] = 102419882;
        $args['dev_mode'] = false;

        $install_video_manager = hw_install_plugin_link('video-list-manager','Cài đặt video',true);

        //video setting
        $sections['video'] = array(
            'icon' => '',
            'title' => 'Cài đặt Videos',
            'fields' => array(
                'support_posttype' => array(
                    'id' => 'support_posttype',
                    'type' => 'hw_post_type_multi_select',
                    'title' => 'Hỗ trợ post type',
                    'desc' => 'Chọn post type sử dụng quản lý đăng videos.'.$install_video_manager,
                    'options' => array()
                ),

            )
        );
        new NHP_Options($sections, $args);
    }
}
new HW_Video_Manager();