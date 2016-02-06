<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/12/2015
 * Time: 17:02
 */
/**
 * add twig template for WooCommerce Product
 * Class HWoo_TimberProduct
 */
class HWoo_TimberProduct extends HW_TimberPost implements HW_Twig_Template_Context_Interface{
    /**
     * WC_Product
     * @var
     */
    public  $wc_product;
    /**
     * @var HW_Twig_Template_Context
     */
    private $context;
    /**
     * @param $product
     */
    public function __construct($product) {
        $id = $this->determine_post_id($product);

        if(is_numeric($id)) parent::__construct($id);
        if($product instanceof WC_Product) {
            $this->wc_product = $product;
        }
        $this->context = new HW_Twig_Template_Context($product);
    }

    /**
     * @param $item
     * @return WC_Product
     */
    protected static function determine_post_id($item) {
        if(is_object($item) && $item instanceof WP_Post) $id = $item->ID;
        elseif(is_numeric($item)) $id = ($item);
        elseif($item instanceof WC_Product) $id = $item->id;
        elseif($item instanceof TimberPost) $id = $item->id;
        else return $item;

        return $id;
    }
    /**
     * @param array $posts
     * @return array
     */
    static function wrap_with_template($posts = array()) {
        $single = is_object($posts)? true: false;
        if( is_object($posts)) {
            $data = array($posts);
        }
        else $data = $posts;
        if(is_array($data)){
            foreach($data as $id => $item) {
                $product = self::determine_post_id($item);
                if(!is_numeric($product)) continue;

                $data[$id] = new HWoo_TimberProduct(wc_get_product($product));
            }
        }

        return $single? reset($data): $data;
    }
    /**
     * get product gallery
     * @return array
     */
    function get_thumbnails($columns='') {
        $galleries = array();
        $attachment_ids = $this->wc_product->get_gallery_attachment_ids();
        $loop = 0;
        if(!$columns) $columns 	= apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

        foreach ( $attachment_ids as $attachment_id ) {
            $classes = array( 'zoom' );

            if ( $loop == 0 || $loop % $columns == 0 )
                $classes[] = 'first';

            if ( ( $loop + 1 ) % $columns == 0 )
                $classes[] = 'last';

            $galleries[$attachment_id] = new HW_TimberImage($attachment_id);
            $galleries[$attachment_id]->class = esc_attr( implode( ' ', $classes ) );   //class attr
        }
        return $galleries ;
    }

    /**
     * count attachment assign to product
     * @return int
     */
    function attachment_count() {
        return count($this->wc_product->get_gallery_attachment_ids());
    }

    /**
     * @return array
     * @param $columns
     */
    public function thumbnails($columns='') {
        return $this->get_thumbnails($columns);
    }

    /**
     * check product for availability
     * @return string
     */
    public function availability() {
        return $this->wc_product->get_availability();
    }
    /**
     * return WC_Product object
     * @return WC_Product
     */
    function wc_product() {
        return $this->wc_product;
    }
    public function get_object() {
        return $this->wc_product();
    }
    /**
     * invoke to any method in this class
     * @param $name
     * @param $args
     */
    public function __call($name, $args) {
        return $this->context->__call($name, $args);
        /*if(!method_exists($this, $name) && method_exists($this->wc_product, $name)) {
            return call_user_func_array(array($this->wc_product, $name), $args);
        }*/
    }

    /**
     * @param $property
     * @return mixed
     */
    function get($property) {
        if($this->wc_product && (isset($this->wc_product->$property) || property_exists($this->wc_product, $property))) {
            return $this->wc_product->$property;
        }
    }
}