<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/11/2015
 * Time: 15:47
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_Download_Attachments
 */
class HW_CLI_Download_Attachments extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_DA_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_DA_PLUGIN_PATH. '/'.$import);
        }
        $this->success( ' config setting page successful.' );  //WP_CLI::success
    }

}
