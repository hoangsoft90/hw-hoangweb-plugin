<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Interface HW_PHP_Library_Implement
 */
interface HW_PHP_Library_Implement {
    public function register_library($callback);
    public function load_library();
    public function init();
    public function _load_library_cb();

}

/**
 * Class HW_PHP_Library
 */
abstract class HW_PHP_Library implements HW_PHP_Library_Implement{
    /**
     * main library object
     * @var null
     */
    public $object = null;

    /**
     * contruct method
     */
    public function __construct() {
        $this->register_library(array($this, '_load_library_cb'));
    }
    /**
     * load library via callback
     * @var null
     */
    private $load_lib_cb = null;

    /**
     * register library using callback function
     * @param $callback callable
     */
    public function register_library($callback) {
        if(is_callable($callback)) $this->load_lib_cb = $callback;
    }

    /**
     * load currrent library
     */
    public function load_library() {
        if(is_callable($this->load_lib_cb)) call_user_func($this->load_lib_cb);
    }

    /**
     * init library after include class
     */
    public function init() {

    }
}