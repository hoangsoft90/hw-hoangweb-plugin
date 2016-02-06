<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//defined constants
define('HWPVC_PLUGIN_URL' , plugins_url('',__FILE__));
define('HWPVC_PLUGIN_PATH' , plugin_dir_path(__FILE__));

include_once('includes/functions.php');
include_once('includes/settings.php');  //settings
include_once('includes/frontend.php');  //frontend

/**
 * getting start
 */
/*
//append this code before includes libs in  post-views-counter.php
//by hoangweb
include_once('hoangweb/hw-pvc.php');
*/
class HW_Post_Views_Counter {
    static $instance;
    /**
     * hold hoangweb options
     * @var
     */
    private $options = array();
    private $defaults = array(
        'hoangweb' => array(
            'use_firebase' => 0
        )
    );

    /**
     * constructor
     */
    function __construct() {
        register_activation_hook( __FILE__, array( &$this, '_activation' ) );
        register_deactivation_hook( __FILE__, array( &$this, '_deactivation' ) );
        // settings
        $this->options['hoangweb'] = array_merge( $this->defaults['hoangweb'], get_option( 'post_views_counter_settings_hoangweb', $this->defaults['hoangweb'] ) );

        //actions, hook: plugins_loaded
        add_action( 'hw_plugins_loaded', array( &$this, '_load_textdomain' ) ,100);
        add_action( 'wp_loaded', array( &$this, '_load_pluggable_functions' ), 12 );
        add_action('init', array($this, '_init'));
    }

    /**
     * initial something
     */
    public function _init(){
        //register help for the plugin
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('post_view_counter', HWPVC_PLUGIN_PATH.'helps');
            HW_HELP::set_helps_url('post_view_counter', HWPVC_PLUGIN_URL.'/helps');
            HW_HELP::register_help('post_view_counter');
            HW_HELP::load_module_help('post_view_counter');
        }
    }
    /**
     * Load pluggable template functions
     */
    public function _load_pluggable_functions() {
        include_once(HWPVC_PLUGIN_PATH . 'includes/functions.php');
    }
    /**
     * Load text domain
     */
    public function _load_textdomain() {
        load_plugin_textdomain( 'post-views-counter', false, HWPVC_PLUGIN_PATH . 'languages/' );
    }
    /**
     * Get allowed attribute
     */
    public function get_attribute( $attribute ) {
        if ( in_array( $attribute, array( 'options', 'defaults' ), true ) ) {
            switch ( func_num_args() ) {
                case 1:
                    return $this->{$attribute};

                case 2:
                    return $this->{$attribute}[func_get_arg( 1 )];

                case 3:
                    return $this->{$attribute}[func_get_arg( 1 )][func_get_arg( 2 )];

                case 4:
                    return $this->{$attribute}[func_get_arg( 1 )][func_get_arg( 2 )][func_get_arg( 3 )];
            }
        } else
            return false;
    }
    /**
     * Execution of plugin activation function
     */
    public function _activation() {

    }

    /**
     * Execution of plugin deactivation function
     */
    public function _deactivation() {
        // delete default options
        if ( $this->options['general']['deactivation_delete'] ) {
            delete_option( 'post_views_counter_settings_general' );
            delete_option( 'post_views_counter_settings_display' );
        }
    }

    /**
     * return all of first class instance
     * @return HW_Post_Views_Counter
     */
    public static function getInstance() {
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    /**
     * magic method
     */
    private function __clone() {

    }

    private function __wakeup() {

    }
}
/**
 * Initialise Post Views Counter
 */
function HW_Post_Views_Counter() {
    static $instance;

    // first call to instance() initializes the plugin
    if ( $instance === null || ! ($instance instanceof HW_Post_Views_Counter) )
        $instance = HW_Post_Views_Counter::getInstance();

    return $instance;
}

if(is_admin()) {
    new HW_Post_Views_Counter;
}