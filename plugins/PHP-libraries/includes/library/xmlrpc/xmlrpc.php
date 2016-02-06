<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 11:03
 */
/**
 * Class HW_XMLRPC_Lib
 */
class HW_XMLRPC_Lib extends HW_PHP_Library {
    public function __construct() {
        parent::__construct();
    }
    /**
     * include this library
     * @require
     */
    public function _load_library_cb() {
        include_once(HW_LIBRARIES_PATH . '/xmlrpc-3.0.1/lib/xmlrpc.inc');
    }
}