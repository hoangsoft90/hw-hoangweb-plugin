<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 30/10/2015
 * Time: 11:52
 */
/**
 * Class HW_SESSION
 */
abstract class HW_SESSION extends hwArray{
    /**
     * @var string
     */
    public static $MAIN_GROUP = 'hoangweb';
    /**
     * save session value
     * @param $key
     * @param $value
     */
    public static function save_session($key, $value) {
        @session_start();
        if(!isset($_SESSION[self::$MAIN_GROUP])) $_SESSION[self::$MAIN_GROUP] = array();
        $_SESSION[self::$MAIN_GROUP][$key] = $value;
    }

    /**
     * get session by name
     * @param $key
     */
    public static function get_session($key) {
        @session_start();
        if(isset($_SESSION[self::$MAIN_GROUP][$key])) return $_SESSION[self::$MAIN_GROUP][$key];
    }

    /**
     * get data holder
     * @param $group
     * @param bool $clear
     * @return mixed
     */
    public static function &get_data_group($group, $clear = false) {
        if(!isset($_SESSION[self::$MAIN_GROUP][$group]) || $clear) $_SESSION[self::$MAIN_GROUP][$group] = array();
        return $_SESSION[self::$MAIN_GROUP][$group];
    }
    /**
     * remove one or more session item value
     * @param $key
     */
    public static function del_session() {
        @session_start();
        $keys = func_get_args();
        foreach($keys as $key) {
            if(isset($_SESSION[self::$MAIN_GROUP][$key])) unset($_SESSION[self::$MAIN_GROUP][$key]);
        }
    }

    /**
     * @param $key
     * @param $value
     * @param bool $merge_array
     * @param string $group
     */
    public static function __save_session($key,$value, $merge_array= false, $group = 'hoangweb'){
        @session_start();
        if(empty($group)) $group = self::$MAIN_GROUP;
        if( !isset($_SESSION[$group])) $_SESSION[$group] = array();
        $data = &$_SESSION[$group];

        if( $merge_array) {
            if(!isset($data[$key]) ) $data[$key] = array();
            if(! is_array($data[$key])) $data[$key] = (array)$data[$key];
            if(!is_array($value) ) $value = (array) $value;

            $data[$key] = array_merge($data[$key],$value);
        }
        else $data[$key] = $value;

    }
}