<?php 
/**
 *HW Template: skin 1
 */
/**
 * modify form class attribute
 */
add_filter('hw_wpcf7_form_class_attr','_hw_wpcf7_form_class_attr_skin1',$priority);
if(!function_exists('_hw_wpcf7_form_class_attr_skin1')){
    function _hw_wpcf7_form_class_attr_skin1(){
        return ' hw-wpcf-skin1'; //note leave a space in the left of string to separate new class
    }
}

add_action('hw_wpcf7_contact_form_css','hw_wpcf7_contact_form_css_skin1',$priority);
if(!function_exists('hw_wpcf7_contact_form_css_skin1')):
function hw_wpcf7_contact_form_css_skin1($form){
    wp_enqueue_style('hw-wpcf7-skin1', HW_SKIN::current()->get_skin_url('style.css'));
    ?>

<?php 
}
    endif;
?>