<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/10/2015
 * Time: 15:10
 */
$theme['styles'][] = 'style.css';
$theme['scripts'][] = 'js.js';
$theme['js-libs'] = array('menus/ddsmoothmenu');

//$theme['scripts'][] = '';
$theme['args'] = array(
    //'ex_separator' => '<span class="separator"></span>',
    'submenu_container_class' => 'sub-menu sublist',
    'container_class' => 'hw-menu-def-container header-menu ddsmoothmenu',
    'container_id' => 'smoothmenu1',
    'menu_item_class_focus' => "current-item",
    'menu_class' => 'menu1',
    //'before' => '<span>',
    //'after' => '</span>',
    //'link_before' => '<div>',
    //'link_after' => '</div>',
    'menu_id' => 'nav',
    'menu_class' => '',
    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',

);

//register filters for this skin
$theme['filters'] = array(
    'wp_nav_menu_items' => array(
        'type' => 'filter',
        'function' => 'hw_menu_def_add_home_link',
        'accepted_args' => 3,
        'priority' => 10
    ),
    'wp_footer' => array(
        'type'=> 'action',
        'function' => 'hwmenu_init_skin',
        'accepted_args' => 2,
        'priority' => 10
    )
);
/**
 * note alway exists first empty param if function have no any params
 * @hook wp_footer
 */
if(!function_exists('hwmenu_init_skin')):
function hwmenu_init_skin($arg,$theme){
    $user_options = $theme['args'][1];  //get user options
    //valid
    if($theme['function'] !== __FUNCTION__) return;

    //ddsmoothmenu
    if( isset($user_options) && isset($user_options['enable_ddsmoothmenu']) && $user_options['enable_ddsmoothmenu']=='__TRUE__') {
        $args=  array(
            'mainmenuid' => $user_options['container_id'],   //Menu DIV id
            'orientation' => $user_options['dd_orientation'],   //Horizontal or vertical menu: Set to "h" or "v"
            'classname' => $user_options['dd_classname'],   //class added to menu's outer DIV
            'method' => $user_options['dd_method'],  // set to 'hover' (default) or 'toggle'
            'arrowswap' => $user_options['hw_arrowswap'],   // enable rollover effect on menu arrow images?
            #'contentsource' => 'markup' //"markup" or ["container_id", "path_to_menu_file"]
            //customtheme: ["#804000", "#482400"],
        );
        ?>
        <script>
            jQuery(document).ready(function() {
                ddsmoothmenu.init(<?php echo HW_SKIN_Option::build_json_options($args)?>);
            });

        </script>
    <?php
    }
}
    endif;
/**
 * run this hook if current nav_menu enable by hw-menu plugin
 * @hook wp_nav_menu_items
 */
if(!function_exists('hw_menu_def_add_home_link')):
    function hw_menu_def_add_home_link($items, $args,$params)
    {
        //get theme args
        if($params['function'] !== __FUNCTION__) return $items;
        $theme = $params['args'][0];

        $menu = HW_NAVMENU::get_menu_name($args);    //get current menu
        if($menu != $theme['menu']) return $items;  //compare real-value to which value that keep in theme args

        #$homeMenuItem='<li ><a class="home456546" href="' . home_url('/') . '">'. $args->link_before . 'XX' . $args->link_after . '</a>' . $args->after . '</li>';
        /*$menu = HW_NAVMENU::get_menu_name($args);
        if (is_front_page()) $class='active';else $class = '';

        $homeMenuItem='<li class="' . $class . '">' . $args->before . '<a class="home active" href="' . home_url('/') . '" title="Trang chủ">'. $args->link_before . '' . $args->link_after . '</a>' . $args->after . '</li>';

        $items=$homeMenuItem . $items;*/
        #return $homeMenuItem.$items;
        return $items;
    }
endif;