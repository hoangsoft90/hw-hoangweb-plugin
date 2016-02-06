<?php
/**
 * Class HW_Rawhtml
 */
class HW_Rawhtml extends HW_PHP_Library {
    public function __construct() {
        parent::__construct();

    }
    /**
     * include this library
     * @require
     */
    public function _load_library_cb() {
        @session_start();
        include_once(HW_LIBRARIES_PATH . '/raw-html/include/screen-options/screen-options.php');

    }
    /**
     * init library
     * @require
     */
    public function init() {
        return null;
    }
}