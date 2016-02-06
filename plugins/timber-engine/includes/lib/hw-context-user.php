<?php
/**
 * Class HW_Template_User
 */
class HW_Template_User  extends HW_Twig_Template_Context implements HW_Twig_Template_Context_Interface {
    /**
     * @var
     */
    public static $instance;
    /**
     * check exists current user session
     * @return bool
     */
    function is_logged_in() {
        return is_user_logged_in();
    }
    public function get_object() {
        return wp_get_current_user();
    }

    /**
     * @param $page
     */
    function get_url($page) {
        switch($page) {
            case 'lostpassword': return esc_url( wp_lostpassword_url() );
            case 'logout': return wp_logout_url(get_permalink());
        }
    }

    /**
     * get avatar for user or user post comment
     * @param $size
     * @param $comment detect who post comment
     * @return false|string
     */
    function get_avatar($size, $comment=null) {
        global $current_user;
        get_currentuserinfo();

        if(is_object($comment) && isset($comment->comment_ID) && isset($comment->comment_post_ID)) {
            return get_avatar( $comment, $size );
        }

        return get_avatar( $current_user->ID, $size );
    }
    function url($page) {
        return $this->get_url($page ) ;
    }

    /**
     * @param $size
     * @return false|string
     */
    function avatar($size, $comment=null) {
        return $this->get_avatar($size, $comment);
    }
}
HW_Template_User::add_context('_user');