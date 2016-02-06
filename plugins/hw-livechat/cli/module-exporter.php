<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/11/2015
 * Time: 20:22
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Livechat_Exporter
 */
class HW_Livechat_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
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
            $hw = $item->children($this->namespaces['hw']);
            #$skin =  $hw->skin->children($this->namespaces['skin']);
            $skins = $this->fetch_skins($hw->skin);

            $setting = array(
                'enable_livechat' => '1',
                'active_on_mobile' => '1',
                'chat_service' => (string) $hw->service,
                'chat_embed_code' => (string) $hw->embed_code,

            );
            //if(!empty($skins['chat_skin'])) $setting['chat_skin'] = $skins['chat_skin']->get_skin(0);
            if(!empty($skins['chat_skin'])) $setting = array_merge($setting, $skins);
            $this->options->add_option('HW_Livechat_settings', $setting);
        }
        $this->do_import();
    }
}