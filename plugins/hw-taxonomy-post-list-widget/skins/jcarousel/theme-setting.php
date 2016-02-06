<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/05/2015
 * Time: 09:02
 */
/**
 * theme settings
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = 'js.js';
//override jcarouse options
$theme['scroll_options']['jcarousellite'] = array(
    'btnNext'=>'.next',
    'btnPrev' => '.prev',
    //'auto' => '800',
    //'speed' => '1000',
    //'scroll' =>2,
    //'mouseWheel' => true,
    //'easing' => 'easeOutBounce',
    //'start' => '0',
    //'visible'=>10,
    //'vertical' => true,

);
$theme['options'] = array(
    'btnNext'=>'.next',
    'btnPrev' => '.prev',
);

$theme['compatible_vars'] = array(
    'cat_posts' => 'wp_query',
    'arrExlpodeFields' => array('title','excerpt','comment_num','date','thumb','author'),
    'metaFields' => array(),
    'instance' => array()
);