<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 09:43
 */
/**
 * Class HW_XMLRPC_API
 */
class HW_XMLRPC_API extends HW_XMLRPC_Server{
    public function __construct() {
        parent::__construct('core');
    }

    /**
     * register new methods
     * @param $methods
     */
    public function add_xml_rpc_methods($methods) {
        //Where frs.helloWorld is the XML-RPC method name, and hello_world is the callback, namespace 'frs' for any you want.
        $methods['module_info'] = array($this, 'modules_info');
        $methods['abc'] = array($this, 'hello_world');
        $methods['test'] = array($this, 'test_phpxmlrpc');
        //remove method
        //unset( $methods['demo.addTwoNumbers'] );
        return $methods;
    }

    /**
     * fetch module info
     * @param $module
     */
    public function modules_info($module) {
        global $wp_xmlrpc_server;
        $_module = HW_TGM_Module_Activation::get_register_modules($module);
        if(count($_module)) {
            $_module = $_module[$module];
            $_module['download_link'] = 'http://api.hoangweb.com/modules/download/'. $module;
        }

        return /*json_encode*/($_module);
    }
    //for testing
    function hello_world($params){#
        global $wp_xmlrpc_server;
        $arg1     = $params;

        return "Hello ".$wp_xmlrpc_server->escape( $arg1 );
    }
    //test simple rpc client
    public function test_client() {
        $client = new HW_XMLRPC_Client( "http://localhost/wordpress/xmlrpc.php" );
        $available_methods = $client->call( 'system.listMethods' );
        print_r( $available_methods );
    }
    //test rpc client with xmlrpcmsg
    function test_phpxmlrpc() {
        $r = $this->call('hw.core.module_info', 'api');

        if (!$r->faultCode()) {
            $v = $r->value();
            __print( htmlentities($v->scalarval())) ;
            #__print (htmlentities($r->serialize()) );
        } else {
            print "Fault <BR>";
            print "Code: " . htmlentities($r->faultCode()) . "<BR>" .
                "Reason: '" . htmlentities($r->faultString()) . "'<BR>";
        }
    }
}
HW_XMLRPC_API::register();