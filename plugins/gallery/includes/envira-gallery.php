<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Envira_Gallery
 */
abstract class HW_Envira_Gallery extends HW_Core{
    /**
     * HW_Gallery_Metaboxes_Lite class instance
     * @var
     */
    public  $gallery = null;

    /**
     * metaboxes
     * @var null
     */
    public $gallery_metaboxes = null;

    /**
     * HW_SKIN object
     * @var HW_SKIN|null
     */
    public $skin = null;

    /**
     * main constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, '_envira_init_gallery' ), 2 );
    }

    /**
     * init envira gallery
     * @hook init
     */
    public function _envira_init_gallery() {
        $this->load_gallery();
    }
    /**
     * init gallery skin
     */
    public function load_gallery() {
        // Load the metabox class.
        if( empty($this->gallery) && class_exists('HW_Gallery_Lite')) {
            $this->gallery = HW_Gallery_Lite::get_instance();   // Load the base class object.
        }
        if(empty($this->gallery_metaboxes) && class_exists('HW_Gallery_Metaboxes_Lite')) {
            $this->gallery_metaboxes = HW_Gallery_Metaboxes_Lite::get_instance(); // Load the base class object.
        }
    }

    /**
     * Helper method for setting default config values.
     *
     * @since 1.0.0
     *
     * @param string $key The default config key to retrieve.
     * @return string Key value on success, false on failure.
     */
    public function get_config_default( $key ) {

        $instance = HW_Gallery_Common_Lite::get_instance();
        return $instance->get_config_default( $key );

    }
    /**
     * Helper method for retrieving config values.
     *
     * @since 1.0.0
     *
     * @global int $id        The current post ID.
     * @global object $post   The current post object.
     * @param string $key     The config key to retrieve.
     * @param string $default A default value to use.
     * @return string         Key value on success, empty string on failure.
     */
    public function get_config( $key, $default = false , $settings = null) {

        global $id, $post;

        // Get the current post ID.
        $post_id = isset( $post->ID ) ? $post->ID : (int) $id;

        if(empty($settings) || !is_array($settings)) $settings = get_post_meta( $post_id, '_eg_gallery_data', true );
        if ( isset( $settings['config'][$key] ) ) {
            return $settings['config'][$key];
        } else {
            return $default ? $default : '';
        }

    }
    /**
     * Helper method for adding custom gallery classes.
     *
     * @since 1.0.0
     *
     * @param array $data The gallery data to use for retrieval.
     * @return string     String of space separated gallery classes.
     */
    public function get_gallery_classes( $data ) {

        // Set default class.
        $classes   = array();
        $classes[] = 'hw-gallery-wrap';

        // Add custom class based on data provided.
        $classes[] = 'hw-gallery-theme-' . $this->get_config( 'gallery_theme', '',$data );
        $classes[] = 'hw-lightbox-theme-' . $this->get_config( 'lightbox_theme', '',$data );

        // If we have custom classes defined for this gallery, output them now.
        foreach ( (array) $this->get_config( 'classes', '',$data ) as $class ) {
            $classes[] = $class;
        }

        // If the gallery has RTL support, add a class for it.
        if ( $this->get_config( 'rtl', '',$data ) ) {
            $classes[] = 'hw-gallery-rtl';
        }

        // Allow filtering of classes and then return what's left.
        $classes   = apply_filters( 'hw_gallery_output_classes', $classes, $data );
        return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

    }
    /**
     * I'm sure some plugins mean well, but they go a bit too far trying to reduce
     * conflicts without thinking of the consequences.
     *
     * 1. Prevents Foobox from completely borking Envirabox as if Foobox rules the world.
     *
     * @since 1.0.0
     */
    public function plugin_humility() {

        if ( class_exists( 'fooboxV2' ) ) {
            remove_action( 'wp_footer', array( $GLOBALS['foobox'], 'disable_other_lightboxes' ), 200 );
        }

    }
    /**
     * Helper method for adding custom gallery classes.
     *
     * @since 1.0.4
     *
     * @param array $item Array of item data.
     * @param int $i      The current position in the gallery.
     * @param array $data The gallery data to use for retrieval.
     * @return string     String of space separated gallery item classes.
     */
    public function get_gallery_item_classes( $item, $i, $data ) {

        // Set default class.
        $classes   = array();
        $classes[] = 'hw-gallery-item';
        $classes[] = 'isotope-item';
        $classes[] = 'hw-gallery-item-' . $i;

        // Allow filtering of classes and then return what's left.
        $classes = apply_filters( 'hw_gallery_output_item_classes', $classes, $item, $i, $data );
        return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

    }

    /**
     * Helper method to retrieve the proper image src attribute based on gallery settings.
     *
     * @since 1.0.0
     *
     * @param int $id      The image attachment ID to use.
     * @param array $item  Gallery item data.
     * @param array $data  The gallery data to use for retrieval.
     * @param bool $mobile Whether or not to retrieve the mobile image.
     * @return string      The proper image src attribute for the image.
     */
    public function get_image_src( $id, $item, $data, $mobile = false ) {

        // Get the full image src. If it does not return the data we need, return the image link instead.
        $src   = wp_get_attachment_image_src( $id, 'full' );
        $image = ! empty( $src[0] ) ? $src[0] : false;
        if ( ! $image ) {
            $image = ! empty( $item['src'] ) ? $item['src'] : false;
            if ( ! $image ) {
                return apply_filters( 'hw_gallery_no_image_src', $item['link'], $id, $item, $data );
            }
        }

        // Generate the cropped image if necessary.
        $type = $mobile ? 'mobile' : 'crop';
        if ( isset( $data['config'][$type] ) && $data['config'][$type] ) {
            $common = HW_Gallery_Common_Lite::get_instance();
            $args   = apply_filters( 'hw_gallery_crop_image_args',
                array(
                    'position' => 'c',
                    'width'    => $this->get_config( $type . '_width', $data ),
                    'height'   => $this->get_config( $type . '_height', $data ),
                    'quality'  => 100,
                    'retina'   => false
                )
            );
            $cropped_image = $common->resize_image( $image, $args['width'], $args['height'], true, $args['position'], $args['quality'], $args['retina'] );

            // If there is an error, possibly output error message and return the default image src.
            if ( is_wp_error( $cropped_image ) ) {
                // If WP_DEBUG is enabled, and we're logged in, output an error to the user
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_user_logged_in() ) {
                    echo '<pre>Envira: Error occured resizing image (these messages are only displayed to logged in WordPress users):<br />';
                    echo 'Error: ' . $cropped_image->get_error_message() . '<br />';
                    echo 'Image: ' . $image . '<br />';
                    echo 'Args: ' . var_export( $args, true ) . '</pre>';
                }

                // Return the non-cropped image as a fallback.
                return apply_filters( 'hw_gallery_image_src', /*$image*/$src, $id, $item, $data );
            } else {
                return apply_filters( 'hw_gallery_image_src', array($cropped_image, $args['width'], $args['height']), $id, $item, $data );
            }
        } else {
            return apply_filters( 'hw_gallery_image_src', /*$image*/$src, $id, $item, $data );
        }

    }
    /**
     * Outputs only the first image of the gallery inside a regular <div> tag
     * to avoid styling issues with feeds.
     *
     * @since 1.0.5
     *
     * @param array $data      Array of gallery data.
     * @return string $gallery Custom gallery output for feeds.
     */
    public function do_feed_output( $data ) {

        $gallery = '<div class="hw-gallery-feed-output">';
        foreach ( $data['gallery'] as $id => $item ) {
            // Skip over images that are pending (ignore if in Preview mode).
            if ( isset( $item['status'] ) && 'pending' == $item['status'] && ! is_preview() ) {
                continue;
            }

            $imagesrc = $this->get_image_src( $id, $item, $data );
            $gallery .= '<img class="hw-gallery-feed-image" src="' . esc_url( $imagesrc ) . '" title="' . trim( esc_html( $item['title'] ) ) . '" alt="' .trim( esc_html( $item['alt'] ) ) . '" />';
            break;
        }
        $gallery .= '</div>';

        return apply_filters( 'hw_gallery_feed_output', $gallery, $data );

    }
}