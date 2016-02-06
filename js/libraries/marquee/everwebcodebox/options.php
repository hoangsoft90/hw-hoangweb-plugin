<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/07/2015
 * Time: 15:59
 */
$theme_options[] = array(
    'name' => 'gap',
    'type' => 'text',
    'description' => 'gap in pixels between the tickers',
    'value' => '50'
);
$theme_options[] = array(
    'name' => 'delayBeforeStart',
    'type' => 'text',
    'description' => 'time in milliseconds before the marquee will start animating',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'duration',
    'type' => 'text',
    'description' => 'speed in milliseconds of the marquee',
    'value' => '5000'
);
$theme_options[] = array(
    'name' => 'showSpeed',
    'type' => 'text',
    'description' => 'speed of drop down animation',
    'value' => '1000'
);
$theme_options[] = array(
    'name' => 'scrollSpeed',
    'type' => 'text',
    'description' => 'lower is faster',
    'value' => '10'
);
$theme_options[] = array(
    'name' => 'yScroll',
    'type' => 'select',
    'description' => "scroll direction on y-axis 'top' for down or 'bottom' for up",
    'options' => array('bottom','top')
);
$theme_options[] = array(
    'name' => 'direction',
    'type' => 'select',
    'description' => '',
    'options' => 'left,right'
);
$theme_options[] = array(
    'name' => 'pauseSpeed',
    'type' => 'text',
    'description' => 'pause before scroll start in milliseconds',
    'value' => '1000'
);
$theme_options[] = array(
    'name' => 'duplicated',
    'type' => 'checkbox',
    'description' => 'should the marquee be duplicated to show an effect of continues flow.'
);