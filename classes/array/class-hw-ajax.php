<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/10/2015
 * Time: 08:36
 */
/**
 * Class HW_Ajax
 * @example includes/library/field-type/apf-upload-field/upload.php
 */
class HW_Ajax extends hwArray {
    /**
     * @var array
     */
    protected $api_result = array();
    public function __construct() {

    }

    /**
     * create new class instance
     * @return HW_Ajax
     */
    public static function create() {
        return new self();
    }

    /**
     * shortcut
     * @param $data
     */
    public static function result($data) {
        $ajax = self::create();
        echo $ajax->add_data('data', $data);
    }
    /**
     * add message
     * @param $msg
     */
    public function message($msg) {
        if(is_string($msg) || is_numeric($msg)) $this->add_data('messages', $msg, true);
        return $this;
    }

    /**
     * @param $stt
     */
    public function status($stt) {
        $this->add_data('status', $stt);
        return $this;
    }

    /**
     * add to ajax result
     * @param $key
     * @param $value
     * @param bool $append wether is array of data
     */
    public function add_data($key, $value, $append= false) {
        if($append ==true && !isset($this->api_result[$key])) $this->api_result[$key] = array();
        if($append ==true) $this->api_result[$key][] = $value;
        else $this->api_result[$key] = $value;
        return $this;
    }

    /**
     * get data given by id or return all data in ajax instance
     * @param $item
     */
    public function get_data($item='') {
        return $item? (isset($this->api_result[$item])? $this->api_result[$item]: '') :$this->api_result;
    }
    /**
     * cast class instance to string
     */
    public function __toString() {
        foreach($this->api_result as $key => $val) {
            #if(is_array($val)) $this->api_result[$key] = join(' ', $val);
        }
        return $this->json_output($this->api_result);
    }
}