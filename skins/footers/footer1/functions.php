<?php

register_sidebar( array(
    'name' => __( 'Footer col1', 'hoangweb' ),
    'id' => 'hwskin-footers-default-footer-col1',
    'description' => __( 'Sidebar footer col', 'hoangweb' ),
    'before_widget' => '<div id="%1$s" style="" class="boxD %2$s">',
    'after_widget' => '</div></div><div class="lineboxD"></div>',
    'before_title' => '<h5>',
    'after_title' => '</h5><div class="widget-content">',
) );
/*
register_sidebar( array(
    'name' => __( 'Footer col 2', 'hoangweb' ),
    'id' => 'hwskin-footers-default-footer-col2',
    'description' => __( 'Sidebar footer', 'hoangweb' ),
    'before_widget' => '<div id="%1$s" style="" class="column %2$s">',
    'after_widget' => '</div>',
    'before_title' => '',
    'after_title' => '',
) );
*/
?>