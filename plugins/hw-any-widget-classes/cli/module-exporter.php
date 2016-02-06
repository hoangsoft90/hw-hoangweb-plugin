<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 16:42
 */
/**
 * Class HW_Any_Widget_Classes_Exporter
 */
class HW_Any_Widget_Classes_Exporter extends HW_Module_Export {
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $sidebar = (string) $atts['sidebar'];
            $type = (string) $atts['type'];
            $hw = $item->children($this->namespaces['hw']);

            if($type =='sidebar_setting') {
                $skins = $this->fetch_skins($hw->skin);

                $args = array(
                    'enable_override_sidebar' => '1',
                    'alphabe_widgets' => '1',
                    'bgcolor_title' => '',
                    'bgimg_title' => '',
                    'bgcolor_box' => '',
                    'bgimg_box' => '',
                );
                $args = array_merge($args, $skins) ;

                $this->options->add_option('HW_Sidebar_Settings', $args, array(
                    'prefix' => HW_Validation::valid_apf_slug($sidebar). '_',
                    'method' => 'append'
                ));
            }
            //sidebar positions
            elseif($type == 'sidebar_position') {
                $setting = array('sidebars_pos' => array('sidebars_position'));
                foreach($hw->params->param as $param) {
                    $sidebar = (string) $param->attributes()->name;
                    $position = (string) $param;
                    $setting['sidebars_pos']['sidebars_position'] = array($sidebar=> array('position' => $position));
                }
                $this->options->add_module_setting_page($setting);
            }
        }
        //posts
        foreach($this->get_posts_xml() as $item) {
            $atts = $item->attributes();
            $wp = $item->children($this->namespaces['wp']);
            $hw = $item->children($this->namespaces['hw']);
            //post meta
            $post_metas = $this->fetch_post_metas($hw->meta);

            $this->posts->addItem(array(
                'title' => (string) $wp->title,
                'description' => '',
                'content'=> '',
                'excerpt' => '',
                'post_type' => 'hw_mysidebar',
                'post_metas' => $post_metas
            ), HW_XML::attributesArray($item));
        }
        //$this->add_export_widgets();    //add widget if that found in this module
        $this->do_import();
    }
}