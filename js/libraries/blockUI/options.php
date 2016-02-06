<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/11/2015
 * Time: 12:57
 */
$theme_options[]= array(
    'name' => 'message',
    'type' => 'text',
    'description' => "message displayed when blocking (use null for no message)"
);
$theme_options[] = array(
    'name' => 'draggable',
    'type' => 'checkbox',
    'description' => 'only used when theme == true (requires jquery-ui.js to be loaded) '
);
$theme_options[] = array(
    'name' => 'theme',
    'type' => 'checkbox',
    'description' => 'set to true to use with jQuery UI themes'
);
$theme_options[] = array(
    'name' => 'cursorReset',
    'type' => 'select',
    'options' => array('default'),
    'description' => 'style to replace wait cursor before unblocking to correct issue'
);
//more: http://malsup.com/jquery/block/#options