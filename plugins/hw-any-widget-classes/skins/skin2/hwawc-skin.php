<?php
/**
 * Plugin Name: skin 2
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="boxrighttitte" style="%1$s {css_title}"><div class="titteboxr">';
$theme['params']['after_title'] = '</div></div><div class="boxrighnd" style="%1$s {css_box}">';
$theme['params']['before_widget'] = '<div id="%1$s" class="widget %2$s" >';
$theme['params']['after_widget'] = '</div><div class="boxrightboder"></div></div>';
$theme['css_title_selector']='.hw-awc-override .boxrighttitte';
$theme['css_content_selector'] = '.hw-awc-override .boxrighnd';