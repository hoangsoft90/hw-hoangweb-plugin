<?php
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/11/2015
 * Time: 22:51
 */
/**
 * Class HW_WP_Languages
 */
if(class_exists('HW_Admin_Options', false)):
class HW_WP_Languages extends HW_Admin_Options{
    /**
     * singleton
     */
    public static $instance;
    /**
     * @return mixed|void
     */
    public function load() {
        // add local filter
        add_filter('locale', array($this, '_set_locale'));
        /**
         * ajax handle url
         */
        add_action("wp_ajax_hw_upload_polang_files", array(&$this,"_hw_upload_polang_files")); //only for admin page
        add_action('wp_ajax_nopriv_hw_upload_polang_files', array(&$this, '_require_login'));
    }
    /**
     * upload po files to wp /languages folder
     * @ajax hw_upload_polang_files
     */
    public function _hw_upload_polang_files(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_upload_polang_files_nonce")) {
            exit("hacked !");
        }
        $po_files = isset($_GET['langs'])? $_GET['langs'] : '';

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            HW_WP_Languages::upload_langs_mo($po_files);
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    /**
     * require login for ajax requesting
     */
    public function _require_login(){
        echo "login required!";
        die();
    }
    /**
     * get avaiable languages from dir
     * @param null $dir
     * @return array
     */
    public static  function hw_get_available_languages( $dir = null ) {
        $languages = array();

        foreach( (array)glob( ( is_null( $dir) ? HW_HOANGWEB_PATH.'/data/all_languages' : $dir ) . '/*.mo' ) as $lang_file ) {
            $lang_file = basename($lang_file, '.mo');
            if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' )
                /*&& 0 !== strpos( $lang_file, 'admin-' )*/    //include admin langs
            )
                $languages[] = $lang_file;
        }
        $avaibles_languages = self::get_available_languages(); //avaiable languages
        $languages = array_merge($languages,$avaibles_languages);   //merge exists installed lang in wp system

        return $languages;
    }
    /**
     * get avaiable languages
     */
    private static function get_available_languages() {
        $installed_langs =  get_available_languages();
        $exists_langs = array();
        //get exists .mo file in wp-content/languages folder
        foreach( (array)glob( ( WP_CONTENT_DIR.'/languages' ) . '/*.mo' ) as $mo_file ) {
            array_push($exists_langs, basename($mo_file, '.mo') );
        }
        return array_merge($installed_langs, $exists_langs);
    }
    /**
     * display list of language in dropdown
     * @param array $args
     */
    public static function hw_dropdown_languages( $args = array() ) {
        $avaibles_languages = self::get_available_languages(); //avaiable languages

        $args = wp_parse_args( $args, array(
            'id'           => '',
            'name'         => '',
            'languages'    => array(),
            'translations' => array(),
            'selected'     => '',
            'show_available_translations' => true,
        ) );

        $translations = $args['translations'];
        if ( empty( $translations ) ) {
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations();
        }

        if(empty($args['languages'])) $args['languages'] = self::hw_get_available_languages();

        /*
         * $args['languages'] should only contain the locales. Find the locale in
         * $translations to get the native name. Fall back to locale.
         */
        $languages = array();
        foreach ( $args['languages'] as $locale ) {
            //check lang for avaiable
            if(in_array($locale,$avaibles_languages)) continue; //exists installed this language
            $found_uninstalled = true;
            if ( isset( $translations[ $locale ] ) ) {
                $translation = $translations[ $locale ];
                $languages[] = array(
                    'language'    => $translation['language'],
                    'native_name' => $translation['native_name'],
                    'lang'        => $translation['iso'][1],
                );

                // Remove installed language from available translations.
                unset( $translations[ $locale ] );
            } else {
                $languages[] = array(
                    'language'    => $locale,
                    'native_name' => $locale,
                    'lang'        => '',
                );
            }
        }
        if(!isset($found_uninstalled)) return false;    //no avaible uninstalled languages in wp system

        printf( '<select name="%s" id="%s" multiple style="max-height:200px;width:200px">', esc_attr( $args['name'] ), esc_attr( $args['id'] ) );

        // Holds the HTML markup.
        $structure = array();

        foreach ( $languages as $language ) {
            $structure[] = sprintf(
                '<option value="%s" lang="%s"%s data-installed="1">%s</option>',
                esc_attr( $language['language'] ),
                esc_attr( $language['lang'] ),
                selected( $language['language'], $args['selected'], false ),
                esc_html( $language['native_name'] )
            );
        }

        echo join( "\n", $structure );

        echo '</select>';
        return true;
    }

    /**
     * copy languages files known as .mo to WP_CONTENT/languages path to make wordpress multiple language
     * @param $po_files
     * @param string $src
     */
    public static function upload_langs_mo($po_files, $src='') {
        if(is_string($po_files)) $po_files = explode(',', $po_files);
        if(!is_array($po_files)) return ;
        $po_files = array_map(function($s){ return $s.'.mo';}, $po_files);

        if(!$src || !file_exists($src)) $src = HW_HOANGWEB_PATH.'/data/all_languages';
        $dst = WP_CONTENT_DIR.'/languages';
        if(!is_dir($dst)) mkdir($dst);   //make directory if not exists
        $files = glob($src.'/*.mo');

        foreach($files as $file){
            preg_match('#[^/]*$#',$file,$name);
            if(!in_array($name[0], $po_files)) continue;

            $file_to_go = str_replace($src,$dst,$file);
            if(!file_exists($file_to_go)){  //upload if not exists
                copy($file, $file_to_go);
            }

        }
    }

    /**
     * change wp site language
     * @param string $lang
     */
    public function change_site_language($lang='') {
        self::upload_langs_mo($lang);   //upload po file
        $languages = self::get_available_languages();
        if(!in_array($lang, $languages)) return; //locale not found

        self::add_wp_option('lang', $lang);

    }

    /**
     * @hook locale
     * @param $locale
     * @return mixed
     */
    function _set_locale($locale) {
        /* Note: user_meta and user_info are two functions made by me,
           user_info will grab the current user ID and use it for
           grabbing user_meta */

        // grab user_meta "lang" value
        //$lang = user_meta(user_info('ID', false), 'lang', false);
        $lang = self::get_wp_option('lang');

        // if user_meta lang is not empty
        if ( !empty($lang) ) {
            $locale = $lang; /* set locale to lang */
        }

        return $locale;
    }
    public static function __init() {

    }
}
endif;