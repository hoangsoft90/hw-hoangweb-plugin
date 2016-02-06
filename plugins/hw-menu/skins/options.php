<?php
$theme_options[] = array(
    'name' => 'show_lang_buttons_outside',
    'type' => 'checkbox',
    'title' => 'Hiển nút chọn ngôn ngữ ra ngoài menu. Chú ý: nếu chọn nằm ngoài thì gọi menu với hàm <code>hw_wp_nav_menu</code>',
    'description' => 'Hiển nút chọn ngôn ngữ ra ngoài menu.'
);
$theme_options[] = array(
    'name' => 'show_items_separator',
    'type' => 'checkbox',
    'title' => 'Thêm Ngăn mỗi menu item',
);
//defined more own args
$theme_options[] = array(
    'name' => 'ex_separator',
    'type' => 'text',
    'title' => 'Ngăn mỗi menu item',
    'value'=> '<span class="separator"></span>',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'submenu_container_class',
    'type' => 'text',
    'title' => 'Thêm class vào container submenu.',
    'value'=> '',
    'method' => 'append'
);
$theme_options[] =  array(
    'name' => 'allow_tags_nav_menu',
    'type' => 'text',
    'title' => 'HTML cho phép trong nội dung wp_nav_menu',
    'value' => '<a>',
    'method' => 'append',
    'description' => 'Chú ý: Tính năng này chỉ cho phép khi kích hoạt cài đặt (Xóa ul,li bao quanh menu).<br/>HTML cho phép trong nội dung wp_nav_menu. ie: '.htmlspecialchars('<a><div><span>')
);
/**
 * anchor <a tag
 */
//anchor attributes
$theme_options[] = array(
    'name' => 'anchor_attrs',
    'type' => 'text',
    'description' => 'Thêm các thuộc tính vào thẻ < a của menu item.',
    'method' => 'append',
    'value' => ''   //default value
);
$theme_options[] = array(
    'name' => 'anchor_attrs_has_submenu',
    'type' => 'text',
    'description' => 'Thêm các thuộc tính vào thẻ < a của menu item có submenu.',
    'method' => 'append',
    'value' => ''   //default value
);
$theme_options[] = array(
    'name' => 'anchor_attrs_submenu',
    'type' => 'text',
    'description' => 'Thêm các thuộc tính vào thẻ < a của menu item trong submenu.',
    'method' => 'append',
    'value' => ''   //default value
);

$theme_options[] = array(
    'name' => 'anchor_class',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < a của menu item.',
    'method' => 'append',
    'value' => 'anchor-class'   //default value
);
$theme_options[] = array(
    'name' => 'anchor_class_has_submenu',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < a của menu item có submenu.',
    'method' => 'append',
    'value' => ''   //default value
);
$theme_options[] = array(
    'name' => 'anchor_class_submenu',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < a của menu item trong submenu.',
    'method' => 'append',
    'value' => ''   //default value
);
/**
 * menu item <Li
 */
$theme_options[] = array(
    'name' => 'menu_item_class',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item.',
    'method' => 'append',
    'value' => 'my-item-normal'
);
$theme_options[] = array(
    'name' => 'menu_item_class_focus',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item khi focus.',
    'method' => 'append',
    'value' => 'my-item-focus'
);
//menu_item_class_has_submenu
$theme_options[] = array(
    'name' => 'menu_item_class_has_submenu',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item có submenu.',
    'method' => 'append',
    'value' => 'my-item-focus'
);
//menu_item_class_submenu
$theme_options[] = array(
    'name' => 'menu_item_class_submenu',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item có submenu.',
    'method' => 'append',
    'value' => 'my-item-focus'
);
//menu_item_class_hover
/*$theme_options[] = array(
    'name' => 'menu_item_class_hover',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item khi hover.',
    'method' => 'append',
    'value' => 'my-item-hover'
);
*/
$theme_options[] = array(
    'name' => 'first_menu_item_class',
    'type' => 'text',
    'description' => 'Thêm class vào < li của menu item đầu tiên.',
    'method' => 'append',
    'value' => 'hw-first-nav-item'
);
$theme_options[] = array(
    'name' => 'last_menu_item_class',
    'type' => 'text',
    'description' => 'Thêm class vào thẻ < li của menu item cuối cùng.',
    'method' => 'append',
    'value' => 'hw-last-nav-item'
);

$theme_options[] = array(
    'type' => 'string',
    'description' => '<hr/><h2>Tham số mặc định wp_nav_menu</h2>'
);
//wp_nav_menu args
$theme_options[] = array(
    'name' => 'container',
    'type' => 'text',
    'description' => 'Chú ý: không để thẻ span (có thể không hoạt động). Whether to wrap the ul, and what to wrap it with. Allowed tags are div and nav. Use false for no container e.g.',
    'value' => 'div',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'container_class',
    'type' => 'text',
    'description' => 'The class that is applied to the container',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'container_id',
    'type' => "text",
    'description' => 'The ID that is applied to the container',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'menu_class',
    'type' => 'text',
    'value' => '',
    'description' => 'The class that is applied to the ul element which encloses the menu items. Multiple classes can be separated with spaces.',
    'method' => 'append'
);

$theme_options[] = array(
    'name' => 'menu_id',
    'type' => 'text',
    'description' => 'The ID that is applied to the ul element which encloses the menu items',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'before',
    'type' => 'text',
    'description' => 'Output text before the < a > of the link',
    'method' => 'override'
);
//submenu_before
$theme_options[] = array(
    'name' => 'submenu_before',
    'type' => 'text',
    'description' => 'Output text before the < a > of the link for submenu',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'after',
    'type' => 'text',
    'description' => ' Output text after the < / a > of the link',
    'method' => 'override'
);
//submenu_after
$theme_options[] = array(
    'name' => 'submenu_after',
    'type' => 'text',
    'description' => ' Output text after the < / a > of the link for submenu',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'link_before',
    'type' => 'text',
    'description' => 'Output text before the link text',
    'method' => 'override'
);
//submenu_link_before
$theme_options[] = array(
    'name' => 'submenu_link_before',
    'type' => 'text',
    'description' => 'Output text before the link text for submenu',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'link_after',
    'type' => 'text',
    'description' => "Output text after the link text",
    'method' =>'override'
);
//submenu_link_after
$theme_options[] = array(
    'name' => 'submenu_link_after',
    'type' => 'text',
    'description' => "Output text after the link text",
    'method' =>'override'
);
$theme_options[] = array(
    'name' => 'items_wrap',
    'type' => 'text',
    'description' => "Evaluated as the format string argument of a sprintf() expression. see: <a href='https://codex.wordpress.org/Function_Reference/wp_nav_menu' target='_blank'>wp_nav_menu</a>",
    'method' =>'override',
    'value' =>  ('<ul id="%1$s" class="%2$s">%3$s</ul>')
);
$theme_options[] = array(
    'name' => 'depth',
    'type' => 'text',
    'value' => '-1',
    'description' => 'How many levels of the hierarchy are to be included where 0 means all. -1 displays links at any depth and arranges them in a single, flat list.',
    'method' => 'override'
);
include(dirname(__FILE__). '/ddsmoothmenu-options.php');