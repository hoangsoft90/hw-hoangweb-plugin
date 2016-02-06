<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/11/2015
 * Time: 13:41
 */
/**
 * Class Map_Exporter
 */
class Map_Exporter extends HW_Module_Export {
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_gmap');
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

            $settings = $this->simplexml_parser->recursive_option_data($item->children())->option;
            $this->options->add_module_setting_page( $settings);
        }
        $this->add_export_widgets();
        $this->do_import();
    }
}