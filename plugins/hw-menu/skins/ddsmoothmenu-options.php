<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/10/2015
 * Time: 14:17
 */
//ddsmoothmenu
$theme_options[] = array(
    'description' => '<br/><h2>Tham số ddsmoothmenu</h2>',
    'type' => 'string'
);
$theme_options[] = array(
    'name' => 'enable_ddsmoothmenu',
    'type' => 'checkbox',
    'title' => 'Kích hoạt menu đa tầng'
);
$theme_options[] = array(
    'name' => 'dd_orientation',
    'type' => 'select',
    'title' => 'orientation',
    'options' => array(
        'h' => 'Horizontal',
        'v' => 'Vertical'
    )
);
$theme_options[] = array(
    'name' => 'dd_classname',
    'type' => 'text',
    'title' => 'classname',
    'description' => "class added to menu's outer DIV"
);
$theme_options[] = array(
    'name' => 'dd_method',
    'type' => 'select',
    'title' => 'method',
    'options' => array(
        'hover' => 'hover',
        'toggle' => 'toggle'
    )
);
$theme_options[] = array(
    'name' => 'dd_arrowswap',
    'type' => 'checkbox',
    'title' => 'arrowswap',
    'description' => 'enable rollover effect on menu arrow images?'
);