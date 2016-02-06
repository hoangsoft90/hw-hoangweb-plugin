<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 23:41
 */
/**
 * Class HW_YARPP_Exporter
 */
class HW_YARPP_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_yarpp_widget' );
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();

            $setting_data = array(
                'threshold' => '5',
                'limit' => '42',
                'recent' => '',
                'before_title' => '<li>',
                'after_title' => '</li>',
                'before_post' => '<small>',
                'after_post' => '</small>',
                'before_related' => '<h3>Related posts:</h3><ol>',
                'after_related' => '</ol>',
                'no_results' => '<p>Không tìm thấy kết quả</p>',
                'order' => 'score DESC',
                'rss_limit'=> '3',
                'rss_excerpt_length' => '10',
                'rss_before_title' => '<li>',
                'rss_after_title'=> '</li>',
                'rss_before_post' => '<small>',
                'rss_after_post' => '</small>',
                'rss_before_related' => '<h3>Related posts:</h3><ol>',
                'rss_after_related' => '</ol>',
                'rss_no_results' => '<p>Không tìm thấy kết quả</p>',
                'rss_order' => 'score DESC',
                'past_only' => '',
                'show_excerpt' => '',
                'rss_show_excerpt' => '',
                'template' => '',
                'rss_template' => '',
                'show_pass_post'=>'',
                'cross_relate' => '',
                'rss_display'=>'',
                'rss_excerpt_display' => '',
                'myisam_override' => '',
                'exclude' => '',

                'optin' => '',
                'thumbnails_heading' => 'Related posts:',
                'thumbnails_default' => 'plugin/yarpp/images/default.png',
                'rss_thumbnails_heading' => 'Related posts:',
                'rss_thumbnails_default' => 'plugin/yarpp/images/default.png',
                'display_code' => '',
                'auto_display_archive' => '',
                'auto_display_post_types'=> '',
                'pools' => '',
                'manually_using_thumbnails' => '',
            );
            //note: if you scan options from wxr in this context that contain skin tag so, instance for skin should match skin name
            $data = $this->recursive_option_data($item->children())->option;
            if(!empty($data)) $setting_data = array_merge($setting_data, $data);

            $this->options->add_option('hw-yarpp', $setting_data);
        }
        //adding widget from theme.xml
        $this->add_export_widgets();
        $this->do_import();
    }
}