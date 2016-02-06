<?php
/**
 * Class HW_mqtranslate
 */
class HW_mqtranslate {
    /**
     * get actived languages
     * @return array
     */
    public static function get_languages() {
        $active_langs = array();
        if(function_exists('qtrans_getSortedLanguages') ) {
            $active_langs = qtrans_getSortedLanguages();
        }
        return $active_langs;
    }
    /**
     * valid flags css
     */
    public static function valid_flags_css () {
        //fixed flags class for mqtranslate
        $flags_url = HW_HOANGWEB_URL.('/images/flags' );        //flags url, plugins_url('mqtranslate/flags')
        $flags_dir = HW_HOANGWEB_PATH . '/images/flags';  #dirname(HW_HOANGWEB_PATH).'/mqtranslate/flags';    //flags images path
        $css =array();      //return string of css
        $active_langs = array();    //enabled languages
        //rename flags name
        $rename_flags = array(
            'gb'=> array('en', true,false),     #new name, true: allow new name, false: use default name as lang slug
            'cn' => array('zh', true,false),
            'vi' => array('vn', false, true)
        );

        $active_langs = self::get_languages();

        if(file_exists($flags_dir)) {
            $all_flags = (array)glob( $flags_dir . '/*.png' );
            foreach ($all_flags as $file) {
                //get flag name
                $name = $flag_file = basename($file,'.png');
                if(isset($rename_flags[$name])) {
                    $flag_name = $rename_flags[$name][0];
                    if($rename_flags[$name][1] == true) $name = $flag_name; //name
                    if($rename_flags[$flag_file][2] == true) $flag_file = $flag_name;
                }

                //check active lang
                if(count($active_langs) && ! in_array($name, $active_langs)) continue;

                $css[] = ".qtrans_flag_{$name}{background-image:url({$flags_url}/"./*basename($file)*/$flag_file.".png) !important;}";
            }
        }
        if(count($css)) {
            echo '<style type="text/css">';
            echo implode("\n", $css);
            echo '</style>';
        }
    }

    /**
     * convert url
     * @param $url
     * @param $language
     * @return mixed|string
     */
    public static function convertURL($url, $language) {
        RETURN qtrans_convertURL($url, $language,false, true);
    }
    /**
     * generate actived languages data
     * @return array
     */
    public static function generateLanguageSelectCode() {
        return self::qtrans_generateLanguageSelectCode();
    }
    /**
     * cover from function qtrans_generateLanguageSelectCode at mqtranslate_widget.php
     * @param $style: extend param by mqtranslate function qtrans_generateLanguageSelectCode
     * @param $id: extend param by mqtranslate function qtrans_generateLanguageSelectCode
     * @param $tpl: twig template
     */
    public static function qtrans_generateLanguageSelectCode() {
        $id = 'mqtranslate';
        $style = hw_option('mqtrans_style');
        //get class name
        switch($style) {
            case 'image': $anchor_class = 'qtrans_flag';break;
            case 'both': $anchor_class = 'qtrans_flag_and_text';break;
            default:
                $anchor_class = '';
        }

        if (function_exists('is_plugin_active') && is_plugin_active('qtranslate-slug/qtranslate-slug.php')){
            qts_language_menu($style, array( 'id' => $id, 'short' => '' ) );
        }
        else{
            global $q_config;
            if($style=='') $style='text';
            if(is_bool($style)&&$style) $style='image';
            if(is_404()) $url = get_option('home'); else $url = '';
            $id .= '-chooser';
            $data = array();

            $data['wrapper'] = array(
                'id' => $id,
                'class' => 'qtrans_language_chooser'
            );
            $data['active_langs'] = array();
            //echo '<ul class="qtrans_language_chooser" id="'.$id.'">';
            //qtrans_getSortedLanguages:    //for mqtranslate & qtranslate-x plugin, note for qtranslate-x enable `Compatibility Functions`
            foreach(self::get_languages() as $language) {
                $item = array();
                //li class
                $classes = array('lang-'.$language);
                if($language == $q_config['language']) {
                    //$item['class'] = 'active';
                    $classes[] = 'active';
                }
                $item['class'] = implode(' ', $classes);

                $item['url'] = self::convertURL($url, $language);
                $item['anchor_class'] = 'qtrans_flag_' . $language . ' '.$anchor_class;
                $item['title'] = $q_config['language_name'][$language];
                $item['text'] = $q_config['language_name'][$language];

                $data['active_langs'][] = $item;
                /*echo '<li';
                if($language == $q_config['language'])
                    echo ' class="active"';
                echo '><a href="'.qtrans_convertURL($url, $language).'"';
                echo ' class="qtrans_flag_'.$language.' qtrans_flag_and_text" title="'.$q_config['language_name'][$language].'"';
                echo '><span>'.$q_config['language_name'][$language].'</span></a></li>';*/
            }
            //echo "</ul><div class=\"qtrans_widget_end\"></div>";
            return $data;
        }
    }
}