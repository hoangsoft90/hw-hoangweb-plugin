<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * load hphp lib
 */
function initialize_hphp()
{
	if(!defined('app_libs')){
		exit('Not found constant "app_libs".');
	}
	require_once(rtrim(app_libs,'/').'/init/require.php');
}

/**
 * function to create the DB / Options / Defaults
 */
function hwdoku_plugin_options_install() {
    global $wpdb;
    $table = HWDOKU_DB_TABLE;

    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    // create the ECPT metabox database table
    if($wpdb->get_var("show tables like '$table'") != $table)
    {
        $sql = "CREATE TABLE " . $table . " (
		`grid` TEXT
		);";
        /*require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );*/
        $wpdb->query($sql);
    }

}
// run the install scripts upon plugin activation
register_activation_hook(HWDOKU_PLUGIN_FILE, 'hwdoku_plugin_options_install');

/**
 * uninstall plugin
 */
function hwdoku_plugin_options_uninstall() {
    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    // Important: Check if the file is the one
    // that was registered during the uninstall hook.
    if ( HWDOKU_PLUGIN_FILE != WP_UNINSTALL_PLUGIN )
        return;

    global $wpdb;
    $table_name = HWDOKU_DB_TABLE;
    $sql = "DROP TABLE IF_EXISTS $table_name;";
    $wpdb->query($sql);

}
if ( function_exists('register_uninstall_hook') )
    register_uninstall_hook(HWDOKU_PLUGIN_FILE, 'hwdoku_plugin_options_uninstall');

/**
 * load plugin text domain
 */
function hwdoku_wnb_load_textdomain() {
    load_plugin_textdomain( 'hwdoku', false, dirname(dirname( plugin_basename( __FILE__ ) )) . '/languages/' );
}
add_action('plugins_loaded', 'hwdoku_wnb_load_textdomain');

/**
 * enqueue scripts
 */
function hwdoku_enqueue_scripts() {
    wp_enqueue_script('jquery');  //load jquery from wp core
    wp_enqueue_script('hw-sudoku', plugins_url('assets/sudoku.js', dirname(__FILE__)) ,array('jquery'));
    wp_enqueue_script('hw-sudoku-script', plugins_url('assets/script.js', dirname(__FILE__)) , array('jquery'));
    wp_enqueue_style('hw-sudoku-style.css', plugins_url('assets/style.css', dirname(__FILE__)) );

    $check_item_nonce = wp_create_nonce("hwdoku_check_item_nonce");
    $valid_game_nonce = wp_create_nonce("hwdoku_valid_game_nonce");
    $suggest_item_nonce = wp_create_nonce("hwdoku_suggest_item_nonce");

    wp_localize_script('hw-sudoku-script', '__hwdoku', array(
        #'ajax_handle' => HWDOKU_PLUGIN_URL. '/ajax.php',
        'ajaxurl' => admin_url( 'admin-ajax.php' ),

        'check_item_url' => admin_url( 'admin-ajax.php?action=hwdoku_check_item&nonce=' . $check_item_nonce),
        'valid_game_url' => admin_url( 'admin-ajax.php?action=hwdoku_valid_game&nonce=' . $valid_game_nonce),
        'suggest_item_url' => admin_url( 'admin-ajax.php?action=hwdoku_suggest_item&nonce=' . $suggest_item_nonce),
    ));
}
add_action('wp_enqueue_scripts', 'hwdoku_enqueue_scripts');

/**
 * show game by shortcode
 * @param $atts
 */
add_shortcode('hw-sudoku', 'hwdoku_playgame_shortcode');
function hwdoku_playgame_shortcode($atts) {
    $pairs = array('size' => 3, 'auto_check' => false);
    extract(shortcode_atts($pairs, $atts, 'hw-sudoku'));

    ob_start();
    include ('play.php');
    $show = ob_get_contents();
    ob_clean();
    return $show;
}

/**
 * ajax handle
 */
add_action("wp_ajax_hwdoku_check_item", "_hwdoku_check_item");
add_action("wp_ajax_nopriv_hwdoku_check_item", "_hwdoku_check_item");

add_action("wp_ajax_hwdoku_valid_game", "_hwdoku_valid_game");
add_action("wp_ajax_nopriv_hwdoku_valid_game", "_hwdoku_valid_game");

add_action("wp_ajax_hwdoku_suggest_item", "_hwdoku_suggest_item");
add_action("wp_ajax_nopriv_hwdoku_suggest_item", "_hwdoku_suggest_item");

/**
 * check item
 */
function _hwdoku_check_item() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "hwdoku_check_item_nonce")) {
        exit("No naughty business please");
    }
    //game size
    $size = isset($_GET['size'])? $_GET['size'] : '3';
    //game matrix
    $matrix = isset($_GET['matrix'])? $_GET['matrix'] : '';

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        HW_Sudoku_Game::hwdoku_check_item($size, $matrix);
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    die();
}

/**
 * valid game
 */
function _hwdoku_valid_game() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "hwdoku_valid_game_nonce")) {
        exit("No naughty business please");
    }
    //game size
    $size = isset($_GET['size'])? $_GET['size'] : '3';
    //game matrix
    $matrix = isset($_GET['matrix'])? $_GET['matrix'] : '';

    $items_string = isset($_GET['items_string'])? $_GET['items_string'] : '';

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        HW_Sudoku_Game::hwdoku_valid_game($size , $matrix, $items_string);
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    die();
}

/**
 * suggest item
 */
function _hwdoku_suggest_item() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "hwdoku_suggest_item_nonce")) {
        exit("No naughty business please");
    }
    //game size
    $size = isset($_GET['size'])? $_GET['size'] : '3';
    //game matrix
    $matrix = isset($_GET['matrix'])? $_GET['matrix'] : '';

    $matrix_origin = isset($_GET['matrix_origin'])? $_GET['matrix_origin'] : '';    //orginal matrix

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        HW_Sudoku_Game::hwdoku_suggest_item($size , $matrix, $matrix_origin);
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    die();
}
function hwdoku_must_login() {
    echo "You must log in to vote";
    die();
}
?>