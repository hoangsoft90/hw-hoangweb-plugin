<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/11/2015
 * Time: 00:24
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_CLI_HW_Taxonomy_Post_List_Widget
 */
class HW_CLI_HW_Taxonomy_Post_List_Widget extends HW_CLI_Command {
    /**
     * init livechat
     * @param $args
     * @param $assoc_args
     */
    public function prepare_widgets($args, $assoc_args) {
        $file = $this->get_cmd_arg($assoc_args, 'wxr', '');
        if(!file_exists($file)) $file = dirname(__FILE__). '/'. $file;

        $this->do_import_file($file);
        WP_CLI::success( ' create widgets successful.' );
    }


}
