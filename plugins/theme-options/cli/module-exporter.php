<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/11/2015
 * Time: 17:31
 */
/**
 * Class Theme_Options_Exporter
 */
class Theme_Options_Exporter extends HW_Module_Export {
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        $options = array(); //total option page
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $name = (string) $atts['name'];
            $hw = $item->children($this->namespaces['hw']);
            $option = array();
            //get skin
            if(!empty($hw->skin)) {
                $skin = $hw->skin->children($this->namespaces['skin']);
                $skin_name = (string)$hw->skin->attributes()->name;
                /*
                $pskin = new HWIE_Skin_Params($skin_name, (string)$skin->instance);
                $pskin->add_hash_skin('hash_skin', array(
                    'skin' => (string)$skin->skin_name,
                    'file' => (string) $skin->file,
                    'source' => (string) $skin->source,
                    'skin_type' => !empty($skin->skin_type)? (string)$skin->skin_type : 'file'
                ));
                $pskin->add_skin_config();
                $skin_params = array(
                    'hwskin_condition' => "",
                    'skin_options' => array(
                        'enqueue_css_position' => 'footer',
                        'enqueue_js_position' => 'footer'
                    )
                );
                $pskin->extra_params($skin_params);
                */
                $skins = $this->fetch_skins($hw->skin, 'hash_skin', 'hwskin_config',0, false);
            }
            else $skins= 0;

            if($name == 'footer') { //for footer setting
                $option = array(
                    #'footer' => '',
                    'before_footer' => '',
                    'after_footer' => ''
                );
                if(!empty($hw->params)) {
                    $option = array_merge($option, $this->recursive_option_data($hw->params[0]->children())->option);
                    $option = array_filter($option);
                }
                if($skins) {
                    $option[$skin_name] = $skins[$skin_name]->get_skin(0);
                }
            }
            //scroll2top option
            elseif($name == 'scroll2top') {
                $option = array(
                    'scroll2top' => '1',
                );
                if($skins) $option[$skin_name] = $skins[$skin_name]->get_skin(0);
            }
            //socials
            elseif($name == 'social') {
                $option = array(
                    'facebook_url' => (string)$hw->facebook,
                    'google_url' => (string)$hw->google,
                    'twitter_url' => (string) $hw->twitter,
                    'youtube_url' => (string) $hw->youtube,

                );
                if($skins) $option[$skin_name] = $skins[$skin_name]->get_skin(0);
            }
            //translate
            elseif($name == 'translate') {
                $option = array(
                    'mqtrans_style' => (string)$hw->style,
                    'enable_googletranslate' => (string) $hw->use_google_translate
                );
                if(!$option['enable_googletranslate'] && $skins) {    //make sure you not turn on google translate
                    $option[$skin_name] = $skins[$skin_name]->get_skin(0);
                }
            }
            //ads setting
            elseif($name == 'ads') {
                if(!empty($hw->left_ads)) {
                    $allow_shortcode = $hw->left_ads->attributes()->shortcode;
                    if(1||$allow_shortcode) {
                        //$left_ad = do_shortcode((string)$hw->left_ads);
                        $left_ad= $this->get_hw_params_element($hw->left_ads, 'params');
                    }
                }
                if(!empty($hw->right_ads)) {
                    $allow_shortcode = $hw->right_ads->attributes()->shortcode;
                    if(1||$allow_shortcode) {
                        //$right_ad = do_shortcode((string)$hw->right_ads);
                        $right_ad= $this->get_hw_params_element($hw->right_ads, 'params');
                    }
                }

                $option = array(
                    'enable_flra' => (string) $hw->enable,
                    'ads_active_mobile' => (string)$hw->active_mobile,
                    'ads_effects' => (string) $hw->effects,
                    'mcontent_div' => (string)$hw->css_selector,
                    'lad_width' => (string) $hw->ads_width,
                    'rad_width' => (string) $hw->ads_width,
                    'top_adjust' => (string) $hw->top,
                    'ad_left' => /*$this->ie_param_object*/($left_ad),
                    'ad_right' => /*$this->ie_param_object*/($right_ad)
                );
            }
            else $option = $this->simplexml_parser->recursive_option_data($item->children())->option;
            if(is_array($option) && count($option)) $options = array_merge($options, $option);
        }

        if(!empty($options)) {
            $this->options->add_nhp_setting_page( $options);
            $this->do_import();
        }

    }
    //test import result filter
    public function test_filter_attachment($value) {
        return  $value;
    }
    public function test_filter_import_result($value) {
        return $value;
    }
}