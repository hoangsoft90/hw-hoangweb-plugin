<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/07/2015
 * Time: 15:59
 */
$theme_options[] = array(
    'name' => 'width',
    'type' => 'text',
    'description' => "Desired div's width",
    'value' => '100%'
);
$theme_options[] = array(
    'name' => 'height',
    'type' => 'text',
    'description' => "Desired div's height.",
    'value' => '100%'
);
$theme_options[] = array(
    'name' => 'steps',
    'type' => 'text',
    'description' => 'pixel step for the scrolling, also controls the direction, a negatif value (left), a positive value (right).',
    'value' => '-2'
);
$theme_options[] = array(
    'name' => 'speed',
    'type' => 'text',
    'description' => 'animation speed, from 0 (quicker) to infinite (slower).',
    'value' => '40'
);
$theme_options[] = array(
    'name' => 'mousestop',
    'type' => 'checkbox',
    'description' => 'if set to true the scrolling stops when the mouse is over the div.',
    'value' => '1'
);

