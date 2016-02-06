<?php
/**
HW Template: classic
 */

$theme['styles'][] = 'style.css';
#$theme['display'] = 'list';
//override bcn_options
#$theme['bcn_options']['hseparator'] = '';

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
    )
);
if(!function_exists('hw_bcn_output_breadcrumb_def')){
    function hw_bcn_output_breadcrumb_def($output, $inst, $theme){
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $output;
        $theme = $theme['do_filters_args_'.__FUNCTION__];

        $out = '<ul class="breadcrumb">';
        $out .= $output;
        $out .= '</ul>';

        return $out;
    }
}
if(!function_exists('hw_bcn_allowed_html_def')){
    function hw_bcn_allowed_html_def($tags, $theme){
        //get theme args
        if(!isset($theme['do_filters_args_'.__FUNCTION__])) return $tags;
        $theme = $theme['do_filters_args_'.__FUNCTION__];
//_print($tags);
        //unset($tags['span']);
        return $tags;
    }
}