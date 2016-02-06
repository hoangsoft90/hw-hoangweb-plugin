<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/05/2015
 * Time: 09:12
 */
/**
 * theme settings
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = '';
//override jcarouse options
$theme['scroll_options']['jcarousellite'] = array(
    'btnNext'=>'.bx-controls-direction .bx-next',
    'btnPrev' => '.bx-controls-direction .bx-prev',

    //'mouseWheel' => true,
    //'easing' => 'easeOutBounce',
    //'start' => '0',
    //'visible'=>10,

);
$theme['compatible_vars'] = array(
    'cat_posts' => 'wp_query',
    'arrExlpodeFields' => array('title','excerpt','comment_num','date','thumb','author'),
    'metaFields' => array(),
    'instance' => array()
);