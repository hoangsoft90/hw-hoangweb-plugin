<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/10/2015
 * Time: 12:22
 */
$theme_options[] = array(
    'name' => 'zoom',
    'type' => 'checkbox',
    'title' => 'zoom',
    'description' => 'Use zoom'
);
$theme_options[] = array(
    'name' => "zoomAction",
    'type' => 'select',
    'title' => 'zoomAction',
    'description' => 'Zoom on action',
    'options' => array(
        'mouseenter' => 'mouseenter',
        'click' => 'click',
        'mouseover' => 'mouseover'
    )
);
$theme_options[] = array(
    'name' => "zoomTimeout",
    'type' => 'text',
    'title' => 'zoomTimeout',
    'description' => 'Timeout before zoom',
    'value' => '500'
);
$theme_options[] = array(
    'name' => "zoomDuration",
    'type' => 'text',
    'title' => 'zoomDuration',
    'description' => 'Zoom duration time',
    'value'=> '100'
);
$theme_options[] = array(
    'name' => "zoomImageBorder",
    'type' => 'text',
    'title' => 'zoomImageBorder',
    'description' => 'Zoomed image border size',
    'value' => '5'
);
$theme_options[] = array(
    'name' => "showBox",
    'type' => 'checkbox',
    'title' => 'showBox',
    'description' => 'Enavle fullscreen mode'
);
$theme_options[] = array(
    'name' => "showBoxSocial",
    'type' => 'checkbox',
    'title' => 'showBoxSocial',
    'description' => 'Show social buttons'
);
$theme_options[] = array(
    'name' => "padding",
    'type' => 'text',
    'title' => 'padding',
    'description' => 'padding between images in gallery',
    'value' => '5'
);
$theme_options[] = array(
    'name' => "lineMaxHeight",
    'type' => 'text',
    'title' => 'lineMaxHeight',
    'description' => 'Max set height of pictures line',
    'value' => '150'
);

$theme_options[] = array(
    'name' => "lineMaxHeightDynamic",
    'type' => 'checkbox',
    'title' => 'lineMaxHeightDynamic',
    'description' => 'Dynamic lineMaxHeight. If set to True,then line height will be changing on resize, coressponding to baseScreenHeight param '
);
$theme_options[] = array(
    'name' => "baseScreenHeight",
    'type' => 'text',
    'title' => 'baseScreenHeight',
    'description' => 'Base screen size from wich calculating dynamic lineMaxHeight  ',
    'value' => '600'
);
