<?php
# .current_path/theme.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 16:45
 */
class HW__Template_taxonomy extends HW__Template {
    public function wp_head() {

    }
    public function enqueue_scripts() {

    }
    public function wp_footer() {

    }
    /**
     * parent class instance
     * @var
     */
    //public static $instance;        //access from extend class

    /**
     * main loop content
     */
    public function Main(){

        if ( have_posts() ) :
            do_action('hw_before_loop');
            while ( have_posts() ) : the_post();

                /* Include the post format-specific template for the content. If you want to
                 * this in a child theme then include a file called called content-___.php
                 * (where ___ is the post format) and that will be used instead.
                 */
                get_template_part( 'content', get_post_format() );
            endwhile;
            do_action('hw_after_loop');
        else:
            get_template_part( 'content', 'none' );
        endif;

    }

    /**
     * trigger when invoke HW_HOANGWEB::register_class method
     */
    public static function __init(){

    }
}