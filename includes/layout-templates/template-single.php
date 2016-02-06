<?php
# .current_path/theme.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 16:45
 */
class HW__Template_single extends HW__Template {

    /**
     * main loop content
     */
    public function Main(){

        do_action('hw_before_loop');
        while ( have_posts() ) : the_post();

            /* Include the post format-specific template for the content. If you want to
             * this in a child theme then include a file called called content-___.php
             * (where ___ is the post format) and that will be used instead.
             */
            get_template_part( 'content', 'single' );
        endwhile;
        do_action('hw_after_loop');

    }

    /**
     * trigger when invoke HW_HOANGWEB::register_class method
     */
    public static function __init(){

    }
}