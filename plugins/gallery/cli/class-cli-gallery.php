<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/11/2015
 * Time: 13:09
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_CLI_Gallery
 */
class HW_CLI_Gallery extends HW_CLI_Command {
    /**
     * add new gallery
     * @example wp gallery create_gallery --create_gallery_xml=/path/to/file
     * @param $args
     * @param $assoc_args
     */
    public function create_gallery($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'create_gallery_xml');
        //$this->do_import();
        if(file_exists(HW_GALLERY_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_GALLERY_PLUGIN_PATH. '/'.$import);
        }
        WP_CLI::success( ' add gallery successful.' );
    }

    /**
     * delete all galleries
     * @cmd wp gallery delete_all_galleries
     * @param $args
     * @param $assoc_args
     */
    public function delete_all_galleries($args, $assoc_args) {
        $forms = get_posts('post_type=hw-gallery&showposts=-1');
        foreach($forms as $post) {
            wp_delete_post($post->ID);
        }
        WP_CLI::success( ' Deleted all galleries.' );
    }
}