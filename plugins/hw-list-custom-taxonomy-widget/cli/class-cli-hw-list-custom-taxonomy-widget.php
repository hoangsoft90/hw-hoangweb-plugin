<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/11/2015
 * Time: 21:46
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_HW_List_Custom_Taxonomy_Widget
 */
class HW_CLI_HW_List_Custom_Taxonomy_Widget extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function init_widgets($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_COUNTER_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_COUNTER_PLUGIN_PATH. '/'.$import);
        }
        WP_CLI::success( ' create LCT widgets successful.' );
    }

}
