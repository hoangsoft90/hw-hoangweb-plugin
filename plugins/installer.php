<?php
#/root/includes/hoangweb-core.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/10/2015
 * Time: 22:06
 */
HW_HOANGWEB::load_class('HW_Shell');
//HW_HOANGWEB::load_class('redrokk_metabox_class');   //metaboxs framework

/**
 * Class HW_Installer
 */
class HW_Installer extends HW_Shell {
    /**
     * singleton
     * @var null
     */
    public static $instance = null;
    /**
     * HW_Logger
     * @var null
     */
    public  $logger = null;
    /**
     * HW_Config_Module_Section
     * @var null
     */
    public  $config = null;
    /**
     * main class constructor
     * @param $global_config
     */
    public function __construct($global_config='') {
        HW_HOANGWEB::load_class('HW_Ajax');
        if($global_config) $this->config = $global_config ;

        $this->setup_actions();
        $reset_logs = hw__get('clear', true);
        if(class_exists('HWIE_Logger', false)) $this->logger = HWIE_Logger::get_instance($reset_logs);    //run first
    }

    /**
     * init hooks
     */
    private function setup_actions() {
        HW_HOANGWEB::register_ajax('hw_installer', array(&$this, '_ajax_installer_handle'));
        HW_HOANGWEB::register_ajax('hw_logger', array(&$this, '_ajax_logs_handle'));
        HW_HOANGWEB::register_ajax('hw_load_wpcli_commands', array(&$this, '_ajax_load_cli_commands'));
        HW_HOANGWEB::register_ajax('hw_run_cli', array(&$this, '_ajax_run_cli_command'));
        //HW_HOANGWEB::register_ajax('hw_list_to_do', array(&$this, '_ajax_show_list_to_do'));

    }

    /**
     * get active module by installer
     * @param $module
     * @param $default_module if specific module not exists, get default other module
     */
    private function get_active_module_from_installer($module='', $default_module='hw-importer') {
        if(!$module) $module = hw__req('module');
        if(!count(HW_TGM_Module_Activation::get_register_modules(array('active'=>1, 'slug'=>$module)))) {
            $module = $default_module;
        }
        if($module) return HW_Module_Settings_page::get_modules($module);
    }

    /**
     * @param $callback
     * @param $_callback
     * @param $module
     * @param $config
     * @param $segments
     * @param $segment
     * @param $active_segments
     * @param $args
     * @param $command
     */
    protected function do_command_segment($callback,$_callback, $module,$config, $segments,$segment,$active_segments, $args, $command) {
        if(is_callable($callback)) {
            ob_start();
            //register first segment
            $segments->add_default_segment('first_segment', $config);
            $result = call_user_func($callback,$segments, $args);
            $result .= ob_get_contents();
            usleep(500);    //make slow process
            ob_clean();
            //ajax object
            $ajax =HW_Ajax::create();
            $ajax->add_data('main_segments', $segments->get_main_segments_name());
            //list to do
            $list2do = array();
            $main_segments = $ajax->get_data('main_segments');  //list main segments as list to do
            foreach($main_segments as $seg=> $text) {
                $list2do[] = array('type'=> 'segment', 'name'=> $seg,'value'=> $seg, 'display' => $text, 'field' => 'checkbox');
            }
            $list2do = $config->list_to_do($list2do, $command, $segments, $ajax); //list to do for the module configuration
            $list2do = apply_filters('hw_list_to_do',$list2do, array(
                'module' => $module, 'command' => $command, 'ajax' => $ajax
            ) );

            //find out segment in list to do, before run segment
            if(is_array($list2do))
                foreach($list2do as &$item) {
                    if(isset($item['callback']) && is_callable($item['callback'])) {
                        $nest_segment = !empty($item['nest_segment'])? true: false;
                        $segment_id = $segments->add_segment($item['callback'], $item['display'], $nest_segment);
                        //change to segment item
                        $item['type'] = 'segment';
                        $item['value'] = $item['name']= $segment_id;
                        $item['field'] = 'checkbox';

                        unset($item['callback']);   //remove callback after that
                    }
                    //get from segment data
                    if($item['type'] =='segment') {
                        $list2do_args = $segments->get_segment_data('list2do', $item['name']);
                        if($list2do_args) {
                            $item = array_merge($item, $list2do_args);
                        }
                    }

                }
            //register end segment for the command
            $end_segment_id = $segments->add_default_segment('end_segment', $config) ;
            if($end_segment_id!==false) {
                $list2do[] = array( //add to list to do
                    'type' => 'segment','name'=> $end_segment_id, 'value'=> $end_segment_id, 'field' => 'checkbox', 'hide'=> '1',
                    'display' => $command.' end segment'
                );
            }

            if($segment) {#HW_Logger::log_file($segment);
                ob_start(); #we also dont output from any segments of command, because in segment maybe contain segments
                $result = $segments->run_segment($segment, $args);
                $result .= ob_get_contents();
                $ajax->add_data('result', $result );   //call command segment
                ob_clean();
                if(is_callable($_callback)) call_user_func($_callback, true, $args);    //result callback
            }
            else $ajax->add_data('result', $result);
            //else {
            //ajax
            $ajax->add_data('segments', $segments->list_segments_name($active_segments));
            $ajax->add_data('main_segments', $segments->get_main_segments_name());  //update main segments
            $ajax->add_data('list_to_do', $list2do);
            echo $ajax;
            //}
        }
    }
    /**
     * do command from active module installer
     * @param null $args
     * @param $_callback
     */
    private function do_command_from_installer($args = null, $_callback=null) {
        $command = hw__req('command');
        $segment = hw__req('cmd_segment');  //get command segment to call
        $active_segments = hw__post('segments');    //get active segments from command
        $module = $this->get_active_module_from_installer();
        $config = $module->get_config();

        if($command && $config) {
            //prepare some data
            $config->theme_config = $config->get_option('theme_config');
            $callback = $config->get_command($command); //HW_Config_Module_Section::register_command($command);
            $segments = $callback['segments'];
            if(!empty($callback['hook'])) { //rarely use
                $hook = is_array($callback['hook'])? $callback['hook'][0] : $callback['hook'] ;
                $priority = is_array($callback['hook']) && isset($callback['hook'][1])? $callback['hook'][1] : 10;
                $accept_args = is_array($callback['hook']) && isset($callback['hook'][2])? $callback['hook'][2] : 1;

                $this->call_by_hook($hook, function() use($command, $args, $_callback, $callback,$config, $module, $segment,$active_segments){
                    $this->do_command_segment(
                        $callback['callback'], $_callback,$module,$config, $callback['segments'],
                        $segment, $active_segments, $args, $command
                    ) ;
                }, $priority, $accept_args);

            }
            else {
                $this->do_command_segment($callback['callback'], $_callback, $module, $config, $segments, $segment, $active_segments, $args, $command);
            }
            return true;
        }
        else if(is_callable($_callback)) call_user_func($_callback, false, $args);
    }

    /**
     * ajax install for command
     * @param $result
     * @param $args
     */
    public function ajax_installer_command($result, $args ) {
        $module = $args['module'];
        $config = $args['config'];
        if($result==false) {
            if(!empty($config)) $script = $config->get_module_setup_script();
            if(!empty($script)) $this->realtime_exec($script);

        }
        if(hw__req('command') !== 'view_stats' && $module->option('module_config_enable_stats') && method_exists($config, 'view_stats')) {
            $config->view_stats();
        }
    }

    /**
     * @ajax hw_load_wpcli_commands
     */
    public function _ajax_load_cli_commands() {
        //$Utilities = HW_CLI_Command_Utilities::get_instance();
        //HW_Ajax::result($Utilities->get_clis());
        $module =$this->get_active_module_from_installer(); //refer to hw-import module
        $config = $module->get_config();
        $script = $config->get_cli_cmd('all_cmds', array(), /*$module->option('module_name')*/'hw-module'); //hw-module is core cli by hoangweb design for module
        #echo json_encode(array('a'=>'A'));
        if(!empty($script))  $this->realtime_exec($script, array($this, '_cli_command_output'));
    }

    /**
     * @ajax hw_run_cli
     */
    public function _ajax_run_cli_command() {
        $cmd= hw__req('_cmd');
        $subcmd = hw__req('_subcmd');
        $params = hw__req('_params', '') ;

        $module =$this->get_active_module_from_installer($cmd); //refer to hw-import module
        //if(empty($module)) $module = HW_Module_Settings_page::get_modules('hw-importer'); //default module for installer, intergate in get_active_module_from_installer
        $config = $module->get_config();
        $script = $config->get_cli_cmd($subcmd, $params, $cmd);#__print($script);
        if(!empty($script))  $this->realtime_exec($script, array($this, '_cli_command_output'));
    }
    /**
     * filter cli command output
     * @param $output
     */
    function _cli_command_output($output) {
        echo htmlspecialchars_decode($output);
    }
    /**
     * ajax handle
     * @ajax hw_logger
     */
    public function _ajax_logs_handle() {
        HW_HOANGWEB::load_class('HW_Ajax');
        //$module = $this->get_active_module_from_installer();
        $logs_data = $this->logger->get_logs();
        $ajx = HW_Ajax::create();
        $ajx->add_data('data', $logs_data);

        echo $ajx;
    }

    /**
     * ajax handle
     * @ajax hw_installer
     */
    public function _ajax_installer_handle() {
        //HW_HOANGWEB::load_class('');
        $module = $this->get_active_module_from_installer();
        $config = $module->get_config();
        if(!empty($config)) $script = $config->get_module_setup_script();

        $this->do_command_from_installer(array('module' => $module, 'config' => $config ), array($this,'ajax_installer_command'));

        /*if(!$this->do_command_from_installer(array('module' => $module, 'config' => $config))) {
            //$cmd= 'C:\\Windows\\System32\\cmd.exe /c E:/HoangData/xampp_htdocs/1.bat';
            if(!empty($script)) $this->realtime_exec($script);
        }
        if(hw__req('command') !== 'view_stats' && $module->option('module_config_enable_stats') && method_exists($config, 'view_stats')) {
            $config->view_stats();
        }
        */
    }
    /**
     * @param $hook
     * @param $callback
     * @param int $priority
     * @param int $accepted_args
     */
    public function call_by_hook($hook, $callback, $priority=10, $accepted_args=1) {
        if(is_callable($callback)) {
            add_action($hook, $callback, $priority, $accepted_args);
        }
    }

}
/**
 * Class HW_Installation_Page
 */
if(class_exists('AdminPageFramework')):
    class HW_Installation_Page extends AdminPageFramework{
        /**
         * menu page slug
         */
        const page_setting_slug = 'hw_installation';

        /**
         * tell the framework what page to create
         */
        public function setUp() {
            //$this->setRootMenuPage( 'sdfgfgdfg' );      # set the top-level page, ie add a page to the Settings page
            $this->setRootMenuPageBySlug(HW_NHP_Main_Settings::HW_NHP_THEME_OPTIONS_PAGE_SLUG);      //add submenu page under hoangweb theme options
            #add sub-menu pages
            $this->addSubMenuItem(
                array(
                    'title'     => 'Installer',
                    'page_slug' => self::page_setting_slug,
                )
            );

            //modify apf hwskin fieldtype
            #add_filter('apf_hwskin', array($this, '_apf_hwskin'),10,2);
            #add_filter('hwskin_field_output', array($this, '_apf_hwskin_field_output'),10,3);   //hack field output
        }
        /**
         * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
         */
        public function do_hw_installation() {


        }
        /**
         * check whether user visiting this page
         * @return bool
         */
        public static function is_current_screen(){
            $screen = get_current_screen(); //only current this page
            return strpos($screen->id , self::page_setting_slug)!==false;
        }
        /**
         * load fields settings
         * @param $oAdminPage
         */
        public function load_hw_installation( $oAdminPage ) {

        }
    }
endif;
/**
 * Interface HW_Config_Module_interface
 */
interface HW_Config_Module_interface {
    /**
     * call in construct of child class
     * @return mixed
     */
    public function start() ;

    /**
     * view_stats command
     * @return mixed
     */
    public function view_stats();

    /**
     * field output callback for hw_html field type
     * @param AdminPageFramework $aField
     * @return mixed
     */
    public function content($aField);


    /**
     * setup module data
     * @return mixed
     */
    public function setup_user_data($xml, $namespaces);

    /**
     * list to do for command
     * @param $data
     * @param $command
     * @param $segments
     * @param $ajax
     * @return mixed
     */
    public function list_to_do($data, $command, $segments, $ajax);
}

/**
 * Class HW_Config_Module_Commands
 */
class HW_Config_Module_Commands {
    /**
     * @var int
     */
    protected $index=0;
    /**
     * command
     * @var
     */
    public $command;
    /**
     * module object
     * @var null
     */
    private $module =null;
    /**
     * @var array
     */
    private $segments = array();
    /**
     * current segment
     * @var
     */
    private $current_segment;
    /**
     * @var array
     */
    public $pre_segments = array(
        'first_segment'=> array(
            'callback'=>'do_%s_first_segment',
            'name' => 'First segment for %s'
        ),
        'end_segment'=> array(
            'callback'=> 'do_%s_end_segment',
            'name' => 'End segment for %s'
        )
    );
    /**
     * @param $config
     * @param $command
     */
    public function __construct(/*HW_Config_Module_Section $config*/$command, $module) {
        $this->command = $command;
        $this->module = $module;
        //init pre segments for the command
        foreach ($this->pre_segments as $name => &$seg) {
            $seg['callback'] = sprintf($seg['callback'], $command);
            $seg['name'] = sprintf($seg['name'], $command);
            $seg['id'] = $this->generate_id($seg['name']);

        }
    }

    /**
     * return current segment id
     * @return mixed
     */
    public function current_segment_id() {
        return $this->current_segment;
    }
    /**
     * check wether segment is default
     * @param $id
     * @return bool
     */
    public function is_default_segments($id) {
        return in_array($id, $this->pre_segments);
    }
    /**
     * @param $name
     * @param null $callback
     * @param bool $nest_segment
     * @return segment id
     */
    public function add_segment( $callback , $name='', $nest_segment=false, $parent='') {
        if(!$name) $name = $this->index++;
        $segment_id = $this->generate_id($name);
        $this->module->get_config()->add_data('install_segments_steps/' .$this->command,
            $segment_id, array('callback' => $callback, 'text'=> $name, 'data' => array()));

        if(!isset($this->segments[$segment_id])) $this->segments[$segment_id] = array();
        if((is_string($parent) || is_numeric($parent)) && trim($parent) && isset($this->segments[$parent])) {
            $this->segments[$parent][] = $segment_id;
            $this->add_segment_data($segment_id, array('parent' => $parent));
        }
        if($nest_segment && is_callable($callback)) {
            $this->current_segment = $segment_id;
            call_user_func($callback, $this, $segment_id);  //find child segments
        }
        return $segment_id;
    }

    /**
     * generate segment id
     * @param $name
     * @return string
     */
    protected static function generate_id($name) {
        return /*md5*/($name);
    }
    /**
     * update segment
     * @param $segment_id
     * @param $args
     */
    public function update_segment($segment_id, $args = array()) {
        $data = $this->get_segment($segment_id);
        if($data) { //make sure segment exists
            if(!isset($data['data'])) $data['data'] = array();
            if(!is_array($args)) $data['data'][]= $args;
            else $data = array_merge($data, $args);
            $this->module->get_config()->add_data('install_segments_steps/' .$this->command,
                $segment_id, $data, true);
        }

    }

    /**
     * update segment data
     * @param $segment_id
     * @param array $data
     */
    public function add_segment_data($segment_id, $data=array()) {
        if(!is_array($data)) $data = array();   //valid segment data
        $childs = $this->get_childs ($segment_id, true);
        foreach($childs as $id) {
            $_data = $this->get_segment($id);
            if(!empty($_data['data'])) $data = array_merge($data, $_data['data']);
            $this->update_segment($id, array('data' => $data)); //propagate to their childs
        }
    }

    /**
     * get segment data
     * @param $segment_id
     * @param string $item
     */
    public function get_segment_data( $item='', $segment_id='') {
        if(!$segment_id) $segment_id = $this->current_segment;  //refer to current segment
        $propagate_uplevel = $this->get_parents($segment_id);
        $merge = array();
        foreach ($propagate_uplevel as $id) {
            $data = $this->get_segment($id);
            if(is_array($data) && !empty($data['data'])) $merge = array_merge($merge, $data['data']);
            /*if(is_array($data) && isset($data['data']) ) {
                return $item? (isset($data['data'][$item])? $data['data'][$item] : '') : $data['data'];
            }*/
        }

        return $item? (isset($merge[$item])? $merge[$item] : null) : $merge;
    }
    /**
     * remove command segment
     * @param $name
     */
    public function remove_segment($name) {
        $this->module->get_config()->remove_data('install_segments_steps/' .$this->command. '/'. $name);
    }

    /**
     * return segment data
     * @param $id
     * @return null
     */
    public function get_segment($id='') {
        if(!$id) $id = $this->current_segment;
        return $id? $this->module->get_config()->get_data('install_segments_steps/' .$this->command. '/'. $id) : null;
    }
    /**
     * get command segments
     * @return mixed
     */
    public function get_segments() {
        return $this->module->get_config()->get_data('install_segments_steps/' .$this->command);
    }

    /**
     * run segment on command
     * @param $segment segment id
     * @param $args pass var to segment callback
     */
    public function run_segment($segment, $args) {
        $segments = $this->get_segments();
        if(isset($segments[$segment]) && is_callable($segments[$segment]['callback'])) {
            $this->current_segment = $segment;
            $result = call_user_func($segments[$segment]['callback'], $this, $args);
            usleep(500);
            //remove this segment after execute
            $this->remove_segment($segment);
            return $result;
        }
    }

    /**
     * list command segments
     * @return array
     */
    public function list_segments_name($parents=array()) {
        $result = array();
        //if(!empty($parents)) $parents = array_keys($this->get_segments());
        if(is_array($this->get_segments())) {
            foreach($this->get_segments() as $name=>$cb) {
                if(!empty($parents) && !$this->is_child_segment($name, $parents) ) continue;
                $result[$name] = $cb['text'];
            }
        }
        return $result;
    }

    /**
     * get main segments
     * @return array
     */
    public function get_main_segments_name() {
        $result = array();
        $segments = $this->get_segments();#__print(array_keys($segments));
        if(is_array($this->segments)) {
            $_childs = array();
            foreach($this->segments as $seg1 => $childs1) {
                #if($this->is_default_segments($seg1)) continue; //ignore default segments
                $_childs =array_flip($childs1) + $_childs;
                if(!isset($_childs[$seg1]) && isset($segments[$seg1])) $result[$seg1] = $segments[$seg1]['text'];
            }
        }
        return $result ;
    }
    /**
     * check if the segment is child of parents in list
     * @param $check
     * @param array $parents
     * @return bool
     */
    public function is_child_segment($check, $parents= array()) {
        //$result= array();
        foreach ($this->segments as $seg => $childs) {
            if(in_array($check, $childs)) $check = $seg;
            if(in_array($check , $parents)) return true;
        }
        return false;
    }

    /**
     * return childs segments in parent segment
     * @param $id
     * @param bool $include_search_item
     */
    public function get_childs($id, $include_search_item=false) {
        $result =array();
        $current =$this->segments;

        foreach($current as $seg=> $childs) {
            if($id==$seg) {
                #$current = $childs; //change children data
                $result = array_merge((array)$childs ,$result);
                #reset($current);
                #continue;

            }
            else
                if(count($result) && in_array($seg, $result)) $result=array_merge( $childs,$result);
        }
        if($include_search_item) array_unshift($result, $id);
        return $result;
    }

    /**
     * get all parents of a segment
     * @param $id
     * @return array
     */
    public function get_parents($id) {
        $chain=array($id);
        foreach($this->segments as $seg1=> $childs1)
        foreach($this->segments as $seg=> $childs) {
            if(in_array($id, $childs)) {
                #$current = $childs; //change children data
                $chain[]=$parent=$seg;
                $id=$parent;
            }
        }
        return $chain;
    }

    /**
     * inherit data from chain of parent of a segment
     * @param $id
     * @return array
     */
    public function get_parents_segment_data($id) {
        $data = array();
        foreach($this->get_parents($id) as $segment_id) {
            $data = array_merge($this->get_segment_data(null,$segment_id) , $data);
        }
        return $data;
    }
    /**
     * get addition data  in segment
     * @param $task
     */
    public static function get_option($key) {
        $data = hw__post('addition_data');
        if(is_array($data) && isset($data[$key])) return $data[$key];
        return false ;
    }

    /**
     * check exists default segment
     * @param $name
     * @param $class
     * @return bool|mixed
     */
    public function exists_default($name, $class) {
        if(!isset($this->pre_segments[$name])) return false;
        $segment = $this->pre_segments[$name];
        if((is_object($class) || class_exists($class, false)) && method_exists($class, $segment['callback'])) {
            return $segment;
        }
        return false;
    }
    /**
     * check command for exists first segment
     * @param $class
     * @return bool
     */
    public function exists_first_segment($class) {
        return $this->exists_default('first_segment', $class)? true: false;
    }
    /**
     * check command for exists end segment
     * @param $class
     * @return bool
     */
    public function exists_end_segment($class) {
        return $this->exists_default('end_segment', $class)? true: false;
    }

    /**
     * add default segment
     * @param $name
     * @param $class
     */
    public function add_default_segment($name, $class) {
        $segment = $this->exists_default($name, $class);
        if($segment) {
            $first_segment_id =$this->add_segment(array($class, $segment['callback']), $segment['name']);
            $this->add_segment_data($first_segment_id, array('list2do'=> array('hide' => '1')) );
            return $first_segment_id;
        }
        return false;
    }

    /**
     * @param $callback
     * @param $num
     * @param array $data
     */
    public function bundle_segments($callback, $num=2, $data = array()) {
        if(!is_array($data)) $data = array();
        //$parent = $this->get_segment_data('parent');
        for($i=0;$i< $num;$i++) {
            $data['page'] = $i;
            $data['hide'] = '1';
            if($this->current_segment===null) $id = $this->add_segment($callback, 'import_segment_'. $i);
            else {
                //bundle segments alway in other segment
                $id = $this->add_segment($callback, 'import_segment_'. $i, 0, $this->current_segment) ;
            }
            $this->add_segment_data($id, $data );
        }

    }
}
/**
 * Class HW_Config_Module_Section
 */
abstract class HW_Config_Module_Section extends AdminPageFramework_MetaBox_Page implements HW_Config_Module_interface{
    /**
     * modules config manager
     * @var array
     */
    protected static $data = array('commands' => array(), 'configs' => array(), 'options' => array());

    /**
     * module object
     * @var null
     */
    public $module = null;
    /**
     * installer instance
     * @var null
     */
    public $installer = null;

    /**
     * module exporter
     * @var null
     */
    public $module_exporter = null;

    /**
     * parent class construct method
     * @param $sMetaBoxID
     * @param $sTitle
     * @param array $asPageSlugs
     * @param string $sContext
     * @param string $sPriority
     * @param string $sCapability
     * @param string $sTextDomain
     */
    public function __construct($sMetaBoxID, $sTitle, $asPageSlugs = array(), $sContext = 'normal', $sPriority = 'default', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        if($this->get_current_module()) {
            $module_info = $this->get_current_module()->module_info;
            if($module_info) $sTitle = $module_info['name'];
        }

        parent::__construct($sMetaBoxID, $sTitle, $asPageSlugs, $sContext , $sPriority , $sCapability, $sTextDomain );

        //get current template context
        $this->option('theme_config', 'HW__Template::get_theme_config');    //callback option

        $this->installer = HW_Installer::get_instance($this);   //installer
        if(get_class($this) == 'HW_Module_Config_general' ) {   //importer module
            $this->installer->config = $this;
        }
        //init hooks
        $this->setup_actions();
        $this->start();

    }

    /**
     * list to do for each module config
     * this is placeholder
     * @param $command
     * @param $segments
     * @param $ajax
     */
    public function list_to_do($data, $command, $segments, $ajax) {}
    /**
     * setup actions
     */
    private function setup_actions() {
        add_action('admin_print_scripts', array($this, 'admin_head'),100);
    }

    /**
     * register config page for the module
     */
    final public static function register_config_page() {
        $call = get_called_class();
        if($call !== __CLASS__) add_action('hw_module_register_config_page', array($call,'init'));
    }
    /**
     * init current module installer
     * @hook admin_head
     */
    public function admin_head() {
        $module = $this->get_current_module();
        if($module) {
            /*echo '<script type="text/javascript">
            __hw_installer.create("'.$module->module_name.'");
            </script>';*/
            echo "<script type='text/javascript'>\n";
            echo 'setTimeout(function(){__hw_installer.create ("'.$module->module_name.'", "'.$module->get_module_info('name').'")},10);';
            echo "\n</script>";
        }
    }

    /**
     * initial
     * @return mixed|void
     */
    public function start() {

    }

    /**
     * init fields
     */
    public function load_fields() {
        if(method_exists($this, 'content')) $this->addHTML(array($this, 'content'));
    }
    /**
     * view_stats command holder
     * @return mixed|void
     */
    public function view_stats(){}
    /**
     * list allow fields type for the module config page
     * @param $fields
     */
    public function support_fields($fields) {
        $class=  get_called_class();
        HW_APF_FieldTypes::apply_fieldtypes($fields, $class);
    }

    /**
     * set visible for module config box
     * @param bool $show
     */
    public function visible($show = true) {
        self::option('visible', $show);
    }

    /**
     * @param $name
     * @param $value
     */
    public static function option($name, $value= '') {
        $module_config_file = self::get_module_config_page();
        if(!isset(self::$data[$module_config_file]['options'])) {
            self::$data[$module_config_file]['options'] = array(); //init holder
        }
        if(is_string($name)) self::$data[$module_config_file]['options'][$name] = $value;
    }

    /**
     * return value for given option
     * @param $name
     * @Param $run_callable get result from callback or return raw callback
     * @return mixed
     */
    public static function get_option($name, $run_callable = true) {
        $module_config_file = self::get_module_config_page();
        if(isset(self::$data[$module_config_file]['options'][$name])) {
            $value= self::$data[$module_config_file]['options'][$name] ;
            if($run_callable && is_callable($value)) return call_user_func($value);
            else return $value;
        }
    }
    /**
     * add apf html field
     * @param $callback callable
     */
    public function addHTML($callback) {
        $this->addField(
            array(
                'field_id' => uniqid('html'),
                'type' => 'hw_html',
                'title'=>'',
                'show_title_column' => false,
                'output_callback' => $callback
            )
        );

    }

    /**
     * add apf field
     * @param $aField
     */
    public function addField($aField) {
        //validation
        if(empty($aField['field_id']) || empty( $aField['type'])) return ;

        $aField['field_id'] = $this->module->create_field_name($aField['field_id']);
        $this->addSettingFields($aField);
    }

    /**
     * add more apf fields
     */
    public function addFields() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if(is_array($arg) && isset($arg['type'])) {
                if($arg['type'] == 'hw_help') $arg['module'] = $this;
                $this->addField($arg);
            }
        }
    }

    /**
     * generate field id for each metabox of module config
     * @param $name
     */
    public function get_unique_id($name) {
        $module = $this->get_current_module();
        return $module->create_field_name($name);
    }

    /**
     * placeholder
     * @param $xml
     * @param $namespaces
     * @return mixed|void
     */
    public function setup_user_data($xml, $namespaces){
        $this->exporter()->_export_wxr_data($xml);
    }


    /**
     * get module complete setup script
     * @return mixed
     */
    public function get_module_setup_script() {
        return $this->get_current_module()->get_module_setup_script();
    }

    /**
     * copy from modules/command.php
     * @param $subcommand
     * @param string|array $param_id get first params for default
     * @param $command if not specific get module name as command name
     * @return string
     */
    public function get_cli_cmd($subcommand, $param_id=0, $command_name = '') {
        $module = $this->get_current_module();
        if((empty($command_name) || !$module->exist_command($command_name)) && !HW_CLI_Command_Utilities::is_sys_cmd($command_name))  {
            $command_name = $module->option('module_name');
        }

        $wp_cli = HW_HOANGWEB_PATH . 'lib/wp.phar';    //wp cli phar
        $home_url = home_url();
        $home_path = get_home_path();

        //prepare arguments
        if(is_array($param_id)) $args = HW_CLI_Command_Utilities::parse_cmd_args($param_id);
        elseif(is_string($param_id) || is_numeric($param_id)) $args = "--___config_id={$param_id}";
        else $args = '';

        //$cmd ="@echo Off&&";
        //$cmd .= "SET PATH=%PATH%;E:/HoangData/xampp_htdocs/wp.phar&&";
        $cmd = "php {$wp_cli} {$command_name} {$subcommand} {$args} --url={$home_url} --path={$home_path}";
        #$cmd .= "timeout /t 50";    //pause for 50s
        return $cmd;
    }

    /**
     * execute cli command in php
     * @param string $name command name
     * @param string $param_id param
     * @param null $command specific command name
     */
    public function run_cli_cmd($name = '', $param_id= 0, $command='') {
        if($name) $script = $this->get_cli_cmd($name, $param_id, $command);
        else $script = $this->get_module_setup_script();
        if(!empty($script)) $this->installer->realtime_exec($script, array($this, '_cli_command_output'));
    }
    /**
     * filter cli command output
     * @param $output
     */
    function _cli_command_output($output) {
        echo htmlspecialchars_decode($output);
    }
    /**
     * run command with all params in config file
     * @param string $name
     */
    public function run_cli_cmds($name = '') {
        $this->run_cli_cmd($name, null);
    }
    /**
     * run setup script
     */
    public function run_setup_script() {
        $this->run_cli_cmd();
    }
    /**
     * return module config file
     */
    public static function get_module_config_page() {
        $class = get_called_class();
        //get file name where a Class was Defined
        $reflector = new ReflectionClass($class);
        return realpath($reflector->getFileName());
    }
    /**
     * return current module
     */
    public function get_current_module() {
        if(!empty($this->module)) return $this->module;
        $module_config_file = self::get_module_config_page();

        if(isset(self::$data[$module_config_file]['module'])) {
            $this->module = self::$data[$module_config_file]['module'];
        }
        return $this->module;
    }
    /**
     * register command
     * @param $command
     * @param $callback
     * @param $after_hook
     * @depricate bool $segment
     */
    public function register_command($command, $callback=null, /*$segment=false,*/$after_hook='') {
        $module_config_file = self::get_module_config_page();

        if(!isset(self::$data[$module_config_file]['commands'])) {
            self::$data[$module_config_file]['commands'] = array(); //init holder
        }
        if(is_string($command) && is_callable($callback) && !isset(self::$data[$module_config_file]['commands'][$command])) {
            $segments = new HW_Config_Module_Commands($command, self::get_module_configs('module'));
            //pre-segments
            /*if(method_exists($this, 'do_'.$command.'_first_segment')) {   //you can't add segment because this method do before init module config
                $segments->add_segment(array($this, 'do_'.$command.'_first_segment'), $command.'_first_segment');
            }*/

            self::$data[$module_config_file]['commands'][$command] = array(
                'callback'=>$callback,
                'hook' => $after_hook,
                'segments' => $segments,
                //'enable_segment' => $segment  #depricated
            );
        }
        if(isset(self::$data[$module_config_file]['commands'][$command])) {
            return self::$data[$module_config_file]['commands'][$command];
        }
    }

    /**
     * @param $group
     * @param $name
     * @param $value
     * @param $override
     * @return mixed
     */
    public static function add_data($group, $name, $value, $override=false) {
        $module_config_file = self::get_module_config_page();
        if(!isset(self::$data[$module_config_file])) self::$data[$module_config_file] = array();    //placeholder
        $access_paths = &self::$data[$module_config_file];
        foreach(explode('/',$group) as $path) {
            /*if($access_paths===null && !isset(self::$data[$module_config_file][$path])) {
                self::$data[$module_config_file][$path] = array(); //init holder
                $access_paths = &self::$data[$module_config_file][$path];
                continue;
            }*/
            if(!isset($access_paths[$path])) {
                $access_paths[$path] = array();
            }
            $access_paths = &$access_paths[$path];
        }

        if(is_string($name) && (!isset($access_paths[$name]) || $override)) {
            $access_paths[$name] =  $value ;
        }
        if(isset($access_paths[$name])) {
            return $access_paths[$name];
        }
    }

    /**
     * get data in paths
     * @param $group
     * @return null
     */
    public static function get_data($group) {
        $module_config_file = self::get_module_config_page();
        $access_paths = self::$data[$module_config_file];

        foreach(explode('/',$group) as $path) {
            /*if($access_paths===null && isset(self::$data[$module_config_file][$path])) {
                $access_paths = hwArray::cloneArray(self::$data[$module_config_file][$path] );
                continue;
            }*/
            if(isset($access_paths[$path])) {
                $access_paths = $access_paths[$path];
            }
            else {
                $access_paths=null;
                break;
            }
        }
        return $access_paths;
    }

    /**
     * remove data from given path
     * @param $paths
     */
    public static function remove_data($paths) {
        $module_config_file = self::get_module_config_page();
        $access_paths = &self::$data[$module_config_file];
        $del_path = '';
        foreach(explode('/',$paths) as $path) {
            if(isset($access_paths[$path])) {
                $del_path.="['{$path}']";
                $access_paths = &$access_paths[$path];
            }
            else {
                return;
            }
        }
        if($del_path) eval('unset(self::$data[$module_config_file]'.$del_path.');');
    }
    /**
     * view command stats for the associate module
     */
    public function enable_command_stats() {
        $this->register_command('view_stats', array(&$this, 'view_stats'));
        $module = $this->get_current_module();
        if($module) $module->option('module_config_enable_stats', 1);
    }
    /**
     * return callback from register command
     * @param $command
     * @return mixed
     */
    public function get_command($command) {
        return $this->register_command($command);
    }

    /**
     * print output from command
     * @param $msg
     */
    public function command_output($msg) {
        if(is_array($msg)) hwArray::json_output($msg);
        else {
            print_r($msg);
        }
        echo '<br/>';
    }

    /**
     * command log message
     * @param $msg
     * @param $level
     */
    public function command_log($msg, $level='success') {
        $this->installer->logger->add_log($msg, $level);
        print_r($msg);
    }
    /**
     * get exporter
     */
    public function exporter() {
        if(empty($this->module_exporter)) {
            $this->module_exporter = self::get_module_configs('exporter');
        }
        return $this->module_exporter;
    }
    /**
     * add module config to database
     * @param $config
     * @param $module
     */
    public static function add_config($config, $module) {
        if(!isset(self::$data[$config])) self::$data[$config] = array();
        if(is_object($module)) self::$data[$config]['module'] = $module;
        else
            if(is_array($module)) self::$data[$config] = array_merge(self::$data[$config], $module);
    }

    /**
     * return module config data
     * @param $config
     * @return module config or get all configs for register modules
     */
    public static function get_config($config='') {
        #if(!$config) $config = self::get_module_config_page();
        if(is_string($config) && isset(self::$data[$config])) {
            return self::$data[$config]['config'];
        }
        if(empty($config)) return self::$data ;
    }

    /**
     * @param string $config
     * @param string $item
     * @return mixed
     */
    public static function get_module_configs($item='', $config='') {
        if(!$config) $config = self::get_module_config_page();
        if(is_string($config) && isset(self::$data[$config]) ) {
            if($item  ) return isset(self::$data[$config][$item])? self::$data[$config][$item] : null;
            return self::$data[$config];
        }

    }


    /**
     * init module config page
     */
    final public static function init() {
        $child_class = get_called_class();
        $module = self::get_module_configs('module');
        $page_settings_slug = HW_Installation_Page::page_setting_slug;

        if(empty($module)) return;  //invalid module
        //hidden metabox configuration for module by set wrong page setting slug where metabox living
        if( $module->option('visible_config_page') == false ) {
            #return;
            $page_settings_slug = md5(rand());
        }

        $config = new $child_class(
            null,                                           // meta box id - passing null will make it auto generate
            __( 'placeholder ' ), // title
            $page_settings_slug,
            'side',                                         // context: side
            'default'                                       // priority
        );
        $module_config_file = self::get_module_config_page();
        $module->option('config_file', $module_config_file);    //save module config file
        //export class for module
        $exporter = $module->get_module_exporter();
        self::add_config($module_config_file, array('config' => $config , 'exporter' => $exporter));

        //default support field types for module config page
        HW_APF_FieldTypes::apply_fieldtypes(array('hw_html','hw_skin'), $child_class);
    }
}
/**
 * Class HW_Config_Modules_MetaBox
 */
class HW_Config_Modules_MetaBox extends HW_Config_Module_Section {
    /**
     * main class constructor
     */
    public function __construct($sMetaBoxID, $sTitle, $asPageSlugs = array(), $sContext = 'normal', $sPriority = 'default', $sCapability = 'manage_options', $sTextDomain = 'admin-page-framework') {
        parent::__construct($sMetaBoxID, $sTitle, $asPageSlugs, $sContext,$sPriority,$sCapability,$sTextDomain);

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts')) ;
    }
    /**
     * @return mixed|void
     */
    function start() {
        #$this->register_command('abc', array($this, 'test'));
        #$this->enable_command_stats();
    }
    /**
     * our code goes here…
     */
    public function setUp() {
        #$this->addHTML(array($this, '_general_installation_page'));
        $this->addSettingFields(
            array(
                'field_id' => uniqid('html'),
                'type' => 'hw_html',
                'show_title_column' => false,
                'output_callback' => array($this, '_general_installation_page')
            )
        );
        //content_{page slug}_{tab slug} filter hook
        add_filter( 'content_'.HW_Installation_Page::page_setting_slug.'_my_tab_c', array( $this, 'replyToInsertContents' ) );


    }

    /**
     * @param $aField
     */
    public function _general_installation_page($aField) {
        echo '<strong>Cài đặt chung.</strong>';
        hw_include_template('general_installation_page');
    }
    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        HW_Libraries::get('jquery-libs')->enqueue_scripts('jquery-ui-1.11.4.js');
        HW_Libraries::get('jquery-libs')->enqueue_styles('jquery-ui-1.11.4.css');
        HW_Libraries::enqueue_jquery_libs('blockUI');

        wp_enqueue_style('hw-installer-css', HW_HOANGWEB_URL. '/css/installer.css');    //css for installation page

        wp_enqueue_script('hw-installer', HW_HOANGWEB_URL . '/js/installer.js', array('jquery'));
        wp_localize_script('hw-installer', '__hw_installer', array('ver'=>'1.0'));
    }
    /**
     * content_{page slug}_{tab slug} filter hook
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {

        /*$_aOptions  = get_option( 'APF_Tabs', array() );
        return $sContent
        . "<h3>" . __( 'Saved Options' ) . "</h3>"
        . AdminPageFramework_Debug::getArray( $_aOptions );*/
        return $sContent;
    }
    /**
     * The content filter callback method.
     *
     * Alternatively use the `content_{instantiated class name}` method instead.
     */
    public function content( $sContent ) {

        $_sInsert   = "<p>" . sprintf( __( 'Cài đặt các cấu hình liên quan đến các modules của Hoangweb.', 'hoangweb' ) ) . "</p>";
        return $_sInsert.$sContent;

    }

    /**
     * style_{instantiated class name}
     * receives the output of the CSS rules applied to the pages of the associated post types with the meta box.
     * http://admin-page-framework.michaeluno.jp/en/v3/package-AdminPageFramework.MetaBox.html
     */
    public function style_HW_Config_Modules_MetaBox(){
        echo '<style>
        #poststuff #post-body.columns-2 #side-sortables{width:350px;}
        #post-body.columns-2 #postbox-container-1{width: 350px;}
        #poststuff .postbox-container{width:95%;}
        </style>';
    }
}

if(is_admin() ){
    //init custom field type
    #if(class_exists('APF_hw_skin_Selector_hwskin')) {
        #new APF_hw_skin_Selector_hwskin('HW_Installation_Page');
        HW_APF_FieldTypes::apply_fieldtypes(array('hw_html','hw_skin'), 'HW_Config_Modules_MetaBox');
    #}
    new HW_Installation_Page;
}

if(is_admin()) {
add_action('hw_hoangweb_loaded', function(){
    new HW_Config_Modules_MetaBox(
        null,                                           // meta box id - passing null will make it auto generate
        __( 'Cấu hình' ), // title
        HW_Installation_Page::page_setting_slug,
        'normal',                                         // context: side
        'default'                                       // priority
    );

    HW_Installer::get_instance();
});

}
