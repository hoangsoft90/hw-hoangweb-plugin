<?php 
/**
 * Plugin Name: Pagination 1
 */

if(!function_exists('hwtpl_pagination_html_def')){
    //add_filter('hwtpl_pagination','hwtpl_pagination_html_def',10,2);
    function hwtpl_pagination_html_def($pagination,$widget){
        //if($widget->id == 'xx'){
            $pagination = str_replace('<span','<a',$pagination);
            $pagination = str_replace('</span','</a',$pagination);
            $pagination = str_replace('<a','<li><span',$pagination);
            $pagination = str_replace('</a','</span><li',$pagination);
        //}
        return $pagination;
    }
}
//pagination container class
$theme['pagination_class'] = 'hwtpl_pagination tsc_pagination tsc_paginationA tsc_paginationA02';
$theme['pagination_container_class'] = 'hw-pagenavi-container';

//declare js & css file here
$theme['styles'][] = 'pagination1.css';
$theme['scripts'][] = '';

?>