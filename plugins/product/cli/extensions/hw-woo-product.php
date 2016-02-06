<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/12/2015
 * Time: 11:33
 */
class HW_WOO_Product extends HW_Module_Export_Extension{

    /**
     * @var
     */
    public $xml_item;

    /**
     * @param $module_export
     */
    public function __construct($module_export) {
        parent::__construct($module_export);


    }
    public function init($data){}
    /**
     * product type
     * @param $type
     */
    public function add_product_type($type='') {
        if(!$type) $type = (string)$this->xml_item->product_type? (string)$this->xml_item->product_type : 'simple';;
        $this->get()->posts->add_term(array(
            'slug' => $type,
            'name' => $type,
            'taxonomy' => 'product_type'
        ));
    }

    /**
     * add product
     * @param $product
     */
    public function add_product($product) {

        $this->get()->posts->addItem(array(
            'title' => $product['title'],
            'description' => isset($product['description'])? $product['description']:'',
            'content' => $product['content'],
            'excerpt' => $product['excerpt'],
            'post_type' => 'product',
            'post_metas' => $product['metas'],
            'attachments' => $product['attachments']
        ));
    }

    /**
     * product meta
     * @return array
     */
    private function meta_data() {
        //product meta data
        //product meta
        $product_meta = array(
            '_stock_status' => 'instock',
            'total_sales' => '0',
            '_downloadable' => 'no',
            '_virtual' => 'no',
            //tax
            '_tax_status' => '',
            '_tax_class' => '',

            '_purchase_note' => '',
            '_featured' => 'no',
            '_weight' => '',
            '_length' => '',
            '_width' => '',
            '_height' => '',
            '_sku' => '',
            '_product_attributes' => array(),
            '_sale_price_dates_from' => '',
            '_sale_price_dates_to' => '',
            '_sold_individually' => '',
            '_stock' => '0',
            '_backorders' => 'no',
            '_manage_stock' => 'yes',
            '_visibility' => 'visible',
            '_edit_last' => '3'
        );
        if(!empty($this->xml_item->meta)) {
            $post_meta = $this->get()->fetch_post_metas_value($this->xml_item->meta);
            if(!empty($post_meta)) $product_meta = array_merge($product_meta, $post_meta);
        }

        //valid
        if(!isset($product_meta['_price'])) $product_meta['_price'] = $product_meta['_sale_price'];
        return $product_meta;
    }

    /**
     * fetch product atttributes
     * @param string $item
     */
    public function get_attributes($item='') {
        if(!$item) $item = $this->xml_item;
        $data = array();
        $hw = $item->children($this->get()->namespaces['hw']);
        $atts = $hw->product_attributes;
        $attributes = $this->get()->recursive_option_data($atts)->option;
        foreach ($attributes as $att => $items) {
            if(!isset($data['pa_'. $att])) $data['pa_'. $att] = array();
            foreach($items as $slug) {
                $term = $this->get()->get('term_'. $att.'_'. $slug);
                $data['pa_'. $att][$slug] = $term['data']['name'];
            }
        }
        return $data;
    }
    /**
     * load product item
     * @param $item
     */
    public  function load_product($item) {
        if(!$item instanceof SimpleXMLElement) return;
        //$p = new self($this);
        $this->xml_item = $item;

        $hw = $item->children($this->get()->namespaces['hw']);
        $wp = $item->children($this->get()->namespaces['wp']);

        //product type
        $this->add_product_type();
        //product meta
        $metas = $this->meta_data();

        //product attributes
        $_product_attributes = array();
        $pos = 0;
        $atts = $this->get_attributes();
        foreach($atts as $tax_att => $terms) {
            $_product_attributes[$tax_att] = array(
                'name' => $tax_att,
                'value' => '',
                'position' => $pos++ ,
                'is_visible' => '1',
                'is_variation' => '1',
                'is_taxonomy'=> '1'
            );
        }
        $metas['_product_attributes'] = $_product_attributes;

        //add post
        $this->add_product(array(
            'title' => (string) $wp->title,
            'description' => '',
            'content' => (string) $wp->content,
            'excerpt' => (string) $wp->excerpt,
            'post_metas' => $metas,
            'attachments' => $this->get()->get_atachments($hw->attachment),
            'terms' => $atts
        ));
    }
}