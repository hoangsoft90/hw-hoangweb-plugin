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
 * Class HW_CLI_HW_Menu
 */
class HW_CLI_HW_Social_Sharing extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function enable_social_sharing($args, $assoc_args) {
        $this->do_import();
        WP_CLI::success( ' enable sharing successful.' );
    }

    /**
     * disable social sharing
     * @param $args
     * @param $assoc_args
     */
    public function disable_social_sharing($args, $assoc_args) {
        $this->do_import();
        WP_CLI::success( ' disabled sharing successful.' );
    }
}
