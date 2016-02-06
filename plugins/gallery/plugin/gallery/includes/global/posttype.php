<?php
/**
 * Posttype class.
 *
 * @since 1.0.0
 *
 * @package HW_Gallery_Lite
 * @author  Thomas Griffin
 */
class HW_Gallery_Posttype_Lite {

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

        // Build the labels for the post type.
        $labels = apply_filters( 'hw_gallery_post_type_labels',
            array(
                'name'               => __( 'Envira Gallery', 'hw-gallery' ),
                'singular_name'      => __( 'Envira Gallery', 'hw-gallery' ),
                'add_new'            => __( 'Add New', 'hw-gallery' ),
                'add_new_item'       => __( 'Add New Envira Gallery', 'hw-gallery' ),
                'edit_item'          => __( 'Edit Envira Gallery', 'hw-gallery' ),
                'new_item'           => __( 'New Envira Gallery', 'hw-gallery' ),
                'view_item'          => __( 'View Envira Gallery', 'hw-gallery' ),
                'search_items'       => __( 'Search Envira Galleries', 'hw-gallery' ),
                'not_found'          => __( 'No Envira galleries found.', 'hw-gallery' ),
                'not_found_in_trash' => __( 'No Envira galleries found in trash.', 'hw-gallery' ),
                'parent_item_colon'  => '',
                'menu_name'          => __( 'Envira Gallery', 'hw-gallery' )
            )
        );

        // Build out the post type arguments.
        $args = apply_filters( 'hw_gallery_post_type_args',
            array(
                'labels'              => $labels,
                'public'              => false,
                'exclude_from_search' => false,
                'show_ui'             => true,#true,
                'show_in_admin_bar'   => true,
                'rewrite'             => false,
                'query_var'           => false,
                'menu_position'       => apply_filters( 'hw_gallery_post_type_menu_position', 247 ),
                'menu_icon'           => plugins_url( 'assets/css/images/menu-icon@2x.png', $this->base->file ),
                'supports'            => array( 'title' )
            )
        );

        // Register the post type with WordPress.
        register_post_type( 'hw-gallery', $args );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The HW_Gallery_Posttype_Lite object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof HW_Gallery_Posttype_Lite ) ) {
            self::$instance = new HW_Gallery_Posttype_Lite();
        }

        return self::$instance;

    }

}

// Load the posttype class.
$hw_gallery_posttype_lite = HW_Gallery_Posttype_Lite::get_instance();