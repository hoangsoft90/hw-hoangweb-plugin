<?php

/**
 * Class HW_Position
 */
if(!class_exists('HW_Module_Positions',false)):
class HW_Module_Positions extends HW_Core{
    /**
     * @var string
     */
    private static $hook_prefix = 'hw_';
    /**
     * singleton
     * @var null
     */
    public static $instance = null;
    /**
     * positions hook
     * @var array
     */
    protected static $positions = array();

    public function __construct() {
        add_action('init', array($this, '_init'));
    }

    /**
     * @hook init
     */
    public function _init() {

    }
    /**
     * register position
     * @param $name
     * @param $text
     */
    public static function register_position($name, $text) {
        self::$positions[$name] = $text;
    }

    /**
     * remove position
     * @param $name
     */
    public static function unregister_position($name) {
        if(isset(self::$positions[$name])) unset(self::$positions[$name]);
    }
    /**
     * do position
     * @param $name
     */
    public static function implement_position($name) {
        do_action(self::get_hook_name($name));
    }

    /**
     * add action for position
     * @param $name
     * @param $callback callback
     */
    public static function add_position_hook($name, $callback) {
        if(is_callable($callback)) add_action(self::get_hook_name($name), $callback);
    }
    /**
     * return hook for position name
     * @param $name
     * @return string
     */
    public static function get_hook_name($name) {
        return self::$hook_prefix.$name;
    }

    /**
     * return all positions
     * @return array
     */
    public static function get_positions() {
        $result = array();
        foreach( self::$positions as $pos) {
            $result[$pos['name']] = $pos['text'];
        }
        return $result ;
    }
}
if(!is_admin()) {
    HW_Module_Positions::get_instance();
}
endif;