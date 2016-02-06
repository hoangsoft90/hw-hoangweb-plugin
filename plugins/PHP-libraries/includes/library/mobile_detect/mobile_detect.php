<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/07/2015
 * Time: 23:00
 */
class HW_Mobile_Detect extends HW_PHP_Library {
    public function __construct() {
        parent::__construct();

    }
    /**
     * include this library
     * @require
     */
    public function _load_library_cb() {
        @session_start();
        include_once(HW_LIBRARIES_PATH . '/Mobile-Detect-2.8.3/Mobile_Detect.php');
    }
    /**
     * init library
     * @require
     */
    public function init() {
        if(class_exists('Mobile_Detect')) {
            return new Mobile_Detect();
        }
    }
}