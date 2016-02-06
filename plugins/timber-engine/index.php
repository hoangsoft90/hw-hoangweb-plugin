<?php
/**
 * Module Name: Timer WP Template Engine
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/11/2015
 * Time: 17:17
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//load timber library
if(!class_exists('Timber', false)) include_once (dirname(__FILE__). '/plugin/timber-library/timber.php');
/**
 * context environment
 */
include ('includes/functions.php');
include ('includes/template-controller.php');

/**
 * extend timber object
 */
include_once ('includes/lib/hw-timberPost.php');
include_once ('includes/lib/hw-timberTerm.php');
include_once ('includes/lib/hw-timberImage.php');

/**
 * context object
 *
 */
include_once ('includes/lib/hw-context-user.php');


/**
 * Class HW_Module_Timber
 */
class HW_Module_Timber extends HW_Module {
    public function __construct() {

    }

}
HW_Module_Timber::register();