<?php
/**
 * Module Name: WP Poll
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_WP_Polls
 */
class HW_WP_Polls extends HW_Module{
    public function __construct() {
        add_action( 'plugins_loaded', array(__CLASS__, '_polls_textdomain') );
    }

    /**
     * textdomain alternative
     */
    public static function _polls_textdomain() {
        load_plugin_textdomain( 'wp-polls', false, dirname( plugin_basename( __FILE__ ) ). '/languages');
    }
}
HW_WP_Polls::register();