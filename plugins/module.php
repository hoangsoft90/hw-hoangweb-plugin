<?php
#/root>includes/hoangweb-core.php
//load classes
HW_HOANGWEB::load_class('HW_Validation');

/**
 * Interface HW_Module_implement
 */
interface HW_Module_implement {
    /**
     * export module data as json/xml data type
     * @return mixed
     */
    public function export();
    /**
     * @hook wp_enqueue_scripts
     * @return mixed
     */
    public function enqueue_scripts();

    /**
     * @hook admin_enqueue_scripts
     * @return mixed
     */
    public function admin_enqueue_scripts();

    /**
     * @hook wp_head
     * @return mixed
     */
    public function print_head();

    /**
     * @hook wp_footer
     * @return mixed
     */
    public function print_footer();

    /**
     * add form fields for module settings page
     * @return mixed
     */
    public function replyToAddFormElements($oAdminPage );

    /**
     * after the module loaded
     * @return mixed
     */
    public function module_loaded();

    /**
     * after all modules loaded
     * @return mixed
     */
    public function modules_loaded();
    /**
     * This hook is run immediately after any plugin is activated when module loaded
     * @return mixed
     */
    public function activated_plugin();
    /**
     * triggered before rendering the page.
     * @return mixed
     */
    public function do_before_tab_hook();

    /**
     * triggered in the middle of rendering the page.
     * @return mixed
     */
    public function do_tab_hook();

    /**
     * triggered after rendering the page
     * @return mixed
     */
    public function do_after_tab_hook();

    /**
     * receives the output of the top part of the page.
     * @return mixed
     */
    public function head_tab_filter();

    /**
     * receives the output of the middle part of the page including form input fields.
     * @return mixed
     */
    public function content_tab_filter($content);

    /**
     *  receives the output of the bottom part of the page.
     * @return mixed
     */
    public function foot_tab_filter();

    /**
     * receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     * @return mixed
     */
    public function validation_tab_filter($values);

    /**
     * receives the output of the CSS rules applied to the tab page of the slug.
     * @return mixed
     */
    public function print_styles();

    /**
     * receives the output of the JavaScript script applied to the tab page of the slug.
     * @return mixed
     */
    public function print_scripts();

    /**
     * receives the exporting array sent from the tab page.
     * @return mixed
     */
    public function export_tab_filter();

    /**
     * receives the importing array submitted from the tab page.
     * @return mixed
     */
    public function import_tab_filter();

}


/**
 * Interface HW_Module_Field_Interface
 */
interface HW_Module_Field_Interface {
    /**
     * @param $name
     * @return mixed
     */
    public function create_full_field_name($name);

    /**
     * @param $name
     * @param string $value
     * @return mixed
     */
    public function get_field_value($name, $value='');

    /**
     * @return mixed
     */
    public function get_values();

    /**
     * @param $tab
     * @return mixed
     */
    public function add_tab( $tab);

    /**
     * @return mixed
     */
    public function before_fields();

    /**
     * @return mixed
     */
    public function after_fields();
}
/**
 * Class HW_Module_Field
 */
class HW_Module_Field extends HW_APF_Field_Page implements HW_Module_Field_Interface{
    //public static $instance = null;
    /**
     * section
     * @var null
     */
    public $tab = null;
    /**
     * AdminPageFramework
     * @var null
     */
    public $oAdminPage = null;

    public function __construct($section_tab) {
        #parent::__construct();
        $this->tab = $section_tab;
    }

    /**
     * add new tab
     * @param AdminPageFrameWork $oAdminPage (depricated)
     * @param array $tab
     * @return tab id
     */
    public function add_tab( $tab, $page_slug = HW_Module_Settings_page::PAGE_SLUG) {
        if(!isset($tab['id']) || !isset($tab['title'])) return;
        #if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;

        if(!isset($tab['description'])) $tab['description'] = 'Module '. $this->get_module_name(). ', tab: '. $tab['title'];
        $section = array(
            'section_tab_slug' => 'module_'. $this->module_name .'_setting_tab',
        );

        foreach($tab as $key => $val) {
            if ($key == 'id'){
                $section['section_id'] = $this->module_name.'_' .$tab['id'];
            } else
                $section[$key] = $val;
        }
        #$this->oAdminPage->addSettingField($tab['id']); //group, this allow you to place field in tab or non-tab
        if(!empty($this->oAdminPage) && $this->oAdminPage instanceof AdminPageFramework) {
            $this->oAdminPage->addSettingSections($page_slug , $section);
        }

        $obj = new self($section);//new HW_Module_tab($tab);

        #$obj = $this->castAs($obj);
        $obj->oAdminPage = $this->oAdminPage;
        $obj->module_name = $this->module_name;
        $this->tabs[$tab['id']] = $obj; //add to manager
        return $obj;
    }


    /**
     * generate field name for current module tab
     * @param $name
     * @return string
     */
    public function create_field_name($name) {
        return HW_Validation::valid_apf_slug($this->module_name) . "_". HW_Validation::valid_apf_slug($name);
        #return  $name . "[{$name}]";
    }
    /**
     * list allow fields type for the module
     * @param $fields
     */
    public function _support_fields($fields) {
        HW_APF_FieldTypes::apply_fieldtypes($fields, 'HW_Module_Settings_page');
    }
    /**
     * get real field name
     * @param $name
     * @return mixed
     */
    public function  real_field_name($name) {
        $prefix = HW_Validation::valid_apf_slug($this->module_name);
        return preg_replace('#^'.$prefix.'_#', '', trim($name));
    }
    /**
     * create full field name for current module
     * @param $name
     * @return string
     */
    public function create_full_field_name($name) {
        return 'HW_Module_Settings_page['.$this->create_field_name($name).']';
    }
    /**
     * get field value for current module
     * @param array|string $name
     * @param $value
     * @return array|mixed|null|void
     */
    public function get_field_value($name, $value='') {
        if(!empty($this->tab)) {
            $fields = HW_Module_Settings_page::get_field_value( $this->tab['section_id']);
            return $fields && isset($fields[$name])? $fields[$name] : $value;
        }
        return HW_Module_Settings_page::get_field_value($this->create_field_name($name), $value);
    }

    /**
     * get all fields values
     * @return array|mixed|null|void
     */
    public function get_values() {
        if(!empty($this->tab)) {
            $fields = HW_Module_Settings_page::get_field_value( $this->tab['section_id']);
            return $fields;
        }
        $values = HW_Module_Settings_page::get_values();
        return $this->pure_fields_result($values);
    }


    /**
     * init fields for module setting page
     * @param AdminPageFramework $oAdminPage (depricated)
     */
    public function after_fields() {
        if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;

        if($this->_get_option('enable_submit_button')) {
            //add submit button
            $this->add_submit_buttons();
        }
    }
    /**
     * before user fields
     */
    public function before_fields() {

    }
}
/**
 * @Class HW_Module
 */
abstract class HW_Module extends HW_Module_Field implements HW_Module_implement{
    /**
     * @var array
     */
    protected $tabs = array();
    /**
     * config box page
     * @var null
     */
    public $config = null;
    /**
     * options data
     * @var array
     */
    protected $__options = array();

    /**
     * parent class constructor
     */
    public function __construct() {
        $this->support_fields('hw_help');
        #add_action('wp_loaded', array($this, '_plugins_loaded'), 100);HW_SESSION::__save_session('abcxx',array('construct'=>1),1);
        add_action('activated_plugin', array($this, '_detect_plugin_activation'),10 ,2);
        add_action( 'deactivated_plugin', array($this, '_detect_plugin_deactivation'), 10, 2 );

    }

    /**
     * register module
     */
    final public static function register() {
        $module_class = get_called_class();
        if(method_exists($module_class, 'init')) add_action('hw_modules_load', array($module_class, 'init'));
    }
    /**
     * reset caches configuration
     */
    protected static function reset_caches() {
        HW_Cache::reset_wp_menus_caches();
        //..
    }
    /**
     *  runs when any plugin is activated
     * @param $plugin
     * @param $network_activation
     * @hook activated_plugin
     */
    public function _detect_plugin_activation(  $plugin, $network_activation ) {
        //$wp_hooks = /*array_unique*/(HW_Setup::_get_static_option('hooks_detected', array()) );HW_SESSION::__save_session('abcxx',array('hooks'=>count($wp_hooks)),1);
        self::reset_caches();
        if(method_exists($this, 'activated_plugin') /*&& in_array('activated_plugin', $wp_hooks*/) {
            $this->activated_plugin();
        }
    }
    /**
     *  runs when any plugin is deactivated
     * @param $plugin
     * @param $network_activation
     * @hook deactivated_plugin
     */
    public function _detect_plugin_deactivation(  $plugin, $network_activation ) {
        //$config = $this->get_config();
        self::reset_caches();

        if(method_exists($this, 'deactivated_plugin') ) {
            $this->deactivated_plugin();
        }
    }
    /**
     * init module
     */
    final public static function init() {
        $class = get_called_class();
        if($class) {
            $inst  = new $class();
            if(!$inst instanceof HW_Module) return;
            //get file name where a Class was Defined
            $reflector = new ReflectionClass($class);
            //path variables
            $inst->option('module_path' , dirname($reflector->getFileName()));
            $module_slug = (basename($inst->option('module_path')));
            $inst->option('module_name' , $module_slug);

            $modules = HW_TGM_Module_Activation::get_register_modules();
            if(isset($modules[$module_slug])) {
                $info = array_filter(HW_Plugins_Manager::get_module_info($reflector->getFileName() ));    //migrate module information
                if(!empty($info)) {
                    $modules[$module_slug] = array_merge($modules[$module_slug], ($info));
                }
                HW_Plugins_Manager::register_module($modules[$module_slug]);    //update module data
                $inst->option('module_info', $modules[$module_slug]);
            }

            $inst->option('module_url',  HW_HOANGWEB_PLUGINS_URL.'/'.$module_slug);

            HW_Module_Settings_page::add_module($module_slug, $inst);
            //setup actions belong to current module instance
            self::setup_actions($inst);
            //occur after all modules loaded
            add_action('hw_modules_loaded', array($inst, 'modules_loaded'));

            //after the module loaded
            if(method_exists($inst, 'module_loaded')) {
                $inst->module_loaded();
            }

            //load cli class for the module
            $command = $inst->get_module_cli_path();
            if (!empty($command) && defined( 'WP_CLI' ) && WP_CLI ) {
                include_once ($command);
                $cli_class = 'HW_CLI_' .HW_Validation::valid_objname($module_slug) ;   //command line class
                WP_CLI::add_command( $module_slug, strtoupper($cli_class));
                //WP_CLI::get_command(); #wrong
                //add module commands to manager
                HW_CLI_Command_Utilities::get_instance()->register_cli_cmds($module_slug, HW_Core::get_child_class_methods($cli_class, ReflectionMethod::IS_PUBLIC));
            }
            //load oher cli class for module
            $cli_files = $inst->get_commands();
            if(is_array($cli_files))
            {
                foreach($cli_files as $cli) {
                    //not found file to process command line,
                    if(!$cli ) continue;
                    //!isset($cli['file']) purpose for module->register_cli that more cli class write in one file
                    if(isset($cli['file'])) include_once($cli['file']);
                    if(class_exists($cli['class'], false)) {
                        WP_CLI::add_command($cli['command'], $cli['class']);
                        //add module commands to manager
                        HW_CLI_Command_Utilities::get_instance()->register_cli_cmds($cli['command'], HW_Core::get_child_class_methods($cli['class'], ReflectionMethod::IS_PUBLIC));
                    }
                }
            }
        }
    }

    /**
     * get export class path
     */
    public function get_module_exporter_path() {
        $path = $this->option('module_path');   //module path
        $exporter_file = $this->option('module_exporter');
        if(empty($exporter_file) ) {

            if( file_exists($path. '/cli/module-exporter.php')) {
                $exporter_file = $path. '/cli/module-exporter.php';
            }
            elseif(file_exists($path .'/module-exporter.php')) {
                $exporter_file = $path. '/module-exporter.php';
            }
            elseif(file_exists($path . '/includes/module-exporter.php')) {
                $exporter_file = $path . '/includes/module-exporter.php';
            }
        }

        return $exporter_file ;
    }

    /**
     * get module export object
     */
    public function get_module_exporter() {
        if(HW_Config_Module_Section::get_module_configs('exporter', $this->option('config_file'))) {
            return HW_Config_Module_Section::get_module_configs('exporter', $this->option('config_file'));
        }
        $file = $this->get_module_exporter_path();
        if($file && file_exists($file) && !is_dir($file)) {
            include_once ($file);
            $export_class = strtoupper(HW_Validation::valid_objname($this->option('module_name')) ). '_Exporter';   //export class
            if(class_exists($export_class, false)) {
                return new $export_class($this);
            }
        }

    }
    /**
     * get module cli definition
     * @return string
     */
    public function get_module_cli_path() {
        $command = '';
        //load cli class for the module
        if(file_exists($this->option('module_path') . '/command.php')) {
            $command = $this->option('module_path') . '/command.php';
        }
        elseif(file_exists($this->option('module_path'). '/includes/class-cli-'. $this->option('module_name'). '.php')) {
            $command = $this->option('module_path'). '/includes/class-cli-'. $this->option('module_name'). '.php';
        }
        elseif(file_exists($this->option('module_path'). '/cli/class-cli-'. $this->option('module_name'). '.php')) {
            $command = $this->option('module_path'). '/cli/class-cli-'. $this->option('module_name'). '.php';
        }
        return $command;
    }

    /**
     * get module cli config
     * @return string
     */
    public function get_module_cli_config() {
        $path = $this->option('module_path');
        if(file_exists($path . '/config.xml')) $config = $path . '/config.xml';
        elseif(file_exists($path . '/cli/config.xml')) $config = $path . '/cli/config.xml';

        if(!empty($config)) {
            $dom = new HW_XML_Module_Config_Parser($config);
            return $dom;
        }
    }

    /**
     * get all modules configuration files
     * @param $item
     * @return array
     */
    public function get_module_wxr_files($item = '') {
        $path = $this->option('module_path');
        $plugin_slug = $this->option('module_name');
        $paths = array();
        $current_theme = get_stylesheet_directory();

        //export data
        if(file_exists($current_theme . '/modules/'. $plugin_slug. '/data.xml')) {  //from theme
            $paths['export'] = $current_theme . '/modules/'. $plugin_slug. '/data.xml';
        }
        elseif(file_exists($current_theme. '/config/modules/'. $plugin_slug.'.xml') ) {
            $paths['export'] = $current_theme. '/config/modules/'. $plugin_slug.'.xml';
        }
        elseif(file_exists($path. '/data.xml')) $paths['export'] = $path. '/data.xml';  //from plugin
        elseif(file_exists($path. '/cli/data.xml')) $paths['export'] = $path. '/cli/data.xml';
        //skins data
        if(file_exists($path .'/cli/skins.xml')) $paths['skins'] = $path .'/cli/skins.xml';

        return $item? (isset($paths[$item])? $paths[$item] : '') :$paths;
    }
    /**
     * return module installation script
     * @return string
     */
    public function get_module_setup_script() {
        $path = $this->option('module_path');
        if(file_exists($path . '/setup.bat')) $script = $path . '/setup.bat';
        elseif(file_exists($path . '/cli/setup.bat')) $script = $path . '/cli/setup.bat';

        if(isset($script)) return $script;
    }

    /**
     * add module to position
     * @param null $content_callback
     */
    public function add_to_position($content_callback=null) {
        $this->option('enable_position', true);
        $this->option('render_module', $content_callback);
    }

    /**
     * render module content by callback
     * @return string
     */
    public function get_module_content_cb() {
        $content_callback = $this->option('render_module');
        if($content_callback && is_callable($content_callback)) return $content_callback;
        return create_function(uniqid(),'');
    }
    /**
     * module loaded event
     * @return mixed|void
     */
    public function module_loaded(){

    }

    /**
     * when all modules loaded
     * @return mixed|void
     */
    public function modules_loaded() {

    }
    public function activated_plugin(){}
    /**
     * current module actived ?
     * @return bool
     */
    public static function is_active($module='') {
        if(!$module) {
            $current = self::get();
            $module = $current->module_name;    //parent module
        }
        return hw_is_active_module($module);
    }
    /**
     * return current module instance
     * @return mixed
     */
    public static function get() {
        $class = get_called_class();
        return HW_Module_Settings_page::get_modules($class);
    }

    /**
     * get module instance by slug or class name
     * @param $name
     * @param bool $actived
     * @return array|bool
     */
    public static function get_module_by_name($name, $actived=false) {
        $module = HW_Module_Settings_page::get_modules($name);
        if($actived ==false || ($actived && self::is_active($name))) {
            return $module;
        }
        return false;
    }
    /**
     * add/get option
     * @param $name
     * @param $value
     * @param bool $merge_array
     */
    public function option($name, $value = '', $merge_array = false) {
        if(is_string($name) && ($value!=='' && $value!==null)){
            if($merge_array) {
                $old = isset($this->__options[$name])? $this->__options[$name] : '';    //get old value
                $old = (array) $old;
                if(!is_array($value) ) $value = (array) $value; //valid value
                $new = array_merge($old, $value);
            }
            else $new = $value;
            $this->__options[$name] = $new;#_print($name.'='.$value);
        }

        return isset($this->__options[$name])? $this->__options[$name] : '';
    }

    /**
     * set option
     * enable tab setting
     */
    public function enable_tab_settings() {
        $this->option('enable_tab_settings', true);
    }

    /**
     * enable configuration box
     * @param $file
     * @param $visible
     */
    public function enable_config_page($file, $visible = true) {
        $this->option('enable_config_page', true);
        $this->option('visible_config_page', $visible);

        #$configs = HW_Config_Module_Section::add_config('modules_configs');
        #if(empty($configs)) $configs = array();

        $path = $this->option('module_path') ;
        //load module config class
        if(!file_exists($file)) {
            $file = $path. '/' .preg_replace('#\.php$#','', trim($file)) . '.php';
        }
        if(file_exists($file) && !is_dir($file)) {
            include_once ($file);
            HW_Config_Module_Section::add_config(realpath($file), $this );
        }

    }

    /**
     * set module exporter file
     * @param $file
     */
    public function set_exporter($file) {
        $path = $this->option('module_path') ;
        if(!file_exists($file)) {
            $file = $path. '/' .preg_replace('#\.php$#','', trim($file)) . '.php';
        }
        if(!is_dir($file) && file_exists($file)) {
            $this->option('module_exporter', $file);
            include_once ($file);
        }
    }
    /**
     * return config instance for the module
     * @return null
     */
    public function get_config() {
        if(empty($this->config)) {
            $configs = HW_Config_Module_Section::get_config();
            foreach($configs as $config) {
                if(isset($config['module']) && $config['module'] == $this && isset($config['config'])) {
                    $this->config = $config['config'];
                }
            }
        }
        return $this->config;
    }
    /**
     * return tab object
     * @param $id
     * @return null
     */
    public function get_tab($id) {
        if(!isset($this->tabs[$id])) {
            $this->add_tab(array('id'=> $id, 'title' => ''));
        }
        return isset($this->tabs[$id])? $this->tabs[$id] : null;
    }

    /**
     * list allow fields type for the module
     * @param $fields
     */
    public function support_fields($fields) {
        HW_APF_FieldTypes::apply_fieldtypes($fields, 'HW_Module_Settings_page');
    }
    /**
     * whether in current module setting page
     * @return bool
     */
    public function is_module_setting_page() {
        if(HW_HOANGWEB::is_current_screen(HW_Module_Settings_page::PAGE_SLUG)
            && hw__get('tab') == HW_Module_Settings_page::valid_tab_slug($this->option('module_name')) )
        {
            return true;
        }
        return false;
    }

    /**
     * register help for module
     * @param $file
     * @param $name
     * @param $path help path
     * @return mixed|void
     */
    public function register_help($name, $file = '', $path = 'help') {
        $name = preg_replace('#\.php$#', '', trim($name));  //help name
        if($file === '') $file = $name.'.html'; //default help file same as help handling file

        if(class_exists('HW_HELP')) {
            HW_HELP::set_helps_path($name, $this->module_path. '/'. ltrim($path, '\/'));
            HW_HELP::set_helps_url($name, $this->get_module_file_url($path)  );
            HW_HELP::register_help($name);
            $help = HW_HELP::load_module_help($name);
            $this->option('help_handler', array('class' => get_class($help), 'file' => $file));
            if(is_object($help)) {
                $help->_option('module', $this);
            }
        }
    }

    /**
     * register cli command
     * @param $file
     * @param $command
     * @param $class
     */
    public function register_cli($file, $command = '', $class='') {
        static $commands = array();

        if($file && !file_exists($file)) {
            $name = rtrim($file, '.php'); //remove extension
            if(!$command || is_numeric($command) ) {
                $command = $name;
            }
            $file = trim(HW_File_Directory::generate_path($this->option('module_path'), 'cli/class-cli-'.$name.'.php'),'\/');

        }
        elseif(!is_dir($file) && file_exists($file) && preg_match('#class-cli-*#', basename($file))){
            $fname = HW_File_Directory::split_filename($file);
            if(!$command) $command = str_replace('class-cli-', '', $fname);   //extract command name base file name
        }
        if(!is_dir($file) && file_exists($file) && $command && !isset($commands[$command])) {   //make sure commands is not same
            if(!$class) $class = 'HW_CLI_HW_'. ucwords(HW_Validation::valid_objname($command)); //desire command class name

            $this->option('cli_files', array($command => array(
                'command' =>$command,
                'path' => $file,
                'class' => $class
            )), true);
            $commands[$command] = 1;
        }
    }

    /**
     * get avaiable cli commands for the module
     */
    public function get_commands() {
        $cmds = $this->option('cli_files');
        if(!is_array($cmds)) $cmds = array();

        /*foreach($cmds as $item) {

        }*/
        return $cmds;
    }
    /**
     * check avaiable command for the module
     * @param $cmd_name
     */
    public function exist_command($cmd_name) {
        if($cmd_name == $this->option('module_name')) return true;  //refer to main command
        $commands = $this->get_commands();
        foreach($commands as $cmd) {
            if($cmd && $cmd['command'] === $cmd_name) {
                return true;
            }
        }
        return false;
    }
    /**
     * get main menu box for current module
     * @return mixed
     */
    public function &get_main_menu_box() {
        $main_boxes = &HW_Module_Settings_page::get_modules_menu_boxes();
        if(isset($main_boxes[$this->option('module_name')])) {
            return $main_boxes[$this->option('module_name')];
        }
    }
    /**
     * add menu box
     * @param $page_title
     * @param $capability
     * @param $menu_slug
     * @param $icon_url
     * @param $url
     * @return string
     */
    public function add_menu_box($icon_url, $url = '#',$capability = 'manage_options', $page_title = '' ,$menu_slug='') {
        //valid
        if(empty($capability)) $capability = 'manage_options';
        if(empty($page_title)) $page_title = $this->get_module_info('name');
        if(empty($menu_slug)) $menu_slug = $this->option('module_name');

        $menu_id = HW_Module_Settings_page::add_menu_box($this->option('module_name'), array(
            'title' => $page_title,
            'page_title' => $page_title,
            'capability' => $capability,
            'menu_slug' => $menu_slug,
            'icon_url' => $icon_url,
            'link' => $url
        ));
        return $menu_id;
    }

    /**
     * add submenu box
     * @param $parent_slug
     * @param $url
     * @param $capability
     * @param string $page_title
     * @param string $menu_slug
     */
    public function add_submenu_box($parent_slug,$url, $page_title ,$capability = 'manage_options', $menu_slug='') {
        //valid
        if(empty($capability)) $capability = 'manage_options';
        if(empty($menu_slug)) $menu_slug = sanitize_title($page_title).'-'.$this->option('module_name');

        $menu_id = HW_Module_Settings_page::add_menu_box($this->option('module_name'), array(
            'title' => $page_title,
            'page_title' => $page_title,
            'capability' => $capability,
            'menu_slug' => $menu_slug,
            'link' => $url
        ), $parent_slug);
        return $menu_id;
    }

    /**
     * fix submenus for module box icon
     * @param $parent_box_id
     */
    protected function migrate_submenus_module_box($parent_box_id= '') {
        static $parent;
        if(empty($parent)) $parent = &$this->get_main_menu_box();  //main module menu box
        if($parent_box_id=='') {
            $parent_box_id = $parent['id'];
        }
        //add submenu box for module icon
        $list_menus = $this->get_other_module_submenus();#_print($list_menus);

        if(!empty($list_menus)) {
            //fix parent menu
            if(isset($list_menus['parent'][6])  ) {  //find plugin icon
                if(empty($parent['icon_url'])   //get first icon
                    && HW_Validation::hw_valid_url($list_menus['parent'][6])) {
                    $parent['icon_url'] = $list_menus['parent'][6];
                }
                elseif(empty($parent['classes'])) {
                    $parent['classes'] = array($list_menus['parent'][6], 'hw-dashicons', 'dashicons');
                    $parent['icon_url']= '';    //remove icon image
                }
            }

            //add submenus
            foreach($list_menus['sub'] as $id=>$item) {
                if($id==0 && (!trim($parent['link']) || trim($parent['link'])=='#')) {  //valid menu link
                    $parent['link'] = $item[2];
                }
                $this->add_submenu_box($parent_box_id, $item[2], $item[0]); //id, url, title
            }
        }
    }
    /**
     * add other wp menu page created by this module
     * @param $page_slug
     * @param $parent_box_id
     * @param bool $is_submenu
     */
    protected function other_wp_menus_page($slugs, $parent_box_id = '', $is_submenu = false) {
        $parent = &$this->get_main_menu_box();  //main module menu box
        if($parent_box_id=='') {
            $parent_box_id = $parent['id'];
        }
        #$args = func_get_args();
        foreach ((array)$slugs as $page_slug) {
            HW_Module_Settings_page::other_menu_page($this->option('module_name'), array('slug'=>$page_slug,'parent_box' => $parent_box_id), $is_submenu);

        }
        $this->migrate_submenus_module_box($parent_box_id);
    }
    /**
     * add other menu page created by this module
     * @param $page_slug
     * @param $parent_box_id
     */
    public function other_menus_page($slugs, $parent_box_id = '') {
        $this->other_wp_menus_page($slugs, $parent_box_id, true);
    }
    /**
     * add other sub menu page created by this module
     * @param $page_slugs
     * @param $parent_box_id
     */
    public function other_submenus_page($page_slugs, $parent_box_id='') {
        $this->other_wp_menus_page($page_slugs, $parent_box_id, false);
    }
    /**
     * get other module submenus
     */
    protected function get_other_module_submenus() {
        $module_slug = $this->option('module_name');
        $sub_menus = HW_HOANGWEB::get_wp_option('other_modules_submenus');
        if(isset($sub_menus[$module_slug]['sub']) && is_array($sub_menus[$module_slug]['sub'])) {
            foreach($sub_menus[$module_slug]['sub'] as &$menu_item) {
                $menu_item = HW_Module_Settings_page::valid_custom_submenu($menu_item);
            }
            return $sub_menus[$module_slug];
        }

    }

    /**
     * get current module name
     * @return string
     */
    public function get_module_name() {
        $class = get_called_class();
        return $class;
    }

    /**
     * return module info
     * @param $key
     * @return mixed
     */
    public function get_module_info($key = '') {
        $info = $this->option('module_info');
        if(is_array($info)  ) return isset($info[$key])? $info[$key] : null;
    }
    /**
     * enqueue script file
     * http://core.trac.wordpress.org/browser/trunk/wp-includes/functions.wp-scripts.php#L195
     * @param $file
     * @param $dependencies
     * @param $handle
     * @return mixed|void
     */
    public function enqueue_script($file, $dependencies = array(), $handle='') {
        //valid
        if(!is_array($dependencies)) $dependencies = array();
        $module = $this->module_name;   //$this->get_module_name(); //module slug: $this->module_name
        //handle script name
        if(!$handle) $handle = (self::get_file_name($file));
        $handle = md5('hw-module-'.$module.'-'. ($handle));
        if(!wp_script_is($handle, 'queue')) {   #http://wordpress.stackexchange.com/questions/11041/check-if-a-script-style-was-enqueued-registered
            wp_enqueue_script(($handle), $this->get_module_file_url($file), $dependencies);
            return $handle;
        }
    }

    /**
     * localize script
     * @param $handle
     * @param $object_name
     * @param array $data
     */
    public function localize_script($handle, $object_name, $data = array()) {
        //validation
        if(!is_array($data)) $data = array();
        #$module = $this->get_module_name();
        //$handle = 'hw-module-'.$module.'-'. md5(self::get_file_name($file));

        wp_localize_script($handle, HW_Validation::valid_objname($object_name) , $data);
    }
    /**
     * addition localize script data
     * https://core.trac.wordpress.org/changeset/18480
     * @param $handle
     * @param $object_name
     * @param $key
     * @param $value
     */
    public function add_data($handle, $object_name, $key, $value) {
        global $wp_scripts;
        if(!wp_script_is($handle, 'queue')) return;
        //valid
        if(is_array($value)) $value = json_encode($value);

        $localize_data = $wp_scripts->get_data($handle, 'data');
        $wp_scripts->add_data($handle, 'data', $localize_data. "{$object_name}['{$key}'] = ". $value. ';' );
    }
    /**
     * return module file url
     * @param $file
     * @return string
     */
    public function get_module_file_url($file) {
        if(filter_var($file, FILTER_VALIDATE_URL)) return $file;
        else {
            return $this->module_url ."/". $file;
        }
    }

    /**
     * enqueue stylesheet file
     * @param $file
     * @param $dependencies
     * @param $handle
     * @return mixed|void
     */
    public function enqueue_style($file, $dependencies = array(), $handle='') {
        //valid
        if(!is_array($dependencies)) $dependencies = array();

        $module = $this->module_name;   //$this->get_module_name();
        if(!$handle) $handle = self::get_file_name($file);
        $handle = md5('hw-module-'.$module.'-'.  ($handle));

        if(wp_style_is($handle, 'queue')) {
            wp_enqueue_style($handle, $this->get_module_file_url($file) ,$dependencies);
            return $handle;
        }

    }

    /**
     * setup actions
     * @param $obj
     */
    private static function setup_actions($obj) {
        //validation
        if(! $obj instanceof HW_Module) return;
        /**
         * enqueue scripts on frontend
         */
        add_action('wp_enqueue_scripts', array($obj, 'enqueue_scripts'));
        /**
         * load stuffs in admin page
         */
        add_action('admin_enqueue_scripts', array($obj, 'admin_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, '_admin_enqueue_scripts'));
        /**
         * @hook wp_head
         */
        add_action('wp_head', array($obj, 'print_head'));
        /**
         * @hook wp_footer
         */
        add_action('wp_footer', array($obj, 'print_footer') );
    }

    /**
     * init fields for module setting page
     * @param AdminPageFramework $oAdminPage (depricated)
     */
    public function after_fields() {
        if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;

        if($this->option('enable_submit_button')) {
            //add submit button
            $this->add_submit_buttons();
        }
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public static function _admin_enqueue_scripts() {
        wp_enqueue_script('hw-module-js', HW_HOANGWEB_URL. '/js/module.js');
    }
    /**
     * before user fields
     */
    public function before_fields() {
        //help icon
        $this->addField(array(
            'field_id' => uniqid('help'),
            'type' => 'hw_help',
            'module' => $this,     //default refer to current module
            'module_info' => $this->option('module_info'),
            'hw_help' => $this->option('help_handler')  //note: help field is default key for apf framework, you can't re-create because it is string data
        ));
    }

    /**
     * start configure data for the module
     * @param $file
     */
    public function setup_demo($file) {
        if($this->get_config()) {
            $xml = HW_WXR_Parser_SimpleXML::read_simplexml_object($file);
            if(!$this->get_module_exporter()) {
                return;
            }
            //update data on exporter
            $this->get_module_exporter()->xml_data = $xml->xml;
            $this->get_module_exporter()->namespaces = $xml->namespaces;

            $this->get_config()->setup_user_data($xml->xml, $xml->namespaces);
        }
    }
    /**
     * export module data
     * @return array|mixed
     */
    public function export(){
        return array();
    }

    /**
     * callbacks
     * @hook wp_enqueue_scripts
     * @return mixed|void
     */
    public function enqueue_scripts(){}

    /**
     * @hook admin_enqueue_scripts
     * @return mixed|void
     */
    public function admin_enqueue_scripts(){}
    /**
     * @hook wp_head
     * @return mixed
     */
    public function print_head(){}

    /**
     * @hook wp_footer
     * @return mixed
     */
    public function print_footer(){}

    /**
     * get into module setting page
     * @param string|array $query
     * @return string
     */
    public function get_setting_page_url($query='') {
        if(is_string($query) && $query) parse_str($query, $query);
        $args = array(
            'tab'       => HW_Module_Settings_page::valid_tab_slug($this->module_name),
            'page'          => urlencode( HW_Module_Settings_page::PAGE_SLUG ),
            'module'        => urlencode( $this->module_name ),
            'module_name'   => urlencode( $this->get_module_name() ),
            #'module_source' => urlencode( $item['url'] ),
            'tgmpa-tab-nonce' => urlencode( wp_create_nonce( 'tgmpa-tab-nonce' ) ),
        );
        if(is_array($query)) $args = array_merge($query, $args);
        return add_query_arg(
            $args,
            #network_admin_url( 'options-general.php' )
            admin_url( 'admin.php' )
        );
    }
    /**
     * @param $oAdminPage
     */
    final public function _replyToAddFormElements($oAdminPage){
        $this->oAdminPage = $oAdminPage;
        //submit button
        /*$oAdminPage->addSettingFields(
            array(
                'field_id' => 'submit_button',
                'type' => 'submit',
                'show_title_column' => false,
            )
        );*/
        $this->before_fields();
        if(method_exists($this, 'replyToAddFormElements')) {
            $this->replyToAddFormElements($oAdminPage);
        }
        $this->after_fields();
    }

    /**
     * @param null $oAdminPage
     * @return mixed|void
     */
    public function replyToAddFormElements($oAdminPage) {

    }
    /**
     * @return mixed|void
     */
    public function do_before_tab_hook() {

    }
    /**
     * triggered in the middle of rendering the page.
     * @return mixed
     */
    public function do_tab_hook(){}

    /**
     * triggered after rendering the page
     * @return mixed
     */
    public function do_after_tab_hook(){}

    /**
     * receives the output of the top part of the page.
     * @return mixed
     */
    public function head_tab_filter(){}

    /**
     * receives the output of the middle part of the page including form input fields.
     * @return mixed
     */
    public function content_tab_filter($content){
        return $content;
    }

    /**
     *  receives the output of the bottom part of the page.
     * @return mixed
     */
    public function foot_tab_filter(){}

    /**
     * receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     * @return mixed
     */
    public function _validation_tab_filter($values){
        #$values = $this->pure_fields_result($values);  //please don't remove prefix module from field name, this cause damany your data
        $values = $this->validation_tab_filter($values);
        return $values;
    }

    /**
     * @hook 'validation_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name)
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        return $values;
    }
    /**
     * receives the output of the CSS rules applied to the tab page of the slug.
     * @return mixed
     */
    public function print_styles(){}

    /**
     * receives the output of the JavaScript script applied to the tab page of the slug.
     * @return mixed
     */
    public function print_scripts(){}

    /**
     * receives the exporting array sent from the tab page.
     * @return mixed
     */
    public function export_tab_filter(){}

    /**
     * receives the importing array submitted from the tab page.
     * @return mixed
     */
    public function import_tab_filter(){}

    /**
     * magic method for getting member
     * @param $key
     * @param $value
     */
    public function __set($key, $value) {#_print($key.'<>'. $value);
        $this->option($key, $value);
    }
    /**
     * magic method for adding member
     * @param $key
     */
    public function __get($key) {
        return $this->option($key);
    }
}
