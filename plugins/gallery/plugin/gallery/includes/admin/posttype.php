<?php
/**
 * Posttype admin class.
 *
 * @since 1.0.0
 *
 * @package HW_Gallery_Lite
 * @author  Thomas Griffin
 */
class HW_Gallery_Posttype_Admin_Lite {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = HW_Gallery_Lite::get_instance();

        // Remove quick editing from the Envira post type row actions.
        add_filter( 'post_row_actions', array( $this, 'row_actions' ) );

        // Manage post type columns.
        add_filter( 'manage_edit-hw-gallery_columns', array( $this, '_gallery_columns' ) );
        add_filter( 'manage_hw-gallery_posts_custom_column', array( $this, '_gallery_custom_columns' ), 10, 2 );

        // Update post type messages.
        add_filter( 'post_updated_messages', array( $this, 'messages' ) );

        // Force the menu icon to be scaled to proper size (for Retina displays).
        add_action( 'admin_head', array( $this, 'menu_icon' ) );

    }

    /**
     * Customize the post columns for the Envira post type.
     *
     * @since 1.0.0
     *
     * @param array $columns  The default columns.
     * @return array $columns Amended columns.
     */
    public function _gallery_columns( $columns ) {

        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title'     => __( 'Title', 'hw-gallery' ),
            'shortcode' => __( 'Shortcode', 'hw-gallery' ),
            'template'  => __( 'Function', 'hw-gallery' ),
            'images'    => __( 'Number of Images', 'hw-gallery' ),
            'modified'  => __( 'Last Modified', 'hw-gallery' ),
            'date'      => __( 'Date', 'hw-gallery' )
        );

        return $columns;

    }

    /**
     * Add data to the custom columns added to the Envira post type.
     *
     * @since 1.0.0
     *
     * @global object $post  The current post object
     * @param string $column The name of the custom column
     * @param int $post_id   The current post ID
     */
    public function _gallery_custom_columns( $column, $post_id ) {

        global $post;
        $post_id = absint( $post_id );

        switch ( $column ) {
            case 'shortcode' :
                echo '<code>[hw_gallery id="' . $post_id . '"]</code>';
                break;

            case 'template' :
                echo '<code>if ( function_exists( \'hw_gallery\' ) ) { hw_gallery( \'' . $post_id . '\' ); }</code>';
                break;

            case 'images' :
                $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
                echo ( ! empty( $gallery_data['gallery'] ) ? count( $gallery_data['gallery'] ) : 0 );
                break;

            case 'modified' :
                the_modified_date();
                break;
        }

    }

    /**
     * Filter out unnecessary row actions from the Envira post table.
     *
     * @since 1.0.0
     *
     * @param array $actions  Default row actions.
     * @return array $actions Amended row actions.
     */
    public function row_actions( $actions ) {

        if ( isset( get_current_screen()->post_type ) && 'hw-gallery' == get_current_screen()->post_type ) {
            unset( $actions['inline hide-if-no-js'] );
        }

        return $actions;

    }

    /**
     * Contextualizes the post updated messages.
     *
     * @since 1.0.0
     *
     * @global object $post    The current post object.
     * @param array $messages  Array of default post updated messages.
     * @return array $messages Amended array of post updated messages.
     */
    public function messages( $messages ) {

        global $post;

        // Contextualize the messages.
        $messages['hw-gallery'] = apply_filters( 'hw_gallery_messages',
            array(
                0  => '',
                1  => __( 'gallery updated.', 'hw-gallery' ),
                2  => __( 'gallery custom field updated.', 'hw-gallery' ),
                3  => __( 'gallery custom field deleted.', 'hw-gallery' ),
                4  => __( 'gallery updated.', 'hw-gallery' ),
                5  => isset( $_GET['revision'] ) ? sprintf( __( ' gallery restored to revision from %s.', 'hw-gallery' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6  => __( 'gallery published.', 'hw-gallery' ),
                7  => __( ' gallery saved.', 'hw-gallery' ),
                8  => __( ' gallery submitted.', 'hw-gallery' ),
                9  => sprintf( __( ' gallery scheduled for: <strong>%1$s</strong>.', 'hw-gallery' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
                10 => __( ' gallery draft updated.', 'hw-gallery' )
            )
        );

        return $messages;

    }

    /**
     * Forces the menu icon width/height for Retina devices.
     *
     * @since 1.0.0
     */
    public function menu_icon() {

        ?>
        <style type="text/css">#menu-posts-hw-gallery .wp-menu-image img { width: 16px; height: 16px; }</style>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The HW_Gallery_Posttype_Admin_Lite object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof HW_Gallery_Posttype_Admin_Lite ) ) {
            self::$instance = new HW_Gallery_Posttype_Admin_Lite();
        }

        return self::$instance;

    }

}

// Load the posttype admin class.
$hw_gallery_posttype_admin_lite = HW_Gallery_Posttype_Admin_Lite::get_instance();