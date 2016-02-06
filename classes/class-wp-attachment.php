<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/10/2015
 * Time: 00:26
 */
class HW_WP_Attachment {
    /**
     * attachment uploader
     * @param $file
     * @param $parent_post_id
     */
    public static function upload_attachment($file, $parent_post_id=0) {
        #$file = dirname(__FILE__).'/images/2.jpg';
        $filename = basename($file);

        $upload_file = wp_upload_bits($filename, null, file_get_contents($file));
        if (!$upload_file['error']) {
            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_parent' => $parent_post_id,
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            }
        }
        return isset($attachment_id)? $attachment_id : 0;
    }

    /**
     * get images attachment
     * @param int $num
     * @param string $size
     * @param string $postid
     * @return array array of attachs ids
     */
    public static function get_attachment_images($num=5, $size ='thumbnail', $postid='') {
        global $wpdb;
        //$res = $wpdb->get_results("SELECT * from {$wpdb->posts} where post_type='attachment' and post_mime_type like '%image%' order by rand() limit $num");
        $data = array();
        $args = array(
            'post_type' => 'attachment',
            'orderby' => 'rand',
            'posts_per_page' => -1,
            'post_mime_type' => 'image',
            'meta_query' => array(
                array(
                    'key' => '_wp_attachment_metadata',
                    'value' => $size,
                    'compare' => 'LIKE'
                )
            )
        );
        if(is_numeric($postid)) $args['post_parent'] = $postid;
        //get attachment or from specific post
        if(!empty($args['post_parent'])) {
            $images =& get_children($args );
        }
        else {
            $images = get_posts( $args );
        }
        /*foreach ($images as $img) :
            $image = wp_get_attachment_image_src( $img->ID, 'extra_large' );
            if ( !empty($image) )
                $data[] = array($img->post_parent, $image);
        endforeach;*/
        return $images;
    }
}