<?php
/**
 * theme settings
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = 'js.js';
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
    'instance' => array(),
    'hwtpl_wrapper_id' => 'hwtpl_wrapper_id-hw-loop-template',
    'hwtpl_scrollbar_wrapper_class' => 'hwtpl_scrollbar_wrapper_class-hw-loop-template',
    'hwtpl_pagination_class' => 'hwtpl_pagination_class',
    'awc_enable_grid_posts' => false,
    'before_widget' => '',
    'after_widget' => '',
    'open_title_link' => '',
    'close_title_link' => '',
    'before_title' => '',
    'widget_title' => '',
    'after_title' => ''
);