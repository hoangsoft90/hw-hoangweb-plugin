<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 20/06/2015
 * Time: 08:49
 */
HW_HOANGWEB::load_class('HW_Validation');
/**
 * Class HW_URL
 */
if(class_exists('HW_Validation')):
class HW_URL extends HW_Validation{
    //plugins dir
    public static function get_plugins_dir() {
        return HW_HOANGWEB_PLUGINS;
    }
    //includes dir
    public static function get_includes_dir() {
        return HW_HOANGWEB_INCLUDES;
    }
    //admin url
    public static function get_admin_url() {
        return HW_ADMIN_URL;
    }

    /**
     * return current page url
     * @return string|void
     */
    public static function get_current_page_url() {
        global $wp;
        return home_url(add_query_arg(array(),$wp->request));
    }

    /**
     * check whether given string is valid URL
     * @param $url: valid string for url
     */
    public static function valid_url($url) {
        //return self::hw_valid_url($url);
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return current page url
     * @example working the same as method get_current_page_url
     * @param string|array $query
     * @param bool $base
     * @return string
     */
    public static function curPageURL($query = '', $base=false) {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].($base? '':$_SERVER["REQUEST_URI"]);
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].($base? '':$_SERVER["REQUEST_URI"]);
        }
        //$url = parse_url($pageURL);
        if($query && is_string($query)) parse_str($query, $query);
        return is_array($query) ? add_query_arg($query, $pageURL) : $pageURL;
        //return $pageURL;
    }

    /**
     * get base url as domain
     * @return string
     */
    public static function baseURL() {
        return self::curPageURL(0, true);
    }
    /**
     * get host domain
     * @return string
     */
    public static function get_domain(){
        //get the full domain
        $urlparts = parse_url(site_url());
        $domain = $urlparts ['host'];
        $domainparts = explode(".", $domain);
        $domain =  (count($domainparts)-2 >=0 && $domainparts[count($domainparts)-2]? $domainparts[count($domainparts)-2]. ".":'') . $domainparts[count($domainparts)-1];
        return $domain;
    }

    /**
     * url to file
     * @param $file
     * @param bool $fir get path or full path of file
     * @return string
     */
    public static function get_path_url($file, $dir=false) {
        if(file_exists($file)) {
            $relative_path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']),'', $dir? dirname($file):realpath($file));
            return self::baseURL(). str_replace('\\','/',$relative_path);
        }
    }
}

/**
 * Class HW_WP_URL
 */
class HW_WP_URL extends HW_URL {
    /**
     * @param $post_type
     * @return string|void
     */
    public static function manage_posttype_url($post_type) {
        return admin_url('edit.php?post_type='. $post_type);
    }

    /**
     * add new data item url
     * @param $post_type
     * @return string|void
     */
    public static function add_new_posttype_url($post_type) {
        return admin_url('post-new.php?post_type='. $post_type);
    }
}
endif;