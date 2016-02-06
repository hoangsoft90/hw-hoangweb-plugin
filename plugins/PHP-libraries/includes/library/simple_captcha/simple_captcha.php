<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/07/2015
 * Time: 08:09
 */
class HW_Simple_Captcha extends HW_PHP_Library {
    private $options = null;

    public function __construct() {
        parent::__construct();
        @session_start();

        $this->options = array(
            'min_length' => 5,
            'max_length' => 5,
            #'backgrounds' => array('white-wave.png','grey-sandbag.png'),
            #'fonts' => array('font.ttf'),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 28,
            'max_font_size' => 28,
            'color' => '#666',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );
    }

    /**
     * include this library
     * @require
     */
    public function _load_library_cb() {
        @session_start();
        include_once(HW_LIBRARIES_PATH . '/simple-php-captcha-master/simple-php-captcha.php');
    }

    /**
     * init library
     * @require
     */
    public function init() {
        @session_start();
        if(function_exists('simple_php_captcha')) $_SESSION['captcha'] = simple_php_captcha($this->options);
        return isset($_SESSION['captcha'])? $_SESSION['captcha'] : '';
    }

    /**
     * captcha validation
     * @param $user_captcha
     * @return bool
     */
    public static function validate($user_captcha) {
        if(isset($_REQUEST['hw_captcha']) && $user_captcha == $_REQUEST['hw_captcha']) {
            return true;
        }
        return false;
    }

    /**
     * display captcha
     */
    public function display_captcha() {
        $captcha = $this->object;
        if($captcha) {
            echo '<img src="' .$captcha['image_src']. '" class="captcha"/>' ;
            echo '<input type="text" value="" name="hw_captcha"/>'; //user input captcha see from image
        }
    }
}
