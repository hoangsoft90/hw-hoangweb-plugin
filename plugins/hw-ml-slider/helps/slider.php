<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 20/10/2015
 * Time: 22:13
 */
if(class_exists('HW_HELP')):
    class HW_HELP_Slider extends HW_HELP{
        function __construct(){
            parent::__construct();  //load parent instance

        }

        public function help_callback(){
            if($this->help_static_file()) {
                echo self::read_help_file($this->help_static_file());
            }
        }
        /**
         * this call by parent class
         */
        function register_admin_menu(){

            //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
            //add_action( self::load_settings_page_hook_slug('pagenavi') , array( $this, 'help_tab' ) );  //old
            // new way
            $this->add_help_content( self::load_admin_page_hook_slug('hw-metaslider'), 'hw-metaslider','Sliders');

        }
        static function __init(){

        }
    }
endif;