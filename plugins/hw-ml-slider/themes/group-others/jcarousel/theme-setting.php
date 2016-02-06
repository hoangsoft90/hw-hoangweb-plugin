<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/06/2015
 * Time: 09:12
 */
//wp_enqueue_style('mlslider-default-skin',plugins_url('',__FILE__));
$theme['styles'][] = 'asset/style.css';
#$theme['scripts'] = array('asset/js/jquery.jcarousellite.min.js','asset/js/jquery.easing.min.js');
$theme['js-libs'] = array('sliders/jcarousellite');
$theme['options'] = array(
    //navigation
    'btnPrev' => '.prev',
    'btnNext' => '.next'
);