<?php
# used by includes/website/hw-settings-implementation.php
/**
 * Class NHP_Options_features_Frontend
 */
class NHP_Options_features_Frontend extends NHP_Options_features {
    function __construct(){
        $this->setup_actions();
    }
    /**
     * add actions
     */
    private function setup_actions() {
        add_action('wp_footer', array($this, '_hw_footer'));     //put stuffs at bottom of website
        add_action('init', array($this, '_init_hook'));

        if(!is_admin() &&0) {   //disable temporary
            if(str_replace('.','',get_bloginfo('version'))>=410){
                add_filter( 'script_loader_tag', array($this, '_hw_script_loader_tag1'),10, 3);
            }
            else add_filter( 'clean_url', array($this, '_hw_defer_parsing_of_js'), 11, 1 );
        }
    }

    /**
     * defer parsing
     * @param $url
     * @return string
     */
    public function _hw_defer_parsing_of_js ( $url ) {
        if ( FALSE === strpos( $url, '.js' ) ) return $url;
        if ( strpos( $url, 'jquery.js' ) !=false) return $url;
        return "$url defer onload='";
    }

    /**
     * @param $tag
     * @param $handle
     * @param $src
     * @return mixed
     */
    public function _hw_script_loader_tag1( $tag, $handle ,$src) {
        if( is_admin() ) {
            return $tag;
        }
        return str_replace( ' src', ' defer src', $tag );
    }

    /**
     * check whether exists hook 'wp_footer' and insert your content after all exists that assign to it
     */
    public function _hw_footer(){
        self::do_scroll2top();  //scroll to top
        self::do_bacground_effect();        //bg effect
    }
    /**
     * init hook
     */
    public function _init_hook(){
        //Increase the memory limit
        if(!defined('WP_MEMORY_LIMIT')) define('WP_MEMORY_LIMIT', '64MB');
        ini_set('memory_limit', '3G');

        //debug mode
        self::do_debug_mode();
        //desploy footer skin
        NHP_Options_footer::do_footer_skin();
    }
}
HW_Options_Frontend::add_fragment(new NHP_Options_features_Frontend());