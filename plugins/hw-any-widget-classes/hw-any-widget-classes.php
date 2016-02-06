<?php 

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_AWC_URL', plugins_url('',__FILE__));
define('HW_AWC_PATH', plugin_dir_path(__FILE__));
/**
 * widget features location
 */
define('HW_AWC_WidgetFeatures_PATH', HW_AWC_PATH . '/includes/widget-features');
define('HW_AWC_WidgetFeatures_URL', HW_AWC_URL . '/includes/widget-features');

//define('HW_AWC_PLUGIN_URL', plugins_url('',__FILE__));
//define('HW_AWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_AWC_PLUGIN_FILE', __FILE__);

/**
 * load functions of plugin
 */
include('includes/functions.php');

/**
 * manage widgets setting
 */
//include_once ('includes/widget-features/autoload.php');
include_once ('includes/widget-features/widget-features.php');

/**
 * sidebar widgets settings
 */
include_once('includes/hw-sidebar-widgets-settings.php');

/**
 * load sidebar params
 */
include_once('includes/awc-sidebar-settings.php');

/**
 * dynamic sidebars
 */
include_once('includes/awc-dynamic-sidebars.php');


/**
 * frontend
 */
include_once('includes/hw-awc-frontend.php');

/*include_once('APF_Widget.php');
new APF_Widget( __( 'Admin Page Framework', 'admin-page-framework-demo' ) );  // the widget title
*/
/**
 * enable HTML in widget title:
 */
remove_filter( 'widget_title', 'esc_html' );


//if(is_admin())
HW_AWC::getInstance();
?>