<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

new HW_Post_Views_Counter_Frontend();
/**
 * Class HW_Post_Views_Counter_Frontend
 */
class HW_Post_Views_Counter_Frontend {
    /**
     * object to serialize to js object
     * @var array
     */
    public $serialize_obj = array();

    public function __construct() {
        // actions
        add_action( 'wp_loaded', array( &$this, '_register_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, '_frontend_scripts_styles' ) );

        // filters
        add_filter( 'the_content', array( &$this, '_add_post_views_count' ) );
        add_filter( 'the_excerpt', array( &$this, '_remove_post_views_count' ) );
    }

    /**
     * register post views shortcode function
     */
    public function _register_shortcode (){
        add_shortcode( 'hw-post-views', array( &$this, '_post_views_shortcode' ) );
    }
    /**
     * Add post views counter to content.
     * extend from hw-post-views-counter/includes/frontend.php method/add_post_views_count
     */
    public function _add_post_views_count( $content ) {
        if (class_exists('Post_Views_Counter') && is_singular()
            && in_array( get_post_type(), Post_Views_Counter()->get_attribute( 'options', 'display', 'post_types_display' ), true ) ) {
            #extend from hw-post-views-counter/includes/frontend.php method/add_post_views_count
            // get groups to check it faster
            $groups = Post_Views_Counter()->get_attribute( 'options', 'display', 'restrict_display', 'groups' );

            // whether to display views
            if ( is_user_logged_in() ) {
                // exclude logged in users?
                if ( in_array( 'users', $groups, true ) )
                    return $content;
                // exclude specific roles?
                elseif ( in_array( 'roles', $groups, true ) && Post_Views_Counter()->get_instance( 'counter' )->is_user_roles_excluded( Post_Views_Counter()->get_attribute( 'options', 'display', 'restrict_display', 'roles' ) ) )
                    return $content;
            }
            // exclude guests?
            elseif ( in_array( 'guests', $groups, true ) )
                return $content;


            switch ( Post_Views_Counter()->get_attribute( 'options', 'display', 'position' ) ) {
                case 'hw_after_content':
                    return $content . '[hw-post-views]';

                case 'hw_before_content':
                    return '[hw-post-views]' . $content;

                default:
                case 'manual':
                    return $content;
            }
        }
        return $content;
    }
    /**
     * Remove post views shortcode from excerpt.
     */
    public function _remove_post_views_count($excerpt) {
        remove_shortcode( 'hw-post-views' );
        $excerpt = preg_replace( '/\[hw-post-views[^\]]*\]/', '', $excerpt );
        return $excerpt;
    }
    /**
     * Post views shortcode function.
     * @param $args
     */
    public function _post_views_shortcode( $args ) {
        $defaults = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'permalink' => get_permalink(),
            'count' => 'true'
        );

        $args = shortcode_atts( $defaults, $args );

        return hwpvc_post_views( $args, false );
    }
    /**
     * frontend scripts styles
     */
    public function _frontend_scripts_styles() {
        //$post_types = Post_Views_Counter()->get_attribute( 'options', 'display', 'post_types_display' );
        $post_types = Post_Views_Counter()->get_attribute( 'options', 'general', 'post_types_count' );
        $hwpvc_options = HW_Post_Views_Counter()->get_attribute( 'options', 'hoangweb' );

        $this->serialize_obj['options'] = $hwpvc_options;

        //get current post
        $post_id = get_the_ID();    //single post or inside loop posts
        if($post_id) {
            $this->serialize_obj['postID'] = $post_id;
            $this->serialize_obj['postType'] = get_post_type();
            $this->serialize_obj['post_title'] = get_the_title($post_id);
            $this->serialize_obj['post_permalink'] = get_permalink($post_id);
        }

        // whether to count this post type or not
        if ( is_single() && (empty( $post_types ) || ! is_singular( $post_types )) )    //allow all
            return;

        //firebase lib
        if(isset($hwpvc_options['use_firebase']) && $hwpvc_options['use_firebase']) {
            wp_register_script('firebase.js', 'https://cdn.firebase.com/js/client/2.2.6/firebase.js');
            wp_enqueue_script('firebase.js');
        }
        //css
        wp_register_style(
            'hw-post-views-counter-frontend', HWPVC_PLUGIN_URL . '/css/hw-frontend.css'
        );
        wp_enqueue_style('hw-post-views-counter-frontend');

        //js
        wp_register_script(
            'hw-post-views-counter-frontend', HWPVC_PLUGIN_URL . '/js/hw-frontend.js', array( 'jquery' )
        );

        wp_enqueue_script( 'hw-post-views-counter-frontend' );
        wp_localize_script('hw-post-views-counter-frontend', '__hw_post_views_count', $this->serialize_obj);

    }
}