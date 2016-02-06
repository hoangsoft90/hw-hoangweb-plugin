<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/10/2015
 * Time: 15:15
 */
$theme['styles'][] = 'style.css';
//$theme['scripts'][] = 'js.js';
$theme['js-libs'] = array('ddsmoothmenu');

$theme['args'] = array(
    //'ex_separator' => '<span class="separator"></span>',
    'submenu_container_class' => 'sub-menu sublist',
    'container_class' => 'hw-menu-container-style3',
    'container_id' => 'menuC',
    'menu_item_class_focus' => "current-item",
    'anchor_class' => 'tabitem',
    'allow_tags_nav_menu' => '<a><img>',
//    'menu_class' => 'menu1',
    //'before' => '<span>',
    //'after' => '</span>',
    //'link_before' => '<div>',
    //'link_after' => '</div>',
    //'menu_id' => 'nav',
    //'menu_class' => 'top-menu',
    'items_wrap' => '%3$s',

);

//register filters for this skin
$theme['filters'] = array(
    'wp_nav_menu_items' => array(
        'type' => 'filter',
        'function' => 'hw_menu_style3_add_home_link',
        'accepted_args' => 3,
        'priority' => 10
    )
);

//run this hook if current nav_menu enable by hw-menu plugin
if(!function_exists('hw_menu_style3_add_home_link')):
    function hw_menu_style3_add_home_link($items, $args,$params)
    {
        //get theme args
        if($params['function'] !== __FUNCTION__) return $items;
        $theme = $params['args'][0];

        $menu = HW_NAVMENU::get_menu_name($args);    //get current menu
        if($menu != $theme['menu']) return $items;  //compare real-value to which value that keep in theme args

        $icon = '<img src="'.plugins_url('images/iconhome.png',__FILE__).'" alt="home" title="Trang chủ" style="float:left"/>';
        $args->link_before = '';
        $args->link_after = '';
        $args->before = '';
        $args->after = '';

        $homeMenuItem = $args->before.'<a class="home1" href="' . home_url('/') . '">'. $args->link_before . $icon . $args->link_after . '</a>' . $args->after;
        /*$menu = HW_NAVMENU::get_menu_name($args);
        if (is_front_page()) $class='active';else $class = '';

        $homeMenuItem='<li class="' . $class . '">' . $args->before . '<a class="home active" href="' . home_url('/') . '" title="Trang ch?">'. $args->link_before . '' . $args->link_after . '</a>' . $args->after . '</li>';

        $items=$homeMenuItem . $items;*/
        return $homeMenuItem.$items;
    }
endif;