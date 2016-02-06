<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/12/2015
 * Time: 17:47
 */
/**
 * HW_Twig_Loader_File
 */
HW_HOANGWEB::load_class('Twig_Autoloader');
if(class_exists('Twig_Loader_Filesystem') && !class_exists('HW_Twig_Loader_File')):
class HW_Twig_Loader_File extends Twig_Loader_Filesystem
{
    /**
     * @param $name
     * @return mixed
     */
    protected function findTemplate($name)
    {
        if(isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if(is_file($name)) {
            $this->cache[$name] = $name;
            return $name;
        }

        return parent::findTemplate($name);
    }
}
endif;
/**
 * Class HW_Twig_Environment
 */
if(class_exists('Twig_Environment')):
class HW_Twig_Environment extends Twig_Environment {
    /**
     * list allow functions for skin using
     * @var
     */
    private $global_functions;
    /**
     * @param Twig_Loader_File $loader
     */
    public function __construct($loader) {
        parent::__construct($loader);
        $this->global_functions = array(
            'esc_url','__','esc_html','esc_attr','esc_textarea', 'urlencode','esc_url_raw','_n',
            'home_url','sanitize_html_class','hw_option','hw__post','hw__get','hw__req','_hw_global'
        );
        foreach ($this->global_functions as $func) {
            if(function_exists($func)) $this->addFunction($func, new Twig_Function_Function($func));
        }
        $this->addFunction('_staticCall', new Twig_Function_Function(array($this, 'staticCall')));
    }

    /**
     * @param $class
     * @param $function
     * @param array $args
     * @return mixed|null
     */
    function staticCall($class, $function, $args = array())
    {
        if (class_exists($class) && method_exists($class, $function))
            return call_user_func_array(array($class, $function), $args);
        return null;
    }
}
endif;