<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/11/2015
 * Time: 09:21
 */
class HW_Logger {
    /**
     * set log
     * @param $str
     * @param bool $merge
     */
    public static function log($str, $merge = false) {
        HW_SESSION::__save_session('log',$str, $merge);
    }

    /**
     * log to file
     * @param $str
     */
    public static function log_file($str) {
        $log_path = HW_HOANGWEB_PATH .'/cache/';
        @mkdir($log_path,0777,TRUE);
        //valid value
        if(!is_string($str)) $str = print_r($str, true);

        $fp = fopen($log_path .'/log.txt', 'a+');
        fwrite($fp, $str.PHP_EOL);
        fclose($fp);
    }

    /**
     * enable debug
     * @param $str
     */
    public static function add_debug($str) {
        if(defined('WP_DEBUG') && WP_DEBUG) self::log_file($str);
    }
    /**
     * print out for any object
     * @param $data
     */
    public static function out($data) {
        echo '<textarea>';
        print_r($data);
        echo '</textarea>';
    }

    /**
     * debug backtrace
     */
    public static function backtrace() {
        $chain = array_reverse(debug_backtrace());
        array_pop($chain);
        foreach($chain as $func) {
            if(isset($func['file']) && (strpos($func['file'], 'wp-load.php')!==false
                || strpos($func['file'], 'wp-config.php')!==false
                || strpos($func['file'], 'wp-settings.php')!==false
                || strpos($func['file'], 'wp-includes\plugin.php')!==false
            )) continue;

            if(isset($func['object'])) unset($func['object']);
            if(isset($func['args'])) {
                //foreach($func['args'] as $id=>$arg) if(is_object($arg)) unset($func['args'][$id]);
                unset($func['args']);
            }
            self::log_file($func);
        }
    }
}