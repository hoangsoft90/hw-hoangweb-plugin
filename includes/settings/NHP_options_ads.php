<?php
# used by includes/settings/hw-nhp-theme-options.php
/**
 * Class NHP_Options_ads
 */
class NHP_Options_ads extends HW_NHP_Options {
    public function get_fields(&$sections) {
        /*float left right advertising*/
        $sections['ads'] =  array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_064_lightbulb.png',
            'title' => 'Quảng cáo chạy 2 bên',
            'fields' => array(
                'enable_flra' => array(
                    'id' => 'enable_flra',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt quảng cáo chạy 2 bên.',
                    'desc' => 'Kích hoạt quảng cáo chạy 2 bên.'
                ),
                'ads_active_mobile' => array(
                    'id' => 'ads_active_mobile',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt quảng cáo trên mobile',
                    'desc' => 'Kích hoạt quảng cáo trên mobile'
                ),
                'ads_effects' => array(
                    'id' => 'ads_effects',
                    'type' => 'select',
                    'title' => 'Hiệu ứng Javascript',
                    'desc' => 'Hiệu ứng Javascript',
                    'options' => array(
                        'follow_scrollbar' => 'Chạy theo thanh cuộn',
                        'fixed_to_top' => 'Fixed to top'
                    )
                ),
                /*'mcontent_width' => array(
                    'id' => 'mcontent_width',
                    'type' => 'text',
                    'title' => 'Main content width (px).'
                ),*/
                'mcontent_div' => array(
                    'id' => 'mcontent_div',
                    'type' => 'text',
                    'title' => 'Div Main content css selector.<br/>VD: #wrapper .main'
                ),
                'lad_width' => array(
                    'id' => 'lad_width',
                    'type' => 'text',
                    'title' => 'Left Banner width (px).',
                ),
                'rad_width' => array(
                    'id' => 'rad_width',
                    'type' => 'text',
                    'title' => 'Right Banner width (px).'
                ),
                'top_adjust' => array(
                    'id' => 'top_adjust',
                    'type' => 'text',
                    'title' => 'Top Adjust (px).'
                ),
                'ad_left' => array(
                    'id' => 'ad_left',
                    #'type'=>'upload',
                    'type'=>'hw_ckeditor',
                    'title' => 'Quảng cáo bên trái'
                ),
                'ad_right' => array(
                    'id' => 'ad_right',
                    #'type'=>'upload',
                    'type'=>'hw_ckeditor',
                    'title' => 'Quảng cáo bên phải'
                ),
            )
        );
    }
}