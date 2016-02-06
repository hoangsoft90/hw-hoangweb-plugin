<?php 

?>
<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

#require_once('class-usage.php');
#require_once('options-page.php');  #we do not need options page

#you should define WP_SITEURL constant in wp-config.php before active this plugin. ie:
/*
#begin ngrok
#define ('WP_CONTENT_DIR', ABSPATH .'my-content') ;
#define ('WP_CONTENT_URL', 'http://your-domain/my-content');
define('NGROK_ID', '2250aa09');
define('NGROK_URL', 'http://'.NGROK_ID.'.ngrok.com');
define('HW_ORIGINAL_URL', 'http://localhost');
define('WP_HOME', NGROK_URL. PATH_CURRENT_SITE);
define('WP_SITEURL', NGROK_URL. PATH_CURRENT_SITE);

#define( 'WP_ACCESSIBLE_HOSTS', NGROK_ID.'.ngrok.com');

# change other related URL.
define( 'WP_CONTENT_URL', NGROK_URL. PATH_CURRENT_SITE.'wp-content' );

define ('WP_PLUGIN_URL', NGROK_URL. '/wp2/wp-content/plugins');
define ('WP_PLUGIN_DIR',  'E:\HoangData\xampp_htdocs\wp2\wp-content\plugins');
define( 'PLUGINDIR',  'E:\HoangData\xampp_htdocs\wp2\wp-content\plugins' );
#end ngrok
*/
#register_activation_hook( __FILE__, 'child_plugin_activate' );
function child_plugin_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'odt-relative-urls-master/odt-relative-urls.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sory, this plugin required plugin "odt-relative-urls-master". <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}

/**
 * check whether ngrok actived?
 * @param string $site
 * @return bool
 */
function is_active_ngrok($site='') {
    global $wpdb;
    $query = 'select domain from wp_blogs';
    if($site) $query .= " where path like %$site%";
    $result = $wpdb->get_row($query);
    return ($result && $result->domain != 'localhost');
}

if(is_active_ngrok()){
    /**
     * return ngrok option
     * @param $name
     */
    function hwngrok_option($name, $default='') {
        $data = get_option( 'hwngrok_settings' ,array());
        return isset( $data[$name] ) ? $data[$name] : $default;
    }

//for ngrok: change to other domain
    add_filter('wp_get_attachment_url', 'honor_ssl_for_attachments');
    function honor_ssl_for_attachments($url) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;
        #$data = get_option('hwngrok_options');
        #$ngrok = hwngrok_option( 'text_baseurl_ngrok',NGROK_ID);
        $ngrok = 'http://'.NGROK_ID. '.ngrok.com';
        return str_replace(HW_ORIGINAL_URL,$ngrok,$url);
    }
    add_filter('the_content','ngrok_the_content');
    function ngrok_the_content($content){
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        #$ngrok = hwngrok_option( 'text_baseurl_ngrok',NGROK_ID);
        $ngrok = NGROK_URL;
        return str_replace(HW_ORIGINAL_URL, $ngrok,$content);
    }

    /**
     * modify header image
     * echo get_theme_mod('header_image')
     */
    add_filter( 'theme_mod_header_image', 'ngrok__child_theme_header_image' );
    function ngrok__child_theme_header_image($src) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;
        #$ngrok = hwngrok_option( 'text_baseurl_ngrok');
        $ngrok = NGROK_URL;
        return str_replace(HW_ORIGINAL_URL, $ngrok,$src);
    }

    /**
     * hoangweb logo
     * echo get_theme_mod('image_logo')
     */
    add_filter( 'theme_mod_image_logo', 'ngrok__child_theme');
    function ngrok__child_theme($url) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        return str_replace(HW_ORIGINAL_URL, NGROK_URL,$url);;
    }

    /**
     * change home_url& valid other URL
     */
    add_filter('home_url', 'hwngrok_home_url');
    function hwngrok_home_url($url) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        #$ngrok = hwngrok_option( 'text_baseurl_ngrok', NGROK_ID);
        $ngrok = NGROK_ID;
        return str_replace(HW_ORIGINAL_URL,NGROK_URL, $url);
    }
    add_filter('site_url',  'hwngrok_wpadmin_filter', 10, 3);
    function hwngrok_wpadmin_filter( $url, $path, $orig_scheme ) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        $old  = array( "/(wp-admin)/");
        $admin_dir = WP_ADMIN_DIR;
        $new  = array($admin_dir);
        #return preg_replace( $old, $new, $url, 1);
        return str_replace(HW_ORIGINAL_URL,NGROK_URL, $url);
    }

    /**
     * wp_nav_menu url
     */
    add_filter('nav_menu_link_attributes','hwngrok_custom_menu',10,3);
    function hwngrok_custom_menu($atts, $item, $args){
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        if(strpos($item->url,'http://localhost/') !== false){
            #$ngrok = hwngrok_option( 'text_baseurl_ngrok', NGROK_ID);
            $ngrok = NGROK_ID;
            $atts['href']= str_replace(HW_ORIGINAL_URL, NGROK_URL, $item->url);
        }
        return $atts;
    }
    add_filter('admin_url', 'hwngrok_admin_url');
    function hwngrok_admin_url($url) {
        if(!defined('NGROK_ID') || !NGROK_ID) return ;

        return str_replace(HW_ORIGINAL_URL, NGROK_URL, $url);
    }
}

?>