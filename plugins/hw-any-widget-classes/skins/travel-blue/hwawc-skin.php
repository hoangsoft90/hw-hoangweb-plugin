<?php
/**
 * Plugin Name: travel blue
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="titteC" style="%1$s {css_title}"><a style="display:none;" href="#" class="xemall2">Xem thêm</a><h4>';
$theme['params']['after_title'] = '</h4></div><div class="bg1boxC">';
$theme['params']['before_widget'] = '<div id="%1$s" class="widget travel-blue %2$s *1" >';
$theme['params']['after_widget'] = '</div></div>';
$theme['css_title_selector']='.hw-awc-override .titteC';
$theme['css_content_selector'] = '.hw-awc-override .widget';