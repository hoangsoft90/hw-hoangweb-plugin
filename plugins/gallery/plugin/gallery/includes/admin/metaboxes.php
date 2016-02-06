<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package HW_Gallery_Lite
 * @author  Thomas Griffin
 */
class HW_Gallery_Metaboxes_Lite {

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

        // Load metabox assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_scripts' ) );

        // Load the metabox hooks and filters.
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 100 );

        // Load all tabs.
        add_action( 'hw_gallery_tab_images', array( $this, 'images_tab' ) );
        add_action( 'hw_gallery_tab_config', array( $this, 'config_tab' ) );
        add_action( 'hw_gallery_tab_lightbox', array( $this, 'lightbox_tab' ) );
        add_action( 'hw_gallery_tab_misc', array( $this, 'misc_tab' ) );

        // Add action to save metabox config options.
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

    }

    /**
     * Loads styles for our metaboxes.
     *
     * @since 1.0.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function meta_box_styles() {

        // Load necessary metabox styles.
        wp_register_style( $this->base->plugin_slug . '-metabox-style', plugins_url( 'assets/css/metabox.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-metabox-style' );

        // If WordPress version < 4.0, add attachment-details-modal-support.css
        // This contains the 4.0 CSS to make the attachment window display correctly
        $version = (float) get_bloginfo( 'version' );
        if ( $version < 4 ) {
            wp_register_style( $this->base->plugin_slug . '-attachment-details-modal-support', plugins_url( 'assets/css/attachment-details-modal-support.css', $this->base->file ), array(), $this->base->version );
            wp_enqueue_style( $this->base->plugin_slug . '-attachment-details-modal-support' );
        }

    }

    /**
     * Loads scripts for our metaboxes.
     *
     * @since 1.0.0
     *
     * @global int $id      The current post ID.
     * @global object $post The current post object..
     * @return null         Return early if not on the proper screen.
     */
    public function meta_box_scripts( $hook ) {

        global $id, $post;

        if ( isset( get_current_screen()->base ) && 'post' !== get_current_screen()->base ) {
            return;
        }

        if ( isset( get_current_screen()->post_type ) && in_array( get_current_screen()->post_type, $this->get_skipped_posttypes() ) ) {
            return;
        }

        // Set the post_id for localization.
        $post_id = isset( $post->ID ) ? $post->ID : (int) $id;

        // Load WordPress necessary scripts.
        wp_enqueue_script( 'plupload-handlers' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media( array( 'post' => $post_id ) );

        // Load necessary metabox scripts (assets/js/min/metabox-min.js)
        //assets/js/metabox.js
        wp_register_script( $this->base->plugin_slug . '-metabox-script', plugins_url( 'assets/js/min/hw-metabox.min.js', $this->base->file ), array( 'jquery', 'plupload-handlers', 'quicktags', 'jquery-ui-sortable' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-metabox-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-metabox-script',
            'hw_gallery_metabox',
            array(
                'ajax'           => admin_url( 'admin-ajax.php' ),
                'gallery'        => esc_attr__( 'Click Here to Insert from Other Image Sources', 'hw-gallery' ),
                'id'             => $post_id,
                'import'         => __( 'You must select a file to import before continuing.', 'hw-gallery' ),
                'insert_nonce'   => wp_create_nonce( 'hw-gallery-insert-images' ),
                'inserting'      => __( 'Inserting...', 'hw-gallery' ),
                'library_search' => wp_create_nonce( 'hw-gallery-library-search' ),
                'load_image'     => wp_create_nonce( 'hw-gallery-load-image' ),
                'load_gallery'   => wp_create_nonce( 'hw-gallery-load-gallery' ),
                'plupload'       => $this->get_plupload_init( $post_id ),
                'refresh_nonce'  => wp_create_nonce( 'hw-gallery-refresh' ),
                'remove'         => __( 'Are you sure you want to remove this image from the gallery?', 'hw-gallery' ),
                'remove_nonce'   => wp_create_nonce( 'hw-gallery-remove-image' ),
                'save_nonce'     => wp_create_nonce( 'hw-gallery-save-meta' ),
                'saving'         => __( 'Saving...', 'hw-gallery' ),
                'sort'           => wp_create_nonce( 'hw-gallery-sort' )
            )
        );

        // If on an Envira post type, add custom CSS for hiding specific things.
        if ( isset( get_current_screen()->post_type ) && 'hw-gallery' == get_current_screen()->post_type ) {
            add_action( 'admin_head', array( $this, 'meta_box_css' ) );
        }

    }

    /**
     * Returns custom plupload init properties for the media uploader.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @return array       Array of plupload init data.
     */
    public function get_plupload_init( $post_id ) {

        // Prepare $_POST form variables and apply backwards compat filter.
    	$post_params = array(
    	    'post_id'  => $post_id,
    	    '_wpnonce' => wp_create_nonce( 'media-form' ),
    	    'type'     => '',
    	    'tab'      => '',
    	    'short'    => 3
    	);
    	$post_params = apply_filters( 'upload_post_params', $post_params );

    	// Prepare upload size parameters.
        $max_upload_size = wp_max_upload_size();

        // Prepare the plupload init array.
        $plupload_init = array(
        	'runtimes'            => 'html5,silverlight,flash,html4',
        	'browse_button'       => 'hw-gallery-plupload-browse-button',
        	'container'           => 'hw-gallery-plupload-upload-ui',
        	'drop_element'        => 'hw-gallery-drag-drop-area',
        	'file_data_name'      => 'async-upload',
        	'multiple_queues'     => true,
        	'max_file_size'       => $max_upload_size . 'b',
        	'url'                 => admin_url( 'async-upload.php' ),
        	'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
        	'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
        	'filters'             => array(
        	    array(
        	        'title'       => __( 'Allowed Files', 'hw-gallery' ),
        	        'extensions'  => '*'
                ),
            ),
        	'multipart'           => true,
        	'urlstream_upload'    => true,
        	'multipart_params'    => $post_params,
        	'resize'              => array(
        	    'width'   => HW_Gallery_Media_Lite::get_instance()->get_resize_width(),
        	    'height'  => HW_Gallery_Media_Lite::get_instance()->get_resize_height(),
        	    'quality' => 100,
        	    'enabled' => true
        	)
        );

        // If we are on a mobile device, disable multi selection.
        if ( wp_is_mobile() ) {
            $plupload_init['multi_selection'] = false;
        }

        // Apply backwards compat filter.
        $plupload_init = apply_filters( 'plupload_init', $plupload_init );

        // Return and apply a custom filter to our init data.
        return apply_filters( 'hw_gallery_plupload_init', $plupload_init, $post_id );

    }

    /**
     * Hides unnecessary meta box items on Envira post type screens.
     *
     * @since 1.0.0
     */
    public function meta_box_css() {

        ?>
        <style type="text/css">.misc-pub-section:not(.misc-pub-post-status) { display: none; }</style>
        <?php

    }

    /**
     * Creates metaboxes for handling and managing galleries.
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {

        // Let's remove all of those dumb metaboxes from our post type screen to control the experience.
        $this->remove_all_the_metaboxes();

        // Get all public post types.
        $post_types = get_post_types( array( 'public' => true ) );

        // Splice the hw post type since it is not visible to the public by default.
        $post_types[] = 'hw-gallery';

        // Loops through the post types and add the metaboxes.
        foreach ( (array) $post_types as $post_type ) {
            // Don't output boxes on these post types.
            if ( in_array( $post_type, $this->get_skipped_posttypes() ) ) {
                continue;
            }

            add_meta_box( 'hw-gallery', __( 'Gallery Settings', 'hw-gallery' ), array( $this, 'meta_box_callback' ), $post_type, 'normal', 'high' );
        }

    }

    /**
     * Removes all the metaboxes except the ones I want on MY POST TYPE. RAGE.
     *
     * @since 1.0.0
     *
     * @global array $wp_meta_boxes Array of registered metaboxes.
     * @return smile $for_my_buyers Happy customers with no spammy metaboxes!
     */
    public function remove_all_the_metaboxes() {

        global $wp_meta_boxes;

        // This is the post type you want to target. Adjust it to match yours.
        $post_type  = 'hw-gallery';

        // These are the metabox IDs you want to pass over. They don't have to match exactly. preg_match will be run on them.
        $pass_over  = apply_filters( 'hw_gallery_metabox_ids', array( 'submitdiv', 'hw-gallery' ) );

        // All the metabox contexts you want to check.
        $contexts   = apply_filters( 'hw_gallery_metabox_contexts', array( 'normal', 'advanced', 'side' ) );

        // All the priorities you want to check.
        $priorities = apply_filters( 'hw_gallery_metabox_priorities', array( 'high', 'core', 'default', 'low' ) );

        // Loop through and target each context.
        foreach ( $contexts as $context ) {
            // Now loop through each priority and start the purging process.
            foreach ( $priorities as $priority ) {
                if ( isset( $wp_meta_boxes[$post_type][$context][$priority] ) ) {
                    foreach ( (array) $wp_meta_boxes[$post_type][$context][$priority] as $id => $metabox_data ) {
                        // If the metabox ID to pass over matches the ID given, remove it from the array and continue.
                        if ( in_array( $id, $pass_over ) ) {
                            unset( $pass_over[$id] );
                            continue;
                        }

                        // Otherwise, loop through the pass_over IDs and if we have a match, continue.
                        foreach ( $pass_over as $to_pass ) {
                            if ( preg_match( '#^' . $id . '#i', $to_pass ) ) {
                                continue;
                            }
                        }

                        // If we reach this point, remove the metabox completely.
                        unset( $wp_meta_boxes[$post_type][$context][$priority][$id] );
                    }
                }
            }
        }

    }

    /**
     * Callback for displaying content in the registered metabox.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function meta_box_callback( $post ) {

        // Keep security first.
        wp_nonce_field( 'hw-gallery', 'hw-gallery' );

        // Check for our meta overlay helper.
        $gallery_data = get_post_meta( $post->ID, '_eg_gallery_data', true );
        $helper       = get_post_meta( $post->ID, '_eg_just_published', true );
        $class        = '';
        if ( $helper ) {
            $class = 'hw-gallery-helper-needed';
        }

        ?>
        <div id="hw-gallery-tabs" class="hw-gallery-clear <?php echo $class; ?>">
            <?php $this->meta_helper( $post, $gallery_data ); ?>
            <ul id="hw-gallery-tabs-nav" class="hw-gallery-clear">
                <?php $i = 0; foreach ( (array) $this->get_hw_gallery_tab_nav() as $id => $title ) : $class = 0 === $i ? 'hw-gallery-active' : ''; ?>
                    <li class="<?php echo $class; ?>"><a href="#hw-gallery-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a></li>
                <?php $i++; endforeach; ?>
            </ul>
            <?php $i = 0; foreach ( (array) $this->get_hw_gallery_tab_nav() as $id => $title ) : $class = 0 === $i ? 'hw-gallery-active' : ''; ?>
                <div id="hw-gallery-tab-<?php echo $id; ?>" class="hw-gallery-tab hw-gallery-clear <?php echo $class; ?>">
                    <?php do_action( 'hw_gallery_tab_' . $id, $post ); ?>
                </div>
            <?php $i++; endforeach; ?>
        </div>
        <?php

    }

    /**
     * Callback for getting all of the tabs for galleries.
     *
     * @since 1.0.0
     *
     * @return array Array of tab information.
     */
    public function get_hw_gallery_tab_nav() {

        $tabs = array(
            'images'     => __( 'Images', 'hw-gallery' ),
            'config'     => __( 'Config', 'hw-gallery' ),
            'lightbox'   => __( 'Lightbox', 'hw-gallery' )
        );
        $tabs = apply_filters( 'hw_gallery_tab_nav', $tabs );

        // "Misc" tab is required.
        $tabs['misc'] = __( 'Misc', 'hw-gallery' );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for main images tab.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function images_tab( $post ) {

        ?>

        <?php

        // Output the custom media upload form.
        HW_Gallery_Media_Lite::get_instance()->media_upload_form();

        // Prepare output data.
        $gallery_data = get_post_meta( $post->ID, '_eg_gallery_data', true );

        ?>
        <ul id="hw-gallery-output" class="hw-gallery-clear">
            <?php if ( ! empty( $gallery_data['gallery'] ) ) : ?>
                <?php foreach ( $gallery_data['gallery'] as $id => $data ) : ?>
                    <?php echo $this->get_gallery_item( $id, $data, $post->ID ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php $this->media_library( $post );

    }

    /**
     * Inserts the meta icon for displaying useful gallery meta like shortcode and template tag.
     *
     * @since 1.0.0
     *
     * @param object $post        The current post object.
     * @param array $gallery_data Array of gallery data for the current post.
     * @return null               Return early if this is an auto-draft.
     */
    public function meta_helper( $post, $gallery_data ) {

        if ( isset( $post->post_status ) && 'auto-draft' == $post->post_status ) {
            return;
        }

        // Check for our meta overlay helper.
        $helper = get_post_meta( $post->ID, '_eg_just_published', true );
        $class  = '';
        if ( $helper ) {
            $class = 'hw-gallery-helper-active';
            delete_post_meta( $post->ID, '_eg_just_published' );
        }

        ?>
        <div class="hw-gallery-meta-helper <?php echo $class; ?>">
            <span class="hw-gallery-meta-close-text"><?php _e( '(click the icon to open and close the overlay dialog)', 'hw-gallery' ); ?></span>
            <a href="#" class="hw-gallery-meta-icon" title="<?php esc_attr__( 'Click here to view meta information about this gallery.', 'hw-gallery' ); ?>"></a>
            <div class="hw-gallery-meta-information">
                <p><?php _e( 'You can place this gallery anywhere into your posts, pages, custom post types or widgets by using the shortcode(s) below:', 'hw-gallery' ); ?></p>
                <code><?php echo '[hw_gallery id="' . $post->ID . '"]'; ?></code>
                <?php if ( ! empty( $gallery_data['config']['slug'] ) ) : ?>
                    <br><code><?php echo '[hw_gallery slug="' . $gallery_data['config']['slug'] . '"]'; ?></code>
                <?php endif; ?>
                <p><?php _e( 'You can also place this gallery into your template files by using the template tag(s) below:', 'hw-gallery' ); ?></p>
                <code><?php echo 'if ( function_exists( \'hw_gallery\' ) ) { hw_gallery( \'' . $post->ID . '\' ); }'; ?></code>
                <?php if ( ! empty( $gallery_data['config']['slug'] ) ) : ?>
                    <br><code><?php echo 'if ( function_exists( \'hw_gallery\' ) ) { hw_gallery( \'' . $gallery_data['config']['slug'] . '\', \'slug\' ); }'; ?></code>
                <?php endif; ?>
            </div>
        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for selecting images from the media library to insert.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function media_library( $post ) {

        ?>
        <div id="hw-gallery-upload-ui-wrapper">
            <div id="hw-gallery-upload-ui" class="hw-gallery-image-meta" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>
                    <div class="media-modal-content">
                        <div class="media-frame hw-gallery-media-frame wp-core-ui hide-menu hw-gallery-meta-wrap">
                            <div class="media-frame-title">
                                <h1><?php _e( 'Insert Images into Gallery', 'hw-gallery' ); ?></h1>
                            </div>
                            <div class="media-frame-router">
                                <div class="media-router">
                                    <a href="#" class="media-menu-item active"><?php _e( 'Images', 'hw-gallery' ); ?></a>
                                    <?php do_action( 'hw_gallery_modal_router', $post ); ?>
                                </div><!-- end .media-router -->
                            </div><!-- end .media-frame-router -->
                            <!-- begin content for inserting slides from media library -->
                            <div id="hw-gallery-select-images">
                                <div class="media-frame-content">
                                    <div class="attachments-browser">
                                        <div class="media-toolbar hw-gallery-library-toolbar">
                                            <div class="media-toolbar-primary">
                                                <input type="search" placeholder="<?php esc_attr_e( 'Search', 'hw-gallery' ); ?>" id="hw-gallery-gallery-search" class="search" value="" />
                                            </div>
                                            <div class="media-toolbar-secondary">
                                                <a class="button media-button button-large button-secodary hw-gallery-load-library" href="#" data-hw-gallery-offset="20"><?php _e( 'Load More Images from Library', 'hw-gallery' ); ?></a></a><span class="spinner hw-gallery-spinner"></span>
                                            </div>
                                        </div>
                                        <?php $library = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => 20 ) ); ?>
                                        <?php if ( $library ) : ?>
                                        <ul class="attachments hw-gallery-gallery">
                                        <?php foreach ( (array) $library as $image ) :
                                            $has_gallery = get_post_meta( $image->ID, '_eg_has_gallery', true );
                                            $class       = $has_gallery && in_array( $post->ID, (array) $has_gallery ) ? ' selected hw-gallery-in-gallery' : ''; ?>
                                            <li class="attachment<?php echo $class; ?>" data-attachment-id="<?php echo absint( $image->ID ); ?>">
                                                <div class="attachment-preview landscape">
                                                    <div class="thumbnail">
                                                        <div class="centered">
                                                            <?php $src = wp_get_attachment_image_src( $image->ID, 'thumbnail' ); ?>
                                                            <img src="<?php echo esc_url( $src[0] ); ?>" />
                                                        </div>
                                                    </div>
                                                    <a class="check" href="#"><div class="media-modal-icon"></div></a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                        </ul><!-- end .hw-gallery-meta -->
                                        <?php endif; ?>
                                        <div class="media-sidebar">
                                            <div class="hw-gallery-meta-sidebar">
                                                <h3><?php _e( 'Helpful Tips', 'hw-gallery' ); ?></h3>
                                                <strong><?php _e( 'Selecting Images', 'hw-gallery' ); ?></strong>
                                                <p><?php _e( 'You can insert any image from your Media Library into your gallery. If the image you want to insert is not shown on the screen, you can either click on the "Load More Images from Library" button to load more images or use the search box to find the images you are looking for.', 'hw-gallery' ); ?></p>
                                            </div><!-- end .hw-gallery-meta-sidebar -->
                                        </div><!-- end .media-sidebar -->
                                    </div><!-- end .attachments-browser -->
                                </div><!-- end .media-frame-content -->
                            </div><!-- end #hw-gallery-image-slides -->
                            <!-- end content for inserting slides from media library -->
                            <div class="media-frame-toolbar">
                                <div class="media-toolbar">
                                    <div class="media-toolbar-primary">
                                        <a href="#" class="hw-gallery-media-insert button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Insert Images into Gallery', 'hw-gallery' ); ?>"><?php _e( 'Insert Images into Gallery', 'hw-gallery' ); ?></a>
                                    </div><!-- end .media-toolbar-primary -->
                                </div><!-- end .media-toolbar -->
                            </div><!-- end .media-frame-toolbar -->
                        </div><!-- end .media-frame -->
                    </div><!-- end .media-modal-content -->
                </div><!-- end .media-modal -->
                <div class="media-modal-backdrop"></div>
            </div><!-- end .hw-gallery-image-meta -->
        </div><!-- end #hw-gallery-upload-ui-wrapper-->
        <?php

    }

    /**
     * Callback for displaying the UI for setting gallery config options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function config_tab( $post ) {

        ?>
        <div id="hw-gallery-config">
            <p class="hw-gallery-intro"><?php _e( 'The settings below adjust the basic configuration options for the gallery lightbox display.', 'hw-gallery' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="hw-gallery-config-columns-box">
                        <th scope="row">
                            <label for="hw-gallery-config-columns"><?php _e( 'Number of Gallery Columns', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <select id="hw-gallery-config-columns" name="_hw_gallery[columns]">
                                <?php foreach ( (array) $this->get_columns() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'columns', $this->get_config_default( 'columns' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Determines the number of columns in the gallery.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-gallery-theme-box" class="hw-gallery-lite-disabled">
                        <th scope="row">
                            <label for="hw-gallery-config-gallery-theme"><?php _e( 'Gallery Theme', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <select id="hw-gallery-config-gallery-theme" name="_hw_gallery[gallery_theme]">
                                <?php foreach ( (array) $this->get_gallery_themes() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'gallery_theme', $this->get_config_default( 'gallery_theme' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the theme for the gallery display.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-gutter-box">
                        <th scope="row">
                            <label for="hw-gallery-config-gutter"><?php _e( 'Column Gutter Width', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-gutter" type="number" name="_hw_gallery[gutter]" value="<?php echo $this->get_config( 'gutter', $this->get_config_default( 'gutter' ) ); ?>" />
                            <span class="hw-gallery-unit"><?php _e( 'px', 'hw-gallery' ); ?></span>
                            <p class="description"><?php _e( 'Sets the space between the columns (defaults to 10).', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-margin-box">
                        <th scope="row">
                            <label for="hw-gallery-config-margin"><?php _e( 'Margin Below Each Image', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-margin" type="number" name="_hw_gallery[margin]" value="<?php echo $this->get_config( 'margin', $this->get_config_default( 'margin' ) ); ?>" />
                            <span class="hw-gallery-unit"><?php _e( 'px', 'hw-gallery' ); ?></span>
                            <p class="description"><?php _e( 'Sets the space below each item in the gallery.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-crop-box">
                        <th scope="row">
                            <label for="hw-gallery-config-crop"><?php _e( 'Crop Images in Gallery?', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-crop" type="checkbox" name="_hw_gallery[crop]" value="<?php echo $this->get_config( 'crop', $this->get_config_default( 'crop' ) ); ?>" <?php checked( $this->get_config( 'crop', $this->get_config_default( 'crop' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables image cropping for the main gallery images.', 'hw-gallery' ); ?></span>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-crop-size-box" style="display:none;">
                        <th scope="row">
                            <label for="hw-gallery-config-crop-width"><?php _e( 'Crop Dimensions', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-crop-width" type="number" name="_hw_gallery[crop_width]" value="<?php echo $this->get_config( 'crop_width', $this->get_config_default( 'crop_width' ) ); ?>" <?php checked( $this->get_config( 'crop_width', $this->get_config_default( 'crop_width' ) ), 1 ); ?> /> &#215; <input id="hw-gallery-config-crop-height" type="number" name="_hw_gallery[crop_height]" value="<?php echo $this->get_config( 'crop_height', $this->get_config_default( 'crop_height' ) ); ?>" <?php checked( $this->get_config( 'crop_height', $this->get_config_default( 'crop_height' ) ), 1 ); ?> />
                            <p class="description"><?php _e( 'You should adjust these dimensions based on the number of columns in your gallery.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <?php do_action( 'hw_gallery_config_box', $post ); ?>
                </tbody>
            </table>

        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for setting gallery lightbox options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function lightbox_tab( $post ) {

        ?>
        <div id="hw-gallery-lightbox">
            <p class="hw-gallery-intro"><?php _e( 'The settings below adjust the lightbox outputs and displays.', 'hw-gallery' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="hw-gallery-config-lightbox-enabled-box">
                        <th scope="row">
                            <label for="hw-gallery-config-lightbox-enabled"><?php _e( 'Enable Lightbox?', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-lightbox-enabled" type="checkbox" name="_hw_gallery[lightbox_enabled]" value="<?php echo $this->get_config( 'lightbox_enabled', $this->get_config_default( 'lightbox_enabled' ) ); ?>" <?php checked( $this->get_config( 'lightbox_enabled', $this->get_config_default( 'lightbox_enabled' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox.', 'hw-gallery' ); ?></span>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-lightbox-theme-box">
                        <th scope="row">
                            <label for="hw-gallery-config-lightbox-theme"><?php _e( 'Gallery Lightbox Theme', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <select id="hw-gallery-config-lightbox-theme" name="_hw_gallery[lightbox_theme]">
                                <?php foreach ( (array) $this->get_lightbox_themes() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'lightbox_theme', $this->get_config_default( 'lightbox_theme' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the theme for the gallery lightbox display.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-lightbox-title-display-box">
                        <th scope="row">
                            <label for="hw-gallery-config-lightbox-title-display"><?php _e( 'Caption Position', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <select id="hw-gallery-config-lightbox-title-display" name="_hw_gallery[title_display]">
                                <?php foreach ( (array) $this->get_title_displays() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'title_display', $this->get_config_default( 'title_display' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the display of the lightbox image\'s caption.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <?php do_action( 'hw_gallery_lightbox_box', $post ); ?>
                </tbody>
            </table>

        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for setting gallery miscellaneous options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function misc_tab( $post ) {

        ?>
        <div id="hw-gallery-misc">
            <p class="hw-gallery-intro"><?php _e( 'The settings below adjust the miscellaneous settings for the gallery lightbox display.', 'hw-gallery' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="hw-gallery-config-title-box">
                        <th scope="row">
                            <label for="hw-gallery-config-title"><?php _e( 'Gallery Title', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-title" type="text" name="_hw_gallery[title]" value="<?php echo $this->get_config( 'title', $this->get_config_default( 'title' ) ); ?>" />
                            <p class="description"><?php _e( 'Internal gallery title for identification in the admin.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-slug-box">
                        <th scope="row">
                            <label for="hw-gallery-config-slug"><?php _e( 'Gallery Slug', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-slug" type="text" name="_hw_gallery[slug]" value="<?php echo $this->get_config( 'slug', $this->get_config_default( 'slug' ) ); ?>" />
                            <p class="description"><?php _e( '<strong>Unique</strong> internal gallery slug for identification and advanced gallery queries.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-classes-box">
                        <th scope="row">
                            <label for="hw-gallery-config-classes"><?php _e( 'Custom Gallery Classes', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <textarea id="hw-gallery-config-classes" rows="5" cols="75" name="_hw_gallery[classes]" placeholder="<?php _e( 'Enter custom gallery CSS classes here, one per line.', 'hw-gallery' ); ?>"><?php echo implode( "\n", (array) $this->get_config( 'classes', $this->get_config_default( 'classes' ) ) ); ?></textarea>
                            <p class="description"><?php _e( 'Adds custom CSS classes to this gallery. Enter one class per line.', 'hw-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="hw-gallery-config-rtl-box">
                        <th scope="row">
                            <label for="hw-gallery-config-rtl"><?php _e( 'Enable RTL Support?', 'hw-gallery' ); ?></label>
                        </th>
                        <td>
                            <input id="hw-gallery-config-rtl" type="checkbox" name="_hw_gallery[rtl]" value="<?php echo $this->get_config( 'rtl', $this->get_config_default( 'rtl' ) ); ?>" <?php checked( $this->get_config( 'rtl', $this->get_config_default( 'rtl' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables RTL support for right-to-left languages.', 'hw-gallery' ); ?></span>
                        </td>
                    </tr>
                    <?php do_action( 'hw_gallery_misc_box', $post ); ?>
                </tbody>
            </table>

        </div>
        <?php

    }

    /**
     * Callback for saving values from gallery metaboxes.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param object $post The current post object.
     */
    public function save_meta_boxes( $post_id, $post ) {

        // Bail out if we fail a security check.
        if ( ! isset( $_POST['hw-gallery'] ) || ! wp_verify_nonce( $_POST['hw-gallery'], 'hw-gallery' ) || ! isset( $_POST['_hw_gallery'] ) ) {
            return;
        }

        // Bail out if running an autosave, ajax, cron or revision.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Bail out if the user doesn't have the correct permissions to update the slider.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // If the post has just been published for the first time, set meta field for the gallery meta overlay helper.
        if ( isset( $post->post_date ) && isset( $post->post_modified ) && $post->post_date === $post->post_modified ) {
            update_post_meta( $post_id, '_eg_just_published', true );
        }

        // Sanitize all user inputs.
        $settings = get_post_meta( $post_id, '_eg_gallery_data', true );
        if ( empty( $settings ) ) {
            $settings = array();
        }

        // Force gallery ID to match Post ID. This is deliberate; if a gallery is duplicated (either using a duplication)
        // plugin or WPML, the ID remains as the original gallery ID, which breaks things for translations etc.
        $settings['id'] = $post_id;
        $settings['title'] = get_the_title($post_id);   //by hoang

        // Save the config settings.
        $settings['config']['columns']      = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_hw_gallery']['columns'] );
        $settings['config']['gutter']       = absint( $_POST['_hw_gallery']['gutter'] );
        $settings['config']['margin']       = absint( $_POST['_hw_gallery']['margin'] );
        $settings['config']['crop']         = isset( $_POST['_hw_gallery']['crop'] ) ? 1 : 0;
        $settings['config']['crop_width']   = absint( $_POST['_hw_gallery']['crop_width'] );
        $settings['config']['crop_height']  = absint( $_POST['_hw_gallery']['crop_height'] );
        
        // Lightbox
        $settings['config']['lightbox_enabled'] = isset( $_POST['_hw_gallery']['lightbox_enabled'] ) ? 1 : 0;
        $settings['config']['title_display']    = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_hw_gallery']['title_display'] );

        // Misc
        $settings['config']['classes']      = explode( "\n", $_POST['_hw_gallery']['classes'] );
        $settings['config']['title']        = trim( strip_tags( $_POST['_hw_gallery']['title'] ) );
        $settings['config']['slug']         = sanitize_text_field( $_POST['_hw_gallery']['slug'] );
        $settings['config']['rtl']          = isset( $_POST['_hw_gallery']['rtl'] ) ? 1 : 0;

        // If on an hw-gallery post type, map the title and slug of the post object to the custom fields if no value exists yet.
        if ( isset( $post->post_type ) && 'hw-gallery' == $post->post_type ) {
            $settings['config']['title'] = trim( strip_tags( $post->post_title ) );
            $settings['config']['slug']  = sanitize_text_field( $post->post_name );
        }

        // Provide a filter to override settings.
        $settings = apply_filters( 'hw_gallery_save_settings', $settings, $post_id, $post );

        // Update the post meta.
        update_post_meta( $post_id, '_eg_gallery_data', $settings );

        // Change states of images in gallery from pending to active.
        $this->change_gallery_states( $post_id );

        // If the crop option is checked, crop images accordingly.
        if ( isset( $settings['config']['crop'] ) && $settings['config']['crop'] ) {
            $args = apply_filters( 'hw_gallery_crop_image_args',
                array(
                    'position' => 'c',
                    'width'    => $this->get_config( 'crop_width', $this->get_config_default( 'crop_width' ) ),
                    'height'   => $this->get_config( 'crop_height', $this->get_config_default( 'crop_height' ) ),
                    'quality'  => 100,
                    'retina'   => false
                )
            );
            $this->crop_images( $args, $post_id );
        }

        // Finally, flush all gallery caches to ensure everything is up to date.
        $this->flush_gallery_caches( $post_id, $settings['config']['slug'] );

    }

    /**
     * Helper method for retrieving the gallery layout for an item in the admin.
     *
     * @since 1.0.0
     *
     * @param int $id The  ID of the item to retrieve.
     * @param array $data  Array of data for the item.
     * @param int $post_id The current post ID.
     * @return string The  HTML output for the gallery item.
     */
    public function get_gallery_item( $id, $data, $post_id = 0 ) {

        $thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' ); ob_start(); ?>
        <li id="<?php echo $id; ?>" class="hw-gallery-image hw-gallery-status-<?php echo $data['status']; ?>" data-hw-gallery-image="<?php echo $id; ?>">
            <img src="<?php echo esc_url( $thumbnail[0] ); ?>" alt="<?php esc_attr_e( $data['alt'] ); ?>" />
            <a href="#" class="hw-gallery-remove-image" title="<?php esc_attr_e( 'Remove Image from Gallery?', 'hw-gallery' ); ?>"></a>
            <a href="#" class="hw-gallery-modify-image" title="<?php esc_attr_e( 'Modify Image', 'hw-gallery' ); ?>"></a>
            <?php echo $this->get_gallery_item_meta( $id, $data, $post_id ); ?>
        </li>
        <?php
        return ob_get_clean();

    }

    /**
     * Helper method for retrieving the gallery metadata editing modal.
     *
     * @since 1.0.0
     *
     * @param int $id      The ID of the item to retrieve.
     * @param array $data  Array of data for the item.
     * @param int $post_id The current post ID.
     * @return string      The HTML output for the gallery item.
     */
    public function get_gallery_item_meta( $id, $data, $post_id ) {

        ob_start(); ?>
        <div id="hw-gallery-meta-<?php echo $id; ?>" class="hw-gallery-meta-container" style="display:none;">
            <div class="media-modal wp-core-ui">
                <a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>
                <div class="media-modal-content">
                    <div class="media-frame hw-gallery-media-frame wp-core-ui hide-menu hide-router hw-gallery-meta-wrap">
                        <div class="media-frame-title">
                            <h1><?php _e( 'Edit Metadata', 'hw-gallery' ); ?></h1>
                        </div>
                        <div class="media-frame-content">
                            <div class="attachments-browser">
                                <div class="hw-gallery-meta attachments">
                                    <?php do_action( 'hw_gallery_before_meta_table', $id, $data, $post_id ); ?>
                                    <table id="hw-gallery-meta-table-<?php echo $id; ?>" class="form-table hw-gallery-meta-table" data-hw-gallery-meta-id="<?php echo $id; ?>">
                                        <tbody>
                                            <?php do_action( 'hw_gallery_before_meta_settings', $id, $data, $post_id ); ?>
                                            <tr id="hw-gallery-title-box-<?php echo $id; ?>" valign="middle">
                                                <th scope="row"><label for="hw-gallery-title-<?php echo $id; ?>"><?php _e( 'Image Title', 'hw-gallery' ); ?></label></th>
                                                <td>
                                                    <?php 
	                                                wp_editor( $data['title'], 'hw-gallery-title-' . $id, array(
	                                                	'media_buttons' => false, 
	                                                	'tinymce' => false, 
	                                                	'textarea_name' => '_hw_gallery[meta_title]',
	                                                	'quicktags' => array( 'buttons' => 'strong,em,link,ul,ol,li,close' ),
	                                                	'textarea_rows' => 5,
	                                                ) ); 
	                                                ?>
                                                    <p class="description"><?php _e( 'Image titles can take any type of HTML.', 'hw-gallery' ); ?></p>
                                                </td>
                                            </tr>
                                            <tr id="hw-gallery-alt-box-<?php echo $id; ?>" valign="middle">
                                                <th scope="row"><label for="hw-gallery-alt-<?php echo $id; ?>"><?php _e( 'Image Alt Text', 'hw-gallery' ); ?></label></th>
                                                <td>
                                                    <input id="hw-gallery-alt-<?php echo $id; ?>" class="hw-gallery-alt" type="text" name="_hw_gallery[meta_alt]" value="<?php echo esc_attr( $data['alt'] ); ?>" data-hw-gallery-meta="alt" />
                                                    <p class="description"><?php _e( 'The image alt text is used for SEO. You should probably fill this one out!', 'hw-gallery' ); ?></p>
                                                </td>
                                            </tr>
                                            <tr id="hw-gallery-link-box-<?php echo $id; ?>" class="hw-gallery-link-cell" valign="middle">
                                                <th scope="row"><label for="hw-gallery-link-<?php echo $id; ?>"><?php _e( 'Image Hyperlink', 'hw-gallery' ); ?></label></th>
                                                <td>
                                                    <input id="hw-gallery-link-<?php echo $id; ?>" class="hw-gallery-link" type="text" name="_hw_gallery[meta_link]" value="<?php echo esc_url( $data['link'] ); ?>" data-hw-gallery-meta="link" />
                                                    <p class="description"><?php _e( 'The image hyperlink determines what opens up in the lightbox once the image is clicked. If this link is set to a regular web page, it will go to that page. Defaults to a larger version of the image itself.', 'hw-gallery' ); ?></p>
                                                </td>
                                            </tr>
                                            <?php do_action( 'hw_gallery_after_meta_settings', $id, $data, $post_id ); ?>
                                        </tbody>
                                    </table>
                                    <?php do_action( 'hw_gallery_after_meta_table', $id, $data, $post_id ); ?>
                                </div><!-- end .hw-gallery-meta -->
                                <div class="media-sidebar">
                                    <div class="hw-gallery-meta-sidebar">
                                        <h3><?php _e( 'Helpful Tips', 'hw-gallery' ); ?></h3>
                                        <strong><?php _e( 'Image Titles', 'hw-gallery' ); ?></strong>
                                        <p><?php _e( 'Image titles can take any type of HTML. You can adjust the position of the titles in the main Lightbox settings.', 'hw-gallery' ); ?></p>
                                        <strong><?php _e( 'Image Alt Text', 'hw-gallery' ); ?></strong>
                                        <p><?php _e( 'The image alt text field is used for accessibility and SEO, and describes your image.', 'hw-gallery' ); ?></p>
                                        <strong><?php _e( 'Image Hyperlinks', 'hw-gallery' ); ?></strong>
                                        <p><?php _e( 'The image hyperlink field is used when you click on an image in the gallery. It determines what is displayed in the lightbox view. It could be a larger version of the image, a video, or some other form of content.', 'hw-gallery' ); ?></p>
                                        <strong><?php _e( 'Saving and Exiting', 'hw-gallery' ); ?></strong>
                                        <p class="no-margin"><?php _e( 'Click on the button below to save your image metadata. You can close this window by either clicking on the "X" above or hitting the <code>esc</code> key on your keyboard.', 'hw-gallery' ); ?></p>
                                    </div><!-- end .hw-gallery-meta-sidebar -->
                                </div><!-- end .media-sidebar -->
                            </div><!-- end .attachments-browser -->
                        </div><!-- end .media-frame-content -->
                        <div class="media-frame-toolbar">
                            <div class="media-toolbar">
                                <div class="media-toolbar-primary">
                                    <a href="#" class="hw-gallery-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'hw-gallery' ); ?>" data-hw-gallery-item="<?php echo $id; ?>"><?php _e( 'Save Metadata', 'hw-gallery' ); ?></a>
                                </div><!-- end .media-toolbar-primary -->
                            </div><!-- end .media-toolbar -->
                        </div><!-- end .media-frame-toolbar -->
                    </div><!-- end .media-frame -->
                </div><!-- end .media-modal-content -->
            </div><!-- end .media-modal -->
            <div class="media-modal-backdrop"></div>
        </div>
        <?php
        return ob_get_clean();

    }

    /**
     * Helper method to change a gallery state from pending to active. This is done
     * automatically on post save. For previewing galleries before publishing,
     * simply click the "Preview" button and Envira will load all the images present
     * in the gallery at that time.
     *
     * @since 1.0.0
     *
     * @param int $id The current post ID.
     */
    public function change_gallery_states( $post_id ) {

        $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
        if ( ! empty( $gallery_data['gallery'] ) ) {
            foreach ( (array) $gallery_data['gallery'] as $id => $item ) {
                $gallery_data['gallery'][$id]['status'] = 'active';
            }
        }

        update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

    }

    /**
     * Helper method to crop gallery images to the specified sizes.
     *
     * @since 1.0.0
     *
     * @param array $args  Array of args used when cropping the images.
     * @param int $post_id The current post ID.
     */
    public function crop_images( $args, $post_id ) {

        // Gather all available images to crop.
        $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
        $images       = ! empty( $gallery_data['gallery'] ) ? $gallery_data['gallery'] : false;
        $common       = HW_Gallery_Common_Lite::get_instance();

        // Loop through the images and crop them.
        if ( $images ) {
            // Increase the time limit to account for large image sets and suspend cache invalidations.
            set_time_limit( 0 );
            wp_suspend_cache_invalidation( true );

            foreach ( $images as $id => $item ) {
                // Get the full image attachment. If it does not return the data we need, skip over it.
                $image = wp_get_attachment_image_src( $id, 'full' );
                if ( ! is_array( $image ) ) {
                    continue;
                }

                // Generate the cropped image.
                $cropped_image = $common->resize_image( $image[0], $args['width'], $args['height'], true, $args['position'], $args['quality'], $args['retina'] );

                // If there is an error, possibly output error message, otherwise woot!
                if ( is_wp_error( $cropped_image ) ) {
                    // If WP_DEBUG is enabled, and we're logged in, output an error to the user
                    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_user_logged_in() ) {
                        echo '<pre>Error occured resizing image (these messages are only displayed to logged in WordPress users):<br />';
                        echo 'Error: ' . $cropped_image->get_error_message() . '<br />';
                        echo 'Image: ' . $image . '<br />';
                        echo 'Args: ' . var_export( $args, true ) . '</pre>';
                    }
                }
            }

            // Turn off cache suspension and flush the cache to remove any cache inconsistencies.
            wp_suspend_cache_invalidation( false );
            wp_cache_flush();
        }

    }

    /**
     * Helper method to flush gallery caches once a gallery is updated.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param string $slug The unique gallery slug.
     */
    public function flush_gallery_caches( $post_id, $slug ) {

        HW_Gallery_Common_Lite::get_instance()->flush_gallery_caches( $post_id, $slug );

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
    public function get_config( $key, $default = false ) {

        global $id, $post;

        // Get the current post ID.
        $post_id = isset( $post->ID ) ? $post->ID : (int) $id;

        $settings = get_post_meta( $post_id, '_eg_gallery_data', true );
        if ( isset( $settings['config'][$key] ) ) {
            return $settings['config'][$key];
        } else {
            return $default ? $default : '';
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
     * Helper method for retrieving columns.
     *
     * @since 1.0.0
     *
     * @return array Array of column data.
     */
    public function get_columns() {

        $instance = HW_Gallery_Common_Lite::get_instance();
        return $instance->get_columns();

    }

    /**
     * Helper method for retrieving gallery themes.
     *
     * @since 1.0.0
     *
     * @return array Array of gallery theme data.
     */
    public function get_gallery_themes() {

        $instance = HW_Gallery_Common_Lite::get_instance();
        return $instance->get_gallery_themes();

    }

    /**
     * Helper method for retrieving lightbox themes.
     *
     * @since 1.0.0
     *
     * @return array Array of lightbox theme data.
     */
    public function get_lightbox_themes() {

        $instance = HW_Gallery_Common_Lite::get_instance();
        return $instance->get_lightbox_themes();

    }

    /**
     * Helper method for retrieving title displays.
     *
     * @since 1.2.7
     *
     * @return array Array of title display data.
     */
    public function get_title_displays() {

        $instance = HW_Gallery_Common_Lite::get_instance();
        return $instance->get_title_displays();

    }

    /**
     * Returns the post types to skip for loading Envira metaboxes.
     *
     * @since 1.0.7
     *
     * @return array Array of skipped posttypes.
     */
    public function get_skipped_posttypes() {

        return apply_filters( 'hw_gallery_skipped_posttypes', array( 'attachment', 'revision', 'nav_menu_item', 'soliloquy', 'soliloquyv2' ) );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The HW_Gallery_Metaboxes_Lite object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof HW_Gallery_Metaboxes_Lite ) ) {
            self::$instance = new HW_Gallery_Metaboxes_Lite();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$hw_gallery_metaboxes_lite = HW_Gallery_Metaboxes_Lite::get_instance();