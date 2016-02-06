<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/10/2015
 * Time: 10:10
 */
class HW_UI_Menus extends HW_UI_Component {
    /**
     * Create the main function to build milti-level menu. It is a recursive function.
     * @param $parent
     * @param $menu
     * @param $args
     */
    public static  function buildMenu($parent, $menu, $args = array(), $sub=false) {
        $html = "";

        //attributes
        if(isset($args['attributes']) && is_array($args['attributes'])) $atts = $args['attributes'];
        else $atts = array();

        if(isset($args['item_attributes']) && is_array($args['item_attributes'])) $item_atts = $args['item_attributes'];
        else $item_atts = array();

        if(isset($args['sub_attributes']) && is_array($args['sub_attributes'])) $sub_atts = $args['sub_attributes'];
        else $sub_atts = array();

        if(isset($args['sub_item_attributes']) && is_array($args['sub_item_attributes'])) $sub_item_atts = $args['sub_item_attributes'];
        else $sub_item_atts = array();

        //classes
        if(isset($args['classes']) && is_array($args['classes'])) {
            $atts['class'] = is_array($args['classes'])? join(' ', $args['classes']) : $args['classes'];
        }
        if(isset($args['item_classes']) && is_array($args['item_classes'])) {
            $item_atts['class'] = is_array($args['item_classes'])? join(' ', $args['item_classes']) : $args['item_classes'];
        }
        if(isset($args['sub_classes']) && is_array($args['sub_classes'])) {
            $sub_atts['class'] = is_array($args['sub_classes'])? join(' ', $args['sub_classes']) : $args['sub_classes'];
        }
        if(isset($args['sub_item_classes']) && is_array($args['sub_item_classes'])) {
            $sub_item_atts['class'] = is_array($args['sub_item_classes'])? join(' ', $args['sub_item_classes']) : $args['sub_item_classes'];
        }

        if (isset($menu['parent_menus'][$parent])) {
            if(!empty($atts) && is_array($atts)) {
                $html .= "<ul ".self::generateAttributes(($sub? $sub_atts : $atts)). ">";
            }
            else $html .= "<ul>";
            foreach ($menu['parent_menus'][$parent] as $menu_id/*$arg*/) {
                if(is_array($menu_id) && isset($menu_id['id'])) {
                    $menu_id = $menu_id['id'];
                }
                if (!isset($menu['parent_menus'][$menu_id])) {
                    $html .= "<li ".self::generateAttributes(($sub? $sub_item_atts: $item_atts))."><a href='" . $menu['menus'][$menu_id]['link'] . "'>" . $menu['menus'][$menu_id]['title'] . "</a></li>";
                }
                if (isset($menu['parent_menus'][$menu_id])) {
                    $html .= "<li ".self::generateAttributes(($sub? $sub_item_atts: $item_atts))."><a href='" . $menu['menus'][$menu_id]['link'] . "'>" . $menu['menus'][$menu_id]['title'] . "</a>";
                    $html .= self::buildMenu($menu_id, $menu, $args, true);
                    $html .= "</li>";
                }
            }
            $html .= "</ul>";
        }
        return $html;
    }
}