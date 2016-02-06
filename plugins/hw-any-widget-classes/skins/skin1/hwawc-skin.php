<?php
/**
 * Plugin Name: about box
 */
$theme['styles'][] = 'style.css';   //register stylesheet

//sidebar widget params
$theme['params']['before_title'] = '<div class="topic-html-content-title"><h2 class="topic-html-content-header">';
$theme['params']['after_title'] = '</h2></div><div class="topic-html-content-body">';
$theme['params']['before_widget'] = '<div id="%1$s" class="block-short-aboutus %2$s" ><div class="topic-html-content">';
$theme['params']['after_widget'] = '</div></div></div>';