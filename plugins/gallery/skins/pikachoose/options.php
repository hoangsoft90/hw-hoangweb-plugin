<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 18/10/2015
 * Time: 11:20
 */
$theme_options[] = array(
    'name' => 'css',
    'type' => 'select',
    'title' => 'Style',
    'description' => 'Style',
    'options' => array(
        'bottom.css' => 'bottom.css',
        'css3.css' => 'css3.css',
        'left.css' => 'left.css',
        'left-without.css' => 'left-without.css',
        'right.css' => 'right.css',
        'right-without.css' => 'right-without.css',
        'simple.css' => 'simple.css',
        'tooltip.css' => 'tooltip.css',
        'transitions.css' => 'transitions.css',
    )
);
$theme_options[] = array(
    'name' => 'showCaption',
    'type' => 'checkbox',
    'title' => 'showCaption',
    'value' => 1
);
$theme_options[] = array(
    'name' => 'autoPlay',
    'type' => 'checkbox',
    'title' => 'autoPlay',
    'description' => 'If set to true the slideshow will start playing automatically',
    'value' => 1
);
$theme_options[] = array(
    'name' => 'hoverPause',
    'type' => 'checkbox',
    'title' => 'hoverPause',
    'description' => 'If set to true the slideshow will pause playing when the main image is ',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'stopOnClick',
    'type' => 'checkbox',
    'title' => 'stopOnClick',
    'description' => 'Determines wether the slideshow stops auto play after the user clicks on an image.',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'speed',
    'type' => 'text',
    'title' => 'speed',
    'description' => 'The speed that a picture will display before moving onto the next during a slideshow',
    'value' => '5000'
);
$theme_options[] = array(
    'name' => 'IESafe',
    'type' => 'checkbox',
    'title' => 'IESafe',
    'description' => "Reverts back to the full frame cross fade of Internet Explorer. This is needed due to IE 6/7′s slowness when using CSS background image animations.",
    'value' => 1
);
$theme_options[] = array(
    'name' => 'showTooltips',
    'type' => 'checkbox',
    'title' => 'showTooltips',
    'description' => 'Uses the caption as a tooltip for thumbnails. Great if you want to turn off captions, but still have some visible text for each image.',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'thumbOpacity',
    'type' => 'text',
    'title' => 'thumbOpacity',
    'description' => 'Sets the opacity for non active images.',
    'value' => '0.4'
);
$theme_options[] = array(
    'name' => 'startOn',
    'type' => 'text',
    'title' => 'startOn',
    'description' => 'The image PikaChoose should start on. (programatic numbering, 0 is the first image)',
    'value' => '0'
);
$theme_options[] = array(
    'name' => 'animationSpeed',
    'type' => 'text',
    'title' => 'animationSpeed',
    'description' => 'The speed at which transitions 0,1,3,4,6 will run at',
    'value' => '600'
);
$theme_options[] = array(
    'name' => 'thumbChangeEvent',
    'type' => 'select',
    'title' => 'thumbChangeEvent',
    'description' => 'This changes the event that causes PikaChoose to change images. Use the following example to change it to mouseover',
    'options' => array(
        'mouseover.pikachoose' => 'mouseover.pikachoose',
        'click.pikachoose' => 'click.pikachoose'
    )
);
$theme_options[] = array(
    'name' => 'carousel',
    'type' => 'checkbox',
    'title' => 'carousel',
    'description' => 'Enabling this activates jCarousel for the slideshow.',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'carouselVertical',
    'type' => 'checkbox',
    'title' => 'carouselVertical',
    'description' => 'If you want a vertical slideshow use the carouselVertical option as well.',
    'value' => 1
);
$theme_options[] = array(
    'name' => 'fadeThumbsIn',
    'type' => 'checkbox',
    'title' => 'fadeThumbsIn',
    'description' => 'Enabling this will fade in the UL after PikaChoose has loaded. You must manually set the UL to display:none in your CSS for this to have an effect.',
    'value' => 0
);
