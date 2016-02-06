<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/11/2015
 * Time: 13:26
 */
include_once ('extensions/hw-woo-product.php');

/**
 * Class Product_Exporter
 */
class Product_Exporter extends HW_Module_Export {
    /**
     * @var
     */
    protected $product;
    /**
     * class entry
     * @param string $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->product = $this->load_extension('HW_WOO_Product' );
    }

    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //import products
        foreach($this->get_posts_xml($xml) as $item) {
            $atts = $item->attributes();
            $hw = $item->children($this->namespaces['hw']);
            $wp = $item->children($this->namespaces['wp']);
            $post_type = (string) $wp->post_type? (string) $wp->post_type : 'product';

            if($post_type =='product') {
                $this->product->load_product($item);

            }
            elseif($post_type =='page') {
                $this->posts->addItem(array(
                    'title' => (string) $wp->title,
                    'description' => '',
                    'content'=> (string)$wp->content,
                    'excerpt' => (string) $wp->excerpt,
                ));
            }
        }
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $name = (string) $atts['name'];
            $hw = $item->children($this->namespaces['hw']);

            $options = $this->recursive_option_data($hw->params->children())->option;
            $this->options->add_module_setting_page($options);
        }
        $this->do_import();
    }
}