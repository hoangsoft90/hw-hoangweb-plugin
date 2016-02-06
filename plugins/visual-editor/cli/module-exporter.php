<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/11/2015
 * Time: 08:20
 */
class Visual_Editor_Exporter extends HW_Module_Export {
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_visualeditor');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        $this->add_export_widgets();
        $this->do_import();
    }
}