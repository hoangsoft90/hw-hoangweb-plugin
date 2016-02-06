<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_List_Custom_Taxonomy_Widget_Exporter
 */
class HW_List_Custom_Taxonomy_Widget_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets(/*'hw_wexr',*/'hwlct_taxonomy');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //add new skin
        $this->skins->add_skin('lct', array(
            'default_skin_path' => 'skins',
            'default_skin' => 'default',
            'skin_type' => 'file',
            'skin_name' => 'hwlct-skin.php',
            'other_folder' => 'hw_lct_skins',
            'allows_skin_names' => array('hwawc-skin.php', 'xx.php')
        ));
        //add custom widget
        $wexr = array(
            'title' => 'Weather, excha ..test',
            'show_opts' => array(
                'c_weather' => 'on',
                'c_weather_source' => 'yahoo',
                'c_weather_def_location' => 'hochiminh',
                'c_exr' => 'on',
                'c_exr_source' => 'vietcombank'
            )
        );
        //$this->add_export($this->widgets->add_widget('widget1', $wexr, 'hw_wexr'));
        $this->add_export_widgets();
        //$t=$this->widgets->get('danhmuc'); //get('hwlct_taxonomy','danhmuc')

        $this->do_import();
    }
}