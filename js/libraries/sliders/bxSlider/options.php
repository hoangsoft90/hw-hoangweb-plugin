<?php
/**
 * remember that file theme options named 'options.php' associated with current theme folder
 */
$theme_options[]=array(
    'name' => 'mode',
    'type' => 'select',
    'options' => array('horizontal','vertical', 'fade'),   //or 'random,fold,fade,sliceDown'
    'description' => "Type of transition between slides."
);
$theme_options[] = array(
    'name' => 'speed',
    'type' => 'text',
    'description' => "Slide transition duration (in ms). default: 500"
);
$theme_options[] = array(
    'name' => 'slideMargin',
    'type' => 'text',
    'description' => 'Margin between each slide. default: 0'
);
$theme_options[] = array(
    'name' => 'startSlide',
    'type' => 'text',
    'description' => 'Starting slide index (zero-based). default: 0'
);
$theme_options[] = array(
    'name' => 'randomStart',
    'type' => 'checkbox',
    'description' => 'Start slider on a random slide.'
);
$theme_options[] = array(
    'name' => 'slideSelector',
    'type' => 'text',
    'description' => 'Element to use as slides (ex. "div.slide"). Note: by default, bxSlider will use all immediate children of the slider element'
);
/*$theme_options[] = array(
    'name' => 'startSlide',
    'type' => 'text',
    'description' => 'Set starting Slide (0 index)'
);*/
$theme_options[] = array(
    'name' => 'infiniteLoop',
    'type' => 'checkbox',
    'description' => 'If true, clicking "Next" while on the last slide will transition to the first slide and vice-versa'
);
$theme_options[] = array(
    'name' => 'hideControlOnEnd',
    'type' => 'checkbox',
    'description' => 'If true, "Next" control will be hidden on last slide and vice-versa<br/>Note: Only used when infiniteLoop: false'
);
$theme_options[] = array(
    'name' => 'easing',
    'type' => 'select',
    'options' => array('linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out','swing', 'linear'),
    'description' => 'The type of "easing" to use during transitions. If using CSS transitions, include a value for the transition-timing-function property<br/>'
);
$theme_options[] = array(
    'name' => 'captions',
    'type' => 'checkbox',
    'description' => 'Include image captions. Captions are derived from the image"s title attribute'
);
$theme_options[] = array(
    'name' => 'ticker',
    'type' => 'checkbox',
    'description' => 'Use slider in ticker mode (similar to a news ticker)'
);
$theme_options[] = array(
    'name' => 'tickerHover',
    'type' => 'checkbox',
    'description' => 'Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!'
);
$theme_options[] = array(
    'name' => 'adaptiveHeight',
    'type' => 'checkbox',
    'description' => 'Dynamically adjust slider height based on each slide"s height'
);
$theme_options[] = array(
    'name' => 'adaptiveHeightSpeed',
    'type' => 'text',
    'description' => 'Slide height transition duration (in ms). Note: only used if adaptiveHeight: true ,default: 500'
);
$theme_options[] = array(
    'name' => 'video',
    'type' => 'checkbox',
    'description' => 'If any slides contain video, set this to true. Also, include plugins/jquery.fitvids.js'
);
$theme_options[] = array(
    'name' => 'responsive',
    'type' => 'checkbox',
    'description' => 'Enable or disable auto resize of the slider. Useful if you need to use fixed width sliders.'
);
$theme_options[] = array(
    'name' => 'useCSS',
    'type' => 'checkbox',
    'description' => 'If true, CSS transitions will be used for horizontal and vertical slide animations (this uses native hardware acceleration). If false, jQuery animate() will be used.'
);
$theme_options[] = array(
    'name' => 'preloadImages',
    'type' => 'select',
    'options' => array('all', 'visible'),
    'description' => "If 'all', preloads all images before starting the slider. If 'visible', preloads only images in the initially visible slides before starting the slider (tip: use 'visible' if all slides are identical dimensions)"
);
$theme_options[] = array(
    'name' => 'touchEnabled',
    'type' => 'checkbox',
    'description' => 'If true, slider will allow touch swipe transitions'
);
$theme_options[] = array(
    'name' => 'swipeThreshold',
    'type' => 'text',
    'description' => 'Amount of pixels a touch swipe needs to exceed in order to execute a slide transition. Note: only used if touchEnabled: true. default: 50'
);
$theme_options[] = array(
    'name' => 'oneToOneTouch',
    'type' => 'checkbox',
    'description' => 'If true, non-fade slides follow the finger as it swipes'
);
$theme_options[] = array(
    'name' => 'preventDefaultSwipeX',
    'type' => 'checkbox',
    'description' => 'If true, touch screen will not move along the x-axis as the finger swipes'
);
$theme_options[] = array(
    'name' => 'preventDefaultSwipeY',
    'type' => 'checkbox',
    'description' => 'If true, touch screen will not move along the y-axis as the finger swipes'
);
$theme_options[] = array(
    'type' => 'string',
    'title' => 'Controls'
);
//-------------------------------------------------------
$theme_options[] = array(
    'name' => 'controls',
    'type' => 'checkbox',
    'description' => 'If true, "Next" / "Prev" controls will be added'
);
$theme_options[] = array(
    'name' => 'nextText',
    'type' => 'text',
    'description' => 'Text to be used for the "Next" control'
);
$theme_options[] = array(
    'name' => 'prevText',
    'type' => 'text',
    'description' => 'Text to be used for the "Prev" control'
);
$theme_options[] = array(
    'name' => 'nextSelector',
    'type' => 'text',
    'description' => 'Element used to populate the "Next" control'
);
$theme_options[] = array(
    'name' => 'prevSelector',
    'type' => 'text',
    'description' => 'Element used to populate the "Prev" control'
);
$theme_options[] = array(
    'name' => 'autoControls',
    'type' => 'checkbox',
    'description' => 'If true, "Start" / "Stop" controls will be added.'
);
$theme_options[] = array(
    'type' => 'string',
    'description' => 'Auto'
);
$theme_options[] = array(
    'name' => 'auto',
    'type' => 'checkbox',
    'description' => 'Slides will automatically transition'
);
$theme_options[] = array(
    'name' => 'pause',
    'type' => 'text',
    'description' => 'The amount of time (in ms) between each auto transition. default: 4000'
);
$theme_options[] = array(
    'name' => 'autoStart',
    'type' => 'checkbox',
    'description' => 'Auto show starts playing on load. If false, slideshow will start when the "Start" control is clicked'
);
$theme_options[] = array(
    'name' => 'autoDirection',
    'type' => 'select',
    'options' => array('next', 'prev'),
    'description' => 'The direction of auto show slide transitions'
);
$theme_options[] = array(
    'name' => 'autoHover',
    'type' => 'checkbox',
    'description' => 'Auto show will pause when mouse hovers over slider'
);

?>