<?php
/**
 * Module Name: Theme Options
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_THEME_OPTIONS_PATH', plugin_dir_path(__FILE__));
define('HW_THEME_OPTIONS_URL', plugins_url('', __FILE__));
define('HW_THEME_OPTIONS_SAMPLE_DATA', HW_THEME_OPTIONS_PATH. '/demo-data') ;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/11/2015
 * Time: 17:25
 */
include_once ('includes/sampleData.php');
/**
 * Class HW_Module_Theme_Options
 */
class HW_Module_Theme_Options extends HW_Module {
    public function __construct() {
        parent::__construct();

    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/admin', false);  //hide config panel
    }
}
HW_Module_Theme_Options::register();
#add_action('hw_modules_load', 'HW_Module_Theme_Options::init');