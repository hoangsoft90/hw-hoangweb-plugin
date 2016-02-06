<?php


// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define( 'HW_HELP_REL_PATH', dirname( plugin_basename( __FILE__ ) ) . '/' );
define( 'HW_HELP_PATH', plugin_dir_path( __FILE__ ) );
define( 'HW_HELP_URL', plugins_url( '', __FILE__ ) );
define( 'HW_HELP_AJAX', plugins_url( 'ajax.php', __FILE__ ) );

require_once('includes/functions.php');
require_once('includes/main.php');

?>
