<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 26/10/2015
 * Time: 09:19
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_CLI_HW_Livechat
 */
class HW_CLI_HW_Livechat extends HW_CLI_Command {
    /**
     * init livechat
     * @param $args
     * @param $assoc_args
     */
    public function setup_livechat($args, $assoc_args) {
        $file = $this->get_cmd_arg($assoc_args, 'wxr', '');
        if(!file_exists($file)) $file = dirname(__FILE__). '/'. $file;

        $this->do_import_file($file);
        WP_CLI::success( ' setup sample livechat successful.' );
    }

    /**
     * @param $args
     * @param $assoc_args
     */
    public function disable_livechat($args, $assoc_args) {
        delete_option('HW_Livechat_settings');
        WP_CLI::success( ' turn off livechat successful.' );
    }
}
