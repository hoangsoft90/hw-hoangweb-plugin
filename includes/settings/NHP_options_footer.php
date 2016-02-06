<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 11:41
 */
class NHP_Options_footer extends HW_NHP_Options {
    /***
     * get nhp fields
     * @return array
     */
    public function get_fields (&$sections) {
        $sections['footer_setting'] = array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_036_file.png',
            'title' => 'Chân trang',
            'fields' => array(
                'footer' => array(
                    'id'=>'footer',
                    'type'=>'hw_ckeditor',
                    'title' => 'Thông tin Footer',
                    'desc' => 'Nội dung chân trang.',
                    //'validate' => 'editor',
                    'validate_callback' => array($this, 'validate_editor')  //why not working?
                ),
                'before_footer' => array(
                    'id'=>'before_footer',
                    'type'=>'hw_ckeditor',
                    'title' => 'Trước Footer',
                    'desc' => 'Nội dung trước chân trang.'
                ),
                'after_footer' => array(
                    'id'=>'after_footer',
                    'type'=>'hw_ckeditor',
                    'title' => 'Sau Footer',
                    'desc' => 'Nội dung sau chân trang.'
                ),
                'footer_skin' => array(
                    'id'=>'footer_skin',
                    'type'=>'hw_skin',
                    'title' => 'Giao diện chân trang',
                    'desc' => 'Giao diện chân trang. Thay vì thông thường gọi file footer (<code>get_footer()</code>) trong template, chúng ta gọi <code>hw_get_footer()</code><br/>Thư mục giao diện: hw_footer_skins',
                    //use by hw_skin
                    'enable_skin_condition' => true,
                    'external_skins_folder' => 'hw_footer_skins',
                    'skin_filename' => 'hw-footer-skin.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => plugin_dir_path(dirname(dirname(__FILE__))),
                    'plugin_url' => plugins_url('',dirname(dirname(__FILE__))),
                    'group' => 'footers' //dynamic
                )
            )
        );
    }

    /**
     * do footer skin
     */
    public static function do_footer_skin() {
        $footer_skin = hw_option('footer_skin');    //get footer skin
        if(isset($footer_skin['hash_skin']) && isset($footer_skin['hwskin_config']) && class_exists('APF_hw_skin_Selector_hwskin')){
            $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($footer_skin);

            $config = ($skin->instance->get_file_skin_resource('functions.php',$skin->hash_skin));
            if(file_exists($config)) {
                include_once($config);    //load footer skin configuration
            }

        }
    }
}