<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/10/2015
 * Time: 15:16
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = 'js.js';
//$theme['js-libs'] = array('ddsmoothmenu');

$theme['args'] = array(
    'ex_separator' => '<li class="Space separator"></li>',
    'submenu_container_class' => 'sub-menu',
    'container_class' => 'hw-menu-style4-container menuleft',
    'menu_item_class_focus' => "menuActive",
    'menu_item_class' => 'menulink',
    //'before' => '<span>',
    //'after' => '</span>',
    //'link_before' => '<div>',
    //'link_after' => '</div>',
    'menu_id' => 'menu',
    'menu_class' => 'menu',
    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',

);

//register filters for this skin
/*$theme['filters'] = array(
    'wp_nav_menu_items' => array(
        'type' => 'filter',
        'function' => 'hw_menu_style4_add_home_link',
        'accepted_args' => 3,
        'priority' => 10
    )
);
*/

//run this hook if current nav_menu enable by hw-menu plugin
if(!function_exists('hw_menu_style4_add_home_link')):
    function hw_menu_style4_add_home_link($items, $args,$params)
    {
        //get theme args
        if($params['function'] !== __FUNCTION__) return $items;
        $theme = $params['args'][0];

        $menu = HW_NAVMENU::get_menu_name($args);    //get current menu
        if($menu != $theme['menu']) return $items;  //compare real-value to which value that keep in theme args

        $homeMenuItem='<li ><a class="home456546" href="' . home_url('/') . '">'. $args->link_before . 'XX' . $args->link_after . '</a>' . $args->after . '</li>';
        /*$menu = HW_NAVMENU::get_menu_name($args);
        if (is_front_page()) $class='active';else $class = '';

        $homeMenuItem='<li class="' . $class . '">' . $args->before . '<a class="home active" href="' . home_url('/') . '" title="Trang chủ">'. $args->link_before . '' . $args->link_after . '</a>' . $args->after . '</li>';

        $items=$homeMenuItem . $items;*/
        return $homeMenuItem.$items;
    }
endif;