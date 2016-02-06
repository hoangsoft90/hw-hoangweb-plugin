<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * require HW_HOANGWEB plugin
 */
register_activation_hook( Post_Views_Counter_PATH, 'hwpcv_require_plugins_activate' );
function hwpcv_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            #'hw-skin/hw-skin.php' => 'hw-skin',

        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}

/**
 * display post view count for given post
 * @param array $args  an array of posts info contain: post_id, permalink, title
 * @param $display  display or return
 */
function hwpvc_post_views($args = array(), $display = true) {
    if(isset($args['post_id'])) $post_id = $args['post_id'];
    else $post_id = get_the_ID();

    //get permalink
    if(isset($args['permalink'])) $permalink = $args['permalink'];
    else $permalink = '';   //get default from function get_permalink();, but stored in __hw_post_views_count variable

    //get title
    if(isset($args['title'])) $title = $args['title'];
    else $title = '';   //get default from function get_the_title();, but stored in __hw_post_views_count variable

    //allow to update views
    if(isset($args['count']) && $args['count']) $count = 'true';
    else $count = 'false';

    // get all data
    $options = Post_Views_Counter()->get_attribute( 'options', 'display' );
    $hw_options = HW_Post_Views_Counter()->get_attribute('options', 'hoangweb');   //hoangweb options
//    $views = pvc_get_post_views( $post_id );

    //prepare display
    $label = apply_filters( 'pvc_post_views_label', (function_exists( 'icl_t' ) ? icl_t( 'Post Views Counter', 'Post Views Label', $options['label'] ) : $options['label'] ), $post_id );
    $icon_class = ($options['icon_class'] !== '' ? ' ' . esc_attr( $options['icon_class'] ) : '');
    $icon = apply_filters( 'pvc_post_views_icon', '<span class="hw-hidden post-views-icon dashicons ' . $icon_class . '"></span>', $post_id );
    $use_firebase = isset($hw_options['use_firebase'])? $hw_options['use_firebase'] : '';

    $view = '<div class="hw-post-views-icon post-views post-' . $post_id . ' entry-meta">'
        . ($options['display_style']['icon'] && $icon_class !== '' ? $icon : '')
        . ($options['display_style']['text'] ? '<span class="post-views-label">' . $label . ' </span>' : '')
        . '<span title="' .$title. '" class="post-views-count hw-post-views-count preloading" data-count="' .$count. '" data-permalink="' .$permalink. '" data-id="' .$post_id. '" data-title="' .$title. '">
        <!-- js process -->
    </span></div>';
    $view = apply_filters('pvc_post_views_html',$view, $args, $use_firebase, $icon);

    if ( $display )
        echo $view;
    else
        return $view;
}