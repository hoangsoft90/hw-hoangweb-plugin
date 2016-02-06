<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/06/2015
 * Time: 10:48
 */
//wp_enqueue_style('mlslider-default-skin',plugins_url('',__FILE__));
$theme['styles'] = array('asset/style.css');
$theme['js-libs'][] = 'sliders/nivoSlider';

//pick selected slider theme stylesheet
if(isset($user_theme_options['theme']) ) {
    $theme['styles'][] = 'asset/themes/'.$user_theme_options['theme'].'/'.$user_theme_options['theme'].'.css';
}