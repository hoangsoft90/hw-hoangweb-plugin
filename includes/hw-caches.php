<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 31/10/2015
 * Time: 22:32
 */
/**
 * Class HW_Cache
 */
class HW_Cache {
    /**
     * @var int
     */
    protected static  $cache_time= 43200;   //60*60*12;
    /**
     * reset all caches created by hoangweb plugin
     */
    public static function reset_wp_menus_caches() {
        static $reset ;
        if(!$reset && class_exists('HW_Module_Config_general')) {
            $config = HW_Module_Config_general::get_module_configs('config');
            $config->refresh_wp_menus_cache(false);
            $reset = 1;
        }
    }

    /**
     * set transient db
     * @param $name
     * @param string $data
     * @return mixed|string
     */
    public static function _set_transient($name, $data='', $time='') {
        if(!$time) $time = self::$cache_time;
        if ( false === ( $special_query_results = get_transient( $name ) ) ) {
            // It wasn't there, so regenerate the data and save the transient
            $special_query_results = $data;
            set_transient( $name, $data, $time );
        }
        return $special_query_results;
    }

    /**
     * get transient
     * @param $name
     * @param string $default
     * @return mixed|string
     */
    public static function _get_transient($name, $default='') {
        $result = get_transient( $name );
        return ($result ===null)? $default : $result;
    }
}