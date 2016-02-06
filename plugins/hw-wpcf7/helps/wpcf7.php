<?php
if(class_exists('HW_HELP')):
class HW_HELP_WPCF7 extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance
    }

    /**
     * default help tab
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file());
        }
    }

    /**
     * contact_page help
     */
    public function help_contact_page_callback () {
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file());
        }
    }
    /**
     * this call by parent class
     */
    function register_admin_menu(){
        //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
        //add_action( self::load_admin_page_hook_slug('wpcf7') , array( $this, 'help_tab' ) );
        //add_action( 'load-contact_page_hw_wpcf7_settings' , array( $this, 'help_tab' ) );
        //new approarch
        $this->add_help_content(self::load_admin_page_hook_slug('wpcf7') ,'admin');
        //if(HW_HOANGWEB::is_current_screen('hw_wpcf7_settings')) {
            $this->add_help_content('load-contact_page_hw_wpcf7_settings' ,'contact_page', 'Cài đặt');
        //}
    }
    static function __init(){

    }
}
endif;