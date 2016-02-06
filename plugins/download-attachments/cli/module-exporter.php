<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/11/2015
 * Time: 11:05
 */
/**
 * Class Download_Attachments_Exporter
 */
class Download_Attachments_Exporter extends HW_Module_Export {
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $option = (string)$atts['name'];
            $hw = $item->children($this->namespaces['hw']);

            //admin page setting
            if($option =='download_attachments_general') {
                $setting = array(
                    'capabilities' => array(
                        'manage_download_attachments'
                    ),
                    'deactivation_delete' => '',
                    'backend_columns' => array(
                        'id'=>'1',
                        'author'=>'1',
                        'title'=>'1',
                        'type' => '1',
                        'size' => '1',
                        'date' => '1',
                        'downloads' => '1',
                    ),
                    'backend_content' => array(
                        'caption' => '1',
                        'description' => '1'
                    ),
                    'attachment_link' => 'modal',
                    'library'=> 'all',
                    'downloads_in_media_library' => '1',
                    'use_css_style' => '0',
                    'pretty_urls'=>"",
                    'download_link' => 'download',
                );
                $data = $this->simplexml_parser->recursive_option_data($item->children())->option;
                if(!empty($data)) $setting = array_merge($setting, $data);
                $this->options->add_option($option, $setting);
            }
            //module tab setting
            if($option =='HW_Module_Settings_page') {
                $setting = array(
                    'content_before' => (string) $hw->content_before,
                    'content_after' => (string) $hw->content_after
                );
                $skin_name = (string)$hw->skin->attributes()->name;
                $skin = $hw->skin->children($this->namespaces['skin']);
                //skin
                $pskin = new HWIE_Skin_Params($skin_name, (string) $skin->instance);
                $pskin->add_hash_skin('hash_skin', array(
                    'skin' => (string)$skin->skin_name,
                    'source' => (string)$skin->source,
                ));
                $pskin->add_skin_config();
                $setting[$skin_name] = $pskin->get_skin(0);

                //other params
                if(!empty($hw->params)) {
                    $other = $this->simplexml_parser->recursive_option_data($hw->params[0]->children())->option;
                    $setting = array_merge($setting, $other);
                }

                $this->options->add_module_setting_page( $setting);
            }
        }
        $this->do_import();
    }
}