<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * define path and URL
 */
define('HWDOKU_PLUGIN_URL', plugins_url('', __FILE__) );
define('HWDOKU_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define('HWDOKU_PLUGIN_FILE', __FILE__);
define('app_libs', HWDOKU_PLUGIN_PATH. '/libraries/');
/**
 * database table to store game data
 */
define('HWDOKU_DB_TABLE', 'hw_sudoku');
/**
 * include something
 */
include_once ('includes/functions.php');

initialize_hphp();
include_once( matrix);
#include_once (mysql);
include_once (plugin_Element);

include_once (class_Element);
include_once (forms);
include_once (Document);

include_once('includes/sudoku.game.php');   //sudoku game functions
include_once (HWDOKU_PLUGIN_PATH. '/includes/sudoku.class.php');

/**
 * init
 */
actived_hDoc_instance();