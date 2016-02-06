<?php
/**
 * Plugin Name: default
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="title" style="%1$s {css_title}"><h2>';
$theme['params']['after_title'] = '</h2></div><div class="listbox" style="%1$s {css_box}">';
$theme['params']['before_widget'] = '<div id="%1$s" class="block block-travelstyle-navigation %2$s" >';
$theme['params']['after_widget'] = '</div></div>';
$theme['css_title_selector']='.hw-awc-override .title';
$theme['css_content_selector'] = '.hw-awc-override .listbox';