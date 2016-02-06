<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/12/2015
 * Time: 17:10
 */
/**
 * Class HWoo_Customer
 */
class HWoo_Customer extends HW_Twig_Template_Context implements HW_Twig_Template_Context_Interface{
    /**
     * @var
     */
    public static $instance;
    /**
     * class construct method
     * @param $object
     */
    function __construct($object) {
        //$object = new WC_Customer();
        parent::__construct($object);//
    }
    /**
     * @param $page
     */
    function get_url($page) {
        switch($page) {
            case 'sign_out': return wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ));
            case 'edit_account_url': return wc_customer_edit_account_url();
        }
    }

    /**
     * @param $page
     * @return string
     */
    function url($page) {
        return $this->get_url($page ) ;
    }
    public function get_object() {
        static $customer;
        if(!$customer) $customer = new WC_Customer();
        return $customer;
    }
}
HWoo_Customer::add_context('hwoo_customer');