<?php
#/.current_path/theme.php
/**
 * Class HW__Template_archive
 */
class HW__Template_archive extends HW__Template {

    /**
     * main loop content
     */
    public function Main(){

        do_action('hw_before_loop');
        $this->content();
        do_action('hw_after_loop');

    }

    /**
     * show content 404
     */
    private  function content() {
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