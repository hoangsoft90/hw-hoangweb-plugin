<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 18/10/2015
 * Time: 10:33
 */
//http://jgallery.jakubkowalczyk.pl/documentation#options
$theme_options[] = array(
    'name' => 'autostart',
    'type' => 'checkbox',
    'title' => 'autostart',
    'description' => 'If set as `true` jGallery will be started automatically after loading the document(only for full-screen or standard mode).'
);
$theme_options[] = array(
    'name' => 'autostartAtImage',
    'type' => 'text',
    'title' => 'autostartAtImage',
    'description' => 'Number of image which will be loaded by autostart(only when "autostart" parameter set as "true"). ',
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'autostartAtAlbum',
    'type' => 'text',
    'title' => 'autostartAtAlbum',
    'description' => 'Number of album which will be loaded by autostart(only when `autostart` parameter set as `true`). ',
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'backgroundColor',
    'type' => 'text',
    'title' => 'backgroundColor',
    'description' => 'Background color for jGallery container. ',
    'value' => '#fff'
);
$theme_options[] = array(
    'name' => 'browserHistory',
    'type' => 'checkbox',
    'title' => 'browserHistory',
    'description' => 'If set as `true`, changes of active image will be saved in browser history. ',
    'value' => 1
);
$theme_options[] = array(
    'name' => 'canChangeMode',
    'type' => 'checkbox',
    'title' => 'canChangeMode',
    'description' => 'If set as `true` you can change display mode(only for full-screen or standard mode). ',
    'value' => 1
);
$theme_options[] = array(
    'name' => 'canClose',
    'type' => 'checkbox',
    'title' => 'canClose',
    'description' => "If set as 'true' you can close jGallery(only for full-screen or standard mode). ",
    'value' => 0
);
$theme_options[] = array(
    'name' => 'canZoom',
    'type' => 'checkbox',
    'title' => 'canZoom',
    'description' => "If set as 'true' you can zoom photos. ",
    'value' => 1
);
$theme_options[] = array(
    'name' => 'draggableZoom',
    'type' => 'checkbox',
    'title' => 'draggableZoom',
    'description' => "If set as 'true' you can drag active image. ",
    'value' => 1
);
$theme_options[] = array(
    'name' => 'height',
    'type' => 'text',
    'title' => 'height',
    'description' => 'Height of jGallery container(only for standard or slider mode). ',
    'value' => '600px'
);
$theme_options[] = array(
    'name' => 'hideThumbnailsOnInit',
    'type' => 'checkbox',
    'title' => 'hideThumbnailsOnInit',
    'description' => "If set as 'true', thumbnails will be minimized by default, when jGallery will be started(only when 'thumbnails' parameter set as 'true'). ",
    'value' => 0
);
$theme_options[] = array(
    'name' => 'maxMobileWidth',
    'type' => 'text',
    'title' => 'maxMobileWidth',
    'description' => "Maximum width(px) for jGallery shows a view for mobile device. ",
    'value' => '767'
);
$theme_options[] = array(
    'name' => 'mode',
    'type' => 'select',
    'title' => 'mode',
    'description' => 'Display mode. ',
    'options' => array(
        'standard' => 'standard',
        'slider' => 'slider',
        'full-screen' => 'full-screen'
    ),
    'value' => "standard"
);
$theme_options[] = array(
    'name' => 'preloadAll',
    'type' => 'checkbox',
    'title' => 'preloadAll',
    'description' => "If set as 'true', all photos will be loaded before first shown photo. ",
    'value' => 0
);
$theme_options[] = array(
    'name' => '',
    'type' => '',
    'title' => '',
    'description' => '',
    'value' => ''
);
$theme_options[] = array(
    'name' => 'slideshow',
    'type' => 'checkbox',
    'title' => 'slideshow',
    'description' => "If set as 'true', option slideshow is enabled. ",
    'value' => 1
);
$theme_options[] = array(
    'name' => 'slideshowAutostart',
    'type' => 'checkbox',
    'title' => 'slideshowAutostart',
    'description' => "If set as 'true', slideshow will be started immediately after initializing jGallery(only when 'slideshow' has been set as true).",
    'value' => 0
);
$theme_options[] = array(
    'name' => 'slideshowCanRandom',
    'type' => 'checkbox',
    'title' => 'slideshowCanRandom',
    'description' => "If set as 'true', you can enable random change photos for slideshow(only when 'slideshow' has been set as true).",
    'value' => 1
);
$theme_options[] = array(
    'name' => 'slideshowInterval',
    'type' => 'select',
    'title' => 'slideshowInterval',
    'description' => "Time between change of photos for slideshow(only when 'slideshow' has been set as true). ",
    'options' => array(
        '3s' => '3 s',
        '6s' => '6 s',
        '8s' => '8 s',
        '10s' => '10 s'
    ),
    'value' => '8s'
);
$theme_options[] = array(
    'name' => 'textColor',
    'type' => 'text',
    'title' => 'textColor',
    'description' => 'Color of text and icons. ',
    'value' => '#000'
);
$theme_options[] = array(
    'name' => 'thumbHeight',
    'type' => 'text',
    'title' => 'thumbHeight',
    'description' => 'Height(pixels) of thumbnails. ',
    'value' => '75'
);
$theme_options[] = array(
    'name' => 'thumbHeightOnFullScreen',
    'type' => 'text',
    'title' => 'thumbHeightOnFullScreen',
    'description' => 'Height(pixels) of thumbnails for thumbnails displayed in full-screen. ',
    'value' => '100'
);
$theme_options[] = array(
    'name' => 'thumbnailsPosition',
    'type' => 'select',
    'title' => 'thumbnailsPosition',
    'description' => "Thumbnails position(only when 'thumbnails' parameter set as 'true'). ",
    'options' => array(
        'bottom' => 'bottom',
        'top' => 'top',
        'left' => 'left',
        'right' => 'right'
    ),
    'value' => 'bottom'
);
$theme_options[] = array(
    'name' => 'thumbType',
    'type' => 'select',
    'title' => 'thumbType',
    'description' => "Thumbnails type(only when 'thumbnails' parameter set as 'true'). ",
    'options' => array(
        'image' => 'image',
        'square' => 'square',
        'number' => 'number'
    ),
    'value' => 'image'
);
