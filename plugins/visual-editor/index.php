<?php
/**
 * Module Name: Visual Editor widget
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/10/2015
 * Time: 10:26
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once(dirname(__FILE__). '/widget/hw-widget-visual-editor.php');  //visual editor widget
/**
 * Class HW_Module_visualEditor
 */
class HW_Module_visualEditor extends HW_Module {
    public function __construct() {

    }

    /**
     * when module complete loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/admin');
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
    }
}
HW_Module_visualEditor::register();