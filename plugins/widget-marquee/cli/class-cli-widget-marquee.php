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
 * Class HW_CLI_Widget_Marquee
 */
class HW_CLI_Widget_Marquee extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function init_widgets($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_MARQUEE_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_MARQUEE_PLUGIN_PATH. '/'.$import);
        }
        WP_CLI::success( ' init marquee widget successful.' );
    }

}
