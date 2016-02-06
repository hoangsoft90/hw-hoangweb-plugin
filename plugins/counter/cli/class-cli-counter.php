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
 * Class HW_CLI_Counter
 */
class HW_CLI_Counter extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_COUNTER_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_COUNTER_PLUGIN_PATH. '/'.$import);
        }
        $this->success( ' config counter module page successful.' );
    }

}
