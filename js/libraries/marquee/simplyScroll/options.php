<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 08/11/2015
 * Time: 11:39
 */
$theme_options[] = array(
    'name' => 'customClass',
    'type' => 'text',
    'description' => "Class name for styling",
    'value' => 'simply-scroll'
);
$theme_options[] = array(
    'name' => 'frameRate',
    'type' => 'text',
    'description' => "Number of movements/frames per second",
    'value' => '24'
);
$theme_options[] = array(
    'name' => 'speed',
    'type' => 'text',
    'description' => "Number of pixels moved per frame, in 'loop' mode must be divisible by total width of scroller",
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'orientation',
    'type' => 'select',
    'description' => "not to be confused with device orientation",
    'options' => array(
        'horizontal' => 'horizontal',
        'vertical' => 'vertical'
    )
);
$theme_options[] = array(
    'name' => 'direction',
    'type' => 'select',
    'description' => "",
    'options' => array(
        'forwards' => 'forwards',
        'backwards' => 'backwards'
    )
);
$theme_options[] = array(
    'name' => 'auto',
    'type' => 'checkbox',
    'description' => "Automatic scrolling, use false for button controls",
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'autoMode',
    'type' => 'select',
    'description' => "",
    'options' => array(
        'loop' => 'loop',
        'bounce' => 'bounce'
    )
);
$theme_options[] = array(
    'name' => 'manualMode',
    'type' => 'select',
    'description' => "",
    'options' => array(
        'loop' => 'loop',
        'end' => 'end'
    )
);
$theme_options[] = array(
    'name' => 'pauseOnHover',
    'type' => 'checkbox',
    'description' => "Pause scroll on hover (auto only)",
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'pauseOnTouch',
    'type' => 'checkbox',
    'description' => "Touch enabled devices only (auto only)",
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'pauseButton',
    'type' => 'checkbox',
    'description' => "Creates a pause button (auto only)",
    'value' => '0'
);
$theme_options[] = array(
    'name' => 'startOnLoad',
    'type' => 'checkbox',
    'description' => "Init plugin on window.load (to allow for image loading etc)",
    'value' => '0'
);
