<?php
/**
 * Class HW_Autoload
 */
class HW_Autoload extends HW_Core{
    /**
     * main class constructor
     */
    public function __construct() {

    }
    /**
     * auto load class
     * note: we rename autoload function to hw__autoload and load directly class in method HW_HOANGWEB::loadclass
     * ->why? because some hosting web server call autoload different behavior.
     * @param $lib: which class will be load
     */
    public static function hw__autoload($lib)
    {
        static $onces = array();
        //include $class_name . '.php';
        $libs = isset(HW_HOANGWEB::$hw_global['classes'])? HW_HOANGWEB::$hw_global['classes'] : array();  //load all libs
        $_lib = HW_HOANGWEB::get_class($lib);   //= $libs[$lib]

        if(!empty($_lib) && is_string($lib) && !isset($onces[$lib]) /*&& isset($libs[$lib])*/ && isset($_lib['class'])
            && !class_exists($_lib['class'], false))  //do not use __autoload to make infinitive loop inside
        {
            $debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
            //debug version
            if(0&&$debug && isset($_lib['debug']) && file_exists($_lib['debug'])){  //for convernion i alway run release version
                $file = $libs[$lib]['debug'];
            }
            //release version
            elseif(isset($_lib['release']) && file_exists($_lib['release'])){
                $file = $_lib['release'];
            }
            //check required other
            if(isset($_lib['deps'])  ) {
                foreach((array) $_lib['deps'] as $require) {
                    if(!$require || class_exists($require, false)) continue;
                    HW_HOANGWEB::register_class($require, HW_HOANGWEB::setup_classes($require));
                }

            }
            if(isset($file)) {
                require_once($file);
            }
            $onces[$lib] = true;
        }
    }

    /**
     * include file
     * @param $file
     */
    private static  function hw_include_file($file) {
        if(file_exists($file)) include ($file);
    }
    /**
     * load your class
     * @param $lib
     */
    public static  function hw_load_class($lib) {
        self::hw__autoload($lib);
    }

    /**
     * return autoload config
     * @param $config
     */
    public static function get_autoload($config) {
        return HW_Libraries::get_default_js_libs($config);
    }

}
HW_Autoload::get_instance();