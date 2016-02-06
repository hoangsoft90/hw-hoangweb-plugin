<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 21/11/2015
 * Time: 12:04
 */
/**
 * Class HW_Sample_Data
 */
class HW_Sample_Data extends HW__Template_Configuration{
    /**
     * initial
     * @var
     */
    public static $instance;
    /**
     * sample data
     * @var array
     */
    protected static $data = array();
    /**
     * HW_Import
     * @var
     */
    public  $importer;
    /**
     * @var int
     */
    public $import_per_page = 2;
    /**
     * @var
     */
    public $import_file;
    /**
     * @var array
     */
    public  $demo_info = array();

    /**
     * main class constructor
     * @param $name demo folder
     */
    public function __construct($name='') {
        $this->importer = HW_Import::get_instance();  //get wxr parser
        if(empty(self::$data)) {
            $list_demos = HW_File_Directory::list_folders(HW_THEME_OPTIONS_SAMPLE_DATA);
            foreach($list_demos as $path => $demo) {
                $this->add_demo($path, $demo);
            }
        }
        if(!isset(self::$data[$name])) return null;

    }

    /**
     * @param $path
     * @param $demo
     */
    private  function add_demo($path, $demo) {
        if(isset(self::$data[$demo])) return self::$data[$demo];
        $meta = $path .'/theme.xml'; //fetch meta data
        $xml =$this->importer->parser->simplexml_parser->read_simplexml_object($meta, false);
        if($xml!==false) {
            $hw = $xml->xml->channel->children($xml->namespaces['hw']);
            //$title = /*(string)*/ $xml->xml->xpath('/theme/channel/hw:title');
            $title = (string)$hw->title;
            //parse theme config
            $this->import($meta);//self::parse_theme_config()->import($meta);
        }
        if(empty($title)) $title= $demo;

        self::$data[$demo] = 'placeholder'; //this important to prevent infinitive loop
        $inst = new self($demo);
        $inst->demo_info = array(
            'title' => print_r($title, true), 'path' => $path, 'import_file' => $path. '/data.xml',
            'base_url' => rtrim(HW_URL::get_path_url($meta, true),'\/'). '/'
        );
        self::$data[$demo] = $inst; //add to manager
        return $inst;
    }

    /**
     * set active demo data
     * @param $demo
     */
    public function set_active() {
        self::parse_theme_config()->import($this->get_demo_config_file());
    }
    /**
     * return demo config file
     * @return string
     */
    public function get_demo_config_file() {
        return $this->info('path'). '/theme.xml';
    }
    /**
     * @param $item
     * @param $default
     * @return bool
     */
    public function info($item, $default='') {
        return isset($this->demo_info[$item])? $this->demo_info[$item] : $default;
    }
    /**
     * parse wxr data
     * @param $file
     */
    public function parse($file) {
        $this->update_variables();
        return $this->importer->parser->simplexml_parser->parse($file);
    }

    /**
     * return url to file in demo folder
     * @param $file
     * @return string
     */
    public function get_file_url($file) {
        if(!HW_URL::valid_url($file)) $file = HW_File_Directory::generate_url($this->info('base_url') , $file);
        return $file;
    }

    /**
     * update demo variables
     */
    public function update_variables() {
        $data = array('demo' => $this);
        if($this->info('base_url')) $data['import_path'] = $this->info('base_url');
        if($this->info('path')) $data['import_dir'] = $this->info('path');

        $this->importer->parser->update_variables( $data);
    }
    /**
     * get all demos
     * @return bool
     */
    public static function get_all_demos() {
        return self::get_demo();
    }
    /**
     * @param $demo
     * @return HW_Sample_Data|null
     */
    public static function get_demo($demo='') {
        self::init();
        return $demo? (isset(self::$data[$demo])? self::$data[$demo] : null) : self::$data;
    }

    /**
     * install demo data
     */
    public function install( $page=0) {
        $this->update_variables();
        $file = $this->demo_info['import_file'];
        if(file_exists($file)) $this->importer->import_file($file, $this->import_per_page ,$page);
    }
    /**
     * initialize demo instance
     */
    private  static function init() {
        if(empty(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    /**
     * magic function
     * @param $func
     * @param $args
     */
    public function __call($func, $args){

    }
}