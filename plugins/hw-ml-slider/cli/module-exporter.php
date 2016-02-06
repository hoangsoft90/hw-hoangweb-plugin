<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 22:08
 */
/**
 * Class HW_ML_Slider_Exporter
 */
class HW_ML_Slider_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('hw_metaslider_widget','hwml_widget');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        foreach($this->get_posts_xml($xml) as $item) {
            $atts = $item->attributes();    //get xml element attributes
            $hw = $item->children($this->namespaces['hw']);
            $wp = $item->children($this->namespaces['wp']);
            $title = (string)$wp->title;
            //get skins
            $skins = $this->fetch_skins($hw->skin, 'hash_skin', 'hwskin_config',0, false);

            //source path
            $source= (string) $hw->source;
            #if(!file_exists($source)) $source = get_stylesheet_directory().'/' .$source;

            $settings = $this->recursive_option_data($hw->settings->children())->option;
            if(isset($skins['theme'])) $settings['theme']= $skins['theme']->get_hash_skin_code();

            $this->run_cli('add_slider', array(
                'title' => $title,
                'source' => 'upload',
                'num' => '3',
                'from_path' => 'theme',
                'source_path' => $source,
                'settings' => HW_Encryptor::encode64($settings)
            ));
        }
        $this->add_export_widgets();
        $this->do_import();
    }
}