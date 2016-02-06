<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/06/2015
 * Time: 15:26
 */
//for frontend
//article
/**
 * Class HW_Options_Frontend
 */
class HW_Options_Frontend extends HW_Core{
    static $fragments = array();

    function __construct() {
        $this->setup_actions();
    }

    /**
     * add fragment options
     * @param $fragment
     */
    public static function add_fragment($fragment) {
        if($fragment instanceof HW_Options) {
            $id = $fragment->name;
            self::$fragments[$id] = $fragment;
        }
    }

    /**
     * get fragment
     * @param $name
     * @return mixed
     */
    public static function get_fragment($name) {
        if(isset(self::$fragments[$name])) return self::$fragments[$name];
    }
    /**
     * add actions
     */
    protected function setup_actions() {
        add_action('wp_footer', array($this, '_hw_nhp_wp_footer'));
        //admin login
        add_action('login_head', array($this, '_hw_nhp_custom_loginlogo'));

        //allow shortcode in widget text
        add_filter('widget_text', 'do_shortcode');
    }
    /**
     * put to footer
     */
    public function _hw_nhp_wp_footer() {

    }
    /**
     * change admin login logo
     */
    public function _hw_nhp_custom_loginlogo() {
        $logo = hw_option('admin_logo',HW_HOANGWEB_URL.'/images/hw-logo.jpg');
        echo '<style type="text/css">
            h1 a {background-image: url('.$logo.') !important; }
         </style>';
    }

    /**
     * magic method
     * @param $var
     * @param $value
     */
    function __set($var, $value) {
        $this->$var = $value;
    }
}
#if(! is_admin()) {
    include_once('hw-features-frontend.php');
    include_once('hw-article-frontend.php');
    include_once('hw-footer-frontend.php');
    include_once('hw-theme-frontend.php');
    include_once('hw-translate-frontend.php');
    include_once('hw-ads-frontend.php');

    new HW_Options_Frontend();
#}
