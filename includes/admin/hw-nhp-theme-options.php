<?php
#/root>includes/hoangweb-core.php

define('HW_NHP_THEME_OPTIONS_PAGE_SLUG', 'hoangweb-theme-options');
//nhp theme options
if(!class_exists('NHP_Options')){
	require_once( plugin_dir_path(dirname(dirname(__FILE__))). 'lib/nhp/options/options.php' );
}

/**
 * Interface HW_NHP_Options_interface
 */
interface HW_NHP_Options_interface {
    /**
     * @param $section
     * @return mixed
     */
    public function get_fields (&$section);
}

/**
 * Class HW_Options
 */
abstract class HW_Options {
    /**
     * identifier
     * @var
     */
    public $name;
}
/**
 * Class HW_NHP_Options
 */
abstract class HW_NHP_Options extends HW_Options implements HW_NHP_Options_interface{
    /**
     * add fields to sections nhp theme options
     * @param array $sections
     */
    public function __construct(&$sections = array()) {
        $this->name = get_called_class();
        if(is_array($sections) ) $this->get_fields($sections);

        //if the function not exists
        if(!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
    }

    /**
     * Custom function for the callback validation referenced above.
     * learn more at: https://github.com/leemason/NHP-Theme-Options-Framework/blob/master/nhp-options.php
     * @param $field
     * @param $value
     * @param $existing_value
     */
    public function validate_editor($field, $value, $existing_value) {
        $error = false;
        $value =  'just testing';
        /*
        do your validation

        if(something){
            $value = $value;
        }elseif(somthing else){
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
        }
        */

        $return['value'] = $value;
        /*if($error == true){
            $return['error'] = $field;
        }*/
        return $return;
    }
}

/**
 * Class HW_NHP_Main_Settings
 */
class HW_NHP_Main_Settings {
    /**
     * page slug
     */
    const HW_NHP_THEME_OPTIONS_PAGE_SLUG = 'hoangweb-theme-options';
    /**
     * option name for option page
     */
    const SETTINGS_OPTION_NAME = 'nhp_hoangweb_theme_opts';

    /**
     * main constructor
     */
    function __construct() {
        //setup hoangweb theme options
        add_action('init', array($this, '_hw_nhp_theme_options') ,1000);
        //add_action('nhp-opts-get-validation', array($this, '_hw_require_my_custom_validation') );
        add_action('admin_footer', array($this, '_hw_admin_footer') );
        /* Filter Tiny MCE Default Settings */
        //add_filter( 'tiny_mce_before_init', array($this, 'my_switch_tinymce_p_br') );
    }

    /**
     * return main settings page url
     * @return string|void
     */
    public static function get_setting_page_url() {
        return admin_url('admin.php?page='. self::HW_NHP_THEME_OPTIONS_PAGE_SLUG);
    }

    /**
     * update options page
     * @param $options
     */
    public static function update_data($options) {
        if(is_array($options)) {
            //get_option(self::SETTINGS_OPTION_NAME);
            hw_add_wp_option(self::SETTINGS_OPTION_NAME, $options, true);   //allow to merge options
        }
    }
    /**
     * nhp theme options
     */
    public function _hw_nhp_theme_options()
    {
        $page_slug = self::HW_NHP_THEME_OPTIONS_PAGE_SLUG;  //nhp theme options page slug
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

        $args['opt_name'] = self::SETTINGS_OPTION_NAME;
        $args['menu_title'] = '(H) Chức năng';
        $args['page_title'] = 'Theme Options';
        $args['page_slug'] = $page_slug;
        $args['show_import_export'] = true;    //enable import/export settings
        $args['page_position'] = 102419882;
        $args['dev_mode'] = false;

        $sections = array();
        global $pagenow;

        //only load when visit theme options of mainy page, something conflict with other feature such as AdminPageFramework
        if($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == $page_slug):
            //install plugins link
            $install_video_manager = hw_install_plugin_link('video-list-manager','Cài đặt video',true);

            /**
             * general
             */
            new NHP_Options_general($sections);

            /**
             * features
             */
            new NHP_Options_features($sections);
            /**
             * template
             */
            new NHP_Options_template($sections);
            /**
             * post settings
             */
            new NHP_Options_article($sections);
            /**
             * gallery
             */
            new NHP_Options_gallery($sections);
            /**
             * multi-languages
             */
            new NHP_Options_mqtranslate($sections);
            /**
             * footer setting
             */
            new NHP_Options_footer($sections);
            /**
             * advertising
             */
            new NHP_Options_ads($sections);
            /**
             * social options
             */
            new NHP_Options_socials($sections);

            /*video setting
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
            );*/


            //contact form
            /*'contact-form' => array(
                    'icon'=>NHP_OPTIONS_URL.'img/glyphicons/glyphicons_280_settings.png',
                    'title' => 'Form liên hệ/đặt hàng',
                    'fields' => array(
                        'wpcf7_js' => array(
                            'id' => 'wpcf7_js',
                            'type' => 'checkbox',
                            'title' => 'Sử lý Ajax trong Contact Form',
                            'desc' => '',
                            'std' => '0'	#disable wpcf7 script for default
                        ),
                        'gform_ID'=> array(
                            'id' => 'gform_ID',
                            'type' => 'text',
                            'title' => 'Google Form ID',
                            'desc' => '',
                        ),
                        'gform_fields' => array(
                            'id' => 'gform_fields',
                            'type' =>'textarea',
                            'title' => 'Google Form Fields',
                            'desc' => 'Nhập tên trường Google Form theo thứ tự là:'.PHP_EOL.'Họ & tên|Công ty|Email|SĐT|Địa chỉ|Tin nhắn|sendEmail|admin_email|website'.PHP_EOL.'VD: entry.652402672|entry.1230491504|entry.343949848|entry.1151396680|entry.1407459728|entry.1998434718|entry.2042320298|entry.1355297923|entry.1522563801'
                        )
                    )
            ),*/


            /*ecommerce settings*/
            $sections['ecommerce'] = array(
                'title' => 'eCommerce',
                'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_202_shopping_cart.png',
                'fields' => array(
                    'product_footer_info' => array(
                        'id'=> 'product_footer_info',
                        'type' => 'editor',
                        'title' => 'Nội dung hiển thị dưới mỗi trang sản phẩm chi tiết'
                    ),
                    'tcp_buy_button_bottom' => array(
                        'id' => 'tcp_buy_button_bottom',
                        'type' => 'editor',
                        'title' => 'Nội dung hiển thị dưới nút mua hàng ở trang chi tiết.'
                    )
                )
            );
            $sections['optimize'] = array(
                'title' => 'Tối ưu',
                'fields' => array(
                    'permalink' => array(
                        'id' => 'permalink',
                        'type' => 'text',
                        'title' => 'Đổi permalink'
                    ),
                    ''
                )
            );
        else:
            $sections['tip'] = array(
                'title' => 'Nhấn vào chức năng để xem thêm'
            );
        endif;
        new NHP_Options($sections, $args);
    }
    /**
     * NHP fields validation
     */
    public function _hw_require_my_custom_validation() {
        require_once(NHP_OPTIONS_DIR.'/validation/editor/validation_editor.php');
    }
    /**
     * remove unwant something
     */
    public function _hw_admin_footer() {
        echo "<script>
    jQuery(document).ready(function($){
        jQuery('a[href=\"admin.php?page=".HW_NHP_THEME_OPTIONS_PAGE_SLUG."&tab=tip\"]').removeAttr('href').addClass('hw_nhp_themeopt_tip');
    });
    </script>
    <style>
    .hw_nhp_themeopt_tip{
        border: 1px solid rgb(183, 183, 183);
        background-color: #E8E7E7;
        color: rgb(74, 71, 71);
    }
    </style>
    ";
    }
    /**
     * Switch Default Behaviour in TinyMCE to use "<br>"
     * On Enter instead of "<p>"
     *
     * @link https://shellcreeper.com/?p=1041
     * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/tiny_mce_before_init
     * @link http://www.tinymce.com/wiki.php/Configuration:forced_root_block
     */
    public function my_switch_tinymce_p_br( $settings ) {
        $settings['forced_root_block'] = false;
        $settings['wpautop'] = true;
        $settings['menubar'] =1;
        return $settings;
    }
}
if(is_admin()) {
    new HW_NHP_Main_Settings();
}


/**
 * NHP options fields
 */
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_mqtranslate.php');  //multi-languages options
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_ads.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_article.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_gallery.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_features.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_footer.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_general.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_socials.php');
include_once(HW_HOANGWEB_INCLUDES. '/settings/NHP_options_template.php');

?>
