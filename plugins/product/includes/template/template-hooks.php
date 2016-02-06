<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/12/2015
 * Time: 12:15
 */
/**
 * single product page
 */
/**
 * init partials of woocommerce template
 * @hook init
 */
function register_partials() {
    if(is_admin()) return;
    //remove default woo hook
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination',10);

    //global
    add_partial('breadcrumb', 'woocommerce_breadcrumb') ;
    //loop
    add_partial('add_to_cart_button', 'woocommerce_template_loop_add_to_cart');

    /**single product***********/
    add_partial('add_to_cart_simple', 'woocommerce_simple_add_to_cart');
    add_partial('add_to_cart_variable', 'woocommerce_variable_add_to_cart');
    add_partial('add_to_cart_grouped', 'woocommerce_grouped_add_to_cart');
    add_partial('product_images', 'woocommerce_show_product_images');
    add_partial('product_attributes', 'hwwoo_show_product_attributes');
    add_partial('product_meta', 'woocommerce_template_single_meta');
    add_partial('share', 'woocommerce_template_single_sharing');

    /*archive product*/
    add_partial('taxonomy_archive_description', 'woocommerce_taxonomy_archive_description');
    add_partial('product_archive_description', 'woocommerce_product_archive_description');
    add_partial('catalog_ordering_form', 'woocommerce_catalog_ordering');
    add_partial('pagination', 'hwoo_display_pagination');

    /*cart*/
    add_partial('cart_collaterals_cross_sell', 'woocommerce_cross_sell_display');
    add_partial('cart_collaterals_totals', 'woocommerce_cart_totals');

    /*checkout*/
    add_partial('checkout_form_login', 'woocommerce_checkout_login_form');
    add_partial('checkout_form_coupon', 'woocommerce_checkout_coupon_form');
    add_partial('review_order_shipping', 'hwoo_review_order_shipping');

    /*myaccount*/
    add_partial('myaccount_show_address', 'hwoo_myaccount_show_address') ;
    add_partial('myaccount_show_downloads', 'hwoo_myaccount_show_downloads') ;
}
add_action('init', 'register_partials');
