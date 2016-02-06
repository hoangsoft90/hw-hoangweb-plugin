<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_Map
 */
class HW_CLI_Map extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        $path = $this->get_current_module()->option('module_path');
        if(file_exists($path. '/'.$import)) {
            $this->do_import_file($path. '/'.$import);
        }
        //WP_CLI::success( ' config yarpp setting page successful.' );
        $this->success('config map setting page successful.');
    }

}
