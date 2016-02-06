<?php
#/.current_path/theme.php
/**
 * Class HW__Template_author
 */
class HW__Template_author extends HW__Template {


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
        ?>

        <?php
    }
    /**
     * trigger when invoke HW_HOANGWEB::register_class method
     */
    public static function __init(){

    }
}