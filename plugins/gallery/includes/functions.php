<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * get all galleries data
 * @return mixed
 */
function hw_gallery_get_galleries() {
    // Load the metabox class.
    if(class_exists('HW_Gallery')) {
        $gallery = HW_Gallery::get_instance();

    }
    elseif(class_exists('HW__Gallery_Lite')) {
        $gallery = HW__Gallery_Lite::get_instance();
    }

    return !empty($gallery)? $gallery->gallery->get_galleries() : null;
}
#add_action('init','_init');
function _init(){
    hw_gallery_get_galleries();
}