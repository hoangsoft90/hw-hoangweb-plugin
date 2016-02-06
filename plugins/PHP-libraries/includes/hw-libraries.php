<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Class HW_PHP_Libraries
 */
class HW_PHP_Libraries {
    /**
     * all libraries stored here
     * @var array
     */
    private static $libraries = array();
    private static $libraries_path = array();

    /**
     * constructor
     */
    public function __construct() {
        $this->setup();
        //self::register_lib('HW_Mobile_Detect','mobile_detect');   //register library example
    }

    /**
     * load library
     * @param $name
     * @return library object
     */
    public static function load_lib($name) {
        if(self::exists($name) && class_exists($name)) {
            //create instance of library class
            $lib = new $name();
            if($lib instanceof HW_PHP_Library) {
                $lib->load_library();
                $lib->object = self::$libraries[$name]['object'] = $lib->init();
            }
            self::$libraries[$name]['manager'] = $lib;

            return (object)self::$libraries[$name];
        }
    }

    /**
     * check library already exists
     * @param $name library class name
     */
    public static function exists($name) {
        return isset(self::$libraries[$name]);
    }

    /**
     * get library instance
     * @param $name
     * @return mixed
     */
    public static function get($name) {
        if(self::exists($name)) return self::$libraries[$name];
    }
    /**
     * register class for library
     * @param $name class to control the library
     * @param $path path to that library (give short or full) (optional)
     */
    public static function register_lib($name, $path = '') {
        if(! self::exists($name)) {
            //get from registered libraries path
            if(empty($path) && isset(self::$libraries_path[$name])) {
                $path = self::$libraries_path[$name];
            }

            //get path to folder of the library
            if(!file_exists($path)) {
                $_path = HW_LIBRARIES_MANAGER_PATH . '/'. $path;
                if(file_exists($_path . '/' . rtrim($path, '.php'). '.php') ) {
                    $file = $_path . '/' . rtrim($path, '.php'). '.php';
                }
                elseif(file_exists($_path . '/index.php')) $file = $_path . '/index.php';
            }
            else $file = $path;

            if(isset($file)) include_once ($file);   //include file for get library class

            //add to manager
            if(class_exists($name)) self::$libraries[$name] = array('class_file' => $file);
        }
    }

    /**
     * pre-load
     */
    public function setup() {
        self::$libraries_path = array(
            'HW_Simple_Captcha' => 'simple_captcha',    //simple captcha
            'HW_Mobile_Detect' => 'mobile_detect'   //mobile detector
        );
        foreach (self::$libraries_path as $name => $path) {
            self::register_lib($name, $path);  //captcha
        }

    }
}