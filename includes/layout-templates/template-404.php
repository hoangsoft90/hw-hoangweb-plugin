<?php
#/.current_path/theme.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 16:45
 */
class HW__Template_404 extends HW__Template {
    public function enqueue_scripts() {

    }
    public function wp_head(){}
    public function wp_footer(){}

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
        <h2><?php _e( 'Không tìm thấy', 'hoangweb' ); ?></h2>
        <div class="defaultContent BlockContent">
            <p><?php _e( 'Rất tiếc ! Dữ liệu bạn truy vấn không có kết quả.', 'hoangweb' ); ?></p>
            <?php get_search_form(); ?>
        </div>
        <?php
    }
    /**
     * trigger when invoke HW_HOANGWEB::register_class method
     */
    public static function __init(){

    }
}