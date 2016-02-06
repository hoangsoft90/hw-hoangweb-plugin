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
 * Class HW_CLI_HW_Widget_Weather_ExchangeRate
 */
class HW_CLI_HW_Widget_Weather_ExchangeRate extends HW_CLI_Command {
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
        WP_CLI::success( ' init weather exchange rate widget successful.' );
    }

}
