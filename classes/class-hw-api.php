<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 10:10
 */
/**
 * Interface HW_XMLRPC_Server_Interface
 */
interface HW_XMLRPC_Server_Interface {
    /**
     * @param $methods
     * @return mixed
     */
    public function add_xml_rpc_methods($methods) ;
}
/**
 * Class HW_XMLRPC_Server
 */
abstract class HW_XMLRPC_Server implements HW_XMLRPC_Server_Interface{
    /**
     * @var array
     */
    static $apis = array();
    /**
     * @var
     */
    private $namespace = 'hw';
    /**
     * construct method of class
     * @param $namespace
     */
    public function __construct($namespace= '') {
        if($namespace) $this->namespace .= '.'.$namespace;
        add_filter( 'xmlrpc_methods', array($this, '_add_xml_rpc_methods' ));
    }

    /**
     * @hook xmlrpc_methods
     * @param $methods
     * @return mixed
     */
    public function _add_xml_rpc_methods($methods) {
        $_methods = $this->add_xml_rpc_methods(array());
        if(is_array($_methods))
        foreach ($_methods as $method => $callback) {
            $methods[$this->valid_rpc_method($method)] = $callback;
        }
        return $methods;
    }

    /**
     * invoke rpc method
     * @doc http://gggeek.github.io/phpxmlrpc/doc-2/
     * @param $method
     * @param string $args
     * @return xmlrpcresp
     */
    public function call($method, $args = '') {
        if(is_string($method)) {
            if(is_string($args)) $args = (array) $args;
            if(is_array($args))
            foreach ($args as $name => $arg) {
                if(!is_numeric($name)) $args[$name] =  new xmlrpcval($arg, "string");
                else $args[] = new xmlrpcval($arg, "string");
            }
            $method = new xmlrpcmsg($method, $args);
        }
        //valid
        if(!$method instanceof xmlrpcmsg) return;
        $xmlrpc_url = site_url('xmlrpc.php');
        $xmlrpc = parse_url($xmlrpc_url);
        if(!isset($xmlrpc['port'])) $xmlrpc['port'] = '80';

        $c = new xmlrpc_client($xmlrpc['path'], $xmlrpc['host'], $xmlrpc['port']);
        #$c->return_type = 'phpvals';
        return $c->send($method);
    }

    /**
     * get current api object
     * @return null
     */
    public static function current() {
        $api = get_called_class();
        return isset(self::$apis[$api])? self::$apis[$api] : null;
    }
    /**
     * valid rpc method
     * @param $name
     */
    private  function valid_rpc_method($name) {
        if(strpos($name, $this->namespace)!==0) return $this->namespace.'.'.$name;
        return $name;
    }
    /**
     * register api
     */
    final public static function register() {
        $api = get_called_class();
        add_action('hw_modules_loaded', array($api,'init'));
    }

    /**
     * init class instance
     */
    final public static function init() {
        $api = get_called_class();
        if(!isset(self::$apis[$api])) self::$apis[$api] = new $api;
    }
}

/**
 * Class HW_XMLRPC_Client
 */
class HW_XMLRPC_Client {

    private $url;

    function __construct( $url ) {
        $this->url = $url;
    }

    /**
     * Call the XML-RPC method named $method and return the results, or die trying!
     *
     * @param string $method XML-RPC method name
     * @param mixed ... optional variable list of parameters to pass to XML-RPC call
     *
     * @return array result of XML-RPC call
     */
    public function call() {

        // get arguments
        $params = func_get_args();
        $method = array_shift( $params );

        $post = xmlrpc_encode_request( $method, $params );

        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt( $ch, CURLOPT_URL,            $this->url );
        curl_setopt( $ch, CURLOPT_POST,           true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS,     $post );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // issue the request
        $response = curl_exec( $ch );
        $response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_errorno = curl_errno( $ch );
        $curl_error   = curl_error( $ch );
        curl_close( $ch );

        // check for curl errors
        if ( $curl_errorno != 0 ) {
            die( "Curl ERROR: {$curl_errorno} - {$curl_error}n" );
        }

        // check for server errors
        if ( $response_code != 200 ) {
            die( "ERROR: non-200 response from server: {$response_code} - {$response}n" );
        }

        return xmlrpc_decode( $response );
    }
}