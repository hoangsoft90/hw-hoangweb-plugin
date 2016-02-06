<?php
//add_action('');
function hwrp_init(){
    global $hwrp;
    $hwrp = new HW_RelatedPosts();

}

/**
 * display related posts
 */
function hwrp_display_related(){
    echo HW_RelatedPosts_Frontend::getInstance()->hwrp_display_related();
    //echo hw_yarpp_related( /*array('post_type' => array('mynews'),'require_tax' => array('news_tax' => 1))*/null,get_the_ID());
}
/**
 * get all post terms (moved to HW_POST class /hw-hoangweb/classes/hw_posts.class.php)
 * @param $post: post id or single post data
 * @param $args: inherit argument param from get_object_taxonomies method
 */
function hwrp_get_all_post_terms($post = '',$args = array()){
    if(!class_exists('HW_POST')) HW_HOANGWEB::load_class('HW_POST');
    return HW_POST::get_all_post_terms($post, $args);
}

/**
 * return all terms taxonomies base post type
 * @param $post_type: post type name
 * @param $args: addition arguments
 */
function hwrp_get_all_terms_taxonomies($post_type, $args  = array()){
    if(!class_exists('HW_POST')) HW_HOANGWEB::load_class('HW_POST');
    return HW_POST::get_all_terms_taxonomies($post_type, $args);
}
