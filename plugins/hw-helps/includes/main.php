<?php
/**
 * Interface iHW_HELP
 */
interface iHW_HELP {
    /**
     * @return mixed
     */
    public function help_callback();

    /**
     * @return mixed
     */
    public function register_admin_menu();
}
/**
 * Class HW_HELP: this class used to be extends
 */
class HW_HELP extends HW_Core implements iHW_HELP{
    /**
     * stored multi class instance
     * @var
     */
    private static $instances = array();
    /**
     * what help to show
     * @var
     */
    private $what_helps = array();

    /**
     * help path
     * @var null
     */
    public $help_path = null;

    /**
     * help url
     */
    public $help_url = null;

    /**
     * plugin help instance
     * @var null
     */
    public $plugin_help = null;

    /**
     * construct method
     */
    public function __construct(){
        //actions
        $this->settup_actions();
        //load class
        //HW_HOANGWEB::loa_class('HW_Validation');
        //init variables
        $this->what_helps = array();
    }

    /**
     * generate popup help url
     * @param $file
     * @return mixed
     */
    public static function get_help_popup_file($file) {
        //get help file path
        if(is_array($file) && count($file) >=2
            && isset($file[0]) && class_exists($file[0]) && is_string($file[1]) )
        {
            $class_help = $file[0];
            $reflector = new ReflectionClass($class_help);
            if($reflector->isSubclassOf('HW_HELP') ) {
                $file = $class_help::current()->help_static_file($file[1]); //get path of help file
            }

        }
        return $file;
    }
    /**
     * generate help link with popup content
     * @param $file path of file help not link
     * @param $text anchor text link
     * @param $title dialog title
     */
    public static function generate_help_popup($file, $text = '', $title = '') {
        //get help file path
        $file = self::get_help_popup_file($file);

        //valid
        if(!is_string($file) /*|| !file_exists($file)*/) return '<em>Không tìm thấy file help.</em>';
        if(empty($text)) $text = __('Hướng dẫn');   //valid
        if(empty($title)) $title = $text;

        $ajax_url = HW_HOANGWEB_AJAX;   //HW_HELP_AJAX
        $ajax_url .= '?action=hw_help_popup&include_head=1&include_footer=1&file=' .urlencode(HW_Encryptor::encrypt($file));
        if(is_multisite()) $ajax_url .= '&blog=' . get_current_blog_id();

        return sprintf('<a href="%s" class="hw-help-link-popup" title="%s">%s</a>',
            $ajax_url, $title, $text);
    }
    /**
     * set path of your helps
     * @param $name
     * @param $path
     */
    public static function set_helps_path($name, $path){
        self::create($name)->help_path = $path;
    }

    /**
     * set url of current help
     * @param $name
     * @param $url
     */
    public static function set_helps_url($name, $url) {
        self::create($name)->help_url = $url;
    }
    /**
     * create help for particular object
     * @param string $name
     * @return mixed
     */
    public static function create($name = ''){
        $name = strtolower($name);
        if(empty(self::$instances[$name])){
            #$main = get_called_class()? get_called_class() : self;
            //$main =  self;
            self::$instances[$name] = new self();   // self();
        }
        return self::$instances[$name];
    }

    /**
     * get plugin help
     * @param $name
     * @return mixed
     */
    public static function get($name){
        if(isset(self::$instances[strtolower($name)])) return self::$instances[strtolower($name)];
    }


    /**
     * return current help instance
     */
    public static function current() {
        $help_name = strtolower(str_replace('HW_HELP_','', get_called_class()));
        return self::get($help_name)->plugin_help;
    }
    /**
     * load module help
     * @param $module_help_class
     * @return HW_HELP extend class object
     */
    public static function load_module_help($module_help_class){
        $name = $module_help_class;
        $module_help_class = 'HW_HELP_'.strtoupper($name);
        if(class_exists($module_help_class)) {
            $help = new $module_help_class();
            $help->help_name = strtolower($name);
            $inst = self::get(strtolower($name));
            if(!empty($inst)) $inst->plugin_help = $help;    //save plugin help instance
            return $help;
        }
    }
    /**
     * init hooks
     */
    protected function settup_actions(){
        if(method_exists($this,'register_admin_menu')) {
            add_action( 'admin_menu', array( &$this, 'register_admin_menu' ), 9553 );
        }

    }

    /**
     * @return mixed|void
     */
    public function register_admin_menu() {

    }
    /**
     * add help content
     * @param $what: help id
     * @param $page_slug_hook: specific screen to show this help
     * @param $label: help tab label
     */
    public function add_help_content($page_slug_hook, $what = '', $label = 'Hướng dẫn'){
        if(!$what) $what = $this->help_name;

        $this->what_helps[$what] = array('id' => $what, 'title' => $label);
        add_action($page_slug_hook, array($this, 'help_tab'));
    }
    /**
     * generate object name
     * @param $name: name
     */
    public static function generate_callback_name ($name){
        if(class_exists('HW_Validation')) {
            return HW_Validation::valid_objname($name);
        }
        else {
            $delimiter = '_';
            return preg_replace('/[\s,.\[\]\/\\#\*@$%^\!~\-\+\=]+/',$delimiter,$name);
        }
    }
    /**
     * register module help
     * usage: HW_HELP::register_help('name1','name2',...)       //register more helps
     */
    public static function register_help(){
        $args = func_get_args();
        foreach($args as $class){
            //all help class of module should be lowercase
            if(class_exists('HW_HOANGWEB') && self::get($class)) {
                if(file_exists(self::get($class)->help_path)) {
                    //HW_HOANGWEB::register_class('HW_HELP_'.strtoupper($class),HW_HELP_PATH.'helps/'.strtolower($class).'.php');
                    HW_HOANGWEB::register_class('HW_HELP_'.strtoupper($class),rtrim(self::get($class)->help_path,'/').'/'.strtolower($class).'.php');

                }

            }
        }

    }

    /**
     * read help file
     * you can access both via static or public
     * @param $file
     */
    public function read_help_file($file){
        if(!file_exists($file)) return; //your request file not found

        $content = htmlspecialchars(file_get_contents($file), ENT_NOQUOTES);
        // $content is the result of htmlspecialchars()
        //note: don't use double quotes for attribute HTML
        $content = preg_replace('#&lt;(/?(?:pre|b|em|u|ul|li|ol|dl|dt|hr|code|h1|h2|h3|h4|h5|h6|p|abbr|blockquote|img.+?|strong|a.+?|a))&gt;#', '<\1>', ($content));

        //parse template variables
        $template_vars = array(
            '{HELP_URL}' => self::get($this->help_name)->help_url,
            '{HELP_VIEW_URL}' => self::get($this->help_name)->help_url.'/helps_view/',
        );
        foreach($template_vars as $var => $value){
            $content = str_replace($var, $value, $content);
        }

        //display
        $out = '<div class="hw-help-snippet">';
        $out .= implode("<br/>",explode("\n",$content));
        $out .= '</div>';
        return $out;
    }
    /**
     * return help static file for current module
     * @return string
     */
    public function help_static_file($file = '', $link = false){
        if(!isset($this->help_name) || !self::get($this->help_name)) return;
        if(empty($file)) $file = $this->help_name.'.html';

        if($link == false) {
            $HW_HELP_PATH = self::get($this->help_name)->help_path;
            $help_file = $HW_HELP_PATH.'/helps_view/'.$file;
            if(file_exists($help_file)) return $help_file;
        }
        else {
            $HW_HELP_URL = self::get($this->help_name)->help_url;
            return $HW_HELP_URL.'/helps_view/'.$file;
        }

        #if(file_exists($help_file)) return $help_file;
    }

    /**
     * return help file with link
     * @param string $file
     */
    public function help_static_link($file = '') {
        return $this->help_static_file($file, true);
    }
    /**
     * return page slug for hook
     * @param $page
     * @param $hook: hook name (default: load-)
     * @return string
     */
    public  static function load_admin_page_hook_slug($page, $hook = 'load-'){
        return $hook.'toplevel_page_'.$page;
    }

    /**
     * load current page now slug with hook
     * @param $hook: default hook 'load-'
     * @return string
     */
    public static function load_current_page_hook_slug($hook = 'load-'){
        global $pagenow;
        return $hook.$pagenow;
    }
    /**
     * core page hook by slug
     * @param $page
     * @param $hook: wordpress hook
     * @return string
     */
    public static function load_core_page_hook_slug($page,$hook = 'load-'){
        $cores_hook = array('post','post-new','widgets','themes','nav-menus','theme-editor','plugins','users','tools','options-general');
        if(in_array($page, $cores_hook)){
            return $hook.$page.'.php';  //ex: load-post.php, load-post-new.php
        }

    }

    /**
     *
     * @param $page: setting page, which submenu under options-general.php or options.php
     * @param $hook: wordpress hook , ie: load-
     * @param string $hook
     */
    public static function load_settings_page_hook_slug($page,$hook = 'load-'){
        return $hook.'settings_page_'.$page;
    }
    /**
     * require from interface
     * default functions
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file());
        }
    }
    /**
     * Add the help tab to the screen.
     */
    public function help_tab(){
        $screen = get_current_screen();

        foreach($this->what_helps as $help_id => $tab) {
            $callback_name = $this->generate_callback_name($help_id);
            if(method_exists($this, 'help_'.$callback_name.'_callback')) {
                $callback = array($this, 'help_'.$callback_name.'_callback');
            }
            else {
                $callback = array($this, 'help_callback');
            }
            // documentation tab
            $screen->add_help_tab( array(
                    'id'    => 'hw-help-'.$help_id,
                    'title' => __( $tab['title'] ),
                    //'content'   => "<p>sdfsgdgdfghfhfgh</p>",
                    'callback' => $callback
                )
            );
        }
    }
    /**
     * get help instance by name
     * this make access help directy from instance like this: HW_HELP::$help_name
     */
    public function __get($name) {
        return isset(self::$instances[$name])? self::$instances[$name] : '';
    }

}

/**
 * Class HW_HELP_MODULE
 */
class HW_HELP_MODULE extends HW_HELP {
    function __construct(){
        parent::__construct();  //load parent instance
    }
    /**
     * help callback
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file());
        }
    }
    /**
     * this call by parent class
     */
    public function register_admin_menu(){
        $all_modules = HW_TGM_Module_Activation::get_register_modules();
        $module = $this->_option('module');
        $name = $module->module_name;
        $label = isset($all_modules[$name])? $all_modules[$name]['name'] : $name;

        $this->add_help_content(self::load_settings_page_hook_slug(HW_Module_Settings_page::PAGE_SLUG) ,$name , $label);
    }
    public static function __init(){

    }
}