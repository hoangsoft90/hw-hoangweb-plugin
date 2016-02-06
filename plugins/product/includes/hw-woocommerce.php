<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 11/12/2015
 * Time: 15:25
 */
/**
 * Class HW_Woocommerce
 */
class HW_Woocommerce extends HW_Twig_Template_Context implements HW_Twig_Template_Context_Interface{
    /**
     * @var
     */
    public static $instance;

    public function get_object() {
        return parent::get_object()? parent::get_object(): WC();
    }

    /**
     * @param $code
     * @return string
     */
    function get_country($code) {
        $wc = WC();
        return isset($wc->countries->countries[$code])? $wc->countries->countries[$code] : '';
    }

    /**
     * @param $page
     * @return string
     */
    public function page_permalink($page) {
        return wc_get_page_permalink($page);
    }
}
HW_Woocommerce::add_context('hwoo');
//HW_Timber::add_context('hwoo', 'HW_Woocommerce');

