<?php

//pause_on_hover
$theme_options[] = array(
    'name' => 'pause_on_hover',
    'type' => 'checkbox',
    'title' => 'pause on hover',
    'description' => 'Pause slider when hover mouse.',
    'value'=>'',
);
//scrollingHotSpotLeftClass
$theme_options[] = array(
    'name' => 'scrollingHotSpotLeftClass',
    'type' => 'text',
    'title' => 'scrollingHotSpotLeftClass',
    'description' => 'The CSS class for the left hot spot.',
    'value'=>'scrollingHotSpotLeft',
    'method' => 'append'
);
//scrollingHotSpotRightClass
$theme_options[] = array(
    'name' => 'scrollingHotSpotRightClass',
    'type' => 'text',
    'title' => 'scrollingHotSpotRightClass',
    'description' => 'The CSS class for the right hot spot.',
    'value'=>'scrollingHotSpotRight',
    'method' => 'append'
);
//scrollableAreaClass
$theme_options[] = array(
    'name' => 'scrollableAreaClass',
    'type' => 'text',
    'title' => 'scrollableAreaClass',
    'description' => 'The CSS class for the actual element that is scrolled left or right.',
    'value'=>'scrollableArea',
    'method' => 'append'
);
//scrollWrapperClass
$theme_options[] = array(
    'name' => 'scrollWrapperClass',
    'type' => 'text',
    'title' => 'scrollWrapperClass',
    'description' => 'The CSS class for the wrapper element that surrounds the scrollable area.',
    'value'=>'scrollWrapper',
    'method' => 'append'
);
//hotSpotScrolling
$theme_options[] = array(
    'name' => 'hotSpotScrolling',
    'type' => 'checkbox',
    'title' => 'hotSpotScrolling',
    'description' => 'Should hot spot scrolling be activated or not? If you want a touch scroller (see the option touchScrolling) its best to set this option to false.',
    'value'=>''
);
//hotSpotScrollingStep
$theme_options[] = array(
    'name' => 'hotSpotScrollingStep',
    'type' => 'text',
    'title' => 'hotSpotScrollingStep (number (of pixels))',
    'description' => 'Sets the step length for the hot spot scrolling. A larger number will make the hot spot scrolling faster but also make it appear as less smooth.',
    'value'=>'15',
    'method' => 'override'
);
//hotSpotScrollingInterval
$theme_options[] = array(
    'name' => 'hotSpotScrollingInterval',
    'type' => 'text',
    'title' => 'hotSpotScrollingInterval ( number (of milliseconds))',
    'description' => 'Sets the number of milliseconds between each scroll step.',
    'value'=>'45',
    'method' => 'override'
);
/**
 * Mousewheel scrolling options
 */
//mousewheelScrolling
$theme_options[] = array(
    'name' => 'mousewheelScrolling',
    'type' => 'select',
    'title' => 'mousewheelScrolling',
    'description' => 'Determines if mousewheel scrolling should be active and if so, how it should work. Possible values are:',
    'options'=> array("" ,'vertical','horizontal','allDirections')
);
/**
 * Touch scrolling options
 */
//touchScrolling
$theme_options[] = array(
    'name' => 'touchScrolling',
    'type' => 'checkbox',
    'title' => 'touchScrolling',
    'description' => ' touch scrolling to work you need to include jquery.kinetic.js on your page.',
    'value'=>''
);
//manualContinuousScrolling
$theme_options[] = array(
    'name' => 'manualContinuousScrolling',
    'type' => 'checkbox',
    'title' => 'manualContinuousScrolling',
    'description' => 'These settings apply to hot spot, mousewheel and touch scrolling.'
);
/**
 * Auto scrolling options
 */
//autoScrollingMode
$theme_options[] = array(
    'name' => 'autoScrollingMode',
    'type' => 'select',
    'title' => 'autoScrollingMode',
    'description' => 'These have to do with the auto scrolling functionality of Smooth Div Scroll.',
    'options' => array('', 'onStart', 'always'),
    'method' => 'override'
);
//autoScrollingDirection
$theme_options[] = array(
    'name' => 'autoScrollingDirection',
    'type' => 'select',
    'title' => 'autoScrollingDirection',
    'description' => 'This option controls the direction and behavior of the auto scrolling and is only used if auto scrolling is activated.',
    'options' => array('right','left', 'backAndForth','endlessLoopRight', 'endlessLoopLeft'),
    #'method' => 'override'
);
//autoScrollingStep
$theme_options[] = array(
    'name' => 'autoScrollingStep',
    'type' => 'text',
    'title' => 'autoScrollingStep (number (of pixels))',
    'description' => 'Sets the step length for the auto scrolling. A larger number will make the auto scrolling faster but also make it appear as less smooth.',
    'value' => '1',
);
