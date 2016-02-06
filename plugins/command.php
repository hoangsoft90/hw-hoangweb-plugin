<?php

/**
 * Class HW_CLI_Command
 */
if(!class_exists('WP_CLI_Command', false)) {
    //include_once (HW_HOANGWEB_PATH. 'lib/wp.phar');   //will conflict with exists wp core
}
/**
 * Class HW_CLI_Command_Utilities
 */
class HW_CLI_Command_Utilities extends HW_Core{
    /**
     * singleton
     * @var
     */
    public static $instance;
    /**
     * @var array
     */
    protected $cli_cmds = array();

    /**
     * class construct method
     */
    public function __construct() {
        foreach($this->default_commands() as $cmd => $subcommands) {
            $this->register_cli_cmds($cmd, $subcommands);
        }
    }
    /**
     * check if command is default belong to wpcli package
     * @param $cmd
     */
    public static  function is_sys_cmd($cmd) {
        $cmds = 'core,plugin,theme,option,media,post,db,theme,site,user,search,menu,rewrite';
        return in_array(trim($cmd), explode(',', $cmds));
    }

    /**
     * parse command arguments
     * @param array $data
     */
    public static function parse_cmd_args($data = array()) {
        $args = '';
        if(is_array($data)) {
            foreach ($data as $key => $val) {
                if(is_object($val)) continue;
                if(!is_numeric($key)) $args .= "--{$key}={$val} ";
                else $args .= "{$val} ";
            }
        }
        return trim($args);
    }

    /**
     * return HW_Logger object
     * @return mixed
     */
    public static function get_logger() {
        return HWIE_Logger::get_instance();
    }

    /**
     * @param $cmd
     * @param $subcommands
     */
    public function register_cli_cmds($cmd, $subcommands) {
        if(!isset($this->cli_cmds[$cmd])) $this->cli_cmds[$cmd] = array();  //placeholder
        foreach($subcommands as $name => $params) {
            $cmd_name = is_numeric($name) && is_string($params)? $params : $name;
            $this->cli_cmds[$cmd][$cmd_name] = is_numeric($name)? '' : $params;
        }

    }

    /**
     * return cli commmand info
     * @param $cmd
     * @return string
     */
    public function get_clis($cmd='') {
        return $cmd? (isset($this->cli_cmds[$cmd])? $this->cli_cmds[$cmd] : '') : $this->cli_cmds;
    }
    /**
     * return default wp cli commands
     */
    public function default_commands() {
        $default_cmds = array(
            'db' => array(
                'reset'=>'user',
                'create' => 'user',
                'drop' => '',
                'export'=> '',
            ),
        );
        return $default_cmds;
    }
}

/**
 * Class HW_CLI_Command
 */
if(class_exists('WP_CLI_Command', false)):
abstract class HW_CLI_Command extends WP_CLI_Command {
    /**
     * reference module object
     * @var null
     */
    public $module = null;
    /**
     * HW_XML_Module_Config_Parser instance
     * @var null
     */
    protected $config = null;
    /**
     * HW_CLI_Command_Utilities
     * @var
     */
    protected $cli;
    /**
     * main class constructor
     */
    public function __construct() {
        //get current module from cli command
        $this->get_current_module();
        $this->get_config() ;   //config module
        $this->cli = HW_CLI_Command_Utilities::get_instance();
    }

    /**
     * log message
     * @param $msg
     * @param string $level
     */
    public function result($msg, $level='success') {
        //WP_CLI::success( ' deactivate module '.$name.' successful.' );
        $logger = $this->cli->get_logger();   //run after hw-import
        $logger->add_log($msg, $level);
    }

    /**
     * success message
     * @param $msg
     */
    public function success($msg) {
        $this->result($msg, 'success');
    }

    /**
     * error message
     * @param $msg
     */
    public function error($msg) {
        $this->result($msg, 'error');
    }
    /**
     * get current module for this cli
     */
    protected function get_current_module() {
        if(empty($this->module)) {
            $class = get_called_class();
            $reflector = new ReflectionClass($class);
            $file = $reflector->getFileName();
            $split= explode(DIRECTORY_SEPARATOR, $file);
            $file = end($split);
            preg_match('#^class-cli-(.+)\.php$#', trim($file), $arr);
            if(count($arr) > 1) {
                $module = $arr[1];
                $this->module = HW_Module_Settings_page::get_modules($module);
            };
        }

        return $this->module;
    }

    /**
     * get module configuration
     * @return null
     */
    public function get_config() {
        if(!$this->get_current_module()) return ;
        if(empty($this->config)) {
            $this->config = $this->module->get_module_cli_config();
        }
        return $this->config;
    }
    /**
     * get params of invoke command name
     * note: call this method from cli command function
     * @param $dom extend HW_XML_Module_Config_Parser::get_params arguments
     * @param int|string $id
     */
    public function get_params($dom = null, $id = 0) {
        if(!$this->get_current_module() || empty(debug_backtrace()[1]['function'])) return ;
        $this->get_config();
        $from_func = debug_backtrace()[1]['function'];
        if($dom == null) {
            $dom = $from_func;
        }
        $dom = $this->config->get_params(array($dom, /*'params'*/), $id);
        return $dom;    //$this->config->extract_params($dom);
    }

    /**
     * parse command data
     * @param string $item
     * @return mixed
     */
    public function get_cmd_data($item = '') {
        if(!$this->get_current_module() || empty(debug_backtrace()[1]['function'])) return ;
        $this->get_config();
        $from_func = debug_backtrace()[1]['function'];

        return $this->config->parse_wxr_data($from_func, $item);
    }
    /**
     * get param on cli command
     * @param $assoc_args
     * @param $name
     * @param string $default
     * @return value of param
     */
    public function get_cmd_arg($assoc_args, $name, $default= '') {
        $params_id = isset($assoc_args['___config_id'])? $assoc_args['___config_id'] : 0;   //get params id
        $params = $this->get_params(debug_backtrace()[1]['function'], $params_id);
        if(! empty( $assoc_args[$name] )) $value = $assoc_args[$name];
        elseif(isset($params[$name])) $value = $params[$name];
        else $value = $default;
        return $value ;
    }

    /**
     * get all pre-params for command
     * @param array $default
     * @return array|extend|null
     */
    public function get_cmd_args( $default= array()) {
        $params = $this->get_params(debug_backtrace()[1]['function'], null);
        return !empty($params)? $params : $default;
    }

    /**
     * do import data available on command config
     * @return array|WP_Error
     */
    public function do_import() {
        $this->get_config();
        $command = debug_backtrace()[1]['function'];    //wp cli sub command name
        $this->config->do_import($command);
    }

    /**
     * do import file
     * @param $file
     */
    public function do_import_file($file) {
        $module = $this->get_current_module();
        $importer = HW_Import::get_instance($module->get_module_exporter());
        $importer->import_file($file);
    }
}
endif;

/**
 * Class HW_XML_Config_Parser
 */
class HW_XML_Module_Config_Parser extends HW_Core{
    /**
     * singleton
     * @var null
     */
    //public static $instance = null;
    /**
     * SimpleXMLElement
     * @var null
     */
    protected $xml = null;
    /**
     * @var null
     */
    protected $wxr_parser = null;

    /**
     * namespaces
     * @var array
     */
    protected $namespaces = array();
    /**
     * xml data
     * @var array
     */
    private $config_data = array();

    /**
     * main class construct
     * @param $file
     */
    function __construct($file = '') {
        if($file && is_string($file)) {
            $this->xml = HW_SimpleXMLElement::parser_xml($file);
        }
        elseif(is_object($file) && $file instanceof HW_SimpleXMLElement) {
            $this->xml = $file;
        }
        //get doc namespaces
        if(!empty($this->xml)) {
            $this->namespaces = $this->xml->getDocNamespaces();
        }
        if ( ! isset( $this->namespaces['wp'] ) )
            $this->namespaces['wp'] = 'http://wordpress.org/export/1.1/';
        if ( ! isset( $this->namespaces['excerpt'] ) )
            $this->namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';

        //get config data
        $parser = new HW_WXR_Parser_SimpleXML ;
        $this->config_data = $parser->parse( $this->xml );
    }

    /**
     * get wxr data
     * @param string $item
     */
    public function get_config_data($item='') {
        if($item && !empty($this->config_data)) {
            return isset($this->config_data[$item])? $this->config_data[$item] : '';
        }
        return $this->config_data;
    }
    /**
     * get xml dom root element
     * @param $tags access dom element
     * @param int|string $index element index if has more than one
     * @return HW_SimpleXMLElement|null
     */
    public function get_params($tags = '', $index=0) {
        $data = array();
        if($tags && !empty($this->xml) ) {
            if(is_string($tags)) $tags = preg_split('#[\s,]+#', $tags);
            $target_element = $this->xml;

            if(is_array($tags))
            foreach ($tags as $tag) {
                if(isset($target_element->$tag)) $target_element = $target_element->$tag;
            }
            foreach($target_element as $item) {
                #$t=HW_XML::xml2array($item, 'param', 'params');
                $data = array_merge($data, $this->extract_params($item));
            }
            if( is_numeric($index) && is_string($index)) {
                return isset($data[$index]) ? $data[$index] : null;
            }
        }
        return $data;   //$this->xml;
    }

    /**
     * parse config data for command
     * @param $cmd_name
     * @param $item
     * @return array|WP_Error
     */
    public function parse_wxr_data($cmd_name, $item = '') {
        if(empty($this->xml)) return;
        $cmd_data = array();
        if(isset($this->xml->$cmd_name)) {
            //$parser = new HW_WXR_Parser_SimpleXML ;
            $parser = new HW_WXR_Parser ;
            $cmd_data = $parser->parse( $this->xml->$cmd_name );
            /*foreach($this->xml->$cmd_name as $item) {
                //$cmd_data = $parser->parse( $item->children() );
            }*/

        }
        return ($item? (isset($cmd_data[$item])? $cmd_data[$item] : '') : $cmd_data);
    }

    /**
     * do import data from command
     * @param $cmd_name
     */
    public function do_import($cmd_name) {
        if(empty($this->xml)) return;
        $import = HW_Import::get_instance() ;
        if(isset($this->xml->$cmd_name)) {
            $import->import_file( $this->xml->$cmd_name );
        }
    }
    /**
     * get params
     * extend from HW_XML::xml2array
     * @param $dom start from this dom
     * @return SimpleXMLElement[]
     */
    public function extract_params($dom = null) {
        #if(empty($this->xml)) return;
        $data = array();
        //if(!is_object($dom) && isset($this->xml->params)) $dom = $this->xml->params;
        #if(!is_object($dom) && isset($this->xml->params)) $dom = $this->xml->params;
        //validation
        #if(!is_object($dom) || !$dom instanceof SimpleXMLElement) return ;

        /*if(!empty($dom)) {
            foreach ($dom->children() as $param) {
                $value = (string) $param['value'];
                //valid field type
                if(isset($param['type'])) {
                    if($param['type'] == 'bool') $value = ($value!=='false')? true : false;
                }
                $data[(string)$param['name']] = $value;
            }
        }
        return $data;*/

        $arr = array();
        foreach ($dom as $element) {
            $tag = $element->getName();
            if($tag !== 'param' && $tag !== 'params') continue ;

            $atts = $element->attributes();
            $key = isset($atts['name'])? (string)$atts['name'] : '';
            $val = isset($atts['value'])? (string)$atts['value'] : HW_String::truncate_empty_lines((string)$element);
            if(isset($atts['type']) && $atts['type'] == 'bool') $val = $val? true: false;

            $e = get_object_vars($element);
            if (!empty($e)) {
                $val = (count($element->children()) && $tag == 'params')? $this->extract_params($element) : $val;
                if($key!=='') $arr[$key] = $val;
                else {$arr[] = $val;}
            }
            else {
                if($key !=='') $arr[$key] = trim($element);
                else $arr[] = trim($element);
            }
        }
        return $arr;
    }
}