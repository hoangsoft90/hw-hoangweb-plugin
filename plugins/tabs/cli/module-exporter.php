<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/11/2015
 * Time: 13:26
 */
/**
 * Class Tabs_Exporter
 */
class Tabs_Exporter extends HW_Module_Export {
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $name = (string) $atts['name'];
            $hw = $item->children($this->namespaces['hw']);

            $options = $this->recursive_option_data($hw->params->children())->option;
            $this->options->add_module_setting_page($options);
        }
        $this->do_import();
    }
}