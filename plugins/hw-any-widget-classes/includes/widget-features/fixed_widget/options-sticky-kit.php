<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/10/2015
 * Time: 08:17
 */
$theme_options[] = array(
    'name' => 'parent',
    'type' => 'text',
    'description' => "The element will be the parent of the sticky item.",
    'value' => ''
);
$theme_options[] = array(
    'name' => 'inner_scrolling',
    'type' => 'checkbox',
    'description' => "Boolean to enable or disable the ability of the sticky element to scroll independently of the scrollbar when it’s taller than the viewport. ",
);
$theme_options[] = array(
    'name' => 'sticky_class',
    'type' => 'text',
    'description' => 'The name of the CSS class to apply to elements when they have become stuck. Defaults to "is_stuck".',
    'value' => ''
);
$theme_options[] = array(
    'name' => 'offset_top',
    'type' => 'text',
    'description' => 'offsets the initial sticking position by of number of pixels, can be either negative or positive',
    'value' => ''
);
$theme_options[] = array(
    'name' => 'spacer',
    'type' => 'checkbox',
    'description' => 'either a selector to use for the spacer element, or false to disable the spacer.',
    #'value' => ''
);
$theme_options[] = array(
    'name' => 'bottoming',
    'type' => 'checkbox',
    'description' => 'Boolean to control whether elements bottom out. Defaults to true',

);
$theme_options[] = array(
    'name' => 'recalc_every',
    'type' => 'text',
    'description' => 'Integeger specifying that a recalc should automatically take place between that many ticks. A tick takes place on every scroll event. Defaults to never calling recalc on a tick.',
    'value' => ''
);
/*
$theme_options[] = array(
    'name' => '',
    'type' => '',
    'description' => '',
    'value' => ''
);
$theme_options[] = array(
    'name' => '',
    'type' => '',
    'description' => '',
    'value' => ''
);
*/