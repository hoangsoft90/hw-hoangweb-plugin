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
 * Class HW_CLI_Visual_Editor
 */
class HW_CLI_Visual_Editor extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function init_widgets($args, $assoc_args) {
        $import = $this->get_cmd_arg($assoc_args, 'import');
        //$this->do_import();
        if(file_exists(dirname(dirname(__FILE__)). '/'.$import)) {
            $this->do_import_file(dirname(dirname(__FILE__)). '/'.$import);
        }
        WP_CLI::success( ' init visual editor widget successful.' );
    }

}
