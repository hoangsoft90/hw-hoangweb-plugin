<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/12/2015
 * Time: 15:03
 */
include_once('template/template-functions.php');
include_once('template/template-hooks.php');

/**
 * For some reason products in the loop don't get the right context by default.
 * @param $post
 */
function hwoo_set_product( $post ) {
    global $post, $product;
    if ( is_woocommerce() ) {
        $product = wc_get_product($post->ID);
    }
}

/**
 * @param WP_Post $_post
 */
function hwoo_reset_product_post($_post) {
    global $post, $product;
    _setup_postdata($_post);
    $product = wc_get_product($_post->ID) ;
}
/*
* goes in theme functions.php or a custom plugin. Replace the image filename/path with your own <img src="http://www.wpexplorer.com/wp-content/themes/wpexplorer-twenty-fourteen/images/smileys/icon_smile.gif" alt=":)" class="wp-smiley">
*
**/
function custom_woocommerce_placeholder_img_src( $src ,$context='') {
    $upload_dir = wp_upload_dir();
    $uploads = untrailingslashit( $upload_dir['baseurl'] );
    $src = $uploads . '/2012/07/thumb1.jpg';

    return $src;
}
add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src',10,2);

/* Change thumb size for correct zoom work */
function change_catalog_thumbnail($size, $context=''){
    //$size = 'shop_single';
    return $size;
}
add_filter( 'single_product_small_thumbnail_size', 'change_catalog_thumbnail', 10, 2 );


/**
 * woocommerce_before_single_product hook
 *
 * @hooked wc_print_notices - 10
 */
function hw_woocommerce_before_single_product() {
    if(is_singular('product')) {
        if ( post_password_required() ) {
            echo get_the_password_form();
            return;
        }
    }
}
//add_action('woocommerce_before_single_product', 'hw_woocommerce_before_single_product');

/**
 * @param $data
 * @return mixed
 */
function hwoo_timber_context($data) {
    global $woocommerce, $product;
    if(is_singular('product')) {
        //should implement in particular template
        #$data['post'] = Timber::query_post($product);   //important! you must to fetch post data before
        #if(!empty($product)) $data['product']= new HWoo_TimberProduct($product);//*/Timber::query_post($product);//, 'HWoo_TimberProduct') ;
    }
    return $data;
}
add_filter('timber_context', 'hwoo_timber_context');

/**
 * support product module with pagination from woocommerce
 */
function hwoo_filter_pagination_output($html) {
    if(class_exists('HWPageNavi_Core',false)) {
        HWPageNavi_Core::render_pagination_skin($html);
    }
    return $html;
}
add_filter('hwoo_pagination', 'hwoo_filter_pagination_output');