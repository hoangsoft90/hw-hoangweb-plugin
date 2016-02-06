<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Gallery_Shortcodes
 */
class HW_Gallery_Shortcodes extends HW_Envira_Gallery{
    /**
     * singleton
     * @var null
     */
    public static $instance = null;
    /**
     * main class constructor
     */
    public function __construct() {
        parent::__construct();

        add_shortcode('hw_gallery', array(&$this, '_gallery_shortcode'));
    }
    /**
     * @param $atts
     */
    public function _gallery_shortcode($atts) {
        global $post;

        // If no attributes have been passed, the gallery should be pulled from the current post.
        $gallery_id = false;
        if ( empty( $atts ) ) {
            $gallery_id = $post->ID;
            $data       = is_preview() ? $this->gallery->_get_gallery( $gallery_id ) : $this->gallery->get_gallery( $gallery_id );
        } else if ( isset( $atts['id'] ) ) {
            $gallery_id = (int) $atts['id'];
            $data       = is_preview() ? $this->gallery->_get_gallery( $gallery_id ) : $this->gallery->get_gallery( $gallery_id );
        } else if ( isset( $atts['slug'] ) ) {
            $gallery_id = $atts['slug'];
            $data       = is_preview() ? $this->gallery->_get_gallery_by_slug( $gallery_id ) : $this->gallery->get_gallery_by_slug( $gallery_id );
        } else {
            // A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
            $data = apply_filters( 'hw_envira_gallery_custom_gallery_data', false, $atts, $post );
        }

        // If there is no data to output or the gallery is inactive, do nothing.
        if ( ! $data || empty( $data['gallery'] ) || isset( $data['status'] ) && 'inactive' == $data['status'] && ! is_preview() ) {
            return;
        }
        // Get rid of any external plugins trying to jack up our stuff where a gallery is present.
        $this->plugin_humility();
        // If this is a feed view, customize the output and return early.
        if ( is_feed() ) {
            return $this->do_feed_output( $data );
        }
        $i            = 1;
        $args = array();
        $args['wrapper_id'] = sanitize_html_class( $data['id'] );
        $args['wrapper_class'] = $this->get_gallery_classes( $data );
        $args['columns_class'] =  $this->get_config( 'columns', $data ) ;
        $galleries = array();

        foreach ( $data['gallery'] as $id => $item ) {
            // Skip over images that are pending (ignore if in Preview mode).
            if ( isset( $item['status'] ) && 'pending' == $item['status'] && ! is_preview() ) {
                continue;
            }
            $gallery = array();
            $image = $this->get_image_src( $id, $item, $data );

            $gallery = array_merge($item);
            $gallery['index'] = $i;
            $gallery['id'] = $id;
            $gallery['item_classes'] = $this->get_gallery_item_classes( $item, $i, $data );
            $gallery['margin_bottom'] = HW_Validation::format_unit($this->get_config( 'margin', $data ) );

            $gallery['img_src'] = esc_url($image[0]);
            $gallery['img_width'] = esc_url($image[1]);
            $gallery['img_height'] = esc_url($image[2]);

            $thumb   = wp_get_attachment_image_src( $id, 'thumbnail' );
            $gallery['thumb_src'] = $thumb[0];
            $gallery['thumb_width'] = $thumb[1];
            $gallery['thumb_height'] = $thumb[2];

            $gallery['placeholder'] = esc_url( plugins_url( 'assets/css/images/holder.gif', dirname( dirname( __FILE__ ) ) ) );

            $galleries[$id] = $gallery;

            // Increment the iterator.
            $i++;
        }
        /*---start skin---*/
        //$skin = $this->get_config( 'hw_skin', $data );
        $skin = !empty($data['config']['hw_skin'])? $data['config']['hw_skin'] : $this->get_config( 'hw_skin', array() );
        if(empty($skin['hash_skin'])) return ;

        //get skin options
        $data['options'] = isset($skin['skin_options'])? $skin['skin_options'] : array();
        $data['json_options'] = HW_SKIN_Option::build_json_options($data['options']);
        //$this->skin->get_current();
        //change sidebar params from skin
        return HW_SKIN::apply_skin_data($skin,  array('callback_before' => array(__CLASS__, '_hw_skin_before_include_skin_file') ), array(
            'galleries' => $galleries,
            'args' => $args,
            'data' => $data
        ), false);
    }
}
//for frontend
if(!is_admin() || class_exists('HW_CLI_Command', false)) {
    HW_Gallery_Shortcodes::get_instance();
}
