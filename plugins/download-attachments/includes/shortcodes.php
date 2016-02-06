<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

new HW_Downloadattachment_shortcode();

/**
 * Class HW_Downloadattachment_shortcode
 */
class HW_Downloadattachment_shortcode {
    /**
     * @var array|null
     */
    private  $options = null;
    /**
     * Constructor.
     */
    public function __construct() {
        // settings
        $this->options = array_merge(
            array( 'general' => get_option( 'download_attachments_general' ) )
        );
        // actions
        add_action( 'init', array( &$this, 'register_download_shortcodes' ) );
    }
    /**
     * Register download attachments shortcodes.
     */
    public function register_download_shortcodes() {
        add_shortcode('hw-download-attachments', array(&$this, '_download_attachments_shortcode'));
    }

    /**
     * Handle download-attachments shortcode.
     * @param $atts
     */
    public function _download_attachments_shortcode($args) {
        $defaults = array(
            'post_id'				 => 0,
            'container'				 => 'div',
            'container_class'		 => 'download-attachments',
            'container_id'			 => '',
            'style'					 => isset( $this->options['general']['display_style'] ) ? esc_attr( $this->options['general']['display_style'] ) : 'list',
            'link_before'			 => '',
            'link_after'			 => '',
            'display_index'			 => isset( $options['frontend_columns']['index'] ) ? (int) $options['frontend_columns']['index'] : 0,
            'display_user'			 => (int) $this->options['general']['frontend_columns']['author'],
            'display_icon'			 => (int) $this->options['general']['frontend_columns']['icon'],
            'display_count'			 => (int) $this->options['general']['frontend_columns']['downloads'],
            'display_size'			 => (int) $this->options['general']['frontend_columns']['size'],
            'display_date'			 => (int) $this->options['general']['frontend_columns']['date'],
            'display_caption'		 => (int) $this->options['general']['frontend_content']['caption'],
            'display_description'	 => (int) $this->options['general']['frontend_content']['description'],
            'display_empty'			 => 0,
            'display_option_none'	 => __( 'No attachments to download', 'download-attachments' ),
            'use_desc_for_title'	 => 0,
            'exclude'				 => '',
            'include'				 => '',
            'title'					 => __( 'Download Attachments', 'download-attachments' ),
            'title_container'		 => 'p',
            'title_class'			 => 'download-title',
            'orderby'				 => 'menu_order',
            'order'					 => 'asc',
            'echo'					 => 1
        );

        // we have to force return in shortcodes
        $args['echo'] = 0;

        if ( ! isset( $args['title'] ) ) {
            $args['title'] = '';

            if ( $this->options['general']['label'] !== '' )
                $args['title'] = $this->options['general']['label'];
        }

        $args = shortcode_atts( $defaults, $args );

        // reassign post id
        $post_id = (int) (empty( $args['post_id'] ) ? get_the_ID() : $args['post_id']);

        // unset from args
        unset( $args['post_id'] );

        return $this->hw_da_display_download_attachments( $post_id, $args );
    }
    /**
     * Display download attachments for a given post
     * from: wp-content/plugins/download-attachments/includes/functions.php
     *
     * @param 	int $post_id
     * @param	array $args
     * @return 	mixed
     */
    public function hw_da_display_download_attachments( $post_id = 0, $args = array() ) {
        $post_id = (int) (empty( $post_id ) ? get_the_ID() : $post_id);

        $options = get_option( 'download_attachments_general' );
        $setting = HW_Module_downloadattachment::get()->get_values();

        $defaults = array(
            'container'				 => 'div',
            'container_class'		 => 'download-attachments',
            'container_id'			 => '',
            'style'					 => isset( $options['display_style'] ) ? esc_attr( $options['display_style'] ) : 'list',
            'link_before'			 => '',
            'link_after'			 => '',
            'content_before'		 => isset($setting['content_before'])? $setting['content_before'] : '',
            'content_after'			 => isset($setting['content_after'])? $setting['content_after'] : '',
            'display_index'			 => isset( $options['frontend_columns']['index'] ) ? (int) $options['frontend_columns']['index'] : false,
            'display_user'			 => (int) $options['frontend_columns']['author'],
            'display_icon'			 => (int) $options['frontend_columns']['icon'],
            'display_count'			 => (int) $options['frontend_columns']['downloads'],
            'display_size'			 => (int) $options['frontend_columns']['size'],
            'display_date'			 => (int) $options['frontend_columns']['date'],
            'display_caption'		 => (int) $options['frontend_content']['caption'],
            'display_description'	 => (int) $options['frontend_content']['description'],
            'display_empty'			 => 0,
            'display_option_none'	 => __( 'No attachments to download', 'download-attachments' ),
            'use_desc_for_title'	 => 0,
            'exclude'				 => '',
            'include'				 => '',
            'title'					 => __( 'Download Attachments', 'download-attachments' ),
            'title_container'		 => 'p',
            'title_class'			 => 'download-title',
            'orderby'				 => 'menu_order',
            'order'					 => 'asc',
            'echo'					 => 1
        );

        $args = apply_filters( 'da_display_attachments_defaults', array_merge( $defaults, $args ), $post_id );

        $args['display_index'] = apply_filters( 'da_display_attachments_index', (int) $args['display_index'] );
        $args['display_user'] = apply_filters( 'da_display_attachments_user', (int) $args['display_user'] );
        $args['display_icon'] = apply_filters( 'da_display_attachments_icon', (int) $args['display_icon'] );
        $args['display_count'] = apply_filters( 'da_display_attachments_count', (int) $args['display_count'] );
        $args['display_size'] = apply_filters( 'da_display_attachments_size', (int) $args['display_size'] );
        $args['display_date'] = apply_filters( 'da_display_attachments_date', (int) $args['display_date'] );
        $args['display_caption'] = apply_filters( 'da_display_attachments_caption', (int) $args['display_caption'] );
        $args['display_description'] = apply_filters( 'da_display_attachments_description', (int) $args['display_description'] );
        $args['display_empty'] = apply_filters( 'da_display_attachments_empty', (int) $args['display_empty'] );
        $args['use_desc_for_title'] = (int) $args['use_desc_for_title'];
        $args['echo'] = (int) $args['echo'];
        $args['style'] = (in_array( $args['style'], array( 'list', 'table', 'none', '' ), true ) ? $args['style'] : $defaults['style']);
        $args['orderby'] = (in_array( $args['orderby'], array( 'menu_order', 'attachment_id', 'attachment_date', 'attachment_title', 'attachment_size', 'attachment_downloads' ), true ) ? $args['orderby'] : $defaults['orderby']);
        $args['order'] = (in_array( $args['order'], array( 'asc', 'desc' ), true ) ? $args['order'] : $defaults['order']);
        $args['link_before'] = trim( $args['link_before'] );
        $args['link_after'] = trim( $args['link_after'] );
        $args['display_option_none'] = (($info = trim( $args['display_option_none'] )) !== '' ? $info : $defaults['display_option_none']);

        $args['title'] = apply_filters( 'da_display_attachments_title', trim( $args['title'] ) );

        $attachments = da_get_download_attachments(
            $post_id, apply_filters(
                'da_display_attachments_args', array(
                    'include'	 => $args['include'],
                    'exclude'	 => $args['exclude'],
                    'orderby'	 => $args['orderby'],
                    'order'		 => $args['order']
                )
            )
        );
        $count = count( $attachments );
        //load active skin
        $current_skin = HW_Module_downloadattachment::get()->get_field_value('skin');

        $headers = array(
            'index' => '',  //order
            'file' => __('Tệp'),    //title
        );
        $data = array();

        if($count > 0) {
            $i = 1;
            //caption
            if ( $args['display_caption'] === 1 || ($args['display_description'] === 1 && $args['use_desc_for_title'] === 0) )
                $headers['caption'] = __('Mô tả');

            //date
            if ( $args['display_date'] === 1 ) $headers['attachment-date'] = __( 'Date added', 'download-attachments' );
            //user
            if ( $args['display_user'] === 1 ) $headers['attachment-user'] = __( 'Added by', 'download-attachments' );
            //size
            if ( $args['display_size'] === 1 ) $headers['attachment-size'] = __( 'File size', 'download-attachments' );
            //display download count
            if ( $args['display_count'] === 1 ) $headers['attachment-downloads'] = __( 'Downloads', 'download-attachments' );

            foreach ( $attachments as $attachment ) {
                if ( $attachment['attachment_exclude'] )
                    continue;

                $row = array();
                if ( $args['use_desc_for_title'] === 1 && $attachment['attachment_description'] !== '' ) {
                    $title = apply_filters( 'da_display_attachment_title', $attachment['attachment_description'] );
                } else {
                    $title = apply_filters( 'da_display_attachment_title', $attachment['attachment_title'] );
                }
                //attachment type
                $row['class'] = $attachment['attachment_type'];
                #if ( $args['display_index'] === 1 )
                    $row['index'] = $i; //index count

                // title
                // type
                if ( $args['display_icon'] === 1 ) {
                    $row['icon'] = '<img class="attachment-icon" src="' . $attachment['attachment_icon_url'] . '" alt="' . $attachment['attachment_type'] . '" /> ';
                }
                else $row['icon'] = '';

                // link before
                if ( $args['link_before'] !== '' ) $row['link_before'] = $args['link_before'];
                else $row['link_before'] = '';

                $row['url'] = ($options['pretty_urls'] === true ? home_url( '/' . $options['download_link'] . '/' . $attachment['attachment_id'] . '/' ) : hw_modules_url( 'download-attachments/includes/download.php?id=' . $attachment['attachment_id'] ));
                $row['title'] = $title;
                $row['link'] =  '<a href="' . ($options['pretty_urls'] === true ? home_url( '/' . $options['download_link'] . '/' . $attachment['attachment_id'] . '/' ) : hw_modules_url( 'download-attachments/includes/download.php?id=' . $attachment['attachment_id'] )) . '" class="attachment-link" title="' . $title . '">' . $title . '</a>';

                $row['link_after'] = $args['link_after'];   //link after
                // caption
                if ( $args['display_caption'] === 1) $row['caption'] = $attachment['attachment_caption'];
                // description
                if ( $args['display_description'] === 1 && $args['use_desc_for_title'] === 0)
                    $row['description'] = $attachment['attachment_description'];

                // date
                if ( $args['display_date'] === 1 ) $row['date'] = $attachment['attachment_date'];
                // user
                if ( $args['display_user'] === 1 ) $row['user'] = $attachment['attachment_user_name'] ;
                // size
                if ( $args['display_size'] === 1 ) $row['size'] = $attachment['attachment_size'];
                // downloads
                if ( $args['display_count'] === 1 ) $row['count'] = $attachment['attachment_downloads'];

                $data[] = $row ;
                $i ++;
            }
        }

        //change sidebar params from skin
        return HW_SKIN::apply_skin_data($current_skin,  array('callback_before' => array(__CLASS__, '_hw_skin_before_include_skin_file') ), array(
            'attachments' => $data,
            'headers' => $headers,
            'args' => $args
        ), false);
    }
    /**
     * HW_SKIN::apply_skin_data callback
     * @param $args
     */
    public static function _hw_skin_before_include_skin_file($args) {
        #extract($args);
        return $args;
    }
}