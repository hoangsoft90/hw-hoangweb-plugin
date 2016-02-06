<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/11/2015
 * Time: 17:10
 */
/**
 * Class HW_WPCF7_Exporter
 */
class HW_WPCF7_Exporter extends HW_Module_Export {
    /**
     * main class constructor
     * @param null $module
     */
    public function __construct($module) {
        parent::__construct($module);
    }
    public function test() {
        if(empty($xml)) $xml = $this->xml_data;
        $module = $this->get_module()->option('module_name');
        $dom = new DOMDocument();
        $item = $dom->createElement('item');
        $dom->appendChild($item);
        $data = array(
            '_wpcf7-form'=> 'sfsdf',
            '_mail_2' => array(
                'active' => 'A',
                'subject' => 'B',
                'sender' => '1',
                'body' => '',
                'recipient' => '',
                'additional_headers' => ''
            )
        );
        $ele = HW_Export::array_to_xml_params($data,0, false);
        #$ele=new DOMElement('a','B');
        $i=$dom->importNode($ele,true);
        $item->appendChild($i);


    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    public function export_wxr_data($xml=null) {
        #$this->test();return;
        if(empty($xml)) $xml = $this->xml_data;
        $module = $this->get_module()->option('module_name');

        foreach($this->get_posts_xml($xml) as $item) {
            $item_atts = (array)$item->attributes();    //get xml element attributes

            $wp = $item->children($this->namespaces['wp']);
            $hw = $item->children($this->namespaces['hw']);
            $title = (string)$wp->title;

            $form = $hw->form_html->children($this->namespaces['hw']);
            $form_html = (string)$form->html;
            if(!empty($hw->mail)) {
                //$mail = self::array_to_xml_params($hw->mail, false);
                $mail = dom_import_simplexml($hw->mail );
            }
            else $mail = array();
            //form skin

            $atts = $hw->skin->attributes();
            $skin =  $hw->skin->children($this->namespaces['skin']);
            $skin_name = ($atts['name']? $atts['name'] : 'skin');
            /*$skin_instance = !empty($skin->instance)? (string) $skin->instance : 'hw-wpcf7';
            $skin_source = !empty($skin->source)? (string) $skin->source : 'plugin';

            $pskin = new HWIE_Skin_Params(($atts['name']? $atts['name'] : 'skin'), $skin_instance);
            $pskin->add_hash_skin('hash_skin', array(
                'skin' => (string)$skin->skin_name,
                'source' => $skin_source
            ));
            $pskin->add_skin_config();

            $params = array(
                'hwskin_condition' => '',
                'skin_options' => array(
                    'enqueue_css_position' => 'head',
                    'enqueue_js_position' => 'footer'
                )
            );
            if(!empty($skin->params)) {
                $skin_params = $this->simplexml_parser->recursive_option_data($skin->params)->option;
                if(!empty($skin_params)) $params = array_merge($params, $skin_params);
            }
            $pskin->extra_params($params);
            */
            $skins = $this->fetch_skins($skin, 'hash_skin', 'hwskin_config', 0,false);

            //google form id
            if(isset($hw->google_formID)) $gform_id = (string) $hw->google_formID;
            else $gform_id = '1GzynAtb3hiv6E0mFE0KhxMwARSYGdGSY8oJ5ImGM7m4';

            $this->posts->addItem(array(
                'title' => $title,
                'description' => '',
                'content'=> '',
                'excerpt' => '',
                'post_type' => 'wpcf7_contact_form',
                'post_metas'=> array(
                    '_wpcf7-form'=> $form_html,
                    '_form' => $form_html,
                    '_mail' => $mail,
                    '_mail_2' => array(
                        'active' => '0',
                        'subject' => '',
                        'sender' => '',
                        'body' => '',
                        'recipient' => '',
                        'additional_headers' => ''
                    ),
                    '_messages' => array(
                        'mail_sent_ok' => '',
                        'mail_sent_ng' => '',
                        'validation_error' =>'',
                        'spam' => '',
                        'accept_terms' => '',
                        'invalid_required' => '',
                        'invalid_too_long' => '',
                        'invalid_too_short' => ''
                    ),
                    '_additional_settings' => 'on_sent_ok: ""
on_submit: ""',
                    '_locale' => 'vi',
                    '_hw_wpcf7_use_skin' => 'on',
                    '_hw_wpcf7_skin' => $skins[$skin_name]->get_hash_skin(),    //$pskin->get_hash_skin(),
                    '_hw_wpcf7skin_setting' => $skins[$skin_name]->get_skin(),
                    '_hw_custom_css' => '',
                    '_hw_form_template' => 'basic-contact-form',
                    '_hw_gformID' => $gform_id,
                    '_enable_email_by_gapp' => 'on',
                    '_hook_url' => '',
                    '_hwcf_data_hook' => 'google_form',
                    '_hw_sent_ok_redirect_page' => '-1',
                    '_hw_on_sent_ok_js_event' => '',
                    '_hw_on_submit_js_event' => '',
                    '_hw_default_gform' => '',
                    '_hw_form_class_attr' => (string)$form->form_class,
                    '_hw_form_id_attr' => (string) $form->form_id,
                    '_hw_form_name_attr' => (string) $form->form_name,
                    '_hw_form_enctype_attr' => (string) $form->form_enctype,

                )
            ),
                $item_atts['@attributes']);
            /*$ele=new HWIE_Param('params:skin_encoded', array('instance'=> $module, 'name' => 'hash_skin'));
            $ele->add_child('skin:skin', 'skin1');
            $ele->add_child('skin:skin_type', 'file');*/

        }
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $name = !empty($atts['name'])? (string) $atts['name'] : 'loadingImg';
            $hw = $item->children($this->namespaces['hw']);
            $skin =  $hw->skin->children($this->namespaces['skin']);
            $skin_instance = !empty($skin->instance)? (string) $skin->instance : 'hwcf7_images';
            $skin_source = !empty($skin->source)? (string) $skin->source : 'plugin';
            $group = (string) $skin->group;

            //wpcf7 loading
            $skin_image = new HWIE_Skin_Params( $name, $skin_instance);
            $skin_image->add_hash_skin('hash_skin', array(
                'skin' => (string)$skin->skin_name,
                'source' => $skin_source,
                'skin_type' => (string) $skin->skin_type,
                'group' => $group
            ));
            $skin_image->add_skin_config('hwskin_config', array('group' => $group));
            $skin_image->add_skin_file('url', array(
                'file' => (string) $skin->file
            ));
            break;
        }
        if(isset($skin_image))
        $this->options->add_option('HW_Wpcf_settings',array(
            'general' => array(
                'enable_wpcf7_css' => '1',
                'enable_wpcf7_js' => '1',
                'exclude_pages' => array('__all__'),
                'loadingImg' => $skin_image->get_skin(false),
            ),
            'webhook' => array(
                'webhook_url' => ''
            )
        ));
        //start import
        $this->do_import();

    }
}
