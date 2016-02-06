<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 24/06/2015
 * Time: 09:26
 */
/*$theme_options[] = array(
    'name' => 'title_li',
    'type' => 'text',
    'description' => 'Set the title and style of the outer list item. Defaults to "Categories".',
    'value' => '',
    'method' => 'override'
);
$theme_options[] = array(
    'name' => 'remove_wrap',
    'type' => 'checkbox',
    'description' => 'loại bỏ wrap bao ngoài toàn bộ danh mục.'
);
*/
$theme_options[] = array(
    'name' => 'show_icon',
    'type' => 'checkbox',
    'title' => 'Hiển thị biểu tượng',
    'description' => 'Hiển thị biểu tượng cho danh mục.'
);
$theme_options[] = array(
    'name' => 'remove_link_parent',
    'type' => 'checkbox',
    'title' => 'remove_link_parent',
    'description' => 'Xóa liên kết của mục cha'
);
$theme_options[] = array(
    'name' => 'ul_classes',
    'type' => 'text',
    'title' => 'ul_classes',
    'description' => 'Thêm class vào thẻ < ul'
);
$theme_options[] = array(
    'name' => 'submenu_container_class',
    'type' => 'text',
    'title' => 'ul submenu class',
    'description' => 'Thêm class vào thẻ < ul ở tầng 2'
);
$theme_options[] = array(
    'name' => 'current_item_class',
    'type' => 'text',
    'description' => 'Current item class',
    'value' => 'current-cat-item',
    'method' => 'append'
);

//attributes
$theme_options[] = array(
    'name' => 'anchor_attrs',
    'type' => 'text',
    'title' => 'anchor_attrs',
    'description' => 'Thêm các thuộc tính vào thẻ < a của menu item.',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'anchor_attrs_has_submenu',
    'type' => 'text',
    'title' => 'anchor_attrs_has_submenu',
    'Thêm các thuộc tính vào thẻ < a của menu item có submenu.',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'anchor_attrs_submenu',
    'type' => 'text',
    'title' => 'anchor_attrs_submenu',
    'description' => 'Thêm các thuộc tính vào thẻ < a của menu item trong submenu.',
    'method' => 'append'
);
//anchor classes
$theme_options[] = array(
    'name' => 'anchor_class',
    'type' => 'text',
    'title' => 'anchor_class',
    'description' => 'Thêm class vào thẻ < a của menu item.',
    'value' =>'cat-anchor',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'anchor_class_has_submenu',
    'type' => 'text',
    'title' => 'anchor_class_has_submenu',
    'description' => 'Thêm class vào thẻ < a của menu item có submenu.',
    'value' => 'cat-anchor-has-sub',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'anchor_class_submenu',
    'type' => 'text',
    'title' => 'anchor_class_submenu',
    'description' => 'Thêm class vào thẻ < a của menu item trong submenu.',
    'value' => 'cat-anchor-sub',
    'method' => 'append'
);
//menu item classes
$theme_options[] = array(
    'name' => 'menu_item_class',
    'type' => 'text',
    'title' => 'menu_item_class',
    'description' => 'Thêm class vào thẻ < li của menu item.',
    'value' => 'cat-item',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'menu_item_class_focus',
    'type' => 'text',
    'title' => 'menu_item_class_focus',
    'description' => 'Thêm class vào thẻ < li của menu item khi focus.',
    'value' => 'current-cat-item',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'menu_item_class_has_submenu',
    'type' => 'text',
    'title' => 'menu_item_class_has_submenu',
    'description' => 'Thêm class vào thẻ < li của menu item có submenu.',
    'value' => 'cat-item-has-sub',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'menu_item_class_submenu',
    'type' => 'text',
    'title' => 'menu_item_class_submenu',
    'description' => 'Thêm class vào thẻ < li của menu item có submenu.',
    'value' => 'cat-item-sub',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'first_menu_item_class',
    'type' => 'text',
    'title' => 'first_menu_item_class',
    'description' => 'Thêm class vào < li của menu item đầu tiên.',
    'value' => 'first-cat-item',
    'method' => 'append'
);
$theme_options[] = array(
    'name' => 'last_menu_item_class',
    'type' => 'text',
    'title' => 'last_menu_item_class',
    'description' => 'Thêm class vào thẻ < li của menu item cuối cùng.',
    'value' => 'last-cat-item',
    'method' => 'append'
);
