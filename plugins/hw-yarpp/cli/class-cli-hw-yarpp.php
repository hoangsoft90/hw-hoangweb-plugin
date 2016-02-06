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
 * Class HW_CLI_HW_YARPP
 */
class HW_CLI_HW_YARPP extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_YARPP_DIR. '/'.$import)) {
            $this->do_import_file(HW_YARPP_DIR. '/'.$import);
        }
        WP_CLI::success( ' config yarpp setting page successful.' );
    }

}
