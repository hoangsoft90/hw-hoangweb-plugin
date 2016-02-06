<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class Counter_Exporter
 */
class Counter_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_StatsMechanic');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //options
        foreach($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $hw = $item->children($this->namespaces['hw']);

            $setting = array(
                'statsmechanic_style' => (string) $hw->counter_style
            );
            $this->options->add_module_setting_page( $setting);
        }
        //here you create other widgets
        $this->add_export_widgets();
        $this->do_import();
    }
}