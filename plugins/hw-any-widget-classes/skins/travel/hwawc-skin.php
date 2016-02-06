<?php
/**
 * Plugin Name: travel
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="titteA" style="%1$s {css_title}"><h2><strong>';
$theme['params']['after_title'] = '</strong></h2></div>';
$theme['params']['before_widget'] = '<div id="%1$s" class="boxtourhome %2$s *1" >';
$theme['params']['after_widget'] = '</div>';
$theme['css_title_selector']='.hw-awc-override .titleA';
$theme['css_content_selector'] = '.hw-awc-override .boxtourhome';