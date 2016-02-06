<?php
# /root>includes/website/hw-settings-implementation.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/06/2015
 * Time: 10:21
 */
class NHP_Options_article_Frontend extends NHP_Options_article {
    function __construct() {
        add_filter('excerpt_more',  array($this, '_hw_customize_excerpt_more') ); //change excerpt more text
        // Add Read More button to blog page and archives
        add_filter( 'excerpt_more', array($this, '_hw_the_content_more_link') );
        add_filter( 'get_the_content_more_link',  array($this, '_hw_the_content_more_link') );
        add_filter( 'the_content_more_link',  array($this, '_hw_the_content_more_link')  );

        //remove p tag around excerpt content
        add_filter('the_excerpt',  array($this, '_hw_remove_p_in_except'));

        add_filter( 'excerpt_length', array($this, '_hw_custom_excerpt_length'), 999 );
        //enable wpautop for post content
        #if(hw_option('wpautop_content'))
            add_filter('the_content', 'wpautop');
        #else remove_filter('the_content', 'wpautop');

        if(hw_option('wpautop_excerpt')) add_filter('the_excerpt', 'wpautop');
        else remove_filter('the_excerpt', 'wpautop');
    }
    /**
     * excerpt more text
     */
    public function _hw_customize_excerpt_more($more){
        $more_str = hw_option('excerpt_more','...');
        return $more_str? $more_str : $more;
    }
    /**
     * post excerpt more link
     */
    public function _hw_the_content_more_link($more_text){
        $more_link = hw_option('morelink_text','&nbsp;>> Đọc tiếp');
        if($more_link) {
            return '<span class="more-link"><a href="' . get_permalink() . '" rel="nofollow">'.$more_link.'</a></span>';
        }
        return $more_text;
    }
    /**
     * remove p tag surround excerpt
     */
    public function _hw_remove_p_in_except($excerpt){
        $tags = array("<p>", "</p>");
        return str_replace($tags, "", $excerpt);
    }
    /**
     * excerpt length customizing
     */
    public function _hw_custom_excerpt_length($length){
        $n = trim(hw_option('excerpt_leng',$length));
        if(empty($n) && ($n != '0' || $n != null || $n != false) ) $n = $length;    //default
        return $n;
    }

}
HW_Options_Frontend::add_fragment(new NHP_Options_article_Frontend());