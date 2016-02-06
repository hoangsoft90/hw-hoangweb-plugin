<?php
/**
 * theme settings
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = '';
//override jcarouse options
$theme['scroll_options']['jcarousellite'] = array(
    'btnNext'=>'.next',
    'btnPrev' => '.prev',
    'auto' => '800',
    'speed' => '1000',
    'scroll' =>2,
    //'mouseWheel' => true,
    //'easing' => 'easeOutBounce',
    //'start' => '0',
    //'visible'=>10,
    'vertical' => true,

);

$theme['compatible_vars'] = array(
    'cat_posts' => 'wp_query',
    'arrExlpodeFields' => array('title','excerpt','comment_num','date','thumb','author'),
    'metaFields' => array(),
    'instance' => array()
);