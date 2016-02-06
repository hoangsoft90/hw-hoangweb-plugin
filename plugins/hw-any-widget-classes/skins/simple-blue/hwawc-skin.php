<?php
/**
 * Plugin Name: simple blue
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="lefttitte" style="%1$s {css_title}">';
$theme['params']['after_title'] = '</div><div class="leftcontent" style="%1$s {css_box}">';
$theme['params']['before_widget'] = '<div id="%1$s" class="widget simple-blue %2$s" >';
$theme['params']['after_widget'] = '</div></div>';
$theme['css_title_selector']='.hw-awc-override .lefttitte';
$theme['css_content_selector'] = '.hw-awc-override .leftcontent';