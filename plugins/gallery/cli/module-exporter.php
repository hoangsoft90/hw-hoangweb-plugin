<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/11/2015
 * Time: 11:36
 */
/**
 * Class Gallery_Exporter
 */
class Gallery_Exporter extends HW_Module_Export {
    /**
     * @param string $module
     */
    public function __construct($module) {
        parent::__construct($module);
        //$this->add_module_widgets('');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //add posts
        foreach($this->get_posts_xml($xml) as $item) {
            $atts = $item->attributes();    //get xml element attributes
            $hw = $item->children($this->namespaces['hw']);
            $wp = $item->children($this->namespaces['wp']);

            $post_type = (string) $wp->post_type;
            $title = (string) $wp->title;

            //post/page..
            if((string)$atts->type != 'gallery' || $post_type !='hw-gallery') {
                /*if(isset($hw->content) && ($hw->content->xpath('hw:params'))) {
                    //$content = $this->get_result_content($hw->content[0]->params->children());    //is deprecated
                    //$content = $this->get_import_result($hw->content[0]->params->children());
                    //$content = $this->recursive_option_data($hw->content->xpath('hw:params'));
                    $content = $hw->content->xpath('hw:params');
                    foreach($hw->content->xpath('hw:params') as $content){break;};

                }
                else $content = (string) $hw->content;
                */
                $content = $this->get_hw_params_element($hw->content,'hw:params', true);
                $this->posts->addItem(array(
                    'title' => $title,
                    'description' => '',
                    'content' => $content,
                    'post_type' => (string)$wp->post_type
                ));
                continue;
            }
            //skin data
            $skin = $hw->skin->children($this->namespaces['skin']);
            $skin_name = ($hw->skin->attributes()->name? (string)$hw->skin->attributes()->name : 'skin');
            $skin_instance = (string) $skin->instance;

            //skin
            $pskin = new HWIE_Skin_Params($skin_name, $skin_instance);
            $pskin->add_hash_skin('hash_skin', array(
                'skin' => (string)$skin->skin_name,
                'source' => (string)$skin->source
            ));
            //other skin params
            $params = array(
                'hwskin_condition' => '',
                'skin_options' => array(
                    'enqueue_css_position' => 'footer',
                    'enqueue_js_position' => 'footer'
                )
            );
            $pskin->add_skin_config();
            if(!empty($skin->params)) {
                $skin_options = $this->simplexml_parser->recursive_option_data($skin->params[0]->children() )->option;
                if(!empty($skin_options)) $params = array_merge($params, $skin_options);
            }
            $pskin->extra_params($params);
            //gallery data
            $galleries = array();
            if(!empty($hw->data) && !empty($hw->data->item)) {
                foreach($hw->data->item as $item) {
                    //$this->simplexml_parser->recursive_option_data($item->children())->option;
                    $gallery = HW_XML::xml2array($item->children());
                    if(!isset($gallery['src'])) continue;

                    $gallery['status'] = !isset($gallery['status']) || $gallery['status']? 'active' : '';
                    if(!isset($gallery['link'])) $gallery['link'] = $gallery['src'];
                    if(!isset($gallery['alt'])) $gallery['alt'] = '';
                    if(!isset($gallery['thumb'])) $gallery['thumb'] = '';

                    $galleries[] = $gallery;
                }

            }

            $post_name = $this->posts->addItem(array(
                'title' => $title,
                'description' => '',
                'content'=> '',
                'excerpt' => '',
                'post_type' => 'hw-gallery',
                'post_metas'=> array(
                    '_eg_gallery_data' => array(
                        'title' => $title,
                        'config' => array(
                            'columns' => '1',
                            'gutter' => '10',
                            'margin' => '10',
                            'crop' => '0',
                            'crop_width' => '960',
                            'crop_height' => '300',
                            'lightbox_enabled' => '1',
                            'title_display' => 'float',
                            'classes' => array(),
                            'title' => $title,
                            'slug' => '',
                            'rtl' => '0',
                            'hw_skin' => $pskin->get_skin(0)
                        ),
                        'gallery' => $galleries
                    ),
                    '_eg_in_gallery' => array(),
                    '_edit_last' => '1'
                ),

            ), HW_XML::attributesArray($item) );
        }
        $this->add_export_widgets();    //import widgets if exists
        $this->do_import(); //start import data
        $posts = $this->importer->get_import_results('posts');

        /*if(!empty($post_name) && isset($posts[$post_name])) { //don't need
            $gallery_id = $posts[$post_name]['ID'];
            //create page to display gallery
            wp_insert_post(array(
                'post_type'             => 'page',
                'import_id'             => 0,
                'post_title'    => 'Gallery',
                'post_content'  => '[hw-gallery id="'.$gallery_id.'"]',
                'post_status'   => 'publish',
            ));
        }*/

    }

    /**
     * @param $id
     * @return string
     */
    function gallery_shortcode($id) {
        return sprintf('[hw-gallery id="%s"]', $id);
    }
}