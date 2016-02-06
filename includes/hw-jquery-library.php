<?php
#/root>includes/hoangweb-core.php
//load core class
HW_HOANGWEB::load_class('hwArray');
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/07/2015
 * Time: 15:44
 */
class HW_Libraries {
    /**
     * @var null
     */
    private $handle = null;
    /**
     * library name
     * @var null
     */
    private $name = null;

    /**
     * store all libraries instance
     *
     * @var array
     */
    private static $liraries_inst = array();

    /**
     * @param string $handle library handle
     * @param string $name library name
     */
    public function __construct($handle = '', $name = '') {
        if(!empty($handle)) $this->handle = $handle;
        if(!empty($name)) $this->set_name($name);
    }

    /**
     * add library to manager
     * @param $name lirary name as path to that library in folder js/libraries
     * @param $handle
     * @param bool $override allow to override exists library
     * @return library object
     */
    public static function add($name, $handle, $override = false) {
        if($override || !self::get($name)) {
            $obj = new self($handle, $name);
            //$obj->set_name($name);
            self::$liraries_inst[$name] = $obj;
            return $obj;
        }
        return self::get($name);
    }

    /**
     * return library object by given name
     * @param $lib library name
     */
    public static function get($lib) {
        if(is_string($lib) && isset(self::$liraries_inst[$lib])) return self::$liraries_inst[$lib];
    }

    /**
     * set library name
     * @param $lib
     */
    public function set_name($lib) {
        $this->name = $lib;
    }

    /**
     * return name of current library
     * @return null
     */
    public function get_name() {
        return $this->name;
    }
    /**
     * return current library handle
     * @return null|string
     */
    public function get_handle() {
        return $this->handle;
    }

    /**
     * return path of file in this lib directory
     * @param $file
     * @param $force
     */
    public function get_resource_path($file, $force=true) {
        if(!file_exists($file) || $force) $file = HW_HOANGWEB_JQUERY_LIBS_PATH . '/'.$this->get_name().'/'.$file;
        return $file;
    }

    /**
     * return url to file inside lib folder
     * @param $file
     * @return string
     */
    public function get_resource_url($file) {
        if(!HW_URL::valid_url($file)) $file = HW_HOANGWEB_JQUERY_LIBS . '/'.$this->get_name().'/'.$file;
        return $file;
    }

    /**
     * return options file for the lib
     * @return string
     */
    public function get_lib_options($data = false) {
        $file = $this->get_resource_path('options.php', true);
        if($data == true && file_exists($file)) {
            ob_start();
            include ($file);
            ob_clean();
            if(empty($theme_options)) $theme_options = array();
            return $theme_options;
        }
        return $file;
    }
    /**
     * enqueue stuff in library
     */
    public function enqueue() {
        HW_HOANGWEB::load_class('HW_URL');  //load HW_URL utility

        $this->enqueue_libs();  //other libs of dependencies for this lib
        $this->enqueue_scripts();   //enqueue required scripts of this lib
        $this->enqueue_styles();    //enqueue required styles of this lib
    }

    /**
     * enqueue libs required for other lib
     */
    public function enqueue_libs() {
        $_handle =  $this->get_handle();
        if($_handle && !empty($_handle['libs'])) {
            foreach ( (array)$_handle['libs'] as $lib)
            {
                if(is_string($lib) && self::get($lib)) {  //make sure the lib already exists
                    $this->enqueue_jquery_libs($lib);    //enqueue  lib
                }
                elseif(is_array($lib) && count($lib)) {
                    foreach ($lib as $name => $files) {
                        //validation
                        if(!self::get($name) && !self::get($files) && !is_array($files)) continue;

                        if(is_string($files)) $this->enqueue_jquery_libs($files);
                        elseif(is_array($files) && self::get($name)) {
                            foreach($files as $type => $handles) {
                                if($type == 'scripts') self::get($name)->enqueue_scripts($handles);
                                elseif($type == 'styles') self::get($name)->enqueue_styles($handles);
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * enqueue required scripts in library or specific resource
     * @example ->enqueue_scripts('jquery.colorbox.js','depend-example.js',...)
     * @example ->enqueue_scripts()     #enqueue required scripts from the lib
     */
    public function enqueue_scripts() {
        $args = hwArray::multi2single(func_get_args());

        $_handle =  $this->get_handle();
        //scripts
        if($_handle && !empty($_handle['scripts']) && is_array($_handle['scripts'])) {

            foreach ( $_handle['scripts'] as $name => $handle)
            {
                if(!is_array($handle) || !isset($handle['file'])) continue;
                //valid, make sure enqueue required script or match given file name
                if((count($args)>0 && !in_array($name, $args))
                    || (count($args)==0 && isset($handle['required']) && $handle['required'] == false)) continue;

                $handle_name = $this->get_name().'-'.$name;
                //valid resource url
                if(class_exists('HW_URL') && !HW_URL::valid_url($handle['file'])) {
                    $handle['file'] = $this->get_resource_url($handle['file']);
                }
                wp_enqueue_script($handle_name , $handle['file'], isset($handle['depends'])? $this->valid_depends_enqueue($handle['depends']) : array());
            }
        }
    }

    /**
     * valid dependencies handle name
     * @param $depends
     */
    private function valid_depends_enqueue($depends) {
        $depends = (array)$depends ;
        $_handle =  $this->get_handle();
        if(!isset($_handle['scripts']) || !is_array($_handle['scripts'])) {
            $_handle['scripts'] = array();
        }
        if(!isset($_handle['styles']) || !is_array($_handle['styles'])) {
            $_handle['styles'] = array();
        }
        foreach((array)$depends as $id => $name) {
            if(isset($_handle['scripts'][$name]) || isset( $_handle['styles'][$name] )) {
                $depends[$id] = $this->get_name(). '-' .$name;
            }
        }
        return $depends;
    }
    /**
     * enqueue required styles in library or specific resource
     * @example ->enqueue_styles('colorbox.css',..)
     * @example ->enqueue_styles()   #enqueue required styles from the lib
     */
    public function enqueue_styles() {
        $args = hwArray::multi2single(func_get_args());
        $_handle =  $this->get_handle();
        //stylesheets
        if($_handle && !empty($_handle['styles']) && is_array($_handle['styles'])) {
            foreach ( $_handle['styles'] as $name => $_file)
            {
                //valid, make sure enqueue required script or match given file name
                if((count($args)>0 && !in_array($name, $args))
                    || (count($args)==0 && is_array($_file) && isset($_file['required']) && $_file['required'] == false)) continue;

                $handle_name = $this->get_name().'-'.$name;
                //valid file name
                if(is_array($_file) && isset($_file['file'])) {
                    $file = $_file['file'] ;    //resource file
                }
                elseif(is_string($_file)) {
                    $file =  $_file;
                }
                else {
                    continue;
                }

                //valid resource url
                if(class_exists('HW_URL') && !HW_URL::valid_url($file)) {
                    $file = $this->get_resource_url($file);
                }
                wp_enqueue_style($handle_name , $file, isset($handle['depends'])? $this->valid_depends_enqueue($handle['depends']) : array());
            }
        }
    }
    /**
     * enqueue scripts
     * @example ::enqueue_jquery_libs('jquery-colorbox')
     */
    public static function enqueue_jquery_libs() {
        $files = hwArray::multi2single(func_get_args());
        foreach ($files as $file) {
            if(self::get($file)) {
                //enqueue required both of scripts & styles from the lib
                self::get($file)->enqueue();
            }
        }
    }
    /**
     * register js libs
     * @param $name library name
     * @param $resource define resrouce
     */
    public static function registers_jquery_libs($name = '', $resource = '') {
        //self::$jquery_libs    ->this variable no longer use
        $init_libs = hw_get_autoload('jquery-libs');
        if(is_callable($init_libs)) call_user_func($init_libs);

        if(!empty($name) && is_string($name) && is_array($resource)) {
            //do not allow override exists library
            if(!HW_Libraries::get($name)) HW_Libraries::add($name, $resource);  //or HW_Libraries::add($name, $resource,false)
        }
    }
    /**
     * return get register js libs
     * @param $lib
     */
    public static function get_default_js_libs($lib) {
        $hw_config = array();
        include_once (HW_HOANGWEB_PATH . '/includes/library/load-js-libs.php');

        if(isset($hw_config[$lib]) ) return $hw_config[$lib];
    }
}