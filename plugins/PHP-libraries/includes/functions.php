<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * load specific library
 * @param $name
 * @param $path location of file for getting new library
 * @return library manager
 */
function hwlib_load_library($name, $path = '') {
    if(!HW_PHP_Libraries::exists($name) && $path) HW_PHP_Libraries::register_lib($name, $path);
    return HW_PHP_Libraries::load_lib($name);
}

/**
 * register library but not load
 * @param string $name extends params from method HW_PHP_Libraries::register_lib
 * @param string $path same as $name arg
 */
function hwlib_register($name, $path = '') {
    HW_PHP_Libraries::register_lib($name, $path);
}