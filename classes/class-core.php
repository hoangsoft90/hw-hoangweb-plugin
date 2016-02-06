<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 17/10/2015
 * Time: 13:58
 */
class HW_WP {
    /**
     * footer callback output
     * @var string
     */
    static $footer_output = '';

    /**
     * load wp system
     */
    public static function load_wp() {
        define( 'BASE_PATH', self::find_wordpress_base_path()."/" );
        global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
        if(file_exists(BASE_PATH . 'wp-load.php')) {
            require(BASE_PATH . 'wp-load.php');
            return true;
        }
        return false;
    }
    /**
     * BEGIN LOAD WORDPRESS
     * find wp base path
     */
    private static function find_wordpress_base_path() {
        $dir = dirname(__FILE__);
        do {
            //it is possible to check for other files here
            if( file_exists($dir."/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath("$dir/..") );
        return null;
    }
    /**
     * clean wp_head
     */
    public static function hw_clean_wp_head() {
        if(!defined('DOING_AJAX')) return;
        /* This will remove Really Simple Discovery link from the header */
        remove_action('wp_head', 'rsd_link');

        /* This will remove the Wordpress generator tag  */
        remove_action('wp_head', 'wp_generator');

        /* This will remove the standard feed links */
        remove_action( 'wp_head', 'feed_links', 2 );

        /* This will remove the extra feed links */
        remove_action( 'wp_head', 'feed_links_extra', 3 );

        /* This will remove index link */
        remove_action('wp_head', 'index_rel_link');

        /* This will remove wlwmanifest */
        remove_action('wp_head', 'wlwmanifest_link');

        /* This will remove parent post link */
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);

        /*This will remove start post link */
        remove_action('wp_head', 'start_post_rel_link');

        /* This will remove the prev and next post link */
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

        /* This will remove shortlink for the page */
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

        //remove all hook assigned to wp_head
        global $wp_filter;
        //scan all assets
        $assets = array();
        //for both stylesheets & scripts (admin hooks)
        if(!empty($wp_filter['admin_print_styles'])) $assets = array_merge($assets, $wp_filter['admin_print_styles']);
        if(!empty($wp_filter['admin_print_scripts'])) $assets = array_merge($assets, $wp_filter['admin_print_scripts']);
        if(!empty($wp_filter['admin_enqueue_scripts'])) $assets = array_merge($assets, $wp_filter['admin_enqueue_scripts']);

        if(!empty($wp_filter['login_enqueue_scripts'])) $assets = array_merge($assets, $wp_filter['login_enqueue_scripts']);
        //Front-End Hooks
        if(!empty($wp_filter['wp_enqueue_scripts']) ) $assets = array_merge($assets, $wp_filter['wp_enqueue_scripts']);
        if(!empty($wp_filter['wp_print_scripts'])) $assets = array_merge($assets, $wp_filter['wp_print_scripts']);
        if(!empty($wp_filter['wp_print_styles'])) $assets = array_merge($assets, $wp_filter['wp_print_styles']);

        foreach($assets as $periror => &$cb){
            foreach($cb as $name => $d){//if(is_array($d['function']))_print($d['function']);
                //if(strpos($name,'wpcf7')===false)
                remove_action('wp_enqueue_scripts',$d['function']);      //remove scripts from frontend
                remove_action('wp_print_scripts',$d['function']);      //remove scripts from frontend
                remove_action('wp_print_styles',$d['function']);      //remove scripts from frontend

                remove_action('admin_enqueue_scripts',$d['function']);   //remove scripts from admin
                remove_action('admin_print_scripts',$d['function']);   //remove scripts from admin
                remove_action('admin_print_styles',$d['function']);   //remove scripts from admin

                remove_action('login_enqueue_scripts',$d['function']);   //remove scripts from admin
            }
        }

    }
    static function hw_clean_wp_footer() {

    }

    /**
     * @param $callback
     */
    public static function change_footer_stuffs($callback) {
        if(is_callable($callback)) self::$footer_output = $callback;
        add_action('wp_footer', array(__CLASS__, '_start_footer_ob') , 1);
        add_action('wp_footer', array(__CLASS__, '_end_footer_ob') , 10000);
    }

    function _start_footer_ob() {
        if(is_callable(self::$footer_output)) {
            ob_start(self::$footer_output);
        }
        else ob_start(array(__CLASS__, '_end_footer_ob_callback'));
    }
    function _end_footer_ob() {
        ob_end_flush();
    }

    function _end_footer_ob_callback($buffer) {
        // remove what you need from he buffer
        // remove what you need from he buffer

        /*$doc = new DOMDocument;
        $doc->loadHTML($buffer);

        $docElem = $doc->getElementById("yarppWidgetCss-css");__save_session('a2',$docElem);
        foreach($docElem as $ele) {
            _print($ele);
        }

        if($docElem !== NULL) // if it exists
            $docElem->parentNode->removeChild($docElem);

        return $doc->getElementsByTagName('body')->firstChild->nodeValue;
        */
        return $buffer;
    }
}