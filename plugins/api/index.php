<?php
/**
 * Module Name: HW API
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 09:40
 */
include_once ('includes/class.xmlrpc.api.php');

/**
 * Class HW_Module_API
 * //this module should after php-libraries module
 */
class HW_Module_API extends HW_Module {
    public function __construct() {
        hwlib_register('HW_XMLRPC_Lib', 'xmlrpc');
        hwlib_load_library('HW_XMLRPC_Lib');
    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {

    }
}
HW_Module_API::register();