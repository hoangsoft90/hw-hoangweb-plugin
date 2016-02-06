<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/07/2015
 * Time: 10:20
 */
class NHP_Options_mqtranslate_Frontend extends NHP_Options_mqtranslate {

    /**
     * return google translate layout param by display mode
     * @param $display_mode
     */
    public static function get_googletrans_layout_param($display_mode) {
        switch($display_mode) {
            case 'inline-veritcal': return '';
            case 'inline-horizontal': return 'google.translate.TranslateElement.InlineLayout.HORIZONTAL';
            case 'inline-dropdown-only': return 'google.translate.TranslateElement.InlineLayout.SIMPLE';
            case 'tabbed-lower-right': return 'google.translate.TranslateElement.FloatPosition.BOTTOM_RIGHT';
            case 'tabbed-lower-left': return 'google.translate.TranslateElement.FloatPosition.BOTTOM_LEFT';
            case 'tabbed-upper-right': return 'google.translate.TranslateElement.FloatPosition.TOP_RIGHT';
            case 'tabbed-upper-left': return 'google.translate.TranslateElement.FloatPosition.TOP_LEFT';
            default: return '';
        }
    }
    /**
     * get qtranslate switcher
     */
    public static function get_qtrans_switcher() {
        $mqtrans_skin = hw_option('mqtrans_skin');    //get mqtrans skin
        $other_service = hw_option('enable_googletranslate');   //use google translate?

        if(isset($mqtrans_skin['hash_skin']) && isset($mqtrans_skin['hwskin_config'])){
            $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($mqtrans_skin);
            //skin options
            //$skin_options = $mqtrans_skin['skin_options'];
            $html = '';     //output
            $file = ($skin->instance->get_skin_file($skin->hash_skin));

            //load footer template
            if(file_exists($file)) {
                HW_HOANGWEB::load_class('HW_String');
                HW_HOANGWEB::load_class('HW_Twig_Template');
                HW_HOANGWEB::load_class('HW_mqtranslate');

                //get theme setting file
                $setting = $skin->instance->get_file_skin_setting() ;//(new HW_SKIN);
                if(file_exists($setting)) include($setting);

                //skin options
                $skin_options_file = $skin->instance->get_file_skin_options();  //(new HW_SKIN)->enqueue_files_from_skin()
                $skin_options = isset($mqtrans_skin['skin_options'])? $mqtrans_skin['skin_options'] : array();  //user options
                $skin_options = HW_SKIN::merge_skin_options_values($skin_options, $setting, $skin_options_file);

                $data = array();    //data send to twig template

                /*active google translate*/
                if($other_service) {
                    $TranslateElement_opts = array(
                        'pageLanguage' => 'vi',
                    );
                    //layout
                    $layout = self::get_googletrans_layout_param(isset($skin_options['display_mode'])? $skin_options['display_mode'] : '');
                    if($layout) $TranslateElement_opts['layout'] = $layout;
                    //include languages
                    if(!empty($skin_options['specific_langs']) && is_array($skin_options['specific_langs'])) {
                        $TranslateElement_opts['includedLanguages'] = join($skin_options['specific_langs'], ',');
                    }
                    $data['google_translate_ID'] = !empty($skin_options['google_translate_ID'])? $skin_options['google_translate_ID'] : HW_String::generateRandomString();
                }
                /*mqtranslate plugin*/
                else {

                    //prepare data for template
                    if(class_exists('HW_mqtranslate')) {    // make sure use __autoload
                        $data = HW_mqtranslate::generateLanguageSelectCode();
                    }

                }
                //get templates folder from skin
                if(isset($theme) && isset($theme['templates_folder'])) {
                    $tpl = $theme['templates_folder'];
                }
                else $tpl = '';
                if(class_exists('HW_Twig_Template')) {
                    $twig = HW_Twig_Template::create($skin->instance->get_file_skin_resource($tpl));
                    if(isset($data)) $twig->set_template_data($data);    //inject data to current twig for skin using
                }

                ob_start();
                //google translate
                if(isset($TranslateElement_opts)) {
                    $json = HW_SKIN_Option::build_json_options($TranslateElement_opts, null, 'layout');
                    echo '<script type="text/javascript">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement('.$json.', "'. $data['google_translate_ID'] .'");
                    }
                    </script>
                    ';
                }
                $content = $skin->instance->render_skin_template(compact('wrapper','active_langs','text'),false);  //data, return=false
                if($content!==false) echo $content;
                if($skin->instance->allow_skin_file()) include($file);
                $html = ob_get_contents();
                if($html && ob_get_length()) ob_end_clean();
            }

            //valid
            if(!isset($theme['styles'])) $theme['styles'] = array();
            if(!isset($theme['scripts'])) $theme['scripts'] = array();
            //put stuff from skin
            if(count($theme['styles']) || count($theme['scripts'])) {
                $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
            }
            return $html;
        }
    }
}
