<?php
/**
 * Module Name: Sudoku Game
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_Module_Breadcrumb
 */
class HW_Module_SudokuGame extends HW_Module {
    public function __construct() {
        //main file
        include_once ('hw-sudoku.php');
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
add_action('hw_modules_load', 'HW_Module_SudokuGame::init');