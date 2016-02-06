<?php
# /root>

/**
 * Class HW_Core
 */
abstract class HW_Core {
    /**
     * singleton instance
     * @var
     */
    public static $instance;
    /**
     * class options
     * @var array
     */
    protected $__options = array();
    /**
     * static options
     * @var array
     */
    protected static $__static_options=array();

    /**
     * return first of all instance of the class
     */
    public static function get_instance($param = null) {
        $class = get_called_class();
        if(! $class::$instance) $class::$instance = new $class($param);
        return $class::$instance;
    }

    /**
     * @param $class
     * @param $filter ReflectionMethod::IS_STATIC,..
     * @return array
     */
    public static function get_child_class_methods($class, $filter='') {
        $f = new ReflectionClass($class);
        $methods = array();
        foreach ($filter? $f->getMethods($filter) : $f->getMethods()  as $m) {
            if (strtoupper($m->class) == strtoupper($class)) {
                $methods[] = $m->name;
            }
        }
        return $methods;
    }
    /**
     * @param $file
     * @return mixed
     */
    public static function get_file_name($file) {
        $arr = explode('\\', $file);
        $file= array_pop($arr);
        $arr=explode('/', $file);
        return array_pop($arr);
    }
    /**
     * add/get option
     * @param $name
     * @param $value
     * @param $merge_array
     */
    public function _option($name, $value = '', $merge_array = false) {
        if(is_string($name) && ($value!=='' && $value!==null)){
            $data = &$this->__options;
            if( $merge_array) {
                if(!isset($data[$name]) ) $data[$name] = array();
                if(! is_array($data[$name])) $data[$name] = (array)$data[$name];
                if(!is_array($value) ) $value = (array) $value;

                $data[$name] = array_merge($data[$name],$value );
            }
            else $data[$name] = $value;
        }

        return isset($this->__options[$name])? $this->__options[$name] : '';
    }
    /**
     * get an option
     * @param $name
     * @param string $default
     * @return string
     */
    public function _get_option($name, $default = '') {
        $value = $this->_option($name);
        return $value !==''? $value: $default;
    }
    /**
     * set/get static option
     * @param $name
     * @param string $value
     * @param $merge_array
     * @return string
     */
    public static function _static_option($name, $value = '', $merge_array = false) {
        $class = get_called_class() ;
        if(!isset(self::$__static_options[$class])) self::$__static_options[$class] = array();
        $data = &self::$__static_options[$class];

        if(is_string($name) && ($value!=='' && $value!==null)){
            if( $merge_array) {
                if(!isset($data[$name]) ) $data[$name] = array();
                if(! is_array($data[$name])) $data[$name] = (array)$data[$name];
                if(!is_array($value) ) $value = (array) $value;

                $data[$name] = array_merge($data[$name],$value);
            }
            else $data[$name] = $value;
            self::$__static_options[$class] = $data;
        }
        //HW_SESSION::__save_session('abcxx',$data,1);
        return isset($data[$name])? $data[$name] : '';
    }

    /**
     * get static option
     * @param $name
     * @param string $default
     * @return string
     */
    public static function _get_static_option($name, $default = '') {
        $value = self::_static_option($name);
        return $value !==''? $value: $default;
    }
    /**
     * cast object to other type
     * @param $newClass
     * @return mixed
     */
    public function castAs($newClass) {
        if(is_string($newClass) && class_exists($newClass)) {
            $obj = new $newClass;
        }
        elseif(is_object($newClass)) $obj =  $newClass;

        foreach (get_object_vars($this) as $key => $name) {
            $obj->$key = $name;
        }
        return $obj;
    }

    /**
     * add new or update wp option
     * @param $name
     * @param $value
     * @param $merge_array
     */
    public static function add_wp_option($name, $value, $merge_array = false) {
        $class = get_called_class();
        $data = get_option($class);
        if(!$data) {
            $data = array();
            add_option($class, $data);
        }
        if( $merge_array) {
            if(!isset($data[$name]) ) $data[$name] = array();
            if(! is_array($data[$name])) $data[$name] = (array)$data[$name];
            if(!is_array($value) ) $value = (array) $value;

            $data[$name] = array_merge($data[$name],$value);
        }
        else $data[$name] = $value;

        update_option($class, $data);
    }

    /**
     * delete wp option
     * @param $name
     */
    public static function del_wp_option($name) {
        $class = get_called_class();
        $data = get_option($class);
        if($data && isset($data[$name])) {
            unset($data[$name]);
            update_option($class, $data);
        }
    }

    /**
     * get wp option
     * @param $name
     * @param $default default value
     * @return mixed
     */
    public static function get_wp_option($name, $default= '') {
        $class = get_called_class();
        $data = get_option($class);
        if($data && isset($data[$name])) {
            return ($data[$name]);
        }
        return $default;
    }
}

/**
 * Class HW_NHP_Field
 */
class HW_NHP_Field {
    /**
     * get current skin file
     * @param $data
     */
    public static function get_skin_link($data) {
        if(isset($data['hwskin_link_source'])) $source = $data['hwskin_link_source'];
        else $source = 'plugin';    //default source
        //parse file url
        if(!empty($data['hwskin_link_file_url'])) {
            $file_url = @unserialize(base64_decode($data['hwskin_link_file_url']));
            $file_url = HW_SKIN::valid_skin_path(array('file_url' => $file_url, 'source' => $source));
            $file_url = $file_url['file_url'];
        }
        else $file_url = '';
        //default template file
        if(!empty($data['hwskin_link_default_skin_file'])) {
            $file = @unserialize(base64_decode($data['hwskin_link_default_skin_file']));
            $file = HW_SKIN::valid_skin_path(array('file_path' => $file, 'source' => $source));
            $file = $file['file_path'];
        }
        else $file= '';

        /*$theme = array();   //init theme config
        $theme['styles']= array();
        $theme['scripts']= array();
*/
        return array('template' => $file, 'file_url' => $file_url);
        /*if(file_exists($file)){
            include($file);
        }*/
    }
}
/**
 * Interface HW_APF_Field_Interface
 */
interface HW_APF_Field_Interface {
    /**
     * register single field
     * @return mixed
     */
    public function get_field_definition();     //
    /**
     * register more fields
     * @return mixed
     */
    public function get_fields_definition();    //
    /**
     * register field
     * @param AdminPageFramework $apf
     * @return mixed
     */
    public static function register_field(AdminPageFramework &$apf);

    /**
     * reply to form elements callback
     * @return mixed
     */
    public static function replyToAddFormElements($oAdminPage) ;
    public static function init($slug, $tab, $setting);
}

/**
 * Class HW_APF_Field
 */
abstract class HW_APF_Field extends HW_Core implements HW_APF_Field_Interface{
    /**
     * setting page tab
     * @var null
     */
    public static $setting = null;
    public static $setting_tab = null;

    /**
     * register APF field
     * @param AdminPageFramework $apf
     */
    static public function register_field(AdminPageFramework &$apf, $enabled_submit= false) {
        $class = get_called_class();    //refer to child class that extend this class
        $fields = array();      //fields setting
        $fields[] = $class::get_instance()->get_field_definition();
        //add more fields
        if(method_exists($class::get_instance(), 'get_fields_definition')) {
            $_fields = $class::get_instance()->get_fields_definition();
            if(is_array($_fields)) $fields = array_merge($fields, $_fields);
        }
        //register field
        foreach($fields as $field) {
            $apf->addSettingField($field);
        }
        if($enabled_submit) {
            //add submit button
            $apf->addSettingField(array(
                'field_id' => 'submit',
                'type' => 'submit',
                'label' => 'Lưu lại',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            ));
        }
    }

    /**
     * return skin file url for skin link type
     * @param $hash_skin
     * @return mixed
     */
    public static function get_skin_link($value) {
        //if(!empty($value['url'])) $url = $value['url'];
        if(!empty($value['hash_skin'])) {
            return HW_SKIN::get_skin_link_url($value['hash_skin']);
        }
    }
    /**
     * define more fields
     */
    public function get_fields_definition() {
        return null;
    }

    /**
     * @return mixed|void
     */
    public static function replyToAddFormElements($oAdminPage) {

    }

    /**
     * init
     */
    public static function init($slug, $tab, $setting) {

    }

    /**
     * magic method to prevent error, purpose of calling from hw skin file
     * @param $name
     * @param $params
     */
    public function __call($name, $params){

    }
}

/**
 * Class HW_APF_FieldTypes
 */
class HW_APF_FieldTypes extends HW_Core{
    /**
     * field types definition
     * @var array
     */
    private static $field_types = array();

    /**
     * @return array
     */
    public static function get_apf_field_types () {
        $types = array(
            'hw_skin' => 'APF_hw_skin_Selector_hwskin',
            'hw_ckeditor' => 'APF_hw_ckeditor_field',

            'hw_table_fields' => 'APF_hw_table_fields',
            'hw_more_fields' => 'APF_hw_table_fields',

            'hw_condition_rules' => 'APF_hw_condition_rules',
            'hw_rules' => 'APF_hw_condition_rules',

            'hw_admin_table' => 'APF_hw_admin_table',
            'hw_admin_tables' => 'APF_hw_admin_table',

            'hw_help' => 'APF_hw_help_fields',
            'hw_html' => 'APF_hw_html_field',
            'hw_upload' => 'APF_hw_upload_field'
        );
        return $types;
    }

    /**
     * load more field types
     */
    public static function init_fieldtypes () {
        self::$field_types['APF_hw_condition_rules'] = HW_HOANGWEB_INCLUDES.'/library/field-type/apf-rules-field/apf-rules-field.php';
        self::$field_types['APF_hw_table_fields'] = array(
            'release' =>HW_HOANGWEB_INCLUDES.'/library/field-type/apf-table-fields.php',
            'class' => 'APF_hw_table_fields',
            'deps' => 'HW_APF_FormField'
        );
        self::$field_types['APF_hw_admin_table'] = array(
            'release' => HW_HOANGWEB_INCLUDES. '/library/field-type/apf-admin-table/apf-admin-table.php',
            'class' => 'APF_hw_admin_table',
            'deps' => 'HW_APF_FormField'
        );
        self::$field_types['APF_hw_ckeditor_field'] = array(
            'release'=> HW_HOANGWEB_INCLUDES. '/library/field-type/apf-editor-field/apf-ckeditor-field.php',
            'class' => 'APF_hw_ckeditor_field',
            'deps' => 'HW_APF_FormField'
        );
        self::$field_types['APF_hw_help_fields'] = array(
            'release'=> HW_HOANGWEB_INCLUDES. '/library/field-type/apf-help-field/apf-help-field.php',
            'class' => 'APF_hw_help_fields',
            'deps' => array('HW_APF_FormField')
        );
        self::$field_types['APF_hw_html_field'] = array(
            'release' => HW_HOANGWEB_INCLUDES. '/library/field-type/apf-html-field.php',
            'class' => 'APF_hw_html_field',
            'deps' => 'HW_APF_FormField'
        );
        self::$field_types['APF_hw_upload_field'] = array(
            'release'=> HW_HOANGWEB_INCLUDES. '/library/field-type/apf-upload-field/apf-upload-field.php',
            'class' => 'APF_hw_upload_field',
            'deps' => 'HW_APF_FormField'
        );
    }


    /**
     * load given field type
     * @param string $typename: field type class
     * @return field class name
     */
    public static function load_fieldtype ($typename = '') {
        if(count(self::$field_types) == 0) self::init_fieldtypes(); //load all register fields type
        //list all fields type
        $types = HW_APF_FieldTypes::get_apf_field_types();

        if(isset($types[$typename])) {  //get field class name from field type
            $typename = $types[$typename];
        }

        if(isset(self::$field_types[$typename]) /*&& file_exists(self::$field_types[$typename] )*/) {
            HW_HOANGWEB::register_class($typename, self::$field_types[$typename]);
            HW_HOANGWEB::load_class($typename);
        }
        return $typename;
    }

    /**
     * apply field type to apf page
     * @param $fields
     * @param $apf_page
     */
    public static function apply_fieldtypes($fields, $apf_page) {
        $types = self::get_apf_field_types();   //get all apf types
        //$module_class = $this->get_module_name();   //module name

        foreach((array) $fields as $type) {
            if(isset($types[$type])  /*&& class_exists($types[$type])*/) {
                $field_class = $types[$type] ;
                if(!class_exists($field_class)) {  //load new APF field type
                    self::load_fieldtype($field_class);
                }
                if(class_exists($field_class) && class_exists($apf_page)) {
                    new $field_class($apf_page);
                }
            }
        }
    }
}


/**
 * Class HW_HOANGWEB
 * go admin
 */
class HW_HOANGWEB extends HW_Core {
    /**
     * defined more classes
     * @var array
     */
    private static $classes_libs = array();

    /**
     * global data
     * @var array
     */
    public static $hw_global = array();
    /**
     * classes alias
     * @var array
     */
    protected static $hw_classes_alias = array();

    /**
     * javascript libraries
     * @var array
     */
    protected static  $jquery_libs = array();

    /**
     * serialize objects
     * @var array
     */
    private $serialize_obj_admin = array();
    private $serialize_obj = array();

    /**
     * constructor
     */
    public function __construct(){

        $this->setup_actions(); //prepare actions

        //register system classes automatically
        /*self::register_class('AdminPageFramework', array(
            'debug' => HW_HOANGWEB_PATH.'/lib/admin-page-framework/development/admin-page-framework.php',
            'release' =>  HW_HOANGWEB_PATH.'/lib/admin-page-framework/admin-page-framework.min.php',
            'class' => 'AdminPageFramework'
        ));*/

        //load apf field type
        self::load_class('HW_APF_FormField');
        //curl
        self::load_class('HW_URL' );   //curl
        self::load_class('HW_CURL' );   //curl
        self::load_class('HW_XML' );   //xml
        self::load_class('HW_SESSION' );   //php session
        self::load_class('HW_Logger' );   //logging system
        self::load_class('HW_File_Directory' );   //logging system
        self::load_class('HW_XMLRPC_Server' );   //xmlrpc api

        //notices class
        HW_HOANGWEB::load_class('HW_WP_NOTICES' );  //admin notice
        //auto load Twig template engine
        //HW_HOANGWEB::register_class('Twig_Loader_Filesystem', HW_HOANGWEB_PATH.'/lib/vendor/autoload.php');
        //validation
        HW_HOANGWEB::load_class('HW_Validation' );  //validation
        HW_HOANGWEB::load_class('HW_CLI_Command');  //wpcli command class
        if(!is_admin()) self::load_class('HW_Twig_Template');   //twig template

        //get more field types
        //self::init_fieldtypes();

        //registers jquery libs stuff
        HW_Libraries::registers_jquery_libs();

    }


    /**
     * setup actions
     */
    private function setup_actions()
    {
        add_action('init', 'session_start');    //start session
        add_action('init', array(&$this, '_init_hook'));
        // actions
        add_action( 'plugins_loaded', array( &$this, '_load_textdomain' ) );
        add_action( 'wp_loaded', array( &$this, '_load_pluggable_functions' ), 10 );
        //enqueue head
        add_action( 'wp_enqueue_scripts',  array( &$this, '_wp_enqueue_scripts') );
        //admin styles & scripts
        add_action( 'admin_enqueue_scripts',  array( &$this, '_admin_enqueue_scripts') );

        #add_action('admin_footer', array($this, '_hw_footer'));  //put something on admin footer , also used for wp_footer hook
        #add_action('wp_footer', array($this, '_hw_footer'));     //put stuffs at bottom of website

        //preload plugins
        add_action('hw_hoangweb_loaded', array(&$this, '_load_plugins'));

        //load module hooks
        add_action( 'widgets_init', array(&$this,'_load_module_widgets' ));
        add_action( 'plugins_loaded', array(&$this,'_module_plugins_loaded' ));
        #add_action( 'activated_plugin', array(&$this,'_module_activated_plugin' ),10,2);
        #add_action( 'deactivated_plugin', '_module_detect_plugin_deactivation', 10, 2 );
        #add_action('init', array($this, '_init'));

        //main ajax process
        add_action('wp_ajax_hoangweb', array(&$this, '_ajax_hoangweb'));
        add_action('wp_ajax_nopriv_hoangweb', array(&$this, '_ajax_hoangweb')); //both on frontend
    }

    /**
     * @hook init
     */
    public function _init() {
        #self::load_all_modules();  //note: done in plugins_loaded hook
        do_action('hw_wp__init');
    }

    /**
     * init widgets
     * @hook widgets_init
     */
    public function _load_module_widgets() {
        #self::load_all_modules();  //note: done in plugins_loaded hook
        do_action( 'hw_widgets_init' );
    }

    /**
     * @hook plugins_loaded
     */
    public function _module_plugins_loaded() {
        self::load_all_modules();
        do_action('hw_plugins_loaded'); //do plugins_loaded hook

    }

    /**
     * @param $plugin
     * @param $network_activation
     */
    /*function _module_detect_plugin_deactivation(  $plugin, $network_activation ) {
        self::load_all_modules();
        do_action('deactivated_plugin'); //do deactivated_plugin hook
    }*/
    /**
     * load addition plugins (modules)
     * @hook hw_hoangweb_loaded
     */
    public function _load_plugins() {
        //note: loaded in _load_module_widgets hook
        #self::load_all_modules();  //note: done in plugins_loaded hook
        do_action('hw_modules_load');
        do_action('hw_trigger_modules_settings');
        do_action('hw_module_register_config_page');    //init modules metabox
        do_action('hw_modules_loaded'); //when all modules loaded
    }
    /**
     * init hook
     * @hook init
     */
    public function _init_hook(){
        //Increase the memory limit
        if(!defined('WP_MEMORY_LIMIT')) define('WP_MEMORY_LIMIT', '64MB');
        ini_set('memory_limit', '3G');

        do_action('hw_hoangweb_loaded');    //load after all masterial loaded in this plugin

        //desploy footer skin
        NHP_Options_footer::do_footer_skin();
    }
    /**
     * Load pluggable template functions
     */
    public function _load_pluggable_functions() {
        //include_once(HW_HOANGWEB_PATH . 'includes/functions.php');
        #do_action('hw_hoangweb_loaded');
    }

    /**
     * register module activation hook
     * @param $module_file
     * @param $callback
     * @return callback for module activation
     */
    public static function register_activation_hook($module_file, $callback='') {
        if(is_file($module_file) && !is_dir($module_file)) {
            $module_path = realpath(dirname($module_file));
        }
        else $module_path = $module_file;
        if(!isset(self::$hw_global['activation_hooks'])) self::$hw_global['activation_hooks'] = array();

        #if($callback=='') {_print(self::$hw_global['activation_hooks']);exit();};
        if(!isset(self::$hw_global['activation_hooks'][$module_path])) {
            self::$hw_global['activation_hooks'][$module_path] = array();
        }
        if( is_callable($callback)) {
            self::$hw_global['activation_hooks'][$module_path][] = $callback;
        }
        if(!empty(self::$hw_global['activation_hooks'][$module_path])) {
            return self::$hw_global['activation_hooks'][$module_path] ;
        }
    }
    /**
     * register module deactivation hook
     * @param $module_file
     * @param $callback
     * @return callback for module activation
     */
    public static function register_deactivation_hook($module_file, $callback='') {
        $module_path = realpath(dirname($module_file));
        if(!isset(self::$hw_global['deactivation_hooks'])) self::$hw_global['deactivation_hooks'] = array();
        if(!isset(self::$hw_global['deactivation_hooks'][$module_path])) {
            self::$hw_global['deactivation_hooks'][$module_path] = array();
        }
        if( is_callable($callback)) {
            self::$hw_global['deactivation_hooks'][$module_path][] = $callback;
        }
        if(!empty(self::$hw_global['deactivation_hooks'][$module_path])) {
            return self::$hw_global['deactivation_hooks'][$module_path] ;
        }
    }
    /**
     * register class
     * @param $classname  class name
     * @param $lib  libs path
     * @param $alias set class name alias
     * @param $group manage classes into group
     * @access public static
     */
    public static function register_class($classname, $lib, $alias = '', $group = ''){
        if(!isset(self::$hw_global['classes'])) self::$hw_global['classes'] = array();
        if(!isset(self::$hw_global['classes'][$classname]) ) {
            if(is_string($lib)) {
                $lib = array(
                    'class' => $classname,
                    'release' => $lib
                );
            }
            if(is_array($lib) && $group) $lib['group'] = $group;    //class group
            if(is_array($lib) && $alias) $lib['alias'] = $alias;    //class alias

            self::$hw_global['classes'][$classname] = $lib;
        }
        //save class alias
        if($alias) self::$hw_classes_alias[$alias] = $classname;
        #if($classname=='HW_Gmap') {echo '<textarea>';print_r(self::$hw_global['classes']['HW_Gmap']);echo '</textarea>';}
        //init class
        if(class_exists($classname)  //use __autoload, dont' use class_exists($classname, false). some hosting require to check class exists because  method_exists assume it's already exists
            && method_exists($classname,'__init') && is_callable(array($classname,'__init'))){
            call_user_func(array($classname,'__init'));
        }
        return true;
    }

    /**
     * get class info
     * @param $classname
     * @return mixed
     */
    public static function get_class($classname) {
        if(isset(self::$hw_global['classes'][$classname]) ) return self::$hw_global['classes'][$classname];
    }
    /**
     * return path to file of class
     * @param $classname
     * @return string
     */
    public static function get_class_path($classname) {
        if(isset(self::$hw_global['classes'][$classname]) ) {
            if(is_array(self::$hw_global['classes'][$classname])) {
                if(isset(self::$hw_global['classes'][$classname]['release']) ) {
                    $location_file = (self::$hw_global['classes'][$classname]['release']);
                }
                elseif(isset(self::$hw_global['classes'][$classname]['debug']) ) {
                    $location_file = (self::$hw_global['classes'][$classname]['debug']);
                }
            }
            if(is_string(self::$hw_global['classes'][$classname])) {
                $location_file = self::$hw_global['classes'][$classname];
            }
            if(isset($location_file) && file_exists($location_file)) return dirname($location_file);
        }
    }

    /**
     * get all classes by group that register from method 'register_class'
     * @param $group
     * @return array
     */
    public static function get_classes_by_group($group) {
        $data = array();
        if(isset(self::$hw_global['classes']) ) {

            foreach(self::$hw_global['classes'] as $classname => $lib) {
                if($lib && isset($lib['group']) && $lib['group'] == $group) {
                    $data[] = $lib;
                }
            }
        }
        return $data;
    }
    /**
     * get class by alias register with register_class
     * @param string $alias
     * @return mixed
     */
    public static function get_class_by_alias($alias = '') {
        #if(isset(self::$hw_classes_alias[$alias]) ) return self::$hw_classes_alias[$alias];
        //other way (recommend)
        if(isset(self::$hw_global['classes']) ) {

            foreach(self::$hw_global['classes'] as $classname => $lib) {
                if($lib && isset($lib['alias']) && $lib['alias'] == $alias) {
                    return $lib['class'];
                }
            }
        }
    }

    /**
     * register ajax
     * @param $handle
     * @param $callback
     * @param $allow_fontend
     */
    public static function register_ajax($handle, $callback = null, $allow_fontend= false) {
        if(!isset(self::$hw_global['ajax'])) self::$hw_global['ajax'] = array();
        if(is_string($handle) && is_callable($callback) && !isset(self::$hw_global['ajax'][$handle])) {
            self::$hw_global['ajax'][$handle] = array('callback' => $callback, 'frontend' => $allow_fontend);
        }
        if(!empty(self::$hw_global['ajax'][$handle])) return self::$hw_global['ajax'][$handle];
    }
    public static function register_hooks() {

    }
    /**
     * return ajax handle
     */
    public static function get_active_ajax_handle() {
        $handle = hw__req('ajax_name');
        return self::register_ajax($handle);
    }

    /**
     * @param $handle
     */
    public static function get_ajax_url($handle='') {
        $url = admin_url('admin-ajax.php?action=hoangweb&nonce='. wp_create_nonce('hoangweb_nonce'));
        if($handle) $url .= '&ajax_name='. $handle;
        return $url;
    }
    /**
     * call current ajax handle
     * @param $args arguments pass to ajax handle function
     */
    public static function do_active_ajax($args = null) {
        $handle = self::get_active_ajax_handle();
        if(!empty($handle) && is_callable($handle['callback'])) {
            if(($handle['frontend']== true && !is_admin()) ||  is_admin())
                call_user_func($handle['callback'], $args );
        }
    }
    /**
     * prepare classes
     * @param $class: return path of class file
     */
    public static function setup_classes($class = '') {
        //more classes
        if(!self::$classes_libs) self::$classes_libs = array();
        if(empty(self::$classes_libs)) {
            $default_classes = array(
                //frameworks
                'AdminPageFramework' => array(
                    'debug' => HW_HOANGWEB_PATH.'/lib/admin-page-framework/development/admin-page-framework.php',
                    'release' =>  HW_HOANGWEB_PATH.'/lib/admin-page-framework/admin-page-framework.min.php',
                    'class' => 'AdminPageFramework'
                ),
                'redrokk_metabox_class' => HW_HOANGWEB_PATH .'/classes/class-redrokk-metabox-class.php',
                'redrokk_post_class' => HW_HOANGWEB_PATH .'/classes/class-redrokk-post-class.php',
                //APF field type
                'HW_APF_FormField' => array(
                    'release' => HW_HOANGWEB_PATH. '/classes/class-hw-apf-field-type.php',
                    'class' => 'HW_APF_FormField',
                    'deps' => 'AdminPageFramework'
                ),
                //array
                'hwArray' => HW_HOANGWEB_PATH . '/classes/array/class-core-array.php',
                'HW_Ajax' => array(
                    'release' => HW_HOANGWEB_PATH . '/classes/array/class-hw-ajax.php',
                    'class' => 'HW_Ajax',
                    'deps' => 'hwArray'
                ),
                'HW_SESSION' => array(
                    'release' => HW_HOANGWEB_PATH . '/classes/class-core-session.php',
                    'class' => 'HW_SESSION',
                    'deps' => array('hwArray')
                ),
                'HW_Logger' => array(
                    'release' => HW_HOANGWEB_PATH . '/classes/class-hw-logger.php',
                    'class' => 'HW_Logger'
                ),
                'HW_XML' => HW_HOANGWEB_PATH . '/classes/class-core-XML.php',  //xml

                //cli
                'HW_Shell' => HW_HOANGWEB_PATH . '/classes/class-hw-shell.php',
                //api
                'HW_XMLRPC_Server' => HW_HOANGWEB_PATH . '/classes/class-hw-api.php',
                //directory
                'HW_File_Directory' => HW_HOANGWEB_PATH . '/classes/class-core-directories.php',
                //template engine
                'Twig_Autoloader' => HW_HOANGWEB_PATH.'/lib/vendor/autoload.php',
                'HW_Twig_Template' => HW_HOANGWEB_PATH . '/classes/class-template-twig.php',

                //plugins
                #'HW_mqtranslate' => HW_HOANGWEB_PATH . '/classes/plugins/class-hw_mqtranslate.php',
                'HW_ACF_API' => HW_HOANGWEB_PATH. '/classes/plugins/class-hw_acf_api.php',

                'HW_ButtonToggle_widget' => HW_HOANGWEB_PATH . '/classes/class-ui-button_toggle_widget.php',

                //wordpress
                'HW_WP' => HW_HOANGWEB_PATH.'/classes/class-core.php',
                'HW_WP_NOTICES' => HW_HOANGWEB_PATH.'/classes/admin/class-ui-notices.php',
                'HW_POST' => HW_HOANGWEB_PATH . '/classes/class-core-posts.php',
                'HW_URL' => HW_HOANGWEB_PATH . '/classes/class-URL.php',
                'HW_Screen_Option' => HW_HOANGWEB_PATH . '/classes/admin/class-core-screen-option.php',
                'HW_WP_Attachment' => HW_HOANGWEB_PATH . '/classes/class-wp-attachment.php',

                //php
                'HW_CURL' => HW_HOANGWEB_PATH . '/classes/class-core-CURL.php' ,
                'HW_Encryptor' => HW_HOANGWEB_PATH . '/classes/class-core-EnDecrypt.php',
                'HW_String' => HW_HOANGWEB_PATH. '/classes/class-core-string.php',
                'CreateZipFile' => HW_HOANGWEB_PATH . '/classes/class-core-CreateZipFile_helper.php',
                'JSLikeHTMLElement' => HW_HOANGWEB_PATH . '/classes/class-core-JSLikeHTMLElement.php',
                'HW_Validation' => HW_HOANGWEB_PATH . '/classes/class-core-validation.php',

                //ui
                'HW_List_Table' => HW_HOANGWEB_PATH . '/classes/admin/class-ui-admin-table.php',
                'HW_UI_Component' => HW_HOANGWEB_PATH . '/classes/class-ui.php',     //HTML components
                'HW_UI_Menus' => HW_HOANGWEB_PATH . '/classes/class-ui-menu.php',     //HTML menu generator
                //moved to module
                #'HW_Tabs' => HW_HOANGWEB_PATH. '/classes/component-ui/class-ui-tabs.php', //tabs
                #'HW_Cloudzoom' => HW_HOANGWEB_PATH. '/classes/component-ui/class-ui-cloudzoom.php'
                //features


            );
            self::$classes_libs = array_merge( self::$classes_libs, $default_classes);
        }

        if($class  ) {
            if(isset(self::$classes_libs[$class])) return self::$classes_libs[$class];
        }
        else return self::$classes_libs;
    }

    /**
     * load all modules for first
     */
    public static function load_all_modules() {
        static $trigger = false;
        if(!$trigger) {
            $trigger = true;    //run once time
            $modules = hw_get_modules();
            foreach ($modules as $module) {
                HW_HOANGWEB::load_module($module[0]);
                usleep(500);
            }
        }
    }

    /**
     * load module
     * @param $plugin module slug
     * @return module activation hook
     */
    public static function load_module($plugin) {
        if(file_exists(HW_HOANGWEB_PLUGINS. '/'.$plugin. '/index.php')) {
            include_once(HW_HOANGWEB_PLUGINS. '/'.$plugin. '/index.php');
        }
        elseif(file_exists(HW_HOANGWEB_PLUGINS. '/'.$plugin. '/'. $plugin .'.php')) {
            include_once(HW_HOANGWEB_PLUGINS. '/'.$plugin. '/'. $plugin .'.php');
        }
    }
    /**
     * load core class libs
     * @param $class: class name to load
     */
    public static function load_class($class) {
        //setup classes libs
        self::setup_classes();
        $_class = self::setup_classes($class);
        if(is_string($class) && $_class ) {
            self::register_class($class, $_class);
            //special class must to include (why? do not nessessary)
            /*if($class === 'Twig_Autoloader' ) {
                if(!class_exists($class,false) ) include_once (self::setup_classes($class));    //for composer lib
            }
            else{
                self::register_class($class, self::setup_classes($class));
            }*/

            hw_load_class($class);   //fix if __autoload not recognize the class
        }
        if(is_array($class) ) {
            foreach ($class as $c) self::load_class($c);
        }
    }

    /**
     * load given field type
     * @param string $typename: field type class
     * @return field class name
     */
    public static function load_fieldtype ($typename = '') {
        return HW_APF_FieldTypes::load_fieldtype($typename);
    }
    /**
     * check whether user visiting this page
     * @param $page: check page name
     * @param $post_type: check current post type
     * @return bool
     */
    static function is_current_screen($page,$post_type = ''){

        //$pages = (array)$page;
        if(is_array($page)){
            $condition = false;
            foreach($page as $p){
                $condition = self::is_current_screen($p,$post_type);
                if($condition == true) {
                    break;
                }
            }
            return $condition;
        }
        else{
            if(function_exists('get_current_screen') && (get_current_screen()) ) {
                $screen = get_current_screen();

                //only current this page
                $first = $screen->base === $page || (preg_replace("#{$page}$#",'',trim($screen->base))!== $screen->base)/*strpos($screen->base,$page)*/
                    || $screen->parent_base === $page
                    || $screen->id === $page|| /*strpos($screen->id , $page)*/(preg_replace("#{$page}$#",'',trim($screen->id))!== $screen->id);
                $second = ($post_type? $screen->post_type === $post_type : 1);
                return ($first && $second);
            }
            elseif(isset($_GET['page'])) {
                return $_GET['page'] === $page || /*strpos($_GET['page'],$page)*/preg_replace("#{$page}$#",'',trim($_GET['page']) )!== $_GET['page'];
            }
        }
    }


    /**
     * Load text domain
     */
    public function _load_textdomain() {
        load_plugin_textdomain( 'hoangweb', false, HW_HOANGWEB_REL_PATH . 'languages/' );
    }

    /**
     * admin enqueue js+css
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts(){
        $this->serialize_obj_admin['wp_admin'] = admin_url();   //admin url
        $this->serialize_obj_admin['loading_image'] = admin_url('/images/wpspin_light.gif');        //loading animation
        //common ajax session
        $this->serialize_obj_admin['main_ajax_url'] = self::get_ajax_url();

        if(self::is_current_screen('options-general') || self::is_current_screen('widgets')){
            //ajax handle
            $this->serialize_obj_admin['upload_lang_ajax'] = admin_url('admin-ajax.php?action=hw_upload_polang_files&nonce='.wp_create_nonce("hw_upload_polang_files_nonce"));
            $this->serialize_obj_admin['serve_static_content_subdomain_ajax'] = admin_url('admin-ajax.php?action=hw_install_static_url_subdomain&nonce='.wp_create_nonce("hw_install_static_url_subdomain_nonce"));

            wp_enqueue_script('hw_admin_js',plugins_url('js/admin-js.js',dirname(__FILE__)),array('jquery'));
            wp_localize_script('hw_admin_js','__hw_localize_object',$this->serialize_obj_admin);
            wp_enqueue_style('hw-general-setting-style',plugins_url('css/hw-general-option.css',dirname(__FILE__)));

        }
        wp_enqueue_style('hw-style',plugins_url('css/style.css',dirname(__FILE__)));
        wp_enqueue_script('hw_scripts',plugins_url('js/hw-scripts.js',dirname(__FILE__)),array('jquery'));
        //jquery plugin
        wp_enqueue_script('hw_jquery_plugin',plugins_url('js/hw-jquery-plugin.js',dirname(__FILE__)),array('jquery'));
        wp_localize_script('hw_jquery_plugin', '__hw_global_object', $this->serialize_obj_admin);
    }
    /**
     * put something on head tag
     * @hook wp_enqueue_scripts
     */
    public function _wp_enqueue_scripts(){
        $all_opts = get_option('nhp_hoangweb_theme_opts', array());
        if(!is_array($all_opts)) $all_opts = array();

        $allow = array('address');

        foreach($all_opts as $opt => &$val){
            if(!in_array($opt, $allow)) unset($all_opts[$opt]);
            //parse geocode from address
            /*if($opt == 'address'){
                $val = HW_Gmap::getLocationFromAddress($val);
            }*/
        }
        $this->serialize_obj = $all_opts;

        wp_enqueue_script('hw-scripts', HW_HOANGWEB_URL.'/js/hw-scripts.js');   //script on frontend
        wp_localize_script('hw-scripts', '__hw_localize_object', $this->serialize_obj);

        /*if(hw_option('scroll2top')){    //enable scroll to top    //moved to skin /skins/scroll2top/default
            wp_enqueue_script('hw-scroll2top',NHP_OPTIONS_URL.'/fields/hw_scroll2top/js/jss-script.js',array('jquery'));
        }*/
        wp_localize_script('hw-scripts', '__hw_modules', array() ); //for holding all modules object
    }

    /**
     * @ajax hoangweb
     */
    public function _ajax_hoangweb() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hoangweb_nonce")) {
            exit("hacked !");
        }
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            self::do_active_ajax();

        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
}
/**
 * common functions
 */
require_once(dirname(__FILE__). '/hw-functions.php');
/**
 * autoload
 */
require_once(HW_HOANGWEB_INCLUDES . '/autoload.php');

/**
 * cache system
 */
require_once (HW_HOANGWEB_INCLUDES. '/hw-caches.php');
/**
 * rewrite
 */
require_once (HW_HOANGWEB_INCLUDES. '/hw-rewrite.php');
/**
 * load main settings form
 */
require_once('admin/hoangweb-settings.php');

##############################################################################
/**
 * JS libraries manager
 */
require_once('hw-jquery-library.php');

/**
 * init modules/libraries
 */
include_once('library/library.php');
##############################################################################
/**
 * module stuff
 */
include_once(HW_HOANGWEB_PLUGINS.'/module.php');
include_once(HW_HOANGWEB_PLUGINS.'/modules-manager.php');
include_once(HW_HOANGWEB_PLUGINS.'/modules-installer.php');

//include_once(HW_HOANGWEB_PHP_LIBS.'/library.php');//we move to core module
include_once(HW_HOANGWEB_PLUGINS.'/command.php');

##############################################################################
/**
 * dynamic templates settings
 */
require_once('layout-templates/admin/dynamic-templates.php');
/**
 * admin menu
 */
include_once ('admin/admin-menu.php');
/**
 * admin theme options
 */
require_once('admin/hw-nhp-theme-options.php');  //theme options buit on nhp framework
include_once('admin/hw-menu-item-media.php');
include_once(HW_HOANGWEB_PLUGINS.'/installer.php');


/**
 * desploy settings
 * theme options implementation, only on frontend
 */
if(!is_admin() || class_exists('HW_CLI_HW_Module', false)) {
    include_once(HW_HOANGWEB_INCLUDES. '/website/hw-settings-implementation.php');
}

?>