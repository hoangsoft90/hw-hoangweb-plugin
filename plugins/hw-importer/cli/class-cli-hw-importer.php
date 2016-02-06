<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//cli work with module
include_once (HW_HOANGWEB_PLUGINS. '/module-cli.php');
/**
 * Class HW_CLI_HW_Importer
 */
class HW_CLI_HW_Importer extends HW_CLI_Command {
    /**
     * do import file
     * @example wp hw-importer import --file=/path/to/file
     * @param $args
     * @param $assoc_args
     */
    public function import($args, $assoc_args) {
        //$this->do_import();
        $file = $this->get_cmd_arg($assoc_args, 'file');
        if($file && !file_exists($file)) $file = HW_IE_PLUGIN_PATH. '/'.$file;
        if($file && file_exists($file)) {
            $this->do_import_file($file);
        }
        WP_CLI::success( 'import file "'.$file.'" successful.' );
    }
}
