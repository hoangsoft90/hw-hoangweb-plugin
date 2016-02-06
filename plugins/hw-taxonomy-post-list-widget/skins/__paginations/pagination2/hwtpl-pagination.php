<?php 
/**
 * Plugin Name: pagination 2
 */
if(!function_exists('hwtpl_pagination_html2')){
    //add_filter('hwtpl_pagination','hwtpl_pagination_html2',10,2);
    function hwtpl_pagination_html2($pagination,$widget){
        //if($widget->id == 'xx'){
            $pagination = str_replace('<span','<a',$pagination);
            $pagination = str_replace('</span','</a',$pagination);
            $pagination = str_replace('<a','<li><a',$pagination);
            $pagination = str_replace('</a','</a><li',$pagination);_print($pagination);
        //}
        return $pagination;
    }
}

//pagination container class
$theme['pagination_class'] = 'tsc_pagination tsc_paginationA tsc_paginationA03';

//declare js & css file here
$theme['styles'][] = 'pagination2.css';
$theme['scripts'][] = '';
?>

