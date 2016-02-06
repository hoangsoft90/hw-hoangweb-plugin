<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_SKIN_PLUGIN_FILE', __FILE__);

/**
 * load functions
 */
require_once('functions.php');

/**
 * HW_SKIN_Option class
 */
require_once('includes/hw_skin_options.php');


if(!class_exists('HW_SKIN', false) && class_exists('HW_SKIN_Option', false)):
    /**
     * Class HW_SKIN
     */
    class HW_SKIN extends HW_SKIN_Option
    {
        //static $instance;	//refer to widget instance this class link to
        private $_options = array();    //options manager
        private $data = array();		//storing data
        private $skins_data = array();  //skins data
        public $apply_plugin = null;		//what plugin using this file
        public $skin_folder = null;     //folder name to hold all skins

        protected  $skin_name = null;   //skin file
        protected $skin_options = 'options.php'; //skin options file

        public $skins_holders = array();		//where locate skins folder, default use wp-content/ path
        public $plugin_url = null;  //reference plugin url
        private $default_skin_path = null;  //path to skins folder nest some plugin that apply skin create by this class
        private $_default_skin_path = null;  //skins folder locate in plugin

        public $files_skin_folder = null;  //skin folder files, skin to files such as image

        private $active_skin = null;       //path to active skin folder
        private $active_skin_info = null;   //copy original value of $active_skin
        private static $current_skin = null;  //current skin object

        public $widget_ref = null;      //reference to widget that using this class
        const DEFAULT_SKIN_FOLDER = 'default';
        const DEFAULT_GROUP = '{Plugin}';
        static $count = 0;
        static $FILE_TYPES = array();  //list file types

        public $custom_skins_preview = false;     //default use internal skins preview durring render skins picker
        private $skins_manager = array();  //manage related skins
        private $skins_preview_id = null;  //skin preview holder
        public $skinID = null;     //current skin ID

        /**
         * match skins name
         * @var array
         */
        private $allows_skin_name = array();
        private $matches_skin_name = array();   //check regex matching

        /**
         * template file header info
         * @var null
         */
        private $template_header_info = null;

        private $group_name = null;    //save group skin name

        /**
         * skin types
         */
        const SKIN_FILES = 'skin_files';
        const SKIN_LINKS = 'skin_links';
        /**
         * dropdown ddslick
         * @var unknown
         */
        const DROPDOWN_DDSSLICK_THEME = 'dropdown_ddslick';
        private $ddslick_settings = array();    //dropdown ddslick settings

        /**
         * chunk of js code trigger on change skin event
         * @var unknown
         */
        private $jsEvents = array();   //manage js inline script for event trigger

        static $callbacks = array();
        /**
         * enable external callbacks save data for skin change event. this variable should call before method load_skins_data
         * @var unknown
         */
        public $enable_external_callback = true;
        /**
         * external storage for callback js code used in change skin event
         * @var unknown
         */
        private $save_callbackJS_events_handle = null;

        /**
         * migrate data to fit other skin file that in list of this skin
         * @var array
         */
        private $migrate_data = array();
        /**
         * @var bool
         */
        private $enable_skin_tpl = true;
        /**
         * @var bool
         */
        private $enable_skin_file = true;
        /**
         * skin settings table constant
         */
        const SKINS_SETTINGS_DB = 'hw_skin_settings';
        /**
         * skins assets manager
         * @var array
         */
        protected static $skins_assets_manager = array();
        /**
         * construct method
         * @param unknown $widget
         * @param string $apply_plugin
         * @param string $skin_folder
         * @param string $skin_name
         * @param string $default_skin_path
         */
        function __construct($widget,$apply_plugin='',$skin_folder='',$skin_name='',$default_skin_path='')
        {
            if(is_object($widget)) $this->widget_ref = $widget;
            if($skin_folder) {
                $this->skin_folder=$skin_folder;    //skin folder. note: you can set path to skin folder like this: 'folder1/folder2'
            }
            if($skin_name) $this->skin_name=$skin_name;  //skin name
            $this->add_skin_name_list($skin_name);  //add list allow skin name

            $this->is_main = true;  //this can be modify by sub-skin
            $this->skinID = null;   //skin id

            $this->_apply_plugin =  $this->apply_plugin = $apply_plugin;
            if(!is_dir($apply_plugin) && is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $apply_plugin)) {
                $this->apply_plugin = rtrim(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $apply_plugin,DIRECTORY_SEPARATOR);
            }
            if($default_skin_path) $this->_default_skin_path = $default_skin_path;
            //default skins folder locate in this plugins
            /*if(!is_dir($default_skin_path)) { //since we use relative path to skin file
                $default_skin_path = rtrim($this->apply_plugin,'/').'/'.ltrim($default_skin_path,'/');
            }
            */
            $this->default_skin_path = $default_skin_path;	//plugin_dir_path(__FILE__).'/skins';

            $this->skins_holders = array(
                WP_CONTENT_DIR => array('folder'=>0, 'url' => WP_CONTENT_URL, 'group' => 'WP_CONTENT'),		//wp-content/ folder, use variable $skin_folder
                $this->apply_plugin => array('folder' => $this->_default_skin_path, 'url' => 'get_ref_plugin_url', 'group' => self::DEFAULT_GROUP),	//current plugin path, and get plugin url by method
                get_stylesheet_directory() => array('folder'=>0, 'url' => get_bloginfo('template_url'), 'group'=>'Theme')	//return absolute server path for current theme/child. 0 refer to $skin_folder
            );
            //get file types
            self::$FILE_TYPES = array(
                'link'=>'gif,png,jpeg,jpg,bmp,tif',       #link: refer to resource
                'file' => 'php'
            );
            if(empty($this->template_header_info)){
                $this->template_header_info = array(
                    'name'          => 'HW Template',
                    'description'   => 'Description',
                    'author'        => 'Author',
                    'uri'           => 'Author URI',
                );
            }

            //init actions
            $this->setup_actions();

            if($this->widget_ref instanceof WP_Widget  && isset($this->widget_ref->id)) {
                //maintain reference widget id
                //$this->number = $this->widget_ref->number;
                $this->id = $this->widget_ref->id;
            }
            //init default ddslick params
            $this->set_dropdown_ddslick_setting();
            $this->enable_template_engine();    //by default enable skin template & disable skin file
            //next instance indentifier
            self::$count++;

            //init skin options manager
            parent::__construct($this /*, $this*/);
        }
        /**
         * setup actions
         */
        private function setup_actions() {

            add_action('admin_enqueue_scripts',array(__CLASS__,'_admin_enqueue_styles_scripts'),30/*,self::$count++*/);
            //add_action( 'admin_footer', array(&$this,'_custom_admin_footer'),1000 );
            add_action("wp_ajax_hw_skin_choose_skin_evt", array(__CLASS__, "_hw_skin_choose_skin_js_evt"));    //ajax handle
        }
        /**
         * add/get option
         * @param $name
         * @param $value
         * @param $merge_array
         */
        public function _option($name, $value = '', $merge_array = false) {
            if(is_string($name) && ($value!=='' && $value!==null)){
                $data = &$this->_options;
                if( $merge_array) {
                    if(!isset($data[$name]) ) $data[$name] = array();
                    if(! is_array($data[$name])) $data[$name] = (array)$data[$name];
                    if(!is_array($value) ) $value = (array) $value;

                    $data[$name] = array_merge($data[$name],$value );
                }
                else $data[$name] = $value;
            }

            return isset($this->_options[$name])? $this->_options[$name] : '';
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
         * init skin
         */
        public function init(){
            //enqueue some script in wp admin

        }
        /**
         * save current skin to db
         * @param $skin_config
         * @param $name
         */
        public static function save_enqueue_skin($skin_config, $name ='') {
            hwskin_save_enqueue_skin($skin_config, $name);
        }
        /**
         * @param $skin_config
         * @param string $name
         */
        public function save_skin_assets($skin_config, $name ='') {
            //skin identifier
            if(!isset($skin_config['object'])) $skin_config['object'] = '';
            $id = ($name);  //$this->ref_uniqueID
            if(isset($this->widget_ref) && $this->widget_ref instanceof WP_Widget) {
                $id = $this->widget_ref->get_field_id($id);
            }
            $skin_config['object'] .= $id;    //because unique ID is dynamic in any time so this make crazy when update skin status
            //hwskin_config
            if(!isset($skin_config['skin']['hwskin_config'])) $skin_config['skin']['hwskin_config'] = $this->get_config();

            hwskin_save_enqueue_skin($skin_config, $name);
        }
        /*public static function enqueue_skins() {

        }*/
        /**
         * add skin name exception
         * @param $skin_name
         */
        public function add_skin_name_list($skin_name){
            if(is_string($skin_name)) $this->allows_skin_name[] = $skin_name;
            elseif(is_array($skin_name)){
                foreach($skin_name as $name) $this->add_skin_name_list($name);
            }
        }
        /**
         * add skin name exception with regex format
         * @param $skin_name
         */
        public function match_skin_name_list($skin_name) {
            if(is_string($skin_name)) $this->matches_skin_name[] = $skin_name;
            elseif(is_array($skin_name)){
                foreach($skin_name as $name) $this->match_skin_name_list($name);
            }
        }
        /**
         * valid skin file to match requirement
         * @param $file
         * @return mixed
         */
        private function match_template_file($file) {
            if(in_array($file, (array)$this->allows_skin_name)) return true;
            //skin name match regular expression
            foreach((array)$this->matches_skin_name as $match) {
                if($file == $match || preg_match($match, $file)) return true;
            }
            return false;
        }

        /**
         * migrate to compatible with other skin file
         * @param $data: array
         */
        public function migrate($data = null) {
            if(is_array($data)) $this->migrate_data =  $data;
            return $this->migrate_data;
        }
        /**
         * get migration data
         * @return array
         */
        public function get_migrate() {
            $extract = array();
            $compatible_vars = $this->get_skin_compatible_vars();
            $this->migrate_data = array_merge($this->migrate_data, $compatible_vars);

            foreach ($this->migrate_data as $var => $val){
                $extract[$var] = $val;  //default

                if(is_string($val)) {   //global var
                    global $$val;
                    if(!empty($$val)) $extract[$var] = $$val;
                    continue;
                }
                if (is_callable($val)) {    //callback
                    $extract[$var] = call_user_func($val);
                    continue;
                }
                //$extract[$var] = is_string($val) && isset($$val)? $$val : $val;
            }
            return $extract;
        }
        /**
         * get skin compatiable vars to fit other
         */
        public function get_skin_compatible_vars() {
            $theme_setting = $this->get_file_skin_setting();    //set active skin requirement for first
            if($theme_setting && file_exists($theme_setting)) {
                include ($theme_setting);
                //get skin compatible with other skin
                if(isset($theme['compatible_vars'])) {
                    return (array) $theme['compatible_vars'];
                }
            }
            return array();
        }
        /**
         * enable/disable twig template for skin
         * @param bool $enable_skin_file
         * @param bool $enable
         */
        public function enable_template_engine($enable=true, $enable_skin_file=false) {
            #$this->enable_skin_tpl = $enable;
            #$this->enable_skin_file = $enable_skin_file;
            $this->_option('enable_skin_tpl', $enable);
            $this->_option('enable_skin_file', $enable_skin_file);
        }
        /**
         * check if allow main skin file
         * @return bool
         */
        public function allow_skin_file() {
            return $this->_get_option('enable_skin_file');
        }
        /**
         * check if allow skin template
         * @return bool
         */
        public function allow_skin_tpl() {
            return $this->_get_option('enable_skin_tpl');
        }
        /**
         * compact all config for this skin
         * @param bool $serialize: zip array into string to easy storing
         */
        public function get_config($serialize = true){
            $config =  array();
            if($this->widget_ref && isset($this->widget_ref->id)) {
                $config['widget'] = $this->widget_ref->id;
            }  //save widget id if linking
            $apply_plugin = ltrim(str_replace(realpath(HW_HOANGWEB_PATH), '', realpath($this->_apply_plugin)), '\/');
            $plugin_url = ltrim(str_replace(HW_File_Directory::generate_url(HW_HOANGWEB_URL), '', HW_File_Directory::generate_url($this->plugin_url)), '\/');

            $config['apply_current_path'] = $apply_plugin;  //$this->_apply_plugin;   //current path to file that create instance of HW_SKIN
            $config['plugin_url'] = $plugin_url; //$this->plugin_url;  //get assign plugin url
            $config['skin_folder'] = $this->skin_folder;    //specific folders where store external skins
            $config['skin_name'] = $this->skin_name;    //skin file name
            $config['default_skin_path'] = $this->_default_skin_path;   //skins folder name inside plugin
            $config['group'] = $this->group_name;
            $config['enable_external_callback'] = $this->enable_external_callback;
            if(isset($this->files_skin_folder)) $config['files_skin_folder'] = $this->files_skin_folder;    //for skin link
            $config['allows_skin_name'] = $this->allows_skin_name;

            //backup other properties of this class
            $_properties = array(
                'migrate_data',/*'apply_current_path','plugin_url','skin_folder','skin_name','default_skin_path','group','enable_external_callback',
            'files_skin_folder','allows_skin_name'*/
            );
            $config['properties'] = array(); //array('migrate_data'=>$this->migrate_data);
            foreach($_properties as $prop){
                if(property_exists($this,$prop)) $config['properties'][$prop] = ($this->$prop);
            }
            //backup options
            $config['options'] = $this->_options;
//_print($config);
            return $serialize? self::generate_skin_config($config) : $config;
        }
        /**
         * encrypt skin config
         * @param array $config
         * @return string
         */
        public static function generate_skin_config($config = array()) {
            if(!is_array($config)) $config = array();
            return self::encrypt($config);
        }
        /**
         * resume skin object
         * @param array $config: skin config
         */
        static function resume_skin($config = array()){
            HW_HOANGWEB::load_class('HW_File_Directory');
            if(is_string($config)) $config = self::decrypt($config);
            global $wp_registered_widgets;
            //turn widget obj from their id
            $widget_inst= null;
            if(isset($config['widget']) && isset($wp_registered_widgets[$config['widget']])){

                $widget_obj = $wp_registered_widgets[$config['widget']];
                if(is_array($widget_obj['callback'])) $widget_inst = $widget_obj['callback'][0];
                elseif(isset($widget_obj['callback_wl_redirect'][0])){  //modify by widget_logic plugin
                    $widget_inst = $widget_obj['callback_wl_redirect'][0];
                }
            }
            if(isset($config['apply_current_path'])) {
                $apply_current_path = $config['apply_current_path'];
                if(!file_exists($apply_current_path)) {
                    //alway start from hw-hoangweb plugin
                    $apply_current_path = HW_File_Directory::generate_path(HW_HOANGWEB_PATH, $apply_current_path);
                }
            }
            else return;

            if(isset($config['plugin_url'])) {
                $plugin_url = $config['plugin_url'];
                if(!HW_URL::valid_url($plugin_url)) {
                    $plugin_url = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $plugin_url);
                }
            }
            else return;

            $external_skins_folder = isset($config['skin_folder'])? $config['skin_folder'] : $config['other_folder'];   //two alias

            if(isset($config['skin_name'])) $skin_name = $config['skin_name'];
            else return;

            if(isset($config['default_skin_path'])) $default_skin_path = $config['default_skin_path'];
            else $config['default_skin_path'] = 'skins';

            $group = isset($config['group'])? $config['group'] : '';

            $enable_external_callback = $config['enable_external_callback'];
            /**
             * start createn new  HW_SKIN instance
             */
            $skin = new self($widget_inst, $apply_current_path, $external_skins_folder,$skin_name,$default_skin_path);
            $skin->plugin_url = $plugin_url;
            $skin->enable_external_callback = $enable_external_callback;
            if($group) $skin->set_group($group);
            if(isset($config['allows_skin_name'])) $skin->add_skin_name_list($config['allows_skin_name']);

            //set other properties
            if(isset($config['properties']) && is_array($config['properties'])) {
                foreach ($config['properties'] as $name => $value) {
                    $skin->$name = $value;
                }
            }
            //update options
            if(isset($config['options'])) $skin->_options = $config['options'];
            //$skin->init();

            return $skin;
        }
        /**
         * create twig template for current skin
         * @param string $hash_skin
         */
        public function create_skin_twig($hash_skin = '') {
            static $twigs = array();
            $id = $this->uniqueID();
            if(!isset($twigs[$id])) {
                $twigs[$id] = HW_Twig_Template::create($this->get_file_skin_resource('tpl', $hash_skin));
                $twig = $twigs[$id]->get();
                //allows functions
                $allows_funcs = array('hwtpl_limit_str', 'is_ajax');
                foreach($allows_funcs as $func) {
                    if(function_exists($func)) $twig->addFunction(new Twig_SimpleFunction($func,$func));
                }
            }
            return $twigs[$id];
        }
        /**
         * render skin template engine
         * @param string $hash_skin
         * @param array $data
         * @param bool $echo print out or return
         * @param $tpl
         * @return bool
         */
        public function render_skin_template( $data = array(), $echo = true, $hash_skin='', $tpl = '') {
            if(!$this->allow_skin_tpl()) return false;  //not allow skin template
            //init twig template object
            $hw_tpl = $this->create_skin_twig($hash_skin);
            $skin_info = $this->get_skin_info($hash_skin);
            if(!$tpl) $tpl = $skin_info['skin_tpl_path'];

            if(file_exists($tpl) || HW_Twig_Template::twig_asset_exists($tpl, $hw_tpl->get())) {
                //if(!isset($data['_current_skin'])) $data['_current_skin'] = HW_SKIN::current();
                $hw_tpl->get()->addGlobal('_current_skin', HW_SKIN::current());
                $hw_tpl->get()->addGlobal('is_ajax_context', is_ajax());

                $content = $hw_tpl->_render($tpl , $data);
                if($echo) echo $content;else return $content;
                return true;
            }
            return false;
        }
        /**
         * skin data: contain 'hash_skin','hwskin_config'
         * @param $skin_data
         * @param $_callback: callbacks function
         * @param Array $params2callback: send this to callback
         * @param bool $echo decide to return or print out
         */
        public static function apply_skin_data($skin_data = array(), $_callback = null, $params2callback = array(), $echo = true) {
            ob_start();
            //current hash skin && skin config
            if(isset($skin_data['hash_skin'])) $hash_skin = $skin_data['hash_skin'];
            if(isset($skin_data['hwskin_config'])) $skin_config = $skin_data['hwskin_config'];

            //HW_SKIN object
            if(!empty($skin_data['instance']) && $skin_data['instance'] instanceof HW_SKIN) $skin = $skin_data['instance'];
            //skin options
            if(!empty($skin_data['skin_options']) ) $skin_options = $skin_data['skin_options'];
            else $skin_options = array();

            if((!empty($skin_config) || !empty($skin)) && isset($hash_skin)){
                if(!isset($skin)) $skin = self::resume_skin($skin_config); //resume HW_SKIN with given config

                $file = $skin->get_skin_file($hash_skin);   //main skin file
                //init twig template object
                $hw_tpl = $skin->create_skin_twig();
                $skin_info = $skin->get_skin_info($hash_skin);
                $tpl_file = HW_Twig_Template::twig_asset_exists($skin_info['skin_tpl_path'], $hw_tpl->get());

                if( file_exists($file) || $tpl_file) {

                    $skin_data['skin_file'] = $file;    //save skin file

                    //parse callbacks
                    if(isset($_callback['callback_before']) && is_callable($_callback['callback_before'])) {
                        $callback_before = $_callback['callback_before'];
                    }
                    if(isset($_callback['callback_after']) && is_callable($_callback['callback_after'])) {
                        $callback_after = $_callback['callback_after'];
                    }
                    if(is_callable($_callback)) {   //just one callback
                        $callback_after = $_callback;
                    }

                    //theme setting
                    $theme_setting = $skin->get_file_skin_setting();
                    if(file_exists($theme_setting)) include ($theme_setting);
                    //theme options
                    $skin_options_file = $skin->get_file_skin_options();
                    $skin_options = HW_SKIN::merge_skin_options_values($skin_options, $theme_setting, $skin_options_file);
                    //update data
                    $skin_data['skin_options'] = $skin_options;
                    if(isset($theme)) $skin_data['theme_settings'] = $theme;

                    if(isset($callback_before)) {
                        $var = 'cb_data_return_'.rand(5,10);
                        $$var = call_user_func($callback_before, array_merge(get_defined_vars(), (array)$params2callback));
                        if(is_array($$var)) extract($$var);
                        unset($$var);
                    }
                    //load both main skin file & twig template
                    if($skin->allow_skin_file() && file_exists($file)) {
                        if(is_array($params2callback)) extract($params2callback);
                        include($file); //note add hook does't work if include statement inside a function with 2 level
                        #self::include_skin_file($file);
                    }
                    if($skin->allow_skin_tpl() && ($tpl_file)) {
                        echo $hw_tpl->_render($skin_info['skin_tpl_path'], array_merge($params2callback, $skin_data));
                    }
                    //$wp_registered_sidebars[$sidebar]['skin'] = $skin;     //bring skin object into params

                    if(isset($callback_after)) {
                        $var = 'cb_data_return_'.rand(5,10);
                        $$var = call_user_func($callback_after, array_merge(get_defined_vars(), (array)$params2callback));
                        if(is_array($$var)) extract($$var);
                        unset($$var);
                    }

                    /**
                     * enqueue css & js -> depricated
                     */
                    /*if(!isset($theme['styles'])) $theme['styles'] = array();
                    if(!isset($theme['scripts'])) $theme['scripts'] = array();

                    if(count($theme['styles']) || count($theme['scripts'])) {
                        $skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                    }*/
                    self::enqueue_skin_assets($skin_data);
                    if(!empty($theme['filters']) ) $skin->do_filters($theme['filters'], array($theme, $skin_options));
                }
            }
            $content = ob_get_contents();
            ob_clean();
            if($echo) echo $content; else return $content;
        }
        /**
         * set group skins
         * @param unknown $group: new group name
         * @param string $old_group: old group name
         * @return this instance
         */
        function set_group($group,$old_group = ''){
            //remove group name at the end of skin folder
            $assign_group_path = function (&$path) use ($group,$old_group){

                $path = preg_replace('#(\\\|\/)'.$old_group.'(\\\|\/)?#','', $path); //old group
                $path = preg_replace('#(\\\|\/)'.$group.'(\\\|\/)?#','', $path); //new group
                $path .= '/'.$group;
                return $path;
            };
            $assign_group_path($this->skin_folder);
            //modify skin holders
            if(isset($this->skins_holders[$this->apply_plugin]['folder'])){
                $assign_group_path($this->skins_holders[$this->apply_plugin]['folder'],$group);
            }
            $this->group_name = $group; //save last group

            return $this;
        }
        /**
         * set skins files folder
         * @param string $folder: folder or path to sub-folder
         */
        function set_resource_path_skins($folder){
            if(is_string($folder)) $this->files_skin_folder = $folder;
        }
        /**
         * get reference plugin url
         */
        public function get_ref_plugin_url(){
            //make relative path
            if(!empty($this->plugin_url)) {
                $skins_holder = ltrim(str_replace(HW_File_Directory::generate_url(HW_HOANGWEB_URL), '', HW_File_Directory::generate_url($this->plugin_url)), '\/');
            }
            else $skins_holder = '';
            #return isset($this->plugin_url)? $this->plugin_url : '';
            return $skins_holder;
        }
        /**
         * return this plugin file
         */
        static public function get_this_plugin(){
            $wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
            $this_plugin = plugin_basename(trim($wp_path_to_this_file));
            return $this_plugin;
        }
        /**
         * valid file name
         * @param unknown $file
         */
        function valid_filename($file,$replace ='-'){
            return preg_replace('#[\/,\\|]+#', $replace, $file);
        }
        /**
         * valid object name
         * @param string $name: string name
         * @return valid object name
         */
        public static function valid_objname($name){
            $delimiter = '_';
            return preg_replace('/[\s,.\[\]\/\\\#\*@$%^\!~\-\+\=]+/',$delimiter,$name);
        }
        /**
         * Load skins data
         * @param mixed $skins_: give type of skins data want to get or pass array of skins data
         */
        public function load_skins_data($skins_ = self::SKIN_FILES){
            //static $skins; #move static $skins to $this->__skins
            $widget = $this->widget_ref;	//get current widget or any class instance

            if(!isset($this->__skins)){
                $this->__skins = $this->get_skins_data($skins_);
            }

            $objs = array();
            $data = array();
            foreach($this->__skins as $skin){
                if($skin['type'] == 'link'){  //if skin point to resource file, ex: png,jpg..
                    $skin_path = HW_SKIN::generate_skin_file($skin);
                }
                else $skin_path = HW_SKIN::generate_skin_path($skin);     //generate hash skin string

                $data[$skin_path] = array();
                if(!isset($skin['holder'])){
                    $skin['holder'] = $this->apply_plugin;
                }
                if($skin['path'] != self::DEFAULT_SKIN_FOLDER) {
                    #$data[$skin_path]['url'] = $skin['holder_url'].'/'.$skin['skin_folder'].'/'.$skin['path'];  //old
                    $data[$skin_path]['url'] = $skin['skin_url'];
                }
                else{
                    /*if method 'sk_referer_plugin_url' exists in which use this class
                    public function sk_referer_plugin_url($path){
                        return plugins_url($path,__FILE__);
                    }
                    */
                    if(is_object($widget) && method_exists($widget,'sk_referer_plugin_url')) {
                        //refer to default skin inside parent plugin folder.
                        $data[$skin_path]['url'] = $widget->sk_referer_plugin_url('skins/'.$skin['path']);
                    }
                    elseif(isset($this->plugin_url)) {
                        //if plugin_url variable defined
                        $data[$skin_path]['url'] = $this->plugin_url.'/'.$this->_default_skin_path.'/'.$skin['path'];
                    }

                }
                //valid skin url
                if($skin['source'] == 'plugin') {
                    $data[$skin_path]['url'] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $data[$skin_path]['url']);
                }
                elseif($skin['source'] == 'theme') {
                    //no need because i added in get_skins method
                    $data[$skin_path]['url'] = HW_File_Directory::generate_url(
                        get_stylesheet_directory_uri()/*. '/modules/'.$this->get_module_info('slug')*/, $data[$skin_path]['url']);
                }
                elseif($skin['source'] =='wp_content') {
                    $data[$skin_path]['url'] = HW_File_Directory::generate_url(WP_CONTENT_URL,/*'hw-modules',$this->get_module_info('slug'),*/ $data[$skin_path]['url']);
                }
                //config skin thumbnail url
                if(isset($skin['screenshot'])) {
                    $data[$skin_path]['screenshot'] = $data[$skin_path]['url'].'/'.$skin['screenshot'];
                }
                elseif(isset($skin['file_url'])) {
                    $data[$skin_path]['screenshot'] = $skin['file_url'];
                }
                $data[$skin_path] = apply_filters('hw_skin_data', $data[$skin_path],$skin, $this);   //filer each skin data
            }
            $objs['skins'] = apply_filters('hw_skins_data',$data,$this);
            $objs['images'] = plugins_url('images',__FILE__);
            //if(isset($this->widget_ref->number)) _print($this->widget_ref->number.'->'.$this->widget_ref->id);
            //add_action('admin_footer',function() use($objs){
            $enqueue_handle = 'hw-skin-js'/*.$this->skin_obj_name()*/;     //note: this js file use for all plugins
            wp_register_script($enqueue_handle, plugins_url('js/js.js',__FILE__), array('jquery')); //maybe load in frontend
            wp_enqueue_script($enqueue_handle);

            //wp_enqueue_script('hw-skin-js', plugins_url('js/js.js',__FILE__), array('jquery'));
            //but create different localize object for different plugin which built from this class
            wp_localize_script($enqueue_handle, 'hw_skin_'.$this->skin_obj_name(),$objs);	//make sure create more js object that no conflict with other
            //});
            //init skin event
            $this->skin_js_events();
            //_print('hw_skin_'.$this->skin_obj_name());
            return $objs;
        }

        /**
         * enqueue actived skins assets
         * @param $skin
         */
        public static function enqueue_skins($skin) {

            if(empty($skin->instance)) return ;
            ob_start();
            $file = $skin->instance->get_skin_file($skin->hash_skin);   //
            if(file_exists($file)){
                $options = isset($skin->skin_options)? $skin->skin_options : array();
                $theme_setting = $skin->instance->get_file_skin_setting();  //extract theme setting from skin
                $skin_options = $skin->instance->get_file_skin_options();      //current skin options

                //create skin data holder
                self::$skins_assets_manager[$file] = array('skin' => $skin);
                $skin_status = &self::$skins_assets_manager[$file];
                $skin_status['enqueued_css'] = false;
                $skin_status['enqueued_js'] = false;

                //load theme setting
                if(file_exists($theme_setting)) include($theme_setting);
                if(file_exists($skin_options)) include ($skin_options);

                if(!isset($theme) ) {
                    // note add hook does't work if include statement inside a function with 2 level
                    include ($file);
                    /*$skin_data = self::include_skin_file($file);
                    if(!empty($skin_data) && is_array($skin_data)) {
                        extract($skin_data);
                    }*/
                }

                //valid theme setting
                if(!isset($theme['styles']) || !is_array($theme['styles'])) $theme['styles'] = array();
                if(!isset($theme['scripts']) || !is_array($theme['scripts'])) $theme['scripts'] = array();
                if(!isset($theme['js-libs']) || !is_array($theme['js-libs'])) $theme['js-libs'] = array();
                $theme['styles'] = array_unique($theme['styles']);
                $theme['scripts'] = array_unique($theme['scripts']);
                $theme['js-libs'] = array_unique($theme['js-libs']);

                //parse theme options
                if(isset($theme) && isset($theme['options'])) $default_options = $theme['options']; //default options
                else $default_options = array();

                if( isset($theme_options)) {
                    $options = HW_SKIN::merge_skin_options_values($options, $default_options, $theme_options);
                }
                //save skin data
                $skin_status['theme_settings'] = $theme;
                if(isset($options)) $skin_status['theme_options'] = $options; #__save_session($file.'-options',$options);

                if(count($theme['styles']) || count($theme['scripts']) || count($theme['js-libs'])) {   //only for stylesheets
                    if(!isset($options['enqueue_js_position'])) $options['enqueue_js_position'] = 'footer'; //default put js files at bottom of page
                    if(!isset($options['enqueue_css_position'])) $options['enqueue_css_position'] = 'footer'; //default put js files at bottom of page
                    //alway enqueue stylesheet within head tag
                    if($options['enqueue_css_position'] == 'head' && count($theme['styles'])) {
                        $skin->instance->enqueue_files_from_skin(array_unique($theme['styles']) ,null, false);
                        $skin_status['enqueued_css'] = true;
                    }
                    //enqueue skin scripts
                    if($options['enqueue_js_position'] == 'head' && count($theme['scripts'])) {
                        $skin->instance->enqueue_files_from_skin(null, array_unique($theme['scripts']), false);
                        $skin_status['enqueued_js'] = true;
                    }
                    //enqueue jquery library
                    if($options['enqueue_js_position'] == 'head' && count($theme['js-libs'])) {
                        foreach($theme['js-libs'] as $lib) HW_Libraries::enqueue_jquery_libs($lib);
                        $skin_status['enqueued_lib'] = true;
                    }
                }

            }
            #ob_end_clean();
            ob_clean();
        }
        /**
         * enqueue skin assets
         * @param $skin_data
         */
        public static function enqueue_skin_assets($skin_data) {
            if(is_string($skin_data) && isset(self::$skins_assets_manager[$skin_data]) ) {
                $skin_file = $skin_data;
                $skin = self::$skins_assets_manager[$skin_data]['skin']->instance;  //skin object
                $theme_settings = self::$skins_assets_manager[$skin_data]['theme_settings'];    //theme settings
                if(isset(self::$skins_assets_manager[$skin_data]['theme_options'])) {   //theme options
                    $theme_options = self::$skins_assets_manager[$skin_data]['theme_options'];
                }
            }
            elseif(is_array($skin_data))
            {
                //HW_SKIN object
                if(!empty($skin_data['instance']) && $skin_data['instance'] instanceof HW_SKIN) {
                    $skin = $skin_data['instance'];
                }
                //skin config & active skin file
                if(isset($skin_data['hash_skin'])) $hash_skin = $skin_data['hash_skin'];
                if(isset($skin_data['hwskin_config'])) $skin_config = $skin_data['hwskin_config'];
                elseif(isset($skin)) $skin_config = $skin->get_config();

                if(isset($skin_data['skin_file'])) $skin_file = $skin_data['skin_file'];

                if((!empty($skin_config) && empty($skin)) ){
                    $skin = self::resume_skin($skin_config); //resume HW_SKIN with given config
                }

                //skin options
                if(!empty($skin_data['skin_options']) ) $skin_options = $skin_data['skin_options'];
                else $skin_options = array();

                //theme setting
                if(!empty($skin_data['skin_settings'])) $theme_settings = $theme= $skin_data['skin_settings'];
                elseif(isset($skin) ){
                    $theme_setting = $skin->get_file_skin_setting($hash_skin);
                    if($theme_setting && file_exists($theme_setting)) {
                        include ($theme_setting);
                        if(isset($theme)) $theme_settings = $theme;
                    }
                }
                //main skin file
                if(!isset($skin_file) && isset($hash_skin)) $skin_file =  $skin->get_skin_file($hash_skin);
                if(!isset($theme_settings) && isset($skin_file) && file_exists($skin_file)) {
                    extract(self::include_skin_file($skin_file, true));
                }
            }
            //check already enqueue for assets
            if(isset(self::$skins_assets_manager[$skin_file]) ) {
                if(!empty(self::$skins_assets_manager[$skin_file]['enqueued_css'])) $enqueued_css = 1;
                if(!empty(self::$skins_assets_manager[$skin_file]['enqueued_js'])) $enqueued_js = 1;
                if(!empty(self::$skins_assets_manager[$skin_file]['enqueued_lib'])) $enqueued_lib = 1;
            }

            //valid theme setting
            if(!isset($theme_settings['styles']) || !is_array($theme_settings['styles'])) {
                $theme_settings['styles'] = array();
            }
            if(!isset($theme_settings['scripts']) || !is_array($theme_settings['scripts'])) {
                $theme_settings['scripts'] = array();
            }
            if(!isset($theme_settings['js-libs']) || !is_array($theme_settings['js-libs'])) {
                $theme_settings['js-libs'] = array();
            }
            if(isset($skin) && $skin instanceof HW_SKIN &&
                (count($theme_settings['styles']) || count($theme_settings['scripts']) || count($theme_settings['js-libs'])) ) {
                if(empty($enqueued_css)) {
                    //put css files
                    $skin->enqueue_files_from_skin($theme_settings['styles']);
                }
                if(empty($enqueued_js)) {
                    //put js files
                    $skin->enqueue_files_from_skin(null, $theme_settings['scripts']);
                }
                if(empty($enqueued_lib)) {
                    //enqueue libs
                    foreach($theme_settings['js-libs'] as $lib) HW_Libraries::enqueue_jquery_libs($lib);
                }
            }
        }
        /**
         * include file contents
         * @param $file
         * @param bool $hide_content
         * @return theme setting, because maybe skin setting put in main skin file
         */
        public static function include_skin_file($file, $hide_content = false){
            $data = array();
            //decide to hide or show content from skin
            if($hide_content) ob_start();
            if(file_exists($file)) include ($file);
            if($hide_content ) ob_end_clean();

            if(isset($theme)) $data['theme'] = $theme;
            return $data;
        }
        /**
         * hook 'admin_enqueue_scripts'
         * @hook admin_enqueue_scripts
         */
        public static function _admin_enqueue_styles_scripts(){
            wp_enqueue_style('hw-skin-css',plugins_url('style.css',__FILE__));
            wp_register_script('hw-skin-js', plugins_url('js/js.js',__FILE__), array('jquery'));

            //load tooltip lib
            HW_Libraries::enqueue_jquery_libs('components-ui/collapse/jQuery-Collapse');    //collapsible
            HW_Libraries::enqueue_jquery_libs('tooltips/qtip2');    //tooltip
            //wp_enqueue_style('jquery.qtip.min.css', plugins_url('css/jquery.qtip.min.css',__FILE__));
            //wp_enqueue_script('jquery.qtip.min.js', plugins_url('js/jquery.qtip.min.js',__FILE__),array('jquery'));
            //dropdown ddslick theme
            //wp_register_script(self::DROPDOWN_DDSSLICK_THEME, plugins_url('/js/jquery.ddslick.min.js',__FILE__));
            HW_Libraries::enqueue_jquery_libs('components-ui/dropdown_ddslick');    //ddslick js

            //$this->skins_data = $this->load_skins_data();   //no, it should call while user decide to load different skin data
        }

        /**
         * return skin object name
         * @return mixed
         */
        public function skin_obj_name(){
            return self::valid_objname($this->uniqueID($this->valid_filename($this->skin_folder,'_')));
        }
        /**
         * return generate unique name base skin id
         * @param unknown $str
         * @param string $char
         * @return string
         */
        public function uniqueID($str = '',$char = '_'){
            //don't get widget id from $this->widget_ref->id, this will refer to old widget & you need to refresh page to working properly. solution get widget id that grab from contructor
            $id = ( isset($this->id)? $char.$this->id : '');   //get widget ref id
            if($this->group_name) $id .= $char.$this->group_name;
            if($this->skinID) $id .= $char.$this->skinID;
            $id .= md5(self::valid_objname(str_replace(realpath(HW_HOANGWEB_PATH), '',realpath($this->apply_plugin))) );
            return $str.$id;//.'_'.self::$count;    //please don't because i don't know self::$count on other shared file
        }
        /**
         * inherit from method uniqueID but it's difference for each instance created
         * @param $str
         * @param string $char
         */
        public function instance_uniqueID($str,$char = '_'){
            return $this->uniqueID($str,$char).'_'.self::$count;
        }
        /**
         * generate unique ID for reference object
         * @param $str
         * @return string
         */
        public function ref_uniqueID($str) {
            $id = $this->instance_uniqueID($str);
            if(isset($this->widget_ref) && $this->widget_ref instanceof WP_Widget) {
                $id = $this->widget_ref->get_field_id($id);
            }
            return $id;
        }
        /**
         * generate ajax handle ID for reference object
         */
        private function ajaxHandleID(){
            return md5($this->skin_obj_name());
        }
        /**
         * generate skin change event & ensure that skin object javascript must be existing
         */
        public function skin_js_events(){
            //create ajax link for changing skin
            $id = $this->ajaxHandleID();
            $nonce = wp_create_nonce("hw_skin_choose_skin_evt_nonce");
            $select_skin_ajax = admin_url('admin-ajax.php?action=hw_skin_choose_skin_evt&id='.$id.'&nonce='.$nonce);

            echo '<script>
		  jQuery(document).ready(function($){      
		hw_skin_'.$this->skin_obj_name().'.skin_change_event = function(value,container){
			var current_skin = value;
		    if(!hw_skin_'.$this->skin_obj_name().'.skins[current_skin]){
		             return;    //first ddslick trigger
		           } 
			var	img = jQuery("<img/>").attr({
					"src":hw_skin_'.$this->skin_obj_name().'.skins[current_skin].screenshot,
					"onError": "this.onerror=null;this.src=\""+hw_skin_'.$this->skin_obj_name().'.images+"/error.jpg\";"
					});
			if(typeof container == "string") jQuery("#"+container).html(img); //show preview image of skin
				//callback
				hw_skin_'.$this->skin_obj_name().'.setCallbackSkinChange_event(hw_skin_'.$this->skin_obj_name().'.skins[current_skin]);	        
		};
		//create callback for selecting skin event
		hw_skin_'.$this->skin_obj_name().'.setCallbackSkinChange_event = function(skin){
		        if('.($this->enable_external_callback? 1:0).'){
		        jQuery.ajax({url:"'.$select_skin_ajax.'",success:function(data){
		                eval(data);
	               }});
		        }else{
		             eval("'.$this->parse_callbackJs_event(true).'");   
		         }        
		};     
		       });            		        		        
		</script>';

        }
        /**
         * save data of js event by callback handle from reference object
         * @param unknown $callback: callable accept 1 argument
         */
        public function registerExternalStorage_JSCallback($callback){
            if(is_callable($callback)) $this->save_callbackJS_events_handle = $callback;
        }
        /**
         * add some js code to run when user change skin event
         * if use plan not use ajax to get callbackJs data, invoke this method before render skins selector
         * @param unknown $inlinejs: js inline code.
         * @param $callback: use different callback for save js event data
         */
        public function saveCallbackJs4SkinChangeEvent($inlinejs,$callback = null){
            if(!is_callable($callback)) $callback = $this->save_callbackJS_events_handle;
            //put to array
            if(!isset($this->jsEvents['change_skin'])) $this->jsEvents['change_skin'] = array();
            $this->jsEvents['change_skin'][]= $inlinejs;

            //save code to external, where call this method.
            if(is_callable($callback) && $this->enable_external_callback) {
                call_user_func($callback, $this->parse_callbackJs_event());
            }
        }
        /**
         *
         * @param unknown $inlinejs
         */
        public function parse_callbackJs_event($valid_slash = false){
            if(isset($this->jsEvents['change_skin'])) {
                $js =  implode('',array_unique ($this->jsEvents['change_skin']));
                if($valid_slash) $js = str_replace('"','\"',$js);
                return $js;
            }
        }
        /**
         * get callbacks data that saved from reference object
         * note that: you put this method from where calling that pass through ajax handle
         * @param unknown $callback: method reference address
         */
        public function getSavedCallbacksJs_data($callback){
            $id = $this->ajaxHandleID();
            self::$callbacks[$id] = $callback;
        }
        /**
         * ajax handle
         * @ajax hw_skin_choose_skin_evt
         */
        public static function _hw_skin_choose_skin_js_evt(){
            if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_skin_choose_skin_evt_nonce")) { //valid ajax link
                exit("No naughty business please");
            }

            /*if(isset($this->jsEvents['change_skin']) && is_array($this->jsEvents['change_skin']) && count($this->jsEvents['change_skin'])){
                $skin_change_evt = join("\n",$this->jsEvents['change_skin']);
            }*/
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //$result = json_encode($result);
                if(isset(self::$callbacks[$_GET['id']])) print_r(call_user_func(self::$callbacks[$_GET['id']],''));
            }
            else {
                header("Location: ".$_SERVER["HTTP_REFERER"]);
            }

            die();
        }
        //no longer in use
        public function data($inst = null){
            if($inst) $this->data = $inst;
            return $this->data;
        }
        /**
         * return current skin instance ->but refer to $this, so this medthod no longer in use
         */
        public function get_current(){
            if(!$this->skinID) return $this;
            if(isset($this->skins_manager[$this->skinID])){
                return $this->skins_manager[$this->skinID];
            }
        }
        /**
         * return current skin object for current working skin file
         */
        public static function current() {
            return self::$current_skin;
        }
        /**
         * create new skin for other
         * @param string $name: skin name
         * @param string $group: skin group
         * @param string $skin_name: provide skin name if not will get same from parent
         */
        function create($name,$group = null,$skin_name = ''){
            if(!isset($this->skins_manager[$name])) {
                if(!$skin_name) $skin_name = $this->skin_name; //inherit skin name from parent
                $skin = new self($this->widget_ref,$this->_apply_plugin,$this->skin_folder,$skin_name,$this->_default_skin_path);
                $skin->plugin_url = $this->plugin_url;   //maintain some variable
                $skin->skinID = $name; //skin id
                $skin->is_main = false;
                //$this->is_main = false;
                //if($group) //allow empty group to clear group
                $skin->set_group($group,$this->group_name);   //set skin group
                $skin->init();     //initial
                $this->skins_manager[$name] = $skin;       //add to manager
            }
            return $this->skins_manager[$name];
        }
        /**
         * return skin instance by name
         * @param unknown $name: name of skin class
         */
        function get_skin_instance($name){
            if(isset($this->skins_manager[$name])) return $this->skins_manager[$name];
        }
        /**
         * get instance of this class->nerver in use
         */
        /*static function get_instance($widget=''){
            if(self::$instance == null && $widget) self::$instance = new self($widget);
            return self::$instance;
        }*/
        /**
         * get file type by extension
         * @param unknown $ext
         */
        static function get_skin_type($ext){
            foreach (self::$FILE_TYPES as $type=>$exts){
                if(in_array($ext, explode(',',$exts))) return $type;
            }
        }
        /**
         * generate skin path
         * @param array $skin: skin info
         * @return string encoded skin address
         */
        static function generate_skin_path($skin){
            if(is_array($skin) && isset($skin['holder']) && isset($skin['path']) && isset($skin['skin_folder']) && isset($skin['skin_url'])){
                /*$skin_info = array(
                        'skin_holder'=> rtrim(rtrim($skin['holder'],'/').'/'.ltrim($skin['skin_folder'],'/'),'/'),
                        'path' => ltrim($skin['path'],'/'),
                        'skin_url' => $skin['skin_url'],
                        'type' => $skin['type'],
                        'thumb' => $skin['skin_url'].'/'.$skin['screenshot']
                );*/
                //valid
                $skin['holder'] = /*realpath*/($skin['holder']) . DIRECTORY_SEPARATOR;
                $skin['skin_folder'] = str_replace('\\', '/', $skin['skin_folder']);
                $skin['path'] = preg_replace('#[\s\/\\\]+$|^[\s\/\\\]+#', '',trim($skin['path'])) . DIRECTORY_SEPARATOR;
                $skin['skin_url'] = HW_Validation::clean_url($skin['skin_url']);    //clean url
                $skin['screenshot_url'] = HW_Validation::clean_url($skin['screenshot_url']);    //clean url

                $save_path = rtrim(HW_File_Directory::generate_path($skin['holder'], $skin['skin_folder']),'\/')
                    .'|'.ltrim($skin['path'],'\/')
                    .'|'.$skin['skin_url']  //with no slash trailing
                    .'|'.$skin['type']
                    .'|'.$skin['name']
                    .'|'.$skin['screenshot_url']
                    .'|'.(isset($skin['filename'])? $skin['filename'] : $skin['file'])   //case load skin link
                    .'|'.$skin['source'];   //detect from source since we use relative path

                return self::encrypt($save_path);
            }
        }
        /**
         * generate skin to file such as image
         * @param array $skin: skin info
         * @return string encoded skin address
         */
        static function generate_skin_file($skin){
            if(is_array($skin) && isset($skin['file_url']) && isset($skin['holder']) /*&& isset($skin['skin_name'])*/ && isset($skin['skin_folder'])){
                /*$skin_info = array(
                        'skin_holder'=> rtrim(rtrim($skin['holder'],'/').'/'.ltrim($skin['skin_folder'],'/'),'/'),
                        'skin_name' => ltrim($skin['skin_name'],'/'),
                        'file_url' => $skin['file_url'],
                        'type' => $skin['type'],
                        'thumb' => $skin['file_url']
                );*/
                //valid
                $skin['holder'] = /*realpath*/($skin['holder']) . DIRECTORY_SEPARATOR;
                $skin['skin_folder'] = str_replace('\\', '/', $skin['skin_folder']);
                $skin['path'] = preg_replace('#[\s\/\\\]+$|^[\s\/\\\]+#', '',trim($skin['path'])) . DIRECTORY_SEPARATOR;
                $skin['file_url'] = HW_Validation::clean_url($skin['skin_url']);    //clean url
                $skin['screenshot_url'] = HW_Validation::clean_url($skin['screenshot_url']);    //clean url

                $save_path = rtrim(rtrim($skin['holder'],'/').'/'.ltrim($skin['skin_folder'],'/'),'/')
                    .'|'.ltrim($skin['path'],'/')
                    .'|'.$skin['file_url']
                    .'|'.$skin['type']
                    .'|'.$skin['name']
                    .'|'. $skin['source'];  //detect from source since we use relative path

                return self::encrypt($save_path);
            }
        }

        /**
         * get files skins
         * @param array $skins: start with exists skins
         */
        public function get_files_skins($skins = null){
            if(!$this->files_skin_folder) return;  //make sure set path where hold all files skin
            //skins data
            if ( !$skins )
                $skins = array();
            $this->skins_holders = apply_filters('hw_skin_skins_holders', $this->skins_holders, $this);    //filter skins_folders
            $module = $this->get_module_info();
            foreach($this->skins_holders as $skins_holder => $folder_name):
                $skin_folder = ($folder_name['folder']? $folder_name['folder'] : $this->skin_folder);
                if(realpath($skins_holder) == realpath(get_stylesheet_directory()) ) { //look up skins in active theme path
                    $skin_folder = 'modules/'.$module['slug'].'/'. $skin_folder;
                }
                elseif(realpath($skins_holder) == realpath(WP_CONTENT_DIR)) {
                    $skin_folder = 'hw-modules/'.$module['slug'].'/'. $skin_folder;
                }
                $skins_dir_path = $skins_holder .DIRECTORY_SEPARATOR . $skin_folder ;
                $skins_dir_path .= DIRECTORY_SEPARATOR.$this->files_skin_folder;    //point to folder where contain all resource file

                try {
                    $skins_iterator = new RecursiveDirectoryIterator( $skins_dir_path );
                    $RecursiveIterator = new RecursiveIteratorIterator( $skins_iterator );
                    //$skins_iterator = new DirectoryIterator( $skins_dir_path );
                    //$RecursiveIterator = new IteratorIterator( $skins_iterator );
                    $RecursiveIterator->setMaxDepth(1); //max depth to 1
                    $_skins_holder = $skins_holder; //make sure you got value from array key, because this value will change as bellow

                    foreach ( $RecursiveIterator as $skin ) {

                        // filter out "." and ".."
                        if ($skin->isDir()) {
                            continue;

                        }

                        $temp_skin = array();
                        //valid
                        if($skins_holder == WP_CONTENT_DIR) {
                            $temp_skin['source'] = 'wp_content';
                            $_skins_holder = $temp_skin['holder_url'] = '';
                        }
                        elseif($skins_holder == $this->apply_plugin) {
                            $temp_skin['source'] = 'plugin';
                            if(strlen(realpath(HW_HOANGWEB_PATH)) < strlen(realpath($skins_holder))) {
                                $_skins_holder = ltrim(str_replace(realpath(HW_HOANGWEB_PATH), '', realpath($skins_holder)), '\/');
                            }
                            else {
                                $_skins_holder = '';//ltrim(str_replace(realpath($skins_holder), '', realpath(HW_HOANGWEB_PATH)), '\/');
                                $temp_skin['holder_url'] = '';
                            }
                        }
                        elseif($skins_holder == get_stylesheet_directory()) {
                            $temp_skin['source'] = 'theme';
                            $_skins_holder = $temp_skin['holder_url'] = '';
                        }

                        //skin name
                        $temp_skin['name'] = $skin-> getFilename();
                        $temp_skin['path'] = basename(dirname( $skin )).DIRECTORY_SEPARATOR;
                        $temp_skin['skin_name'] =  basename(dirname( $skin ));   //$temp_skin['name'];
                        $temp_skin['holder'] = $_skins_holder;	//save folder holder any skins that found
                        $temp_skin['file'] = $skin-> getFilename();
                        $temp_skin['skin_folder'] = $skin_folder;//($folder_name['folder']? $folder_name['folder'] : $this->skin_folder)/*.'/'.$this->files_skin_folder*/;   //get skin folder
                        if(!isset($temp_skin['holder_url'])) {
                            $temp_skin['holder_url'] = !method_exists($this,$folder_name['url'])? $folder_name['url'] : call_user_func(array($this,$folder_name['url']));
                        }
                        if(strcmp(trim($temp_skin['skin_folder'], '\/') , trim($temp_skin['path'], '\/')) == 0) {
                            //skin have no nest on folder
                            $temp_skin['skin_url'] = HW_File_Directory::generate_url($temp_skin['holder_url'],rtrim($temp_skin['skin_folder'],'/'));
                        }
                        else $temp_skin['skin_url'] = HW_File_Directory::generate_url($temp_skin['holder_url'],rtrim($temp_skin['skin_folder'],'/'),$temp_skin['path']);    //skin url

                        $temp_skin['file_ext'] = $skin->getExtension();  //get file extension
                        $temp_skin['type'] = self::get_skin_type($temp_skin['file_ext']);    //detect file type
                        //url to file
                        $temp_skin['screenshot_url'] = $temp_skin['file_url'] = $temp_skin['holder_url'].'/'.rtrim($temp_skin['skin_folder'],'/').'/'.$this->files_skin_folder.'/'.$skin-> getFilename();
                        $temp_skin['group'] = $folder_name['group'].'/'.rtrim($temp_skin['skin_folder'],'/').'/'.$this->files_skin_folder;
                        $skins[] = $temp_skin;
                    }
                }
                catch ( UnexpectedValueException $e ) {

                }
            endforeach;
            return $skins;
        }
        /**
         * fetch skins data
         * @param $skins
         */
        public function get_skins( $skins = null) {
            if ( !$skins )
                $skins = array();
            $this->skins_holders = apply_filters('hw_skin_skins_holders', $this->skins_holders, $this);    //filter skins_folders
            $module = $this->get_module_info();
            foreach($this->skins_holders as $skins_holder => $folder_name):
                $skin_folder = ($folder_name['folder']? $folder_name['folder'] : $this->skin_folder);
                if(realpath($skins_holder) == realpath(get_stylesheet_directory()) ) {   //look up skins in active theme path
                    $skin_folder = 'modules/'.$module['slug'].'/'. $skin_folder;
                }
                elseif(realpath($skins_holder) == realpath(WP_CONTENT_DIR)) {
                    $skin_folder = 'hw-modules/'.$module['slug'].'/'. $skin_folder;
                }

                $skins_dir_path = $skins_holder .DIRECTORY_SEPARATOR . $skin_folder ;

                try {
                    $skins_iterator = new RecursiveDirectoryIterator( $skins_dir_path );
                    $RecursiveIterator = new RecursiveIteratorIterator( $skins_iterator );
                    $RecursiveIterator->setMaxDepth(1); //max depth to 1
                    $_skins_holder = $skins_holder; //make sure you got value from array key, because this value will change as bellow

                    foreach ( $RecursiveIterator as $skin ) {
                        if(basename( $skin ) == '.' || basename( $skin ) == '..') continue;
                        if ($this->match_template_file(basename( $skin )) /*basename( $skin ) == $this->skin_name*/) {

                            //$skin_data = get_plugin_data( $skin );
                            $skin_data = $this->get_template_data($skin, $skin_folder, $this->template_header_info);

                            $temp_skin = array();

                            if ( empty($skin_data['name'])  )
                                $temp_skin['name'] = basename( dirname( $skin ) );
                            else
                                $temp_skin['name'] = $skin_data['name'];

                            //valid
                            if($skins_holder == WP_CONTENT_DIR) {
                                $temp_skin['source'] = 'wp_content';
                                $_skins_holder = $temp_skin['holder_url'] = '';
                            }
                            elseif( $skins_holder == $this->apply_plugin
                                /*|| preg_match('#'.trim(trim($skins_holder),'\/').'$#', trim(trim($this->apply_plugin) ,'\/'))*/) {
                                $temp_skin['source'] = 'plugin';
                                if(strlen(realpath(HW_HOANGWEB_PATH)) < strlen(realpath($skins_holder))) {
                                    $_skins_holder = ltrim(str_replace(realpath(HW_HOANGWEB_PATH), '', realpath($skins_holder)), '\/');
                                    //$temp_skin['holder_url'] = '';

                                }
                                else {
                                    $_skins_holder = '';//ltrim(str_replace(realpath($skins_holder), '', realpath(HW_HOANGWEB_PATH)), '\/');
                                    $temp_skin['holder_url'] = '';
                                }
                            }
                            elseif($skins_holder == get_stylesheet_directory()) {
                                $temp_skin['source'] = 'theme';
                                $_skins_holder = $temp_skin['holder_url'] = '';
                            }
                            else{
                                //strlen(trim($this->apply_plugin,'\/')) - strlen(trim($skins_holder,'\/')) == strrpos($str,$key);
                            }

                            $temp_skin['path'] = basename(dirname( $skin )).DIRECTORY_SEPARATOR;//assume skin file stored in folder
                            $temp_skin['description'] = !empty($skin_data['Description'])? $skin_data['Description'] : '';
                            $temp_skin['holder'] = $_skins_holder;	//save folder holder any skins that found
                            //if property 'plugin_url' not defined, you should extend this class (HW_SKIN) and define method 'get_ref_plugin_url' to get parent plugin url.
                            if(!isset($temp_skin['holder_url'])) {
                                $temp_skin['holder_url'] = !method_exists($this,$folder_name['url'])? $folder_name['url'] : call_user_func(array($this,$folder_name['url']));
                            }
                            $temp_skin['skin_folder'] = $skin_folder;//($folder_name['folder']? $folder_name['folder'] : $this->skin_folder);	//modified to be fixed problem
                            if(trim($temp_skin['skin_folder'], '\/') == trim($temp_skin['path'], '\/')) {   //valid skin path for only one skin file
                                $temp_skin['path'] = '';
                            }

                            $temp_skin['file_ext'] = $skin->getExtension();  //get file extension
                            $temp_skin['filename'] = basename($skin);
                            $temp_skin['type'] = self::get_skin_type($temp_skin['file_ext']);    //detect file type
                            //$temp_skin['skin_path'] = $temp_skin['holder'].'/'.dirname(  $skin  );	//careful should't expose to someone
                            //$temp_skin['skin_path'] = $temp_skin['holder'].'/'.$temp_skin['skin_folder'].'/'.$temp_skin['path']; //other way
                            if(strcmp(trim($temp_skin['skin_folder'], '\/') , trim($temp_skin['path'], '\/')) == 0) {
                                //skin have no nest on folder
                                $temp_skin['skin_url'] = HW_File_Directory::generate_url($temp_skin['holder_url'],rtrim($temp_skin['skin_folder'],'/'));
                            }
                            else {
                                $temp_skin['skin_url'] = HW_File_Directory::generate_url($temp_skin['holder_url'],rtrim($temp_skin['skin_folder'],'/'), $temp_skin['path']);
                            }    //skin url
                            $temp_skin['screenshot'] = file_exists(dirname(  $skin  ).'/screenshot.png')? 'screenshot.png' : 'screenshot.jpg';
                            $temp_skin['screenshot_url'] = $temp_skin['skin_url'].'/'.$temp_skin['screenshot'];
                            $temp_skin['group'] = $folder_name['group'].'/'.$temp_skin['skin_folder'];

                            $temp_skin = apply_filters('hwskin_first_skin_data', $temp_skin, $folder_name);
                            $skins[] = $temp_skin;  #__print($temp_skin);

                        }

                    }
                } catch ( UnexpectedValueException $e ) { }
            endforeach;
            return $skins;

        }
        /**
         * get template data from file
         * @param $file
         * @param $parent: check if file have no wrap by folder
         * @param $header other template header
         * @return array
         */
        public static function get_template_data($file, $parent, $header = array()) {
            //list accept headers data for skin file
            $headers_info[] = array(
                'name'          => 'Plugin Name',
                'description'   => 'Description',
                'author'        => 'Author',
                'uri'           => 'Author URI',
            );
            if(is_array($header) && count($header)) $headers_info[] =  $header;

            /*$headers = array(     //sample template info
                'name'          => 'HWRP Template',
                'description'   => 'Description',
                'author'        => 'Author',
                'uri'           => 'Author URI',
            );*/
            foreach($headers_info as $header) {
                if(file_exists($file) && !is_dir($file)) $data = get_file_data($file, $header);
                if(!empty($data['name'])) break;
            }

            $data['file'] = $file;
            //get skin name
            if(rtrim(trim(dirname($file)), trim($parent)) != trim(dirname($file)) ) {
                $data['basename'] = basename($file);    //skin file as just one file
            }
            else $data['basename'] = basename(dirname($file));  //this skin file hold on folder

            if (empty($data['name'])) $data['name'] = $data['basename'];

            return $data;
        }
        /**
         * set template header info
         * @param $headers: header data
         */
        public function set_template_header_info($headers){
            if(is_array($headers) && !empty($headers['name'])){
                $this->template_header_info = $headers;
            }
        }
        /**
         * get skins data base on type
         * @param mixed $data: specific type of skins data or give array of skins data that get from 2 method: get_skins|get_files_skins
         */
        private function get_skins_data($data = self::SKIN_FILES){
            //get results from method get_skins or get_files_skins
            if($data == self::SKIN_FILES) $data = $this->myskins();
            elseif($data == self::SKIN_LINKS) $data = $this->get_files_skins();

            if(!is_array($data)){
                ///throw new Exception("Không tìm thấy dữ liệu skins.");
            }
            return $data;
        }
        /**
         * get full skins
         */
        public function myskins(){
            //skin
            $skins = array();

            //default skin ->no longer need
            /*$skins[] = array(
                'name' => __( 'Default Skin','hoangweb' ),
                'path' => self::DEFAULT_SKIN_FOLDER,
                'description' => '',
                'group' => self::DEFAULT_GROUP,
                //'holder' => plugin_dir_path()		//point to curren plugin path
            );*/
            $skins = $this->get_skins();
            return $skins;
        }
        /**
         * generate unique handle name base skin
         * @param $str: addition string to create your handle
         * @param string $hash_skin: either provide hash skin string or get current skin
         */
        public function generate_unique_handle_base_skin($str, $hash_skin = ''){
            if(!empty($hash_skin) && is_string($hash_skin)) {
                $this->set_active_skin($hash_skin);
            }
            $skin_info = $this->get_active_skin_info(); //get current skin info
            if($skin_info) {
                $unique_handle = str_replace(WP_PLUGIN_URL,'',$skin_info[2]);
            }
            else $unique_handle = $this->skin_name;
            $unique_handle .= $str;

            return self::encrypt($this->uniqueID($unique_handle));
        }
        /**
         * encrypt data
         * @param $data
         * @return string
         */
        public static function encrypt($data) {
            return base64_encode(serialize($data));
        }
        /**
         * decrypt data
         * @param $data
         * @return mixed
         */
        public static function decrypt($data) {
            return @unserialize(base64_decode($data)) ;
        }
        /**
         * enqueue stuff from active skin folder
         * @param array $styles
         * @param array $scripts
         * @Param bool $in_footer
         */
        public function enqueue_files_from_skin($styles = array(),$scripts = array(), $in_footer=false){
            //load css files
            if(is_array($styles) && count($styles)){
                foreach ($styles as $style){
                    if(!$style) continue;

                    //$this->instance_uniqueID;     //don't call this so it will be duplicate asset file
                    if(filter_var($style, FILTER_VALIDATE_URL)) $url =  $style;
                    else $url = $this->get_skin_url($style);

                    wp_enqueue_style($this->generate_unique_handle_base_skin($style),$url);
                }
            }
            //load js files
            if(is_array($scripts) && count($scripts)){
                foreach ($scripts as $js){
                    if(!$js) continue;

                    if(filter_var($js, FILTER_VALIDATE_URL)) $url =  $js;
                    else $url = $this->get_skin_url($js);

                    wp_enqueue_script($this->generate_unique_handle_base_skin($js),$url, array(), $in_footer);
                }
            }
        }
        /**
         * do/apply filters and actions
         * @param array $filters
         * @param array $args: pass other params to hook callback
         */
        public function do_filters($filters = array(), $args = array())
        {
            foreach($filters as $filter => $arg){
                if(empty($arg['type'])) $arg['type'] = 'filter'; //default is filter
                if(empty($arg['priority'])) $arg['priority'] = 10;      //priority
                if(empty($arg['function']) || !is_callable($arg['function'])) {     //require function callback to link to filter
                    continue;
                }
                $this->data['do_filters_args_'. $this->uniqueID($arg['function'])] = array('args' => $args, 'function' => $arg['function']);
                if($arg['type'] == 'filter'){   //for filter hook
                    if(!empty($arg['accepted_args'])) {
                        add_filter($filter , array($this, $arg['function']), (int)$arg['priority'], (int)$arg['accepted_args']);
                    }
                    else add_filter($filter , array($this, $arg['function']), (int)$arg['priority']);
                }
                elseif($arg['type'] == 'action'){   //for action hook
                    if(!empty($arg['accepted_args']))  {
                        add_action($filter, array($this,$arg['function']), (int)$arg['priority'], (int)$arg['accepted_args']);
                    }
                    else add_action($filter , array($this, $arg['function']), (int)$arg['priority']);
                }
            }
        }
        /**
         * remove skin hook filters. no longer use
         * @param array $filters
         */
        /*public static function remove_skin_filters($filters = array()){
            foreach($filters as $filter => $arg){
                if(empty($arg['function']) || !is_callable($arg['function'])) continue; //if not exists callback
                if(empty($arg['type'])) $arg['type'] = 'filter';
                if($arg['type'] == 'filter'){
                    remove_filter($filter, $arg['function']);
                }
                elseif($arg['type'] == 'action'){
                    remove_action($filter, $arg['function']);
                }

            }

        }*/
        /**
         * magic function to create dynamic filter callback
         * @param $func
         * @param $params
         */
        function __call($func, $params){
            if(isset($this->data['do_filters_args_'. $this->uniqueID($func)])) {
                $args = $this->data['do_filters_args_'. $this->uniqueID($func) ];
            }
            else $args =  $this->get_active_skin_info();

            $eval = 'return call_user_func($func';
            for($i=0; $i < count($params); $i++){
                $eval .= ',$params['.$i.']';
            }
            $eval .= ',$args);';
            //$func_params = trim($eval,',');  //valid eval

            if(is_callable($func))
                return eval($eval);
        }
        /**
         * return default skin file
         */
        public function get_default_skin_file(){
            $group = $this->group_name? $this->group_name.DIRECTORY_SEPARATOR : '';
            //get relative of default skin file
            $skin_file = HW_File_Directory::generate_path($this->default_skin_path ,$group, self::DEFAULT_SKIN_FOLDER ,$this->skin_name);
            #if(file_exists($skin_file))
                return rtrim($skin_file, '\/');
        }
        /**
         * get current skin file
         * @param string $skin_path: hash skin path
         */
        public function get_skin_file($skin_path = ''){
            self::$current_skin = $this;    //remind current skin object
            //modified
            if($skin_path) $data = $this->set_active_skin($skin_path);
            $skin_info = $this->get_skin_info();
            return $this->get_skin_path() . $skin_info['skin_name'];    //$this->skin_name;
        }
        /**
         * return skin options file
         * @param string $hash_skin_path hash skin string, inherit from get_file_skin_resource method
         * @param bool $extract extract theme options definition
         * @return string
         */
        public function get_file_skin_options($hash_skin_path ='', $extract = false){
            //$skin_path = $this->get_skin_path();  #no, refer to default skin
            if($hash_skin_path && file_exists($hash_skin_path)) {
                $file = $hash_skin_path;
            }
            else {
                $file = $this->get_file_skin_resource( $this->skin_options,$hash_skin_path);
            }
            if($extract == true && file_exists($file)) {
                include ($file);
                return isset($theme_options)? $theme_options : array();
            }
            return $file;
        }
        /**
         * return path to skin setting file, in this case : 'theme-setting.php'
         * @param string $hash_skin_path: inherit from get_file_skin_resource method
         * @param bool $extract extract theme options definition
         * @return string
         */
        public function get_file_skin_setting($hash_skin_path = '', $extract = false){
            if($hash_skin_path && file_exists($hash_skin_path)) {
                $file = $hash_skin_path;
            }
            else {
                $file = $this->get_file_skin_resource('theme-setting.php', $hash_skin_path);
            }
            if($extract == true && file_exists($file)) {
                include ($file);
                return isset($theme)? $theme : array();
            }
            return $file;
        }
        /**
         * return file skin resource path
         * @param $hash_skin: hash skin string
         * @param $file: resource file
         * @return string
         */
        public function get_file_skin_resource($file, $hash_skin = ''){
            if($hash_skin) $this->get_skin_file($hash_skin);   //set active skin
            $skin_path = $this->get_skin_path();
            if($skin_path && file_exists($skin_path.'/'.$file)){
                return rtrim($skin_path,'/').'/'.$file;
            }
        }
        /**
         *
         * @param unknown $skin_path
         * @return string
         */
        public function get_skin_thumb($skin_path){
            if(!$skin_path) return;
            //get skin info from hash
            $skin = $this->get_skin_info($skin_path);
            if(is_array($skin) ) {
                return $skin['thumb'];
            }
        }
        /**
         * get current module info
         * @param $item
         * @return array
         */
        public function get_module_info($item='') {
            static $data;
            $module_slug = preg_replace('#.+?\/#', '', str_replace('\\', '/',trim($this->_apply_plugin, '\/')) );
            if(!isset($data[$module_slug])) {
                $module = HW_Module_Settings_page::get_modules($module_slug);
                $data[$module_slug] = array('slug' => $module_slug, );
                if($module) {
                    $data[$module_slug]['name'] = $module->get_module_info('name');
                    $data[$module_slug]['object'] = $module;
                }
            }

            return $item? (isset($data[$module_slug][$item])? $data[$module_slug][$item] : '') : $data[$module_slug];
        }
        /**
         * get skin info by hash string
         * @param string $skin_path: skin hash string
         * @return array of skin info
         */
        public function get_skin_info($skin_path = ''){
            if($skin_path) $data = $this->set_active_skin($skin_path);
            else $data = $this->get_active_skin_info();
            if(empty($data)) return;

            $info =  array(
                'skin_holder' => $data[0],
                'path' => $data[1],
                'skin_url' =>$data[2],//known as skin url/skin file url
                'type' =>$data[3],
                'name' =>$data[4],
                'thumb' =>($data[3] == 'file' && isset($data[5]))? $data[5] : ($data[3] == 'link'? $data[2]:''),  //either skin screenshot or skin link file
                'source' => end($data)
            );
            if(isset($data[6])) $info['skin_name'] = $data[6];
            else $info['skin_name'] = $this->skin_name;
            //get group
            $holder = preg_split('#[\/\\\]+#', trim($info['skin_holder'],'\/'));
            if(count($holder)==2) $info['group'] = $holder[1];

            //skin tpl
            $info['skin_tpl'] = preg_replace('%\.php$%', '.twig', $info['skin_name']);
            $file = (!empty($info['group'])? $info['group'].'-':'').preg_replace('%\.php$%', '',$info['skin_name']). '-'. trim($info['path'],'\/');
            $file = preg_replace('%[\s]+%', '-', $file);  //truncate space character
            $info['single_skin_tpl'] = $file. '.twig';    //get path instead of skin display name ($info['name'])

            if($info['source'] == 'theme') {
                $info['skin_tpl_path'] = HW_File_Directory::generate_path(get_stylesheet_directory() , 'modules', $this->get_module_info('slug'), $info['single_skin_tpl']);
            }
            elseif($info['source'] == 'plugin') {   //default twig template alway start in tpl/ folder inside the plugin
                $info['skin_tpl_path'] = $info['skin_tpl'];
            }
            elseif($info['source'] == 'wp_content') {
                $info['skin_tpl_path'] = HW_File_Directory::generate_path(WP_CONTENT_DIR, 'hw-modules',$this->get_module_info('slug'), $info['single_skin_tpl']);
            }
            $info['skin_tpl_path'] = trim($info['skin_tpl_path'], '\/');    //valid path
            return $info;
        }
        /**
         * get current skin resource file
         * @param string $skin_path: hash skin path
         * @param bool $relative
         */
        public function get_skin_link($skin_path = '' , $relative=false){
            if(!empty($skin_path)) $this->set_active_skin($skin_path);
            if(is_array($this->active_skin) && isset($this->active_skin[3]) && $this->active_skin[3] == 'link'){
                $file = HW_File_Directory::generate_url($this->active_skin[2], $this->active_skin[4]);
                $source = $this->active_skin[5];
                if($relative) return array($file, $source);
                else {
                    $skin = $this->valid_skin_path(array('file_url'=>$file, 'source'=>$source));
                    return $skin['file_url'];
                }
            }
        }
        /**
         * return skin file url for skin link type
         * @param $hash_skin
         * @return mixed
         */
        public static function get_skin_link_url($hash_skin) {
            if(is_string($hash_skin)) $hash_skin = explode('|', self::decrypt($hash_skin));
            if(!is_array($hash_skin) || count($hash_skin)<6) return ;
            if($hash_skin[3] == 'link') {
                $file = $file = HW_File_Directory::generate_url($hash_skin[2], $hash_skin[4]);
                $source=  $hash_skin[5];
                $skin = self::valid_skin_path(array('file_url' => $file, 'source' => $source));
                return $skin['file_url'];
            }
        }
        /**
         * return skin data from hash string
         * @param $hash_skin: hash skin string
         * @param $type: skin type either 'file' or 'link'
         */
        public function get_skin_data($hash_skin = '', $type = HW_SKIN::SKIN_FILES){
            if(count($this->skins_data) == 0) $this->skins_data = $this->load_skins_data($type);
            if($hash_skin && is_string($hash_skin)){
                if( isset($this->skins_data['skins'][$hash_skin])) return $this->skins_data['skins'][$hash_skin];
            }
            else return $this->skins_data['skins'];
        }
        /**
         * return path to current skin folder
         */
        public function get_active_skin() {
            $active_skin = $this->get_skin_info();
            //current, change save skin as basename folder of skin, so this condition alway come true
            if(is_array($active_skin) && !is_dir($active_skin['path'])) {
                $this->active_skin = rtrim($active_skin['skin_holder'],'\/').DIRECTORY_SEPARATOR.trim($active_skin['path'],'\/')
                    .'/';      //->fixed in method set_active_skin
            }
            /*current, change save skin as basename folder of skin, so this condition alway come true
            if(is_array($this->active_skin) && !is_dir($this->active_skin[1])) {
                $this->active_skin = $this->active_skin[0].'/'//.$this->skin_folder.'/'//.trim(trim($this->active_skin[1],'/'),'\\')
                    .'/';      //->fixed in method set_active_skin
            }*/
            if(is_string($this->active_skin) && (is_dir($this->active_skin) || is_file($this->active_skin))){	//found skin path should be using by include
                return $this->active_skin;
            }
        }
        /**
         * get path to skin folder
         */
        public function get_skin_path() {

            $skin = $this->get_active_skin();

            $default_skin  = $this->default_skin_path . DIRECTORY_SEPARATOR . self::DEFAULT_SKIN_FOLDER .
                DIRECTORY_SEPARATOR;

            if ( $skin == self::DEFAULT_SKIN_FOLDER || ! $this->validate_skin() )
                return  $default_skin;

            return $skin;

        }
        /**
         * return active skin info
         * @return null
         */
        public function get_active_skin_info(){
            return $this->active_skin_info;
        }
        /**
         * get skin url to file
         * @param string $file: file name inside current skin folder
         */
        public function get_skin_url($file = ''){//_print($this->active_skin_info);
            if(is_array($this->active_skin_info) && count($this->active_skin_info)>=3) {
                return rtrim(trim($this->active_skin_info[2/*skin_url*/]),'/').'/'.$file;
            }
        }
        /**
         * this method should call after set active skin
         * get variables for current skin using
         * @return array
         */
        public function get_skin_variables() {
            $data = array();
            $data['url'] = $this->get_skin_url('');
            return $data;
        }
        /**
         * extract plugin name from path
         * @param $path
         * @return string
         */
        protected static function get_apply_plugin_from_path($path) {
            $split = preg_split('#\/#',$path);
            if($split[0] == 'plugins' && count($split)>=2 && is_dir(HW_HOANGWEB_PLUGINS. '/plugins/'. $split[1])) {
                return $split[1];
            }
            return  '';
        }

        /**
         * @param $_path
         * @param $source
         * @param string $type
         */
        public static function get_skin_realpath($_path, $source, $type = self::SKIN_FILES) {
            if(!is_array($_path)) return $_path;
            $path = hwArray::cloneArray( $_path);
            //apply skin for plugin?
            $apply_plugin = '';
            if(isset($_path['apply_plugin'])) $apply_plugin = $_path['apply_plugin'];

            if($type == self::SKIN_FILES) {
                if($source == 'plugin') {
                    if(isset($path['path']) && strpos($path['path'], HW_HOANGWEB_PATH)===false) {
                        if(!$apply_plugin) {    //get plugin name
                            $apply_plugin = self::get_apply_plugin_from_path($path['path']);
                        }

                        $path['path'] = HW_File_Directory::generate_path(HW_HOANGWEB_PATH, $path['path']);
                    }
                    if(isset($path['url']) && !HW_URL::valid_url($path['url'])) {
                        if(!$apply_plugin) {    //get plugin name
                            $apply_plugin = self::get_apply_plugin_from_path($path['url']);
                        }
                        $path['url'] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $path['url']). '/';
                    }
                }
                elseif($source == 'theme') {
                    $theme_source_url = get_stylesheet_directory_uri();
                    $theme_source_path = get_stylesheet_directory();
                    /*if($apply_plugin) {   //no need because i added in get_skins method
                        $theme_source_url .= '/modules/'. $apply_plugin;
                        $theme_source_path .=  '/modules/'. $apply_plugin;
                    }*/

                    if(isset($path['path']) && strpos($path['path'], get_stylesheet_directory())===false) {
                        $path['path'] = HW_File_Directory::generate_path($theme_source_path, $path['path']);
                    }
                    if(isset($path['url']) && !HW_URL::valid_url($path['url'])) {
                        $path['url'] = HW_File_Directory::generate_url($theme_source_url, $path['url']). '/';
                    }
                }
                elseif($source =='wp_content') {
                    if(isset($path['path']) && strpos($path['path'], WP_CONTENT_DIR)===false) {
                        $path['path'] = HW_File_Directory::generate_path(WP_CONTENT_DIR,/*'hw-modules', $apply_plugin,*/$path['path']);
                    }
                    if(isset($path['url']) && !HW_URL::valid_url($path['url'])) {
                        $path['url'] = HW_File_Directory::generate_url(WP_CONTENT_URL, /*'hw-modules',$apply_plugin,*/ $path['url']). '/';
                    }
                }
            }
            return (object)$path;
        }
        /**
         * set active skin
         * @param string $skin_path: hash skin path generate by method self::generate_skin_path
         */
        public function set_active_skin( $skin_path ) {
            HW_HOANGWEB::load_class('HW_File_Directory');
            if(!is_string($skin_path)) return ; //invalid skin path

            if(self::is_default_skin($skin_path)){
                $this->active_skin = array('',$skin_path);
                return;
            }
            else {
                $skin_path=explode('|', self::decrypt($skin_path));
            }
            if(empty($skin_path) || count($skin_path)<7) return null;
            /*if ( mb_substr( $skin_path[1], -1 ) !== DIRECTORY_SEPARATOR ) =>fixed in method get_skins|get_files_skins
                $skin_path[1] .= DIRECTORY_SEPARATOR;*/
            $source = end($skin_path);
            if( $skin_path[3] =='file') {
                if($source == 'plugin') {
                    if(/*!realpath($skin_path[0])*/strpos($skin_path[0],HW_HOANGWEB_PATH) ===false) {
                        $skin_path[0] = HW_File_Directory::generate_path(HW_HOANGWEB_PATH, $skin_path[0]);
                    }
                    if(!HW_URL::valid_url($skin_path[2])) {
                        $skin_path[2] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $skin_path[2]). '/';
                    }
                    if(!HW_URL::valid_url($skin_path[5])) {
                        $skin_path[5] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $skin_path[5]);
                    }
                }
                elseif($source == 'theme') {    //skin from theme path
                    //no need because i added in get_skins method
                    $theme_source_url = get_stylesheet_directory_uri();//. '/modules/'.$this->get_module_info('slug');
                    $theme_source_path = get_stylesheet_directory();//. '/modules/'. $this->get_module_info('slug');

                    if(/*!realpath($skin_path[0])*/strpos($skin_path[0],get_stylesheet_directory()) ===false) {
                        $skin_path[0] = HW_File_Directory::generate_path($theme_source_path, $skin_path[0]);
                    }
                    if(!HW_URL::valid_url($skin_path[2])) {
                        $skin_path[2] = HW_File_Directory::generate_url($theme_source_url, $skin_path[2]). '/';
                    }
                    if(!HW_URL::valid_url($skin_path[5])) {
                        $skin_path[5] = HW_File_Directory::generate_url($theme_source_url, $skin_path[5]);
                    }
                }
                elseif($source =='wp_content') {
                    if(/*!realpath($skin_path[0])*/strpos($skin_path[0],WP_CONTENT_DIR) ===false) {
                        $skin_path[0] = HW_File_Directory::generate_path(WP_CONTENT_DIR, /*'hw-modules',$this->get_module_info('slug'),*/$skin_path[0]);
                    }
                    if(!HW_URL::valid_url($skin_path[2])) {
                        $skin_path[2] = HW_File_Directory::generate_url(WP_CONTENT_URL,/*'hw-modules',$this->get_module_info('slug'),*/ $skin_path[2]). '/';
                    }
                    if(!HW_URL::valid_url($skin_path[5])) {
                        $skin_path[5] = HW_File_Directory::generate_url(WP_CONTENT_URL,/*'hw-modules',$this->get_module_info('slug'),*/ $skin_path[5]);
                    }
                }
            }
            else {  //nothing for for skin link

            }
            $this->active_skin = $skin_path;  //save active skin info & path

            //only save skin info for skin file
            if(is_array($skin_path) && isset($skin_path[3]) && $skin_path[3] == 'file') {
                $this->active_skin_info = $this->active_skin;
            }

            return $this->active_skin;
        }
        /**
         * validate skin
         * @param string $skin: skin tobe valid
         */
        private function validate_skin( $skin = NULL ) {

            if ( ! $skin )
                $skin = $this->get_active_skin();

            if ( self::is_default_skin( $skin ) )
                return TRUE;

            $exists_skin = false;
            /*if(is_array($this->allows_skin_name)){    //since we accept more skin file by regex pattern, this no longer in use
                foreach($this->allows_skin_name as $skin_name)
                    if (  file_exists( $skin . $skin_name ) )   //$this->skin_name//
                        $exists_skin = true;
            }*/

            return true;    //$exists_skin;
        }
        /**
         * check whether is default skin
         * @param string $skin: check  whether is default theme
         */
        public static function is_default_skin( $skin ) {

            $default_skins = array( self::DEFAULT_SKIN_FOLDER );

            if (is_string($skin) &&  (in_array( $skin , $default_skins ) || strpos($skin,self::DEFAULT_SKIN_FOLDER) !== false))
                return TRUE;

            return FALSE;

        }
        /**
         * convert array to attribute string
         * @param unknown $atts
         * @return string
         */
        public static function array2attrs($atts=array()){
            $str= '';
            foreach($atts as $key=>$val){
                $str.=$key.'="'.$val.'" ';
            }
            return trim($str);
        }
        /**
         * set skin preview container
         * @param $holder: HTML id element
         */
        private function set_skin_preview_id($holder){
            if(is_string($holder)) $this->skins_preview_id = $holder;
        }
        /**
         * return skins preview holder
         * @return null
         */
        public function get_skin_preview_id(){
            return $this->skins_preview_id;
        }
        /**
         * parse skins preview html
         * @param $skin_value: active hash skin string
         */
        public function get_skins_preview_screen($skin_value){
            if(!$this->get_skin_preview_id() ) return;   //make sure render skins selector durring call this method or invoke after
            //preview skin
            $out = '<span class="hw-skin-preview" id="'.$this->get_skin_preview_id().'">';
            if(is_string($skin_value) && !empty($this->skins_data['skins'][$skin_value])) {
                $out .= '<img onError="this.onerror=null;this.src=\''.self::get_image('error.jpg').'\';" src="'.$this->skins_data['skins'][$skin_value]['screenshot'].'"/>';
            }
            $out .= '</span>';
            return $out;
        }
        /**
         *
         * @param array $opts: ddslick js options
         */
        public function set_dropdown_ddslick_setting($opts = array()){
            if(!is_array($opts)) return;
            $default_settings = array(
                'width'=>'260',
                'height'=>'400',
                'background'=>'#eee',
                'imagePosition'=>'left'
            );
            foreach ($default_settings as $opt=>$def_value){
                if(isset($opts[$opt])) $default_settings[$opt] = $opts[$opt];
            }
            $this->ddslick_settings = $default_settings;   //update ddslick_settings
        }
        /**
         * generate skins selector by images
         * @param string $name: skin field name
         * @param string $value: active skin value
         * @param array $skins: skins data
         * @param array $atts: attributes data for input field
         */
        //->removed
        public function get_images_skins_chooser($name,$value='', $skins = null, $atts = array()){
            //if(!isset($this->skins_data))
            $this->skins_data = $this->load_skins_data(self::SKIN_LINKS);
            $widget = $this->widget_ref;	//get current widget
            //valid name
            $holder = 'holder-'.$name;
            if(is_object($widget) && $widget instanceof WP_Widget){
                $fname = $widget->get_field_name($name); //get field name if context is widget
                $holder = $widget->get_field_id('holder-'.$name);      //container id
            }
            else $fname =$name;

            if(!$skins) $skins= $this->get_files_skins();	//get all skins
            //checkbox attributes
            $atts['onclick']= "hw_skin_{$this->skin_obj_name()}.skin_change_event(this.value)";

            $w_instance = self::get_widget_instance($widget);      //get widget
            //get current skin
            if($w_instance && isset($w_instance[$name])) $value = $w_instance[$name];    //alway get skin from widget

            $out = '<div class="'.$holder.' hw-skin-list-container">';
            //get active skin info
            $active_skin = $this->get_skin_info($value);
            if($active_skin){
                $thumb = '<img src="'.$active_skin['thumb'].'" class="hw-skin-current-thumb"/>';   //current skin thumb
                $out .= '<div><table><tr><td valign="middle">'.$thumb.'</td><td valign="middle"><strong>'.$active_skin['name'].'</strong></td></tr></table></div>';
            }
            $out .= '<div class="hwk-skins-list">';
            $out .= '<table class="list-skin-files" width="100%">';
            if(is_array($skins)){
                foreach ( $skins as $skin ):
                    $skin_path = HW_SKIN::generate_skin_file($skin);
                    //populate group
                    if(!isset($skin['group'])) $skin['group'] = self::DEFAULT_GROUP;	//default skin in default group
                    if(!isset($last_group)) $last_group = null;

                    if($last_group !== $skin['group'] ) {
                        if($last_group){
                            $out .= '</td></tr>';	//close group
                            $close_group = 1;
                        }
                        $out .= '<tr><td colspan="3"><div class="group" ><strong>'.$skin['group'].'</strong>';
                        $close_group = 0;
                        $last_group = $skin['group'];	//remind new group
                    }
                    if(!$last_group) $last_group = $skin['group'];	//save first group
                    $checked = ($value == $skin_path)? 'checked="checked"' : '';    //focus on current skin
                    $class = ($value == $skin_path)? 'hw-skin-current':'';
                    //generate file url
                    $skin = $this->valid_skin_path($skin);

                    $out .= '<tr class="'.$class.'"><td valign="middle" class="skin"><label><input '.self::array2attrs($atts).' type="radio" name="'.$fname.'" '.$checked.' value="'.$skin_path.'"/></label></td>';
                    $out .= '<td valign="middle" class="thumb"><img class="hw-skin-tooltip" src="'.$skin['file_url'].'"/><div class="hw-skin-hidden"><img src=\''.$skin['file_url'].'\'/></div></td>';      //image
                    $out .= '<td valign="middle">'.$skin['name'].'</td>';   //skin name
                    $out .= '</tr>';

                endforeach;
            }
            else $out .= sprintf(__('Không tìm thấy skin files của %s.Note: %s khai báo thư mục chứa các file skin vào biến (->files_skin_folder)')
                ,($this->is_main? 'skin chính':$this->skinID)
                ,(($this->is_main || !$this->files_skin_folder)? 'bạn cần gọi từ skin phụ,':'Bạn cần'));
            $out .= '</table>';
            $out .= '</div>';
            $out .= '</div>';
            echo $out;
        }
        /**
         * valid skin holder path for plugin
         * @param string $apply_plugin
         * @param array $skin
         */
        public static function valid_holder_path($apply_plugin='', $skin = array()) {
            //for plugin source
            if($skin['source'] == 'plugin') {
                //first to create absolute path
                if(!isset($skin['holder']) || !file_exists($skin['holder'])) {
                    if(strlen($apply_plugin) - strlen(HW_HOANGWEB_PLUGIN_NAME) !== strrpos($apply_plugin,HW_HOANGWEB_PLUGIN_NAME) ) {
                        $skin['holder'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS , $apply_plugin);
                        $skin['holder_url'] = HW_File_Directory::generate_url(HW_HOANGWEB_PLUGINS_URL , $apply_plugin);
                    }
                    else {//refer to major plugin
                        $skin['holder'] = $skin['holder_url']='';
                    }
                }
                //make relative path
                if($skin['holder']) $skin['holder'] = ltrim(str_replace(realpath(HW_HOANGWEB_PATH), '', realpath($skin['holder'])), '\/');
                if($skin['holder_url']) $skin['holder_url'] = ltrim(str_replace(HW_File_Directory::generate_url(HW_HOANGWEB_URL), '', HW_File_Directory::generate_url($skin['holder_url'])), '\/');
            }
            elseif($skin['source'] == 'theme' ) {   //for theme source
                $skin['holder'] = $skin['holder_url'] = '';
            }
            elseif($skin['source'] == 'wp_content') {   //from wp-content source
                $skin['holder'] = $skin['holder_url'] = '';
            }
            return $skin;
        }
        /**
         * valid skin url & path
         * @param $skin
         */
        public static function valid_skin_path($skin ) {
            if(isset($skin['apply_plugin'])) $apply_plugin = $skin['apply_plugin'];//self::current()->_apply_plugin;
            //from plugin
            if($skin['source'] == 'plugin') {
                if(isset($skin['file_path']) && !file_exists($skin['file_path'])) {
                    if(empty($apply_plugin)) $apply_plugin = self::get_apply_plugin_from_path($skin['file_path']);
                    $skin['file_path'] = rtrim(HW_File_Directory::generate_path(HW_HOANGWEB_PATH, $skin['file_path']),'\/');
                }
                if(isset($skin['file_url']) && !HW_URL::valid_url($skin['file_url'])) {
                    if(empty($apply_plugin)) $apply_plugin = self::get_apply_plugin_from_path($skin['file_url']);
                    $skin['file_url'] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $skin['file_url']);
                }
                if(isset($skin['skin_url']) && !HW_URL::valid_url($skin['skin_url'])) {
                    if(empty($apply_plugin)) $apply_plugin = self::get_apply_plugin_from_path($skin['skin_url']);
                    $skin['skin_url'] = HW_File_Directory::generate_url(HW_HOANGWEB_URL, $skin['skin_url']);
                }

            }
            //from theme
            elseif($skin['source'] == 'theme') {
                $theme_source_path = get_stylesheet_directory();
                $theme_source_url = get_stylesheet_directory_uri();
                /*if(!empty($apply_plugin)) {   //no need i added in get_skins method
                    $theme_source_path .= '/modules/'. $apply_plugin;
                    $theme_source_url .= '/modules/'. $apply_plugin;
                }*/

                if(isset($skin['file_path']) && !file_exists($skin['file_path'])) {

                    $skin['file_path'] = rtrim(HW_File_Directory::generate_path($theme_source_path, $skin['file_path']),'\/');
                }
                if(isset($skin['file_url']) && !HW_URL::valid_url($skin['file_url'])) {

                    $skin['file_url'] = HW_File_Directory::generate_url($theme_source_url, $skin['file_url']);
                }
                if(isset($skin['skin_url']) && !HW_URL::valid_url($skin['skin_url'])) {
                    $skin['skin_url'] = HW_File_Directory::generate_url($theme_source_url, $skin['skin_url']);
                }
            }
            //from wp-content folder
            elseif($skin['source'] == 'wp_content') {
                if(isset($skin['file_path']) && !file_exists($skin['file_path'])) {
                    $skin['file_path'] = rtrim(HW_File_Directory::generate_path(WP_CONTENT_DIR,/*'hw-modules',*/ $skin['file_path']),'\/');
                }
                if(isset($skin['file_url']) && !HW_URL::valid_url($skin['file_url'])) {
                    $skin['file_url'] = HW_File_Directory::generate_url(WP_CONTENT_URL,/*'hw-modules',*/ $skin['file_url']);
                }
                if(isset($skin['skin_url']) && !HW_URL::valid_url($skin['skin_url'])) {
                    $skin['skin_url'] = HW_File_Directory::generate_url(WP_CONTENT_URL,/*'hw-modules',*/ $skin['skin_url']);
                }
            }
            return $skin;
        }
        /**
         * generate skins options tag
         * @param string $current_skin: current skin
         * @param string $theme: try 2 theme 'simple|dropdown_ddslick', with ddslick lib you will see nice dropdown with image
         * @param string $skins: what type of skin data type need to fetch, default is skin files (.php)
         */
        public function generate_skin_options_tag($current_skin = '', $skins = self::SKIN_FILES, $theme = self::DROPDOWN_DDSSLICK_THEME){
            $skins = $this->get_skins_data($skins);  #$this->widget_ref->skin->myskins();->wrong	//get all skins first
            //if(!isset($this->skins_data))
            $this->skins_data = $this->load_skins_data($skins);	//load skin data

            $out = '';	//output
            $last_group = 0;	//group skins
            $option_atts = array();
            $count = 0;   //count items
            $selected_index = 0;  //get selected index

            //if($empty_option) $out .= '<option value="">------Chọn ------</option>';  //empty option, wrong because need to pass through method generate_skin_file
            foreach ( $skins as $skin ):
                if($skin['type'] == 'file') $skin_path = HW_SKIN::generate_skin_path($skin);
                else $skin_path = HW_SKIN::generate_skin_file($skin);

                //populate group
                if(!isset($skin['group'])) $skin['group'] = self::DEFAULT_GROUP;	//default skin in default group
                //style option tag
                if($theme == self::DROPDOWN_DDSSLICK_THEME){
                    $skin = $this->valid_skin_path($skin);
                    if($skin['type'] == 'file') $option_atts['data-imagesrc'] = $skin['skin_url'].'/'.$skin['screenshot'];
                    else $option_atts['data-imagesrc'] = $skin['file_url'];

                    $option_atts['data-description'] = 'Folder: '.$skin['group'];
                }

                if( $last_group !== $skin['group'] ) {
                    if($last_group){
                        $out .= '</optgroup>';	//close group
                        $close_group = 1;
                    }
                    $out .= '<optgroup label="'.$skin['group'].'">';
                    $close_group = 0;
                    $last_group = $skin['group'];	//remind new group
                }
                if(!$last_group) $last_group = $skin['group'];	//save first group
                //active item
                if($skin_path == $current_skin){
                    $selected_index = $count;
                }
                $out.='<option '.self::array2attrs($option_atts).' value="'.$skin_path.'" '.selected( $skin_path, $current_skin ,false).'>'.$skin['name'] .'</option>';
                $count++;
            endforeach;

            if(!isset($close_group) || !$close_group) $out .= '</optgroup>';	//note you should close group after all
            return array(
                'options' => $out,
                'selectedIndex' => $selected_index,
                'skins_data' => $this->skins_data,
                'skins' => $skins
            );
        }
        /**
         * return reference widget instance
         * @param unknown $widget: widget object link to this class
         */
        static function get_widget_instance($widget){
            if(!($widget instanceof WP_Widget)){
                return ;    //invalid WP_Widget object
            }
            global $wp_registered_widgets;
            if(isset($wp_registered_widgets[$widget->id]) && $widget->widget_options['classname'] !== $widget->id) {

                $widget_obj = $wp_registered_widgets[$widget->id];
                $widget_num = $widget_obj['params'][0]['number'];
                if(is_array($widget_obj['callback'])) $option_name = $widget_obj['callback'][0]->option_name;
                //detect if widget_logic installed
                elseif(isset($widget_obj['callback_wl_redirect'])){
                    $option_name = $widget_obj['callback_wl_redirect'][0]->option_name;
                }
                if(isset($option_name)) $widget_opt = get_option($option_name);
                else return;

                /*if(is_string($widget_obj['callback']) && is_callable($widget_obj['callback'])) {  //not -sure
                    //$widget = call_user_func_array($widget_obj['callback'], $widget_obj['params']);
                }*/
                $w_instance = $widget_opt[$widget_num];
            }
            else{
                $w_instance = get_option($widget->widget_options['classname']);
                $w_instance = isset($w_instance[$widget->number])? $w_instance[$widget->number] : array();
            }
            return $w_instance;
        }

        /**
         * generate skins select tag
         * @param $wfname: skin name
         * @param $value: current skin hash string
         * @param array $atts: array format key-pair present HTML attributes
         * @param string $theme: support skin dropdown with image or either using simple with large screenshot. Accept: 'dropdown_ddslick','simple'
         * @param mixed $data: input data, accept name of data in string want to fetch or array generated by methods get_skins|get_files_skins
         */
        public function get_skins_select_tag($wfname,$value='',$atts=array(),$theme = self::DROPDOWN_DDSSLICK_THEME, $data = self::SKIN_FILES){
            $widget = $this->widget_ref;	//get current widget
            //if(!is_object($widget)) return; //validate
            //put lib to style dropdown
            if($theme == self::DROPDOWN_DDSSLICK_THEME)
            {
                //wp_enqueue_script(self::DROPDOWN_DDSSLICK_THEME);
                HW_Libraries::enqueue_jquery_libs('components-ui/dropdown_ddslick');
            }

            if(is_object($widget) && $widget instanceof WP_Widget){   //if whether object reference of widget
                $preview_holder = $widget->get_field_id($this->instance_uniqueID('skin_preview')); //preview holder id
                if($wfname) $name = $widget->get_field_name($wfname);
                elseif(isset($atts['name'])) $name = $widget->get_field_name($atts['name']);    //get key 'name' from $atts

                if($wfname) $id = $widget->get_field_id($wfname);
                elseif(isset($atts['id'])) $id = $widget->get_field_id($atts['id']);    //get key 'id' from $atts
            }
            else{
                $preview_holder = $this->instance_uniqueID('skin_preview');
                if($wfname) $name = $wfname;
                elseif(isset($atts['name'])) $name = $atts['name'];

                if($wfname) $id = $this->uniqueID($wfname);
                elseif(isset($atts['id'])) $id = $this->uniqueID($atts['id']);
            }
            //set skin preview holder
            $this->set_skin_preview_id($preview_holder);

            //set attributes requirement
            if(!is_array($atts)) $atts = array();
            if(!isset($atts['name'])) $atts['name'] = $name;  //you can define attr 'name' with $atts param
            if(!isset($atts['id']) && !empty($id)) $atts['id'] = /*self::get_instance()*/self::valid_objname($id);  //override id attribute if not exists in $atts
            //override onchange event
            $atts['onchange'] = "hw_skin_{$this->skin_obj_name()}.skin_change_event(this.value,'".$this->get_skin_preview_id()."')";


            //get stored data of widget that link to this skin maker
            /*if(count($this->data())){
                $w_instance = $this->data();
            }*/
            $w_instance = self::get_widget_instance($widget);
            if(!$value && isset($w_instance[$wfname])) $value = $w_instance[$wfname];    //get current skin hash string

            $out = '';
            if(is_object($widget->skin)){
                //if(!isset($this->skins_data)) $this->skins_data = $this->load_skins_data();	//detect call by ajax when you save widget instance->nested in method 'generate_skin_options_tag'
                //get skins data, note: $skins= $widget->skin->myskins(); ->wrong
                $data = $this->get_skins_data($data);

                $last_group = 0;	//group skins
                //build attrs & select tag
                $options_tag = $this->generate_skin_options_tag($value, $data,$theme);

                $out .= '<select '.self::array2attrs($atts).'>';
                $out .= $options_tag['options'];
                $out .= '</select>';

                if($theme !== self::DROPDOWN_DDSSLICK_THEME ) {	//display current skin screenshot
                    if($this->custom_skins_preview == false) $out .= $this->get_skins_preview_screen($value);
                }

                if($theme == self::DROPDOWN_DDSSLICK_THEME){

                    $out .= '<input type="hidden" '.self::array2attrs($atts).' value="'.$value.'"/>'; //hiden field to save choose skin by user
                    $out .= '<script>jQuery(document).ready(function($){
			            if('.(count($options_tag['skins'])? 1:0).'){
			             $("#'.$atts['id'].'").ddslick({
			                 onSelected:function(data){
			                     var active_skin = data.selectedData.value;
			                     
			                     $("input#'.$atts['id'].'").val(active_skin);
			                     /*add skin change event*/        
			                     hw_skin_'.$this->skin_obj_name().'.skin_change_event(active_skin,"'.$preview_holder.'");            
			                 },
			                 "height":"'.$this->ddslick_settings['height'].'",
			                 "width":"'.$this->ddslick_settings['width'].'",
			                 "background":"'.$this->ddslick_settings['background'].'",
			                 "imagePosition":"'.$this->ddslick_settings['imagePosition'].'",                                            
			             });
			             $("#'.$atts['id'].'").ddslick("select",{index:'.$options_tag['selectedIndex'].'});
			               }
			     });</script>';
                }
            }
            else  {
                $out.= 'class "HW_SKIN" not exists.';
            }
            return $out;
        }
        /**
         * @param $name
         * @param string $value
         * @param array $atts
         */
        public function get_skin_template_condition_selector($wfname,$value='',$atts=array()) {
            if(!class_exists('HW_Condition')) return 'Error: class HW_Condition not exists, please active module `Condition`.';

            $widget = $this->widget_ref;	//get current widget
            if(is_object($widget) && $widget instanceof WP_Widget){   //if whether object reference of widget

                if($wfname) $name = $widget->get_field_name($wfname);
                elseif(isset($atts['name'])) $name = $widget->get_field_name($atts['name']);    //get key 'name' from $atts

                if($wfname) $id = $widget->get_field_id($wfname);
                elseif(isset($atts['id'])) $id = $widget->get_field_id($atts['id']);    //get key 'id' from $atts
            }
            else{
                if($wfname) $name = $wfname;
                elseif(isset($atts['name'])) $name = $atts['name'];

                if($wfname) $id = $this->uniqueID($wfname);
                elseif(isset($atts['id'])) $id = $this->uniqueID($atts['id']);
            }
            //set attributes requirement
            if(!is_array($atts)) $atts = array();
            if(!isset($atts['name'])) $atts['name'] = $name;  //you can define attr 'name' with $atts param
            if(!isset($atts['id'])) $atts['id'] = self::valid_objname($id);  //override id attribute if not exists in $atts

            $w_instance = self::get_widget_instance($widget);
            if(!$value && isset($w_instance[$wfname])) $value = $w_instance[$wfname];    //get current skin hash string

            //select options
            $dynamic_settings = HW_Condition::get_active_conditions_settings();

            $out = '<p>';
            $out .= '<label for="'.$atts['id'].'">'.__('Lựa chọn điều kiện sử dụng giao diện', 'hw-skin').'</label><br/>';
            $out .= '<select '.self::array2attrs($atts).'>';
            $out .='<option value="">--- Chọn ---</option>';
            foreach($dynamic_settings as $id => $item){
                $out .= sprintf('<option %s value="%s">%s</option>', selected($value, $id, false), $id, $item['title']);
            }
            $out .= '</select>';
            $out .= '</p>';

            return $out;
        }
        /**
         * list all skins with thumbnail
         * @param string $name: skin field name
         * @param string $value: current skin by hash string, default get widget skin field if current context is widget (optional)
         * @param array $skins: skins data (optional)
         * @param array $atts: attributes data for input field
         */
        function get_skins_listview($name,$value='', $skins = self::SKIN_FILES, $atts = array()){
            $skins= $this->get_skins_data($skins);	//get all skins
            //if(!isset($this->skins_data))
            $this->skins_data = $this->load_skins_data($skins);

            $widget = $this->widget_ref;	//get current widget
            //valid name
            $holder = 'holder-'.$name;
            if(is_object($widget) && $widget instanceof WP_Widget){    //if whether object reference of widget instance
                $fname = $widget->get_field_name($name); //get field name if context is widget
                $holder = $widget->get_field_id('holder-'.$name);      //container id
            }
            else {
                $fname =$name;
                $holder = $this->uniqueID('holder-'.$name);
            }

            //checkbox attributes
            $atts['onclick']= "hw_skin_{$this->skin_obj_name()}.skin_change_event(this.value)";

            $w_instance = self::get_widget_instance($widget);      //get widget instance
            //get current skin
            if($w_instance && isset($w_instance[$name])) $value = $w_instance[$name];    //first get skin from widget

            $out = '<div class="'.$holder.' hw-skin-list-container" >';
            //get active skin info
            $active_skin = $this->get_skin_info($value);#__print($active_skin);
            if($active_skin){
                $thumb = '<img src="'.$active_skin['thumb'].'" class="hw-skin-current-thumb"/>';   //current skin thumb
                $out .= '<div><table><tr><td valign="middle">'.$thumb.'</td><td valign="middle"><strong>'.$active_skin['name'].'</strong></td></tr></table></div>';
            }
            $out .= '<div class="hwk-skins-list">';
            $out .= '<table class="list-skin-files" width="100%">';
            if(is_array($skins)){
                foreach ( $skins as $skin ):
                    $skin_path = HW_SKIN::generate_skin_path($skin);
                    $skin = $this->valid_skin_path($skin);  //valid skin file path
                    //populate group
                    if(!isset($skin['group'])) $skin['group'] = self::DEFAULT_GROUP;	//default skin in default group
                    if(!isset($last_group)) $last_group = null;

                    if($last_group !== $skin['group'] ) {
                        if($last_group){
                            $out .= '</td></tr>';	//close group
                            $close_group = 1;
                        }
                        $colspan = apply_filters('HW_SKIN.get_skins_listview.header_colspan', 3, $skin);     //modify colspan for table
                        $out .= '<tr ><td colspan="'.$colspan.'"><div class="group" ><strong>'.$skin['group'].'</strong>';
                        $close_group = 0;
                        $last_group = $skin['group'];	//remind new group
                    }
                    if(!$last_group) $last_group = $skin['group'];	//save first group
                    $checked = ($value == $skin_path)? 'checked="checked"' : '';    //focus on current skin
                    $screenshot = HW_File_Directory::generate_url($skin['skin_url'],$skin['screenshot']);        //present skin screenshot
                    $class= ($value == $skin_path)? 'hw-skin-current':'';

                    $out .= '<tr class="'.$class.'"><td valign="middle" class="skin"><label><input '.self::array2attrs($atts).' type="radio" name="'.$fname.'" '.$checked.' value="'.$skin_path.'"/></label></td>';
                    $out .= '<td class="thumb" ><img class="hw-skin-tooltip" title="" src="'.$screenshot.'"/><div class="hw-skin-hidden"><img src=\''.$screenshot.'\'/></div></td>';  //skin thumb
                    $out .= '<td>'.$skin['name'].'</td>';      //skin name
                    $out .= apply_filters('HW_SKIN.get_skins_listview.column', '',$skin);    //custom column
                    $out .= '</tr>';

                endforeach;
            }
            else $out .= __('Không tìm thấy skins');
            $out .= '</table>';
            $out .= '</div>';
            $out .= '</div>';
            echo $out;
        }
        /**
         * @param $name
         * @param $value
         * @param array $atts
         * @param array $shows
         * @example $value['hash_skin']
         */
        public function create_total_skin_selector($name, $value, $atts = array(), $shows= array('show_main_skin' => true)) {
            $my_attrs = array(
                'name' => "{$name}[hash_skin]",
                #'class'=>
            );
            if(is_array($atts) && count($atts)) {
                $my_attrs = array_merge($atts, $my_attrs);
            }
            //require attributes
            if(!isset($my_attrs['class'])) $my_attrs['class'] = 'hw-skin-selector';

            //hash skin value
            $hash_skin = isset($value['hash_skin'])? $value['hash_skin'] : '';
            $skin_condition = isset($value['hwskin_condition'])? $value['hwskin_condition'] : '';   //skin template condition
            $skin_settings = isset($value['skin_options'])? $value['skin_options'] : '';  //skin options
            $out = '';  //output

            //skin select tag
            if(isset($shows['show_main_skin']) && $shows['show_main_skin']) {
                $out .= $this->get_skins_select_tag(null, $hash_skin, $my_attrs,false);
            }

            //skin options
            if(isset($shows['show_config_field']) && $shows['show_config_field']) {
                $out .= $this->create_config_hiddenfield($name, $value);
            }

            //template condition for skin consuming
            #$my_attrs['name'] = "{$name}[hwskin_condition]";
            if(!empty($shows['show_condition_field'])) {
                $out .= $this->create_skin_template_condition_field($name, $value, $my_attrs);
            }
            //show skin options
            if(!empty($shows['show_skin_options']) /*&& $skin_settings*/ ) {
                $out .= $this->prepare_skin_options_fields('skin_options', $skin_settings, $hash_skin, $name);
            }

            return $out;
        }
        /**
         * create config hidden field for easying resume state
         * @param $field_name
         * @param $skin_value
         */
        public function create_config_hiddenfield($field_name, $skin_value = array()) {
            //get skin config value
            if(!empty($skin_value) && isset($skin_value['hwskin_config']) ) {
                $hwskin_config = $skin_value['hwskin_config'];
            }
            else $hwskin_config = $this->get_config(true);

            return '<input type="hidden" name="'.$field_name.'[hwskin_config]" value="'.esc_attr($hwskin_config).'"/>';
        }
        /**
         * @param $field_name
         * @param array $skin_value
         * @param $atts select tag attributes
         */
        public function create_skin_template_condition_field($field_name, $skin_value=array(), $atts = array()) {
            if(!empty($skin_value) && isset($skin_value['hwskin_condition']) ) {
                $hwskin_condition = $skin_value['hwskin_condition'];
            }
            else $hwskin_condition = '';
            //merge attributes for select tag
            $attrs = array('name' => $field_name.'[hwskin_condition]');
            if(is_array($atts)) $attrs = array_merge($atts, $attrs);

            return $this->get_skin_template_condition_selector($field_name, $hwskin_condition, $attrs);
        }
        /**
         * resume hw_skin instance from this field type value
         * moving from `APF_hw_skin_Selector_hwskin` class
         * @param $apf_hwskin_aValue
         */
        public static function resume_hwskin_instance($apf_hwskin_aValue = array()){
            if(!is_array($apf_hwskin_aValue)) return;   //an array of 2 keys: 'hash_skin', 'hwskin_config' created by this field type
            $return = array();

            //skin metadata
            $return = array_merge($return, $apf_hwskin_aValue);

            if(isset($apf_hwskin_aValue['hash_skin'])) {
                $hash_skin = $apf_hwskin_aValue['hash_skin'];
                $return['hash_skin'] = $hash_skin;
            }
            if(isset($apf_hwskin_aValue['hwskin_config'])) $skin_config = $apf_hwskin_aValue['hwskin_config'];

            if(isset($skin_config) && $skin_config ){
                $skin = HW_SKIN::resume_skin($skin_config);
                $return['instance'] = $skin;
                //$return['skins_data'] = $skin->get_skin_data($hash_skin);
                //$return['hash_skin'] = $hash_skin;
                $return['skin_config'] = $skin_config;  //i know it nerver in use later

            }
            return (object) $return;
        }
        /**
         * generate field name
         * @param $name: field name
         */
        private function get_field_name($name) {
            $widget = $this->widget_ref;	//get current widget
            //valid name
            if(is_object($widget) && $widget instanceof WP_Widget){    //if whether object reference of widget instance
                $fname = $widget->get_field_name($name); //get field name if context is widget
            }
            else {
                $fname =$name;
            }
            return $fname;
        }
        /**
         * generate field id
         * @param $name
         * @return string
         */
        private function get_field_id($name) {
            $widget = $this->widget_ref;	//get current widget
            //valid name
            if(is_object($widget) && $widget instanceof WP_Widget){    //if whether object reference of widget instance
                $fname = $widget->get_field_id($name); //get field name if context is widget
            }
            else {
                $fname = $name;
            }
            //valid object name
            $fname = class_exists('HW_Validation')? HW_Validation::valid_objname($fname) : $this->valid_objname($fname);
            return $fname;
        }

        /**
         * @param string $hash_skin: active hash skin string
         * @param $fname file name
         * @param $base_fname: base field name
         * @param $options_value: store all options values
         * @param $hash_skin: current skin hash
         */
        public function prepare_skin_options_fields($fname, $options_value = '', $hash_skin = '', $base_fname='') {
            //if($hash_skin)  $this->set_active_skin($hash_skin);
            $theme_options = array();

            $option_file = $this->get_file_skin_options($hash_skin);
            $theme_setting = $this->get_file_skin_setting();
            if(file_exists($theme_setting)) {
                include ($theme_setting);
                if(isset($theme['js-libs']) ) {
                    if(is_array($theme['js-libs'])) $lib = trim(reset($theme['js-libs']));
                    elseif(is_string($theme['js-libs'])) $lib = trim($theme['js-libs']);
                    //fetch options from lib
                    if(!empty($lib) && HW_Libraries::get($lib)) {
                        $lib_options = HW_Libraries::get($lib)->get_lib_options(true);
                        if(is_array($lib_options) && count($lib_options)) $theme_options = array_merge($theme_options, $lib_options); //valid
                    }
                }
                //fetch default options
                if(isset($theme['default_options'])) {
                    if(!empty($theme['default_options']) && is_string($theme['default_options'])
                        && file_exists($theme['default_options'])) {
                        $def_options = $this->get_file_skin_options($theme['default_options'], true);
                        if(is_array($def_options) && count($def_options)) $theme_options = array_merge($theme_options, $def_options);
                    }
                }
                if(file_exists(dirname(dirname($theme_setting)). '/options.php')) {
                    $auto_options = $this->get_file_skin_options(dirname(dirname($theme_setting)). '/options.php', true);
                    if(is_array($auto_options) && count($auto_options)) $theme_options = array_merge($theme_options, $auto_options);
                }
            }

            //get document
            $readme = $this->get_file_skin_resource('readme.html');//_print($readme);
            if(file_exists($readme)) $readme_link = $this->get_skin_url('readme.html');

            $notice_skin_option = 'Vui lòng nhấn save để tùy chỉnh options của theme này nếu có.';  //skin options notice
            //get field name/id
            if(!empty($this->widget_ref) && $this->widget_ref instanceof WP_Widget) {
                $myfield_id = $this->widget_ref->get_field_id($fname);
                $myfield_name = $this->widget_ref->get_field_name($fname);
            }
            else{
                $myfield_id =  !empty($base_fname)? $base_fname.'_'.$fname : $fname;
                $myfield_name = !empty($base_fname)? $base_fname.'['.$fname.']' : $fname;
            }

            $theme_options_output = array();

            if(file_exists($option_file)) {
                include($option_file);      //load skin config options
            }
            //prepend default skin options
            if(!isset($theme_options)) $theme_options = array();
            self::default_skin_options($theme_options);

            if(isset($theme_options) && is_array($theme_options)) {
                $skin_data = $this->get_skin_data($hash_skin);  //get active skin data
                $parse_id = isset($skin_data['screenshot'])? md5($skin_data['screenshot']) : md5(rand());

                $theme_options_output[] = '<hr/><div class="hw-skin-options-holder" data-collapse="accordion persist" id="'.$parse_id.'">';
                $theme_options_output[] = '<h2>Tùy biến skin</h2><div class="skin-options">'; //toggle title
                //create fields
                foreach($theme_options as $_field){
                    $theme_options_output[] = $this->renderOptionField(
                        $_field,
                        $myfield_name,
                        $myfield_id,
                        $options_value
                    );

                }
                $theme_options_output[] = '</div></div>';     //close parse_id div tag
                $theme_options_output[] = '<div class="hw-skin-options-notice" style="display: none;color:red !important;">'.$notice_skin_option.'</div>';
            }

            $msg_id = 'hwskin_msg_'.$myfield_id;    //div message id
            $theme_options_output[] = '<div class="message hw-skin-options-notice" id="'.$msg_id.'"></div>';
            if(isset($readme_link)) $theme_options_output[] = '<a href="'.$readme_link.'" target="_blank">Xem tài liệu</a>';

            if(isset($parse_id)){
                $hwskin_event_change_name = 'hwskin_field_'.$this->valid_objname($myfield_id) .'_'.$parse_id.'_change_event';  //change event callback name
                $this->saveCallbackJs4SkinChangeEvent("if(typeof {$hwskin_event_change_name} == 'function') ".$hwskin_event_change_name.'(skin);');
                //$this->skin->saveCallbackJs4SkinChangeEvent('$("#'.$skin_config_field_id.'").val(skin.screenshot);');  //call this before render skins selector

                $theme_options_output[] = '<script>
                        function '.$hwskin_event_change_name.'(skin){
                            console.log(skin,"'.$parse_id.'");
                            if(skin.md5 === "'.$parse_id.'"){
                                jQuery("#'.$parse_id.'").removeClass("hw-skin-options-none").show();jQuery("#'.$parse_id.'").next().hide();
                                jQuery("#'.$parse_id.'").find("input,select").removeAttr("disabled").css({"display":""});
                            }
                            else{
                                jQuery("#'.$parse_id.'").addClass("hw-skin-options-none").hide();jQuery("#'.$parse_id.'").next().show();
                                jQuery("#'.$parse_id.'").find("input,select").attr({"disabled":"disabled"}).css("display","none");
                            }

                        }
                        </script>
                    ';
            }
            else $this->saveCallbackJs4SkinChangeEvent('jQuery("#'.$msg_id.'").html("'.$notice_skin_option.'");');

            return implode("\n", $theme_options_output);
        }
        /**
         * merge skin option values
         * @param array $user_theme_options user skin options data
         * @param array $default  default option define at theme-setting.php
         * @param array $theme_options options file (options.php) in current skin folder
         */
        public static function merge_skin_options_values($user_theme_options, $default, $_theme_options) {
            //parse default skin options from skin setting file (theme-setting.php)
            if(is_string($default) && !empty($default) && file_exists($default)) {
                include($default);
                if(isset($theme) && is_array($theme) && isset($theme['options'])) {
                    $default = $theme['options'];
                }
            }
            //include theme options (options.php)
            if(is_string($_theme_options) /*&& !empty($default)*/ && file_exists($_theme_options)) {
                include ($_theme_options);
            }

            //valid
            if(! is_array($default)) $default = array();
            if(isset($theme_options) && ! is_array($theme_options)) $theme_options = array();

            //- note that: skin options value either set in options file or skin file or both. But get options from theme setting for default.
            //merge skin options with persistent options in skin theme setting file:
            if( isset($theme_options) ){
                //$default = isset($theme['options']) ? $theme['options'] : array();

                $result = HW_SKIN::get_skin_options($user_theme_options, $default, $theme_options);
                $user_theme_options = array_merge($user_theme_options, $result);
            }
            //valid data, here is example of removing unused key
            if(isset($user_theme_options['template_file'])) unset($user_theme_options['template_file']) ;

            return $user_theme_options ;
        }

        /**
         * final skin options data
         * @param array $user_options: user setting
         * @param array $default: default setting
         * @param array $theme_options: define options in file options.php
         */
        public static function get_skin_options ($user_options = array(), $default = array(), $theme_options = array()){
            $skin_options_config = hwskin_parse_theme_options($theme_options);
            $args =  array();
            //validation
            foreach($skin_options_config as $option) {
                if($option['type'] == 'checkbox' ) {
                    if(!isset($user_options[$option['name']]) || strcmp($user_options[$option['name']] , 0) ===0 ) {
                        $default[$option['name']] = empty($option['value'])? '__FALSE__' : '__TRUE__';
                    }
                    else $user_options[$option['name']] = '__TRUE__';
                }
            }
            //extract wp_nav_menu_args from skin file
            if( is_array($default)){
                //$args = array_merge($args, $theme['args']);
                foreach($default as $arg => $val){
                    if(isset($args[$arg])) $args[$arg] .= (!empty($args[$arg])? ' ' : '') . $val;     //append (redundant)
                    else $args[$arg] = $val;    //create if not exists
                }
            }
            //hope $args share to all remain menu filters in order to render final output menu to user
            foreach($user_options as $arg => $value){
                $field_setting = isset($skin_options_config[$arg])? $skin_options_config[$arg] : array();
                if(!isset($args[$arg])) $args[$arg] = $value;   //set menu args from skin

                //append if exists setting
                if(isset($field_setting['method']) && $field_setting['method'] == 'append' ){
                    //from file options value
                    if(!empty($field_setting['value']) && !in_array($field_setting['value'], preg_split('#[\s]+#', $args[$arg]))) {
                        $args[$arg] .= (!empty($args[$arg])? ' ':''). trim($field_setting['value']);
                    }
                    if(trim($value) && !in_array($value, preg_split('#[\s]+#', $args[$arg]))) {
                        $args[$arg] .= (!empty($args[$arg])? ' ':'').trim($value);
                    }

                }
                //override setting if not exists
                if(isset($field_setting['method']) && $field_setting['method'] == 'override' && !empty($value)){    //mean user override value
                    $args[$arg] = $value;
                }
            }
            return $args;
        }
        /**
        get error image
        @param $file: image file name with ext
         */
        static function get_image($file){
            return plugins_url('images/'.$file,__FILE__);
        }
    }
endif;
?>