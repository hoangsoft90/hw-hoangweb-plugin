<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 11:15
 */
class NHP_Options_mqtranslate extends HW_NHP_Options{
    /**
     * @return string|void
     */
    public static function get_setting_tab_page() {
        return admin_url('admin.php?page='. HW_NHP_THEME_OPTIONS_PAGE_SLUG . '&tab=multilang');
    }
    /**
     * register shortcode for rendering languages chooser
     */
    public function _add_shortcode() {
        return self::hw_get_langs_switcher();
    }

    /**
     * init
     */
    public static function init() {
        add_shortcode('hw_multiLanguages', array(__CLASS__, '_add_shortcode') );
    }

    /***
     * get nhp fields
     * @return array
     */
    public function get_fields (&$sections) {
        if(class_exists('HW_NAVMENU_settings')) $tip = '<a href="'.HW_NAVMENU_settings::get_admin_setting_page().'">tại trang này</a>';
        else $tip = 'Kích hoạt plugin '. hw_install_plugin_link('hw-menu', 'hw-menu'). '. Để thêm nút chọn ngôn ngữ vào menu.';

        $sections['multilang'] = array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_266_flag.png',
            'title' => 'Đa ngôn ngữ',
            'fields' => array(
                'mqtrans_multilang_guide' => array(
                    'id' => 'mqtrans_multilang_guide',
                    'type' => 'info',
                    'desc' => 'Chú ý: gắn nút chọn ngôn ngữ vào menu '.$tip
                        . '. Hoặc tự chèn vào template với shortcode: <code>[hw_multiLanguages]</code>'
                        . '. Hoặc sử dụng widget "Đa ngôn ngữ".'
                ),
                'mqtrans_style' => array(
                    'id' => 'mqtrans_style',
                    'type' => 'select',
                    'title' => 'Kiểu hiển thị',
                    'options' => array(
                        'dropdown' => 'Danh sách chọn',
                        'image' => 'Hình ảnh',
                        'both' => 'Cả hình+chữ'
                    )
                ),
                'mqtrans_skin' => array(    //langs switcher skin
                    'id'=>'mqtrans_skin',
                    'type'=>'hw_skin',
                    'title' => 'Giao diện',
                    'desc' => 'Giao diện chọn ngôn ngữ.',
                    //use by hw_skin
                    'external_skins_folder' => 'hw_mqtrans_skins',
                    'skin_filename' => 'hw-mqtrans-skin.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => plugin_dir_path(dirname(dirname(__FILE__))),
                    'plugin_url' => plugins_url('',dirname(dirname(__FILE__))),
                    'group' => 'langs-switcher' //dynamic
                ),
                'enable_googletranslate' => array(  //switch to google translate
                    'id' => 'enable_googletranslate' ,
                    'type' => 'checkbox',
                    'title' => 'Google translate',
                    'desc' => 'Kích hoạt dịch vụ google translate.<br/><img src="'.HW_HOANGWEB_URL.'/images/pv_dm_inline_dropdown.png"/>',
                    'sub_desc' => 'Không hỗ trợ "kiểu hiển thị" & "Giao diện" ở trên.'
                ),

            )
        );
        return $sections;
    }
    /**
     * no longer use, moved to function hw-menu/includes/functions.php/hw_get_qtrans_switcher
     */
    public static function hw_get_langs_switcher() {
        return class_exists('NHP_Options_mqtranslate_Frontend')? NHP_Options_mqtranslate_Frontend::get_qtrans_switcher() : '';
    }
}
NHP_Options_mqtranslate::init();