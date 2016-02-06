<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 23:17
 */
/**
 * Class HW_Social_Sharing_Exporter
 */
class HW_Social_Sharing_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hwss');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $hw = $item->children('hw');

            $settings = $this->simplexml_parser->recursive_option_data($item->children())->option;

            $this->options->add_option('HW_SocialsShare_Settings', $settings);
        }
        $this->add_export_widgets();    //add available widgets if found
        $this->do_import();
    }
}