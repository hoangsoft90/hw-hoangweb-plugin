<?php
/**
HW Template: bcn style 1
 */
$theme['styles'][] = 'style.css';
$theme['display'] = 'normal';

//$theme[''];

//register filters for this skin
$theme['filters'] = array(
    'hw_breadcrumb_output' => array(
        'type' => 'filter',
        'function' => 'hw_bcn_output_breadcrumb_def',
        'accepted_args' => 3,
        'priority' => 10
    ),
    'bcn_allowed_html' => array(
        'type' => 'filter',
        'function' => 'hw_bcn_allowed_html_def',
        'accepted_args' => 2,
        'priority' => 10
    ),
    'bcn_breadcrumb_template_no_anchor' => array(
        'type' => 'filter',
        'function' => 'hw_bcn_breadcrumb_template_no_anchor_def',
        'accepted_args' => 4,
        'priority' => 10
    )
);
if(!function_exists('hw_bcn_breadcrumb_template_no_anchor_def')){
    function hw_bcn_breadcrumb_template_no_anchor_def($template, $type, $id, $theme){
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $template;
        $theme = $theme['do_filters_args_'.__FUNCTION__];

        $template = '<a property="v:title" class="current-item">'.$template.'</a>';
        return $template;
    }
}
if(!function_exists('hw_bcn_output_breadcrumb_def')){
    function hw_bcn_output_breadcrumb_def($output, $inst, $theme){
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $output;
        $theme = $theme['do_filters_args_'.__FUNCTION__];

        $out = '<div class="breadcrumb">';
        $out .= $output;
        $out .= '</div>';

        return $out;
    }
}
if(!function_exists('hw_bcn_allowed_html_def')){
    function hw_bcn_allowed_html_def($tags, $theme){
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $tags;
        $theme = $theme['do_filters_args_'.__FUNCTION__];
//_print($tags);
        unset($tags['span']);
        return $tags;
    }
}