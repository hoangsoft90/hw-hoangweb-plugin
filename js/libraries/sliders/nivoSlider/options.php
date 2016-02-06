<?php
/**
 * remember that file theme options named 'options.php' associated with current theme folder
 */
$theme_options[] = array(
    'name' => 'effect',
    'type' => 'select',
    'options' => 'random,fold,fade,sliceDown,sliceDownLeft,sliceUp,sliceUpLeft,sliceUpDown,sliceUpDownLeft,slideInRight,slideInLeft,boxRandom,boxRain,boxRainReverse,boxRainGrow,boxRainGrowReverse',
    'description' => 'Hiệu ứng'
);
$theme_options[] = array(
    'name' => 'theme',
    'type' => 'select',
    'options' => array('default','bar','dark','light'),
    'description' => 'Select slider theme.'
);
$theme_options[] = array(
    'name' => 'slices',
    'type' => 'text',
    'description' => 'For slice animations. ie: 10',
    'value' => '10'
);
$theme_options[] = array(
    'name' => 'boxCols',
    'type' => 'text',
    'description' => 'For box animations. ie: 6',
    'value' => '6'
);
$theme_options[] = array(
    'name' => 'boxRows',
    'type' => 'text',
    'description' => 'For box animations. ie: 3',
    'value' => '3'
);
$theme_options[] = array(
    'name' => 'animSpeed',
    'type' => 'text',
    'description' => 'Slide transition speed. ie: 500',
    'value' => '500'
);
$theme_options[] = array(
    'name' => 'pauseTime',
    'type' => 'text',
    'value' => '3000',
    'description' => 'How long each slide will show.ie: 3000'
);
$theme_options[] = array(
    'name' => 'startSlide',
    'type' => 'text',
    'value' => '0',
    'description' => 'Set starting Slide (0 index)'
);
$theme_options[] = array(
    'name' => 'directionNav',
    'type' => 'checkbox',
    'description' => 'Next & Prev navigation'
);
$theme_options[] = array(
    'name' => 'controlNav',
    'type' => 'checkbox',
    'description' => '1,2,3... navigation'
);
$theme_options[] = array(
    'name' => 'controlNavThumbs',
    'type' => 'checkbox',
    'description' => 'Use thumbnails for Control Nav'
);
$theme_options[] = array(
    'name' => 'pauseOnHover',
    'type' => 'checkbox',
    'description' => 'Stop animation while hovering'
);
$theme_options[] = array(
    'name' => 'manualAdvance',
    'type' => 'checkbox',
    'description' => 'Force manual transitions'
);
$theme_options[] = array(
    'name' => 'prevText',
    'type' => 'text',
    'description' => 'Prev directionNav text'
);
$theme_options[] = array(
    'name' => 'nextText',
    'type' => 'text',
    'description' => 'Next directionNav text'
);
$theme_options[] = array(
    'name' => 'randomStart',
    'type' => 'checkbox',
    'description' => 'Start on a random slide'
);


?>