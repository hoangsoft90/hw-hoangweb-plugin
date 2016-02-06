<?php
global $hw_twig;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 11/12/2015
 * Time: 11:16
 */
if(!class_exists('HW_Timber')):
class HW_Timber {
    /**
     * store singleton of this class
     * @var
     */
    public static $instance;
    /**
     * @var
     */
    private static $current_working_file;
    /**
     * @var array
     */
    private static $context = array();

    public function __construct() {
        global $timber;
        $this->_init();
    }

    /**
     * return instance of the class
     * @return HW_Timber
     */
    public static function get_instance() {
        if(!self::$instance) self::$instance = new self;
        return self::$instance;
    }
    /**
     * @param $file
     * @return mixed
     */
    public static function load($file) {
        self::$current_working_file = $file;    //change current working template
        return Timber::get_context();
    }

    /**
     * @param $name
     * @param $class
     */
    public static function add_context($name, $class) {
        if(!isset(self::$context[$name]) && get_parent_class($class)=='HW_Twig_Template_Context') {
            self::$context[$name] = call_user_func(array($class, 'get_instance')/*, self::get_instance()*/);    //let self class defined
        }
    }
    /**
     * @param $function
     * @param $alias
     * @param $data
     */
    public static function add_template_utility($function, $alias='', $data = array()) {
        if(!is_array($data)) $data = array();
        $data['current_working_template'] = self::$current_working_file;
        add_template_utilities(self::$current_working_file, $function, $alias, $data);
    }

    /**
     * @param $file
     * @param array $data
     */
    public static function add_template_file($file, $data = array()) {
        template_file(self::$current_working_file , $file, $data);
    }

    /**
     * setup hooks
     */
    public function _init() {
        add_filter('timber_context', array($this, '_timber_context'));
        add_filter('timber/twig/filters', array($this,'hw_timber_twig_filters'));
    }
    /**
     * @hook timber/twig/filters
     * @param $twig
     * @return mixed
     */
    function hw_timber_twig_filters($twig) {
        $twig->addExtension(new MyTagExtension());
        //load partial template
        $partial = new Twig_SimpleFunction('partial', function () {
            $args = func_get_args();
            $alias = current(array_splice($args, 0, 1));
            load_partial($alias, $args);
        });
        $twig->addFunction($partial);

        //other filters
        $filter = new Twig_SimpleFilter('apply_function', array($this, 'apply_function_twig_filter'), array('needs_environment' => true, 'needs_context' => true));
        $twig->addFilter($filter);
        $twig->addFilter(new Twig_SimpleFilter('esc_attr', 'esc_attr'));
        $twig->addFilter(new Twig_SimpleFilter('esc_url', 'esc_url'));
        $twig->addFilter(new Twig_SimpleFilter('esc_html', 'esc_html'));
        $twig->addFilter(new Twig_SimpleFilter('esc_html__', 'esc_html__'));
        $twig->addFilter(new Twig_SimpleFilter('esc_textarea', 'esc_textarea'));
        $twig->addFilter(new Twig_SimpleFilter('esc_attr_e', 'esc_attr_e'));
        $twig->addFilter(new Twig_SimpleFilter('esc_url_raw', 'esc_url_raw'));
        $twig->addFilter(new Twig_SimpleFilter('sanitize_title', 'sanitize_title'));
        $twig->addFilter(new Twig_SimpleFilter('wptexturize', 'wptexturize'));
        $twig->addFilter(new Twig_SimpleFilter('checked', 'checked'));
        $twig->addFilter(new Twig_SimpleFilter('wp_nonce_field', 'wp_nonce_field'));
        $twig->addFilter(new Twig_SimpleFilter('wp_kses_post', 'wp_kses_post'));

        $twig->addFilter(new Twig_SimpleFilter('_treat', '_treat'));
        //date time
        $twig->addFilter(new Twig_SimpleFilter('date_i18n', 'date_i18n'));
        $twig->addFilter(new Twig_SimpleFilter('strtotime', 'strtotime'));
        //php
        $twig->addFunction(new Twig_SimpleFunction('_call', 'call_user_func'));
        $twig->addFunction(new Twig_SimpleFunction('_call_array', 'call_user_func_array'));
        //function
        $twig->addFunction(new Twig_SimpleFunction('_partial', array($this, 'load_partial_template')));

        return $twig;
    }

    /**
     * @param Twig_Environment $env
     * @param $context
     * @return string
     */
    public function apply_function_twig_filter(Twig_Environment $env,$context, $string) {
        // get the current charset for instance
        $charset = $env->getCharset();

        $args = func_get_args();
        $func = current( array_splice( $args, 2, 1 ) );
        return call_user_func_array($func, $args);
    }
    /**
     * filter timber context
     * @hook timber_context
     * @param $data context data
     */
    public function _timber_context($data) {
        $list_func = array(
             '_', '__', '_n','_e','_x',
            'esc_attr','esc_url', 'esc_html','esc_html__', 'esc_textarea','esc_attr_e','esc_attr_x','esc_url_raw',
            'urlencode','home_url','edit_post_link','get_option',
            'sanitize_html_class', '_setup_postdata',
            'hw_option', 'hw__post', 'hw__get','hw__req','_hw_global', 'template_tool','_treat'
        );
        foreach ($list_func as $func) {
            $data[$func] = TimberHelper::function_wrapper( $func );
        }
        //other functions
        #$data['_partial'] = TimberHelper::function_wrapper(array($this, 'load_partial_template')); //wrong
        //context object
        if(!empty(self::$context)) $data = array_merge($data, self::$context);
        return $data;
    }
    /**
     * call Timber::render
     * @param $file
     * @param $data
     */
    public static function _render($file, $data = array(), $current='') {
        //$trace = debug_backtrace(); //determine main template file
        //$last = end($trace);
        if(empty($current)) $current = self::$current_working_file;
        $data['_template_tool'] = template_tool($current );

        Timber::render($file, $data);
    }

    /**
     * @param $name
     * @return mixed
     */
    public  function load_partial_template($name) {
        $name = trim($name);
        if($name{0} == ':' && self::$current_working_file) {
            //return load_partial(self::$current_working_file.$name);
            $utility = HW_Twig_Template_Utilities::get_instance();
            return $utility->render_template(self::$current_working_file.$name);
        }
    }
}
endif;
_hw_global('hw_twig', HW_Timber::get_instance() );