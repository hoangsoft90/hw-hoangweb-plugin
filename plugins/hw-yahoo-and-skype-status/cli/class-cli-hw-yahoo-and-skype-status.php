<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_HW_Yahoo_And_Skype_Status
 */
class HW_CLI_HW_Yahoo_And_Skype_Status extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function init_widgets($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(HW_WEA_EXR_PLUGIN_PATH. '/'.$import)) {
            $this->do_import_file(HW_WEA_EXR_PLUGIN_PATH. '/'.$import);
        }
        WP_CLI::success( ' init yahoo & skype widget successful.' );
    }

}
