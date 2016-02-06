<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 20:50
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Menu_Exporter
 */
class HW_Menu_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('nav_menu');  //no need because this widget is default
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //register menu location
        #$xml->xpath('/rss/hw:'. $this->get_module()->option('module_name') . '/hw:nav_menu');
        /*foreach($xml->xpath('/rss/hw:'. $this->get_module()->option('module_name')) as $lmenu) {

        }*/
        //create nav menu item
        foreach($this->get_posts_xml($xml) as $item) {
            $terms = array();
            $atts = $item->attributes();
            $hw = $item->children($this->namespaces['hw']);
            $wp = $item->children($this->namespaces['wp']);
            $post_type = (string)$wp->post_type;
            $post_meta = array();
            $data = array(
                'post_type' => $post_type
            );

            if($post_type =='post') {   //post
                $data = array(
                    'title' => (string) $wp->title,
                    'content' => (string) $wp->content,
                    'excerpt' => (string) $wp->excerpt,
                    'post_type' => $post_type
                );
            }
            elseif($post_type == 'nav_menu_item') { //nav menu item
                $data = array(
                    'title' => (string) $wp->title,
                    'description' => '',
                    'content'=> '',
                    'excerpt' => '',
                    'post_type' => $post_type,
                );
                $post_meta = array_merge(array(
                    '_menu_item_target' => '',
                    '_menu_item_menu_item_parent' => '0',
                    '_menu_item_url'=> '',
                    '_menu_item_xfn' => ''
                ), $post_meta);

                if(isset($hw->assign_menu)) {
                    $nicename = (string)$hw->assign_menu->menu->attributes()->name;
                    $menu_name = (string) $hw->assign_menu->menu;
                    if(!$menu_name) $menu_name = $nicename;
                    $terms['nav_menu'] = array($nicename => $menu_name);
                    //assign to menu
                    if(isset($hw->assign_menu->item_import_result)) {
                        $_item = $hw->assign_menu->item_import_result;
                        $type = (string)$_item->attributes()->type;
                        if(!empty($_item) ) {

                            if($type =='post') {
                                $menu_item = $this->get_import_result($_item->import_post, 'post');
                                $post_meta['_menu_item_object'] = $menu_item['post_type'];
                                $post_meta['_menu_item_object_id'] = $menu_item['ID'];
                                $post_meta['_menu_item_type'] = 'post_type';
                            }
                            elseif($type =='term') {
                                $menu_item = $this->get_import_result($_item->import_term, 'term');//HW_Logger::log_file($menu_item);
                                $post_meta['_menu_item_object'] = $menu_item['taxonomy'];
                                $post_meta['_menu_item_object_id'] = $menu_item['term_id'];
                                $post_meta['_menu_item_type'] = 'taxonomy';
                            }
                        }
                    }

                    $data['terms'] = $terms;
                }
            }

            if($hw->meta) {
                //you can also use $this->fetch_post_metas_value($hw->meta);
                $post_meta = array_merge($post_meta, $this->fetch_post_metas_value($hw->meta) );

                $data['post_metas'] = $post_meta;
            }

            $this->posts->addItem($data, HW_XML::attributesArray($item));
        }
        //set options
        if($this->get_options_xml($xml));
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $hw = $item->children($this->namespaces['hw']);
            $wp = $item->children($this->namespaces['wp']);
            $type = (string) $atts['type'];

            if($type == 'menu_setting') {
                $option_name = isset($atts['name'])? $atts['name'] : 'HW_NAVMENU_settings';
                $menu = (string)$atts['menu'];
                //$params = $this->simplexml_parser->recursive_option_data($item->children())->option;
                $params = $this->simplexml_parser->recursive_option_data($hw->params->children())->option;
                $setting = array(
                    'enable_filter_menu' => '1',
                );
                $setting = array_merge($setting, $params) ;
                //parse skin
                $skins = $this->fetch_skins($hw->skin);
                if(isset($skins['skin'])) {

                    //add to setting
                    $setting['skin'] = $skins['skin'];
                }

                $this->options->add_option($option_name, $setting, array(
                    'prefix' => HW_Validation::valid_apf_slug($menu). '_',
                    'method' =>'append'
                ));
            }
            //module setting tab
            elseif($type == 'module_setting') {
                $setting =array();
                foreach ($hw->params[0]->children() as $param) {
                    $menu = (string) $param->attributes()->name;
                    $setting[$menu] = $this->get_hw_params_element($param, 'params', true);
                }
                $this->options->add_module_setting_page($setting);
            }
        }
        $this->add_export_widgets();
        $this->do_import();
    }
    //test import result filter
    public function test_filter_import_result($value) {
        return $value;
    }
}