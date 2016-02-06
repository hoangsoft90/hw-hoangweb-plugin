<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/05/2015
 * Time: 14:37
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * display breadcrumb navxt
 */
function hw_display_breadcrumb(){
    global $hw_breadcrumb;
    $hw_breadcrumb->display();
}