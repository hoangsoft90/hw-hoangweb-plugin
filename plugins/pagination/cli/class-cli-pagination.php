<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/11/2015
 * Time: 13:03
 */
/**
 * Class HW_CLI_Pagination
 */
class HW_CLI_Pagination extends HW_CLI_Command {
    /**
     * setup pagination page
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $this->do_import();
        WP_CLI::success( ' config pagination setting page successful.' );
    }
}