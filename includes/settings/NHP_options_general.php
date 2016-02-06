<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 11:39
 */
class NHP_Options_general extends HW_NHP_Options{
    /**
     * custom callback for validate fields
     * @example https://github.com/leemason/NHP-Theme-Options-Framework/blob/master/nhp-options.php
     * @param $field
     * @param $value
     * @param $existing_value
     */
    public function validate_header_fields_cb($field, $value, $existing_value) {
        //$field['msg'] = 'your custom error message';
        if($field == 'admin_logo' && $value) {
            HW__Site::set_logo($value, false);

        }
        if($field == 'site_banner' && $value) {
            HW__Site::set_banner($value, false);

        }
        //bloginfo
        if($field =='site_name' && $value) {
            $site_info['name'] = $value;

        }
        if($field =='slogan' && $value) {
            $site_info['description'] = $value;

        }
        if(!empty($site_info)) HW__Site::update_site_info($site_info, false);
        return $value;
    }
    /***
     * get nhp fields
     * @return array
     */
    public function get_fields (&$sections) {
        //get all my custom metaslider
        if(class_exists('HWML_Slider_Settings_Metabox')) {
            $edit_slider_link = HWML_Slider_Settings_Metabox::get_edit_sliders_url();
            $edit_hw_slider_link = HWML_Slider_Settings_Metabox::get_edit_hw_sliders_url();
            $ml_slides =HWML_Slider_Settings_Metabox::get_all_mlsliders();
            $hw_sliders = hwml_get_all_sliders();   //hwml_shortcode posts list
        }
        else {
            $edit_slider_link = admin_url('admin.php?page=hw-metaslider');
            $edit_hw_slider_link = '';
            $ml_slides = array();
            $hw_sliders = array();
        }

        if(class_exists('HW_HELP')) {
            //$gmap_help = HW_HELP_HOANGWEB::current()->help_static_link('gmap.html');  //old way
            $gmap_help = HW_HELP::generate_help_popup(array('HW_HELP_HOANGWEB','gmap.html'), 'Hướng dẫn.');
        }
        else $gmap_help = '';

        //general settings
        $sections['general'] = array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_151_edit.png',
            'title' => 'Thông tin',
            'fields' => array(    #more: https://github.com/leemason/NHP-Theme-Options-Framework/blob/master/nhp-options.php
                'site_name' => array(
                    'id' => 'site_name',
                    'type' => 'text',
                    'title' => 'Tên website',
                ),
                'slogan' => array(
                    'id' => 'slogan',
                    'type' => 'text',
                    'title' => 'Khẩu hiệu'
                ),
                //logo
                'admin_logo' => array(
                    'id' => 'admin_logo',
                    'type' => 'upload',
                    'title' => 'Logo',
                    'desc' => 'Logo hiển thị ở trang đăng nhập và trên website.',
                    'validate_callback' => array($this, 'validate_header_fields_cb'),
                ),
                //site banner
                'site_banner' => array(
                    'id' => 'site_banner',
                    'type' => 'upload',
                    'title' => 'Banner',
                    'desc' => 'Banner hiển thị ở phần header của website.',
                    'validate_callback' => array($this, 'validate_header_fields_cb'),
                ),
                'phone' => array(
                    'id' => 'phone',
                    'type' => 'text',
                    'title' => 'Số điện thoại',
                    'desc' => 'Nhập số hotline hiển thị trên web',
                    #'sub_desc' => 'The name is the key used to queue the javascript file within wordpress.',
                    //'class' => 'small-text',
                    //'validate' => 'numeric',  #comma_numeric,no_special_chars,

                ),
                'admin_email' => array(
                    'id' => 'admin_email',
                    'type' => 'text',
                    'title' => 'Email',
                    'desc' => 'Địa chỉ email.',
                    'validate' => 'email',
                ),
                'address' => array(
                    'id' => 'address',
                    'type' => 'text',
                    'title' => 'Địa chỉ bản đồ',
                    'desc' => 'Nhập khu vực/địa chỉ của bạn muốn xuất hiện trên bản đồ. ' ,
                    'sub_desc' => $gmap_help
                ),
                'home_slider_id' => array(
                    'id' => 'main_slider_id',
                    //'type' => 'hw_metaslider',
                    'type' => 'select',
                    'title' => 'Slider',
                    'desc' => 'Chọn slider',
                    'sub_desc' => 'Chọn slider chính. Thêm/sửa slider <a href="'.$edit_slider_link.'" target="_blank">tại đây</a>.',
                    'options' => $ml_slides
                ),
                'main_hw_slider' => array(
                    'id' => 'main_hw_slider',
                    'type' => 'select',
                    'title' => 'Hoangweb slider',
                    'sub_desc' => 'Chọn hoangweb slider. Thêm/sửa sliders, <a href="'.$edit_hw_slider_link.'" target="_blank">tại đây</a>',
                    'options' => $hw_sliders
                ),
                'testimonials'=> array(
                    'id'=>'testimonials',
                    'type'=>'hw_ckeditor',
                    'title' => 'Testimonials',
                    'desc' => 'Lời chứng thực ở trang chủ.'
                ),

                /*'livechat' => array(  //moved to hw-livechat plugin
                    'id'=>'livechat',
                    'type'=>'textarea',
                    'title' => 'LiveChat Script',
                    'desc' => 'Chèn code livechat.'
                ),*/
                //insert scripts after wp_head hook
                'wp_head_script' => array(
                    'id'=>'wp_head_script',
                    'type'=>'textarea',
                    'title' => 'Chèn thêm scripts vào thẻ < head'
                ),
                //insert html code after wp_footer hook
                'wp_footer_code' => array(
                    'id' => 'wp_footer_code',
                    'type' => 'textarea',
                    'title' => 'Chèn mã HTML, JS, CSS vào dưới chân website'
                )
            )
        );
    }
}