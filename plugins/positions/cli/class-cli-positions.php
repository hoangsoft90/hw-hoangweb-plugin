<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/11/2015
 * Time: 13:25
 */
/**
 * Class HW_CLI_Positions
 */
class HW_CLI_Positions extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $this->do_import();
        WP_CLI::success( ' config positions setting page successful.' );
    }
}