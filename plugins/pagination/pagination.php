<?php
/**
 * Module Name: Pagination
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/10/2015
 * Time: 20:25
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once ('hw_pagenavi.php');
/**
 * Class HW_Module_Pagenavi
 */
class HW_Module_Pagenavi extends HW_Module {
    public function __construct() {
        //initial
        hwpagenavi_init('HW_PAGENAVI::init');
    }

    /**
     * module loaded event
     */
    public function module_loaded(){
        $this->add_menu_box(null, '#');
        $this->other_submenus_page('pagenavi'/*, 'Phân trang'*/);
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
    public function print_head(){}
    public function print_footer(){}
}
HW_Module_Pagenavi::register();
//add_action('hw_modules_load', 'HW_Module_Pagenavi::init');