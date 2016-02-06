<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_PAGENAVI_URL' , plugins_url('', __FILE__));
define('HW_PAGENAVI_PATH', plugin_dir_path(__FILE__));
define('HW_PAGENAVI_PLUGIN_FILE', __FILE__);

include_once(dirname( __FILE__ ).'/load.php');
require_once( HW_PAGENAVI_PATH . '/lib/scb/load.php');

/**
 * Class HW_PAGENAVI
 */
class HW_PAGENAVI{
    private static $instance;

    /**
     * constructor
     */
    public function __construct(){
        //load plugin text domain
        load_plugin_textdomain( 'wp-pagenavi', '', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
    }

    /**
     * admin enqueue scripts
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts(){
        if(class_exists('HW_HOANGWEB', false) && HW_HOANGWEB::is_current_screen('pagenavi')){
           wp_enqueue_style('hw-pagenavi-style', HW_PAGENAVI_URL.'/css/style.css');
        }

    }

    /**
     * scb init
     */
    public function _hw_pagenavi_plugin_init() {

        //if(is_admin()){   //no, i will also use on website, don;t limit in admin screen
            //we use same wp-pagenavi options name called 'pagenavi_options'
            $options = new scbOptions( 'pagenavi_options' ,__FILE__,array(
                'hw_skin' => '',
                'hw_skin_config' => '',
                'hw_skin_condition' => ''
            ));  // OK
            HWPageNavi_Core::init($options);

            require_once dirname(__FILE__) . '/includes/admin.php'; // using require() can cause activation errors
            new HWPageNavi_Options_Page( __FILE__, $options );
        //}

    }
    /**
     * return this class instance
     * @return HW_PAGENAVI
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    public static function init(){
        $my = self::getInstance();
        scb_init( array($my, '_hw_pagenavi_plugin_init' ));
    }
}

//initial
#hwpagenavi_init('HW_PAGENAVI::init');

?>