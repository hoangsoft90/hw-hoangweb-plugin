<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/07/2015
 * Time: 09:36
 */

$theme_options[] = array(
    'type' => 'string',
    'description' => 'Vui lòng kích hoạt "cuộn nội dung" với kiểu cuộn "ngắt quãng" ở tùy chọn phía dưới.<br/><a href="http://www.gmarwaha.com/jquery/jcarousellite/documentation.php" target="_blank">Xem tài liệu</a>'
);
$theme_options[] = array(
    'name' => 'vertical',
    'type' => 'checkbox',
    'title' => 'Vertical?',
    'description' => 'Determines the direction of the carousel. true, means the carousel will display vertically.'
);
$theme_options[] = array(
    'name' => 'pauseOnHover',
    'type' => 'checkbox',
    'title' => 'pauseOnHover',
    'description' => 'Dừng slides khi dê chuột.'
);
$theme_options[] = array(
    'name' => 'mouseWheel',
    'type' => 'checkbox',
    'title' => 'mouseWheel',
    'description' => 'Using buttons and mouse-wheel are not mutually exclusive. '
);
$theme_options[] = array(
    'name' => 'auto',
    'type' => 'text',
    'title' => 'Auto',
    'description' => 'specifying a millisecond value to this option. IE: 800',
    'value' => '800',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'speed',
    'type' => 'text',
    'title' => 'speed',
    'description' => 'Try it out with different speeds like 800, 600, 1500 etc. Providing 0, will remove the slide effect.',
    'method' => 'override',
    'value' => '800'
);
$theme_options[] = array(
    'name' => 'easing',
    'type' => 'select',
    'title' => 'easing effect',
    'description' => 'Once specified, the carousel will slide based on the provided easing effect.',
    'options' => array('','easeOutBounce','easeOutElastic','easeOutQuad','easeInOutBack'),
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'visible',
    'type' => 'text',
    'title' => 'visible',
    'description' => 'This specifies the number of items visible at all times within the carousel. ie: 1, 2.. Default: 3<br/>Chú ý: chỉ định số lượng hiển thị trên slider để lặp vô hạn slides.',
    'method' => 'override',
);

$theme_options[] = array(
    'name' => 'start',
    'type' => 'text',
    'title' => 'start',
    'description' => 'You can specify from which item the carousel should start. Remember, the first item in the carousel has a start of 0, and so on.',
    'method' => 'override',
    'value' => '0'
);
$theme_options[] = array(
    'name' => 'scroll',
    'type' => 'text',
    'title' => 'Scroll',
    'description' => 'You can scroll more than one item. ie: 1,2..',
    'method' => 'override',
    'value' => '1'
);
$theme_options[] = array(
    'name' => 'circular',
    'type' => 'checkbox',
    'title' => 'Circular?',
    'description' => "(Tùy chỉnh visible và kích hoạt option này để lặp vô hạn). jCarouselLite carousels are circular by default. You have to explicitly disable circular behavior if you don't want them"
);
$theme_options[] = array(
    'name' => 'btnPrev',
    'type' => 'text',
    'value' => '',
    'description' => 'Selector for the previous button.',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'btnNext',
    'type' => 'text',
    'value' => '',
    'description' => 'Selector for the next button.',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'responsive',
    'type' => 'checkbox',
    'title' => 'responsive',
    'description' => 'Kích hoạt chế độ responsive'
);