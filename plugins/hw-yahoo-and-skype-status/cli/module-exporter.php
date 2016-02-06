<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 23:41
 */
/**
 * Class HW_Yahoo_And_Skype_Status_Exporter
 */
class HW_Yahoo_And_Skype_Status_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_yahoo_skype_status' );
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //adding widget from theme.xml
        $this->add_export_widgets();
        $this->do_import();
    }
}