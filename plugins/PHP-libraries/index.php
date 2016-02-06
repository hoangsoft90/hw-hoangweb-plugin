<?php
/**
 * Module Name: PHP Libraries
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


define( 'HW_LIBS_PATH', plugin_dir_path( __FILE__ ) );
define( 'HW_LIBS_URL', plugins_url( '', __FILE__ ) );
define('HW_LIBS_PLUGIN_FILE', __FILE__);
define('HW_LIBRARIES_PATH', HW_LIBS_PATH. '/libraries');
define('HW_LIBRARIES_MANAGER_PATH', HW_LIBS_PATH. '/includes/library');

/**
 * load functions
 */
include_once ('includes/functions.php');
/**
 * libraries manager
 */
include_once ('includes/hw-libraries.php');
/**
 * class to manage single library
 */
include_once ('includes/hw-library.php');

/**
 * Class HW_Module_PHP_Libraries
 */
class HW_Module_PHP_Libraries extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        new HW_PHP_Libraries();
    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {
        $this->register_help('phplib','help.html', 'help');
    }
}
HW_Module_PHP_Libraries::register();