<?php
#/root

/**
 * load template file using shortcode
 * @param $atts
 * @param $content
 */
if(!function_exists('_hwload_template_file')):
function _hwload_template_file($atts,$content) {
    $pairs=array('name' => '');

    extract(shortcode_atts($pairs,$atts,'hw_template'));
    ob_start();
    if(isset($name) && file_exists(locate_template('templates/'.$name.'.php'))) {
        include locate_template('templates/'.$name.'.php');
    }
    $content=ob_get_contents();
    ob_clean();
    return $content;
}
add_shortcode('hw_template', '_hwload_template_file');
endif;