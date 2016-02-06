<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/12/2015
 * Time: 15:22
 */
/**
 * @param $data
 * @return mixed
 */
function _treat($data) {
    if(is_object($data)) return new HW_Twig_Template_Context($data);
    else return $data;
}

/**
 * @param $args
 */
function utility_data($args) {
    return is_array($args)? end($args) : $args;
}

/**
 * add hook as partial of template
 * @param $name
 * @param $function
 */
function add_partial($name, $function) {
    if(class_exists('HW_Twig_Hook_Template')) {
        $hwoo = HW_Twig_Hook_Template::get_instance();
        $hwoo->add_template($name, $function);
    }

}

/**
 * load partial template
 * @param $name
 * @alias partial
 */
function load_partial($name, $args_arr = array()) {
    if(strpos($name, ':')!==false) {
        $utility = HW_Twig_Template_Utilities::get_instance();
        return $utility->render_template($name);
    }
    return HW_Twig_Hook_Template::load_partial($name, $args_arr);

}

/**
 * @param $file
 * @param $function
 * @param $alias
 */
function add_template_utilities($file, $function ,$alias = '', $data= array()) {
    $temp = HW_Twig_Template_Utilities::get_instance();
    $temp->add_utility($file, $function, $alias, $data);
}

/**
 * @param $current
 * @param $tpl
 * @param $data
 */
function template_file($current, $tpl, $data) {
    $temp = HW_Twig_Template_Utilities::get_instance();
    $temp->add_template_twig($current, $tpl, $data);
}
/**
 * @param $name
 * @return mixed
 */
function template_tool($name) {
    $temp = HW_Twig_Template_Utilities::get_instance();
    return $temp->utilities($name);
}

/**
 * @param WP_Post $_post
 */
function _setup_postdata($_post) {
    global $post;
    if($_post instanceof WP_Post) {
        $post = $_post;
        setup_postdata ($post);
    }
}
