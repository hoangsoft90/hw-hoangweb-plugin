<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Utility commands for my theme/plugin
 * https://github.com/wp-cli/wp-cli/wiki/Commands-Cookbook
 */
class HW_CLI_HW_ML_Slider extends HW_CLI_Command {
    /**
     * add new slider tab
     * @param $args
     * @param $assoc_args
     */
    public function add_slider( $args, $assoc_args  ) {
        //load necessary classes
        HW_HOANGWEB::load_class('HW_WP_Attachment');

        //list( $name ) = $args;
        #params
        $title = $this->get_cmd_arg($assoc_args, 'title', HW_String::generateRandomString(10));

        $slide_image_width = $this->get_cmd_arg($assoc_args, 'width','730');
        $slide_image_height = $this->get_cmd_arg($assoc_args, 'height' ,'280');

        //source
        $source = $this->get_cmd_arg($assoc_args, 'source','upload');
        $from = $this->get_cmd_arg($assoc_args, 'from_path','theme');
        $source_path = $this->get_cmd_arg($assoc_args, 'source_path');
        if($from == 'theme') $source_path = get_stylesheet_directory(). '/'. $source_path;
        elseif($from =='plugin') $source_path = HWML_PLUGIN_PATH. '/'. $source_path;

        //number of slides
        $num = $this->get_cmd_arg($assoc_args, 'num', 3);
        if(!is_numeric($num)) $num = 3;
        //settings
        $slider_settings = $this->get_cmd_arg($assoc_args, 'settings', array());
        if(is_string($slider_settings)) $slider_settings = HW_Encryptor::decode64($slider_settings) ;

        $attach_ids = array();

        #get attachments ids
        if($source =='upload') {
            $count=1;
            if ($handle = opendir($source_path)) {

                while (false !== ($entry = readdir($handle))) {
                    if($count > $num) break;  //exceed max number of files allow to upload

                    if ($entry != "." && $entry != ".." && HW_Validation::valid_image_ext($entry)) {

                        #echo "$entry\n";
                        $attach_ids[] = HW_WP_Attachment::upload_attachment($source_path.DIRECTORY_SEPARATOR.$entry);
                        $count++;
                    }
                }

                closedir($handle);
            }

        }
        else {
            global $wpdb;
            $res = $wpdb->get_results("SELECT * from {$wpdb->posts} where post_type='attachment' and post_mime_type like '%image%' order by rand() limit $num");
            foreach($res as $row) {
                $attach_ids[] = $row->ID;
            }
        }
        /**
         * add slider tab
         */

        $mlslider_post = array(
            'post_content' =>'',
            'post_title' => wp_strip_all_tags($title),
            'post_name' => sanitize_title($title),
            'post_status' => 'publish',
            'post_type' => 'hw-ml-slider',
            'post_author' => '1',
            'ping_status' => 'open',
            'post_parent' => '0',
            'post_excerpt' => '',
            'post_date' => date('Y-m-d H:i:s'),
            'post_date_gmt' => date('Y-m-d H:i:s'),
            'comment_status' => 'open',

        );
        $slider_id= wp_insert_post($mlslider_post);
        /**
         * add metaslider setting for created above
         */
        $settings_params = $this->get_params('metaslider_settings');
        $settings = array(
            'type' =>'flex',
            'random' => 'false',
            'cssClass' => '',
            'printCss' => 'true',
            'printJs' => 'true',
            'width' => $slide_image_width,
            'height' => $slide_image_height,
            //hidden options
            'spw' => 7,
            'sph' => 5,
            'delay' => '3000',
            'sDelay' => 30,
            'opacity' => 0.8,
            'titleSpeed' => 500,
            'effect' => 'fade',
            'navigation' => 'true',
            'links' => 'true',
            'hoverPause' => 'true',
            'theme' => '',
            'direction' => 'horizontal',
            'reverse' => 'false',
            'animationSpeed' => '600',
            'prevText' => '<',
            'nextText' => '>',
            'slices' => 15,
            'center' => 'false',
            'smartCrop' => 'true',
            'carouselMode' => 'false',
            'carouselMargin' => '5',
            'easing' => 'linear',
            'autoPlay' => 'true',
            #'thumb_width' => 150,
            #'thumb_height' => 100,
            'fullWidth' => 'false',
            'noConflict' => 'true',
            'hw_mlcontainer_id' => 'false',
            'smoothHeight' => 'false',

        );
        if(is_array($slider_settings) && count($slider_settings)) {
            $settings = array_merge($settings, $slider_settings);
        }
        $this->hwml_add_or_update_or_delete_meta($slider_id, 'settings', array_merge($settings, $settings_params));
        /**
         * add images to slider
         */

        foreach($attach_ids as $slide_id) {
            //$slide_id = $row->ID;

            if ( $this->hwml_slide_exists_in_slideshow( $slider_id, $slide_id ) ) {
                continue;
            }
            $this->hwml_tag_slide_to_slider($slider_id, $slide_id);

            $this->hwml_add_or_update_or_delete_meta($slide_id, 'type', 'image');
            $this->hwml_add_or_update_or_delete_meta($slide_id , 'crop_position', 'center-center');  //for crop tab, set crop position for current image
            $this->hwml_add_or_update_or_delete_meta($slide_id, 'title', 'demo-title-'.$slide_id); //for seo tab
            $this->hwml_add_or_update_or_delete_meta($slide_id , 'url', 'url-here-'.$slide_id);

            update_post_meta( $slide_id , '_wp_attachment_image_alt', 'alt demo '.$slide_id );  //for seo tab
            $this->hwml_add_or_update_or_delete_meta( $slide_id , 'new_window', 'true' );
            //$row->guid;

        }
        // Free up memory
        //$this->stop_the_insanity();
        //WP_CLI::success( ' add slider successful.' );
        $this->result(' add slider successful.');
    }

    /**
     * delete slider
     * @param $args
     * @param $assoc_args
     */
    public function del_slider($args, $assoc_args ) {
        $id = $this->get_cmd_arg($assoc_args, 'id');
        $instance = hw__metaslider::init();
        if(is_numeric($id) && $id) {
            //$instance->delete_slider( absint( $id ) );
            if(get_post_type($id) == 'hw-ml-slider') wp_delete_post(absint( $id ));
        }
        WP_CLI::success( ' delete slider successful.' );
    }

    /**
     * delete all sliders
     * @param $args
     * @param $assoc_args
     */
    public function del_all_sliders($args, $assoc_args ) {
        //$mlslider = hw__metaslider::init();
        $args = array(
            'post_type' => 'hw-ml-slider',
            'showposts' => -1,
            'orderby' => 'menu_order',
            'order' => 'asc'
        );
        $query = new WP_Query($args);
        while($query->have_posts()){
            $query->the_post();//$query->next_post();
            wp_delete_post(get_the_ID(), true);
        }
        wp_reset_query($query);
        WP_CLI::success('deleted all sliders');
    }
    /**
     * If the meta doesn't exist, add it
     * If the meta exists, but the value is empty, delete it
     * If the meta exists, update it
     * @from ml-slider/inc/slide/metaslide.class.php
     */
    private function hwml_add_or_update_or_delete_meta( $post_id, $name, $value ) {

        $key = "hw-ml-slider_" . $name;

        if ( $value == 'false' || $value == "" || ! $value ) {
            if ( get_post_meta( $post_id, $key ) ) {
                delete_post_meta( $post_id, $key );
            }
        } else {
            if ( get_post_meta( $post_id, $key ) ) {
                update_post_meta( $post_id, $key, $value );
            } else {
                add_post_meta( $post_id, $key, $value, true );
            }
        }

    }
    /**
     * Check if a slide already exists in a slideshow
     * @from ml-slider/inc/slide/metaslide.class.php
     */
    private function hwml_slide_exists_in_slideshow( $slider_id, $slide_id ) {

        return has_term( "{$slider_id}", 'hw-ml-slider', $slide_id );

    }
    /**
     * Tag the slide attachment to the slider tax category
     * @from ml-slider/inc/slide/metaslide.class.php
     */
    private function hwml_tag_slide_to_slider($slider_id, $slide_id) {

        if ( ! term_exists( $slider_id, 'hw-ml-slider' ) ) {
            // create the taxonomy term, the term is the ID of the slider itself
            wp_insert_term( $slider_id , 'hw-ml-slider' );
        }

        // get the term thats name is the same as the ID of the slider
        $term = get_term_by( 'name', $slider_id , 'hw-ml-slider' );
        // tag this slide to the taxonomy term
        wp_set_post_terms( $slide_id , $term->term_id, 'hw-ml-slider', true );

        $this->hwml_update_menu_order($slider_id, $slide_id);

    }
    /**
     * Ensure slides are added to the slideshow in the correct order.
     *
     * Find the highest slide menu_order in the slideshow, increment, then
     * update the new slides menu_order.
     * @from ml-slider/inc/slide/metaslide.class.php
     */
    private function hwml_update_menu_order($slider_id, $slide_id) {

        $menu_order = 0;

        // get the slide with the highest menu_order so far
        $args = array(
            'force_no_custom_order' => true,
            'orderby' => 'menu_order',
            'order' => 'DESC',
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'lang' => '', // polylang, ingore language filter
            'suppress_filters' => 1, // wpml, ignore language filter
            'posts_per_page' => 1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'hw-ml-slider',
                    'field' => 'slug',
                    'terms' => $slider_id
                )
            )
        );

        $query = new WP_Query( $args );

        while ( $query->have_posts() ) {
            $query->next_post();
            $menu_order = $query->post->menu_order;
        }

        wp_reset_query();

        // increment
        $menu_order = $menu_order + 1;

        // update the slide
        wp_update_post( array(
                'ID' => $slide_id,
                'menu_order' => $menu_order
            )
        );

    }
}
