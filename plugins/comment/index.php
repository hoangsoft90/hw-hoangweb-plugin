<?php
/**
 * Module Name: Comments
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/10/2015
 * Time: 21:37
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once ('shortcode.php');
/**
 * Class HW_Module_Comments
 */
class HW_Module_Comments extends HW_Module {
    /**
     * module settings
     * @var null
     */
    public $options = null;

    public function __construct() {
        HW_HOANGWEB::load_class('HW_Validation');
        //enable tab settings
        $this->enable_tab_settings();
        $this->enable_submit_button();

        //wp hooks
        $this->_setup_actions();
    }

    /**
     * init hooks
     */
    public function _setup_actions() {
        add_action('wp_head', array($this, '_wp_head'));
        add_action('wp_loaded', array($this, '_init'));
    }

    /**
     * @hook init
     */
    public function _init() {
        if(!is_admin()) $this->options = self::get()->get_values();
    }
    /**
     * @hook wp_head
     */
    public function _wp_head() {
        $facebook = self::get()->get_tab('facebook');
        if(!empty($facebook) && $facebook->get_field_value('appId') ) {
            echo "<meta property='fb:app_id' content='".$facebook->get_field_value('appId')."' />";
        }
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {

        //add tabs
        $facebook_tab = $this->add_tab( array(
            'id'=>'facebook',
            'title' => 'Facebook',
            'description' => 'Facebook comment.'
        ));
        $gplus_tab = $this->add_tab( array(
            'id' => 'googleplus',
            'title' => 'Google Plus'
        ));


        $facebook_tab->addField(array(
            'field_id' => 'appId',
            'type' => 'text',
            'title'=>'Facebook App ID',
            'description' => 'Tạo Facebook App và lấy Id <a href="https://developers.facebook.com" target="_blank">tại đây</a>.'
        ));
        $facebook_tab->addFields(
            array(
                'field_id' => 'html5',
                'type' => 'checkbox',
                'title' => "Enable HTML5"
            ),
            array(
                'field_id' => 'width',
                'type' => 'text',
                'title'=>'Width',
                'default' => '100%',
                'description' => 'default is 100%. Keep at this to ensure the comment box is responsive'
            ),

            array(
                'field_id' => 'num_posts',
                'type' => 'text',
                'title'=> 'Number of Comments',
                'default' => '5'
            )
            ,array(
                'field_id' => 'colorscheme',
                'type' => 'select',
                'title' => 'colorscheme',
                'label' => array('light' => 'light', 'dark'=>'dark')
            ),
            array(
                'field_id' => 'order_by',
                'type' => 'select',
                'title' => 'order_by',
                'label' => array(
                    'social' => 'social',
                    'reverse_time' => 'reverse_time',
                    'time' => 'time'
                ),

            ),
            array(
                'field_id' => 'Title',
                'type' => 'text',
                'title' => 'Title'
            ),
            array(
                'field_id' => 'comment_text',
                'type' => "text",
                'title' => 'Comment text'
            ),
            array(
                'field_id' => 'show_count',
                'type' => 'checkbox',
                'title' => 'Show Comment Count'
            )
        );
        $gplus_tab->addField(array(
            'field_id' => 'field2',
            'type' => 'text',
            'title'=>'46534464fhf'
        ));

        /*$oAdminPage->addSettingFields(
            $gplus_tab,
            array(
                'field_id' => $this->create_field_name('abc'),
                'type' =>'text',
                'title' => 'sdfsdf'
            )
        );*/

    }

    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        foreach(array('xxx') as $option) {
            if(isset($values[$option])) $values[$option] = $values[$option]? true:false;
        }

        return $values;
    }
}
add_action('hw_modules_load', 'HW_Module_Comments::init');