<?php
/**
 * render facebook comment box
 * @param $atts attributes
 */
function _hw_fb_comment_display($atts) {
    HW_HOANGWEB::load_class('HW_UI_Component');

    $inst = HW_Module_Comments::get();
    $options =  $inst->get_tab('facebook')->get_values();
    $atts = array();

    $appId = isset($options['appId'])? $options['appId'] : '';
    $width = !empty($options['width'])? HW_Validation::format_unit($options['width']) : '100%';
    $num_posts = !empty($options['num_posts'])? $options['num_posts'] : '5';
    $colorscheme = !empty($options['colorscheme'])? $options['colorscheme'] : 'light';
    $order_by = !empty($options['order_by'])? $options['order_by'] : 'social';  //order_by

    $show_count = !empty($options['show_count'])? $options['show_count'] : 0;
    $comment_text = !empty($options['comment_text'])? $options['comment_text'] : __('Bình luận');

    echo '<div id="fb-root"></div>';
    #echo '<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>';
    echo "
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = '//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId={$appId}';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>";

    //show count
    if($show_count) {
        echo '<span class="fb-comments-count" data-href="'.get_permalink().'"></span> '. $comment_text;
    }
    if(isset($options['html5'])) {
        $atts['data-href'] = get_permalink();
        $atts['data-width'] = $width;
        $atts['data-numposts'] = $num_posts;
        $atts['data-order-by'] = $order_by;
        $atts['data-colorscheme'] = $colorscheme;

        echo '<div class="fb-comments" '.HW_UI_Component::generateAttributes($atts).'></div>';
    }
    else {
        $atts['href'] = get_permalink();
        $atts['width'] = $width;
        $atts['num_posts'] = $num_posts;
        $atts['order_by'] = $order_by;
        $atts['width'] = $width;

        echo '<fb:comments '.HW_UI_Component::generateAttributes($atts).'></fb:comments>';
    }
}
add_shortcode('hwfb_comment', '_hw_fb_comment_display');
/**
 * shortcode for google plus
 * @param $atts
 */
function _hw_hwgplus_comment($atts) {

}
add_shortcode('hwgplus_comment', '_hw_hwgplus_comment');