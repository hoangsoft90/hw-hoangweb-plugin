<?php

/**
 * Class HW_Twig_Template
 */
class HW_Twig_Template {
    /**
     * __get, __set property
     * @var array
     */
    private $data = array();

    /**
     * Twig_Environment object
     * @var
     */
    private $twig;

    /**
     * initial
     */
    private static function init() {
        if(class_exists('HW_HOANGWEB')) {
            //load twig template engine
            HW_HOANGWEB::load_class('Twig_Autoloader');
            //if not work try this
            //if class not found try include directly
            //include_once(HW_HOANGWEB_PATH . '/lib/vendor/autoload.php');
        }
    }

    /**
     * return twig object
     * @return mixed
     */
    public function get() {
        if(!empty($this->twig)) return $this->twig;
    }
    /**
     * create twig instance for given templates path
     * @param $path
     * @return HW_Twig_Template
     */
    public static function create($path) {
        self::init();
        $inst = new self();
        $inst->register_twig($path);
        return $inst;
    }
    /**
     * get twig work with your template path
     * @param $tpl_path
     * @return Twig_Environment
     */
    function register_twig($tpl_path) {
        //register twig loader
        if(class_exists('Twig_Autoloader')){
            Twig_Autoloader::register();
            include_once ('class-template-twig-system.php');    //modify twig system class of Twig_Loader_Filesystem
            $loader = new HW_Twig_Loader_File($tpl_path);
            $this->twig = new HW_Twig_Environment($loader);
            return $this->twig;
        }
    }
    /**
     * check to see if file exists from twig
     * @param $file
     * @param $twig
     * @return bool
     */
    public static function twig_asset_exists($file, $twig){
        try{
            $twig->loadTemplate($file);
            return true;
        }
        catch(Exception $e){
            if(class_exists('HW_Logger')) {
                HW_Logger::add_debug($e->getFile().':'.$e->getLine().'=>'.$e->getMessage());
            }
            return false;
        }
    }

    /**
     * call twig->display method
     * @param $tpl
     * @param $data: data to be sent to template file
     */
    public function _display($tpl, $data = array()) {
        if(!is_array($data)) $data = array();
        $link = $this->_loadTemplate($tpl);
        if($link) {
            if(empty($data)) $data = $this->get_template_data();
            $link->display($data);
        }
    }

    /**
     * call twig->loadTemplate method
     * @param $tpl
     * @return mixed
     */
    public function _loadTemplate($tpl) {
        if($this->get() && self::twig_asset_exists($tpl, $this->get())) {
            $link = $this->get()->loadTemplate($tpl);
            return $link;
        }
    }
    /**
     * call twig->render method
     * @param $tpl
     * @param $data: data to be sent to template file
     */
    public function _render($tpl, $data = array()) {
        if(!is_array($data)) $data = array();
        $link = $this->_loadTemplate($tpl);
        if($link) {
            if(empty($data)) $data = $this->get_template_data();
            return $link->render($data);
        }
    }

    /**
     * current twig template data
     * @return array
     */
    public function get_template_data() {
        return $this->data;
    }

    /**
     * set template data
     * @param $value
     */
    public function set_template_data($value) {
        $this->data = $value;
    }
    /**
     * magic method
     * @param $name
     * @param $value
     */
    public function __set($name, $value){
        $this->data[$name] = $value;

    }

    /**
     * how to get property
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        if(isset($this->data[$name])) return $this->data[$name];
    }
    public static function __init() {

    }
}