<?php
/**
 * theme options
 */
$theme_options[] = array(
    'name' => 'width',
    'type' => 'text',
    'description' => 'Set width. Chú ý: Thiết lập đủ kích thước chiều rộng để đảm bảo thanh cuộn hiển thị đúng vị trí và hoạt động chính xác.'
);
$theme_options[] = array(
    'name' => 'height',
    'type' => 'text',
    'description' => 'Set height'
);
$theme_options[] = array(
    'name' => 'wheelSpeed',
    'type' => 'text',
    'description' => 'The scroll speed applied to mousewheel event. ie: 2'
);
$theme_options[] = array(
    'name' => 'wheelPropagation',
    'type' => 'checkbox',
    'description' => 'If this option is true, when the scroll reaches the end of the side, mousewheel event will be propagated to parent element.'
);
$theme_options[] = array(
    'name' => 'minScrollbarLength',
    'type' => 'text',
    'description' => 'ie: 20'
);
$theme_options[] = array(
    'name' => 'swipePropagation',
    'type' => 'checkbox',
    'description' => 'If this option is true, when the scroll reaches the end of the side, touch scrolling will be propagated to parent element.'
);
$theme_options[] = array(
    'name' => 'minScrollbarLength',
    'type' => 'text',
    'description' => 'When set to an integer value, the thumb part of the scrollbar will not shrink below that number of pixels.'
);
$theme_options[] = array(
    'name' => 'maxScrollbarLength',
    'type' => 'text',
    'description' => 'When set to an integer value, the thumb part of the scrollbar will not expand over that number of pixels.'
);
$theme_options[] = array(
    'name' => 'useBothWheelAxes',
    'type' => 'checkbox',
    'description' => 'When set to true, and only one (vertical or horizontal) scrollbar is visible then both vertical and horizontal scrolling will affect the scrollbar.'
);
$theme_options[] = array(
    'name' => 'useKeyboard',
    'type' => 'checkbox',
    'description' => 'When set to true, the scroll works with arrow keys on the keyboard. The element is scrolled only when the mouse cursor hovers the element.'
);
$theme_options[] = array(
    'name' => 'suppressScrollX',
    'type' => 'checkbox',
    'description' => 'When set to true, the scroll bar in X axis will not be available, regardless of the content width.'
);
$theme_options[] = array(
    'name' => 'suppressScrollY',
    'type' => 'checkbox',
    'description' => 'When set to true, the scroll bar in Y axis will not be available, regardless of the content height.'
);
$theme_options[] = array(
    'name' => 'scrollXMarginOffset',
    'type' => 'text',
    'description' => 'The number of pixels the content width can surpass the container width without enabling the X axis scroll bar. Allows some "wiggle room" or "offset break", so that X axis scroll bar is not enabled just because of a few pixels.'
);
$theme_options[] = array(
    'name' => 'scrollYMarginOffset',
    'type' => 'text',
    'description' => 'The number of pixels the content height can surpass the container height without enabling the Y axis scroll bar. Allows some "wiggle room" or "offset break", so that Y axis scroll bar is not enabled just because of a few pixels.'
);
