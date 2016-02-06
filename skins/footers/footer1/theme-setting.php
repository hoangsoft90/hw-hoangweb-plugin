<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/06/2015
 * Time: 13:28
 */
$theme['styles'][] = 'style.css';
$theme['filters'] = array(
    'hwskin_nhp_theme_option_repeat' => array(
        'type' => 'filter',
        'function' => '_hwskin_nhp_theme_option_repeat_footer1',
        'accepted_args' => 2,
        'priority' => 10
    )
);
if(!function_exists('_hwskin_nhp_theme_option_repeat_footer1')):
    function _hwskin_nhp_theme_option_repeat_footer1($num ,$theme)
    {
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $num;
        $theme = $theme['do_filters_args_'.__FUNCTION__];

        //$menu = HW_NAVMENU::get_menu_name($args);    //get current menu
        //if($menu != $theme['menu']) return $num;  //compare real-value to which value that keep in theme args

        return $num;
    }
endif;