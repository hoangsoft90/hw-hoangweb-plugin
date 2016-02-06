<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 23:29
 */
/**
 * Class HW_Taxonomy_Post_List_Widget_Exporter
 */
class HW_Taxonomy_Post_List_Widget_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_taxonomy_post_list_widget');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        /*foreach ($this->get_widgets_xml($xml) as $item) {
            $atts = $item->attributes();

        }*/
        //add widget controll by theme.xml
        $this->add_export_widgets();
        $this->do_import();
    }
}