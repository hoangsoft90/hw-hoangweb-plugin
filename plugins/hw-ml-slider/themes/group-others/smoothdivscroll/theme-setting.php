<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/06/2015
 * Time: 09:12
 */
//wp_enqueue_style('mlslider-default-skin',plugins_url('',__FILE__));
$theme['styles'][] = 'asset/style.css';
$theme['scripts'] = array('asset/js/jquery.smoothdivscroll-1.3-min.js','asset/js/jquery.easing.min.js','asset/js/jquery.mousewheel.min.js','asset/js/jquery.kinetic.min.js');
$theme['options'] = array(
    //navigation
    "scrollingHotSpotLeftClass" => "scrollingHotSpotLeft",
    "mousewheelScrolling" => "allDirections",
    "autoScrollingMode"=> "onStart"
);