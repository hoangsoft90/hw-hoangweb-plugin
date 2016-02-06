<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 18/10/2015
 * Time: 11:20
 */
//// General options
$theme_options[] = array(
    'name' => 'current',
    'type' => 'text',
    'title' => 'current',
    'description' => 'Index of start slide',
    'value' => '0'
);
$theme_options[] = array(
    'name' => 'duration',
    'type' => 'text',
    'title' => 'duration',
    'description' => '{Number}  Transition duration',
    'value' => '300'
);
$theme_options[] = array(
    'name' => 'loop',
    'type' => 'checkbox',
    'title' => 'loop',
    'description' => '{Boolean} Loop gallery',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'slidesOnScreen',
    'type' => 'text',
    'title' => 'slidesOnScreen',
    'description' => '{Number}  Number of visible slides in viewport',
    'value' => '1'
);
// Handlers
$theme_options[] = array(
    'name' => 'single',
    'type' => 'checkbox',
    'title' => 'single',
    'description' => '{Boolean} Initialize event handlers if gallery contains only one photo?',
    'value' => 0
);
$theme_options[] = array(
    'name' => 'keyboard',
    'type' => 'checkbox',
    'title' => 'keyboard',
    'description' => '{Boolean} Initialize keyboard event handlers?',
    'value' => 1
);
// Prefixes
$theme_options[] = array(
    'name' => 'slideIdPrefix',
    'type' => 'text',
    'title' => 'slideIdPrefix',
    'description' => '{String}  Prefix for class with slide index (e.g. "_12")',
    'value' => '_'
);
$theme_options[] = array(
    'name' => 'ieClassPrefix',
    'type' => 'text',
    'title' => 'ieClassPrefix',
    'description' => '{String}  Prefix for class with IE version (e.g. "_ie8")',
    'value' => '_ie'
);
//// Classnames
$theme_options[] = array(
    'name' => 'control',
    'type' => 'text',
    'title' => 'control',
    'description' => '',
    'value' => 'photor__viewportControl'
);
$theme_options[] = array(
    'name' => 'next',
    'type' => 'text',
    'title' => 'next',
    'value' => 'photor__viewportControlNext'
);
$theme_options[] = array(
    'name' => 'prev',
    'type' => 'text',
    'title' => 'prev',
    'value' => 'photor__viewportControlPrev'
);
$theme_options[] = array(
    'name' => 'thumbs',
    'type' => 'text',
    'title' => 'thumbs',
    'value' => 'photor__thumbs'
);
$theme_options[] = array(
    'name' => 'thumbsLayer',
    'type' => 'text',
    'title' => 'thumbsLayer',
    'value' => 'photor__thumbsWrap'
);
$theme_options[] = array(
    'name' => 'thumb',
    'type' => 'text',
    'title' => 'thumb',
    'value' => 'photor__thumbsWrapItem'
);
$theme_options[] = array(
    'name' => 'thumbImg',
    'type' => 'text',
    'title' => 'thumbImg',
    'value' => 'photor__thumbsWrapItemImg'
);
$theme_options[] = array(
    'name' => 'thumbFrame',
    'type' => 'text',
    'title' => 'thumbFrame',
    'value' => 'photor__thumbsWrapFrame'
);
$theme_options[] = array(
    'name' => 'viewport',
    'type' => 'text',
    'title' => 'viewport',
    'value' => 'photor__viewport'
);
$theme_options[] = array(
    'name' => 'layer',
    'type' => 'text',
    'title' => 'layer',
    'value' => 'photor__viewportLayer'
);
$theme_options[] = array(
    'name' => 'slide',
    'type' => 'text',
    'title' => 'slide',
    'value' => 'photor__viewportLayerSlide'
);
$theme_options[] = array(
    'name' => 'slideImg',
    'type' => 'text',
    'title' => 'slideImg',
    'value' => 'photor__viewportLayerSlideImg'
);
// State modifiers
$theme_options[] = array(
    'name' => '_loading',
    'type' => 'text',
    'title' => '_loading',
    'value' => '_loading',
    'description' => 'Photo is loading'
);
$theme_options[] = array(
    'name' => '_current',
    'type' => 'text',
    'title' => '_current',
    'description' => 'Current slide or thumbnail',
    'value' => '_current'
);
$theme_options[] = array(
    'name' => '_dragging',
    'type' => 'text',
    'title' => '_dragging',
    'description' => 'Dragging in progress',
    'value' => '_dragging'
);
$theme_options[] = array(
    'name' => '_disabled',
    'type' => 'text',
    'title' => '_disabled',
    'description' => 'Control element is disabled (e.g. left button on first slide)',
    'value' => '_disabled'
);
$theme_options[] = array(
    'name' => '_alt',
    'type' => 'text',
    'title' => '_alt',
    'description' => 'For photos with an alt attribute',
    'value' => '_alt'
);
$theme_options[] = array(
    'name' => '_single',
    'type' => 'text',
    'title' => '_single',
    'description' => 'Gallery contains only one photo',
    'value' => '_single'
);
$theme_options[] = array(
    'name' => '_animated',
    'type' => 'text',
    'title' => '_animated',
    'description' => 'Animation in progress',
    'value' => '_animated'
);
$theme_options[] = array(
    'name' => '_hidden',
    'type' => 'text',
    'title' => '_hidden',
    'value' => '_hidden',
    'description' => 'Slide is hidden'
);
// Orientation
/*
$theme_options[] = array(
    'name' => '_portrait',
    'type' => 'text',
    'title' => '_portrait',
    'value' => '_portrait',
    'description' => '[image width/image height] < [gallery width/gallery height]'
);
$theme_options[] = array(
    'name' => '_landscape',
    'type' => 'text',
    'title' => '_landscape',
    'description' => '[image width/image height] >= [gallery width/gallery height]',
    'value' => '_landscape'
);

$theme_options[] = array(
    'name' => '',
    'type' => 'text',
    'title' => '',
    'value' => '',
    'description' => ''
);
*/