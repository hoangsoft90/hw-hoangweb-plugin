<?php
/**
 * Module Name: Counter
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 20/10/2015
 * Time: 15:45
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

global $wpdb;
define('HW_BMW_TABLE_NAME', $wpdb->prefix . 'hw-mech_statistik');
//define('BMW_PATH', ABSPATH . 'wp-content/plugins/mechanic-visitor-counter');
define('HW_COUNTER_PLUGIN_URL', plugins_url('', __FILE__));
define('HW_COUNTER_PLUGIN_PATH', plugin_dir_path(__FILE__) );

require_once(ABSPATH . 'wp-includes/pluggable.php');
/**
 * functions
 */
require_once('includes/functions.php');
/**
 * widget
 */
require_once('includes/stats-mechanic-widget.php');

/**
 * register activation hook
 */
function hw_module_counter_register_activation_hook() {
    global $wpdb;
    if ( $wpdb->get_var('SHOW TABLES LIKE "' . HW_BMW_TABLE_NAME . '"') != HW_BMW_TABLE_NAME )
    {
        $sql = "CREATE TABLE IF NOT EXISTS `". HW_BMW_TABLE_NAME . "` (";
        $sql .= "`ip` varchar(20) NOT NULL default '',";
        $sql .= "`tanggal` date NOT NULL,";
        $sql .= "`hits` int(10) NOT NULL default '1',";
        $sql .= "`online` varchar(255) NOT NULL,";
        $sql .= "PRIMARY KEY  (`ip`,`tanggal`)";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        $wpdb->query($sql);
    }
}
hw_register_activation_hook(__FILE__, 'hw_module_counter_register_activation_hook');
/**
 * register deactivation hook
 */
function hw_module_counter_register_deactivation_hook(){
    global $wpdb;
    $sql = "DROP TABLE `". HW_BMW_TABLE_NAME . "`;";
    $wpdb->query($sql);
}
hw_register_deactivation_hook(__FILE__, 'hw_module_counter_register_deactivation_hook');

#register_activation_hook(__FILE__, 'hw_module_counter_register_activation_hook');
#register_deactivation_hook(__FILE__, 'hw_module_counter_register_deactivation_hook');

/**
 * Class HW_Module_Counter
 */
class HW_Module_Counter extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        $this->enable_tab_settings();
        $this->enable_submit_button();
    }
    //register help
    public function module_loaded() {
        $this->register_help('social','help.html', 'help'); //  {CURRENT_MODULE}/help/helps_view/help.html
        $this->enable_config_page('cli/admin');
    }
    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        //$this->addFieldLabel('Choose one of the image counter styles below:');
        $this->addField(
            array(
                'field_id' => 'number_icons',
                'type' => 'hw_html',
                'output_callback' => array(&$this, '_images_counter'),
                'show_title_column' => false
            )
        );
    }

    /**
     * output for hw_html field type
     * @param $aField
     */
    public function _images_counter($aField) {
        //fields setting form
        include ('includes/admin/form-settings.php');
    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_Counter::init');