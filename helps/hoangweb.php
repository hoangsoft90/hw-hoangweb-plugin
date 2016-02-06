<?php
if(class_exists('HW_HELP')):
class HW_HELP_HOANGWEB extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance

    }

    /**
     * show help content
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file('hoangweb-settings.html'));
        }
    }
    /**
     * this call by parent class
     */
    function register_admin_menu(){

        //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
        //show help when at options-general.php?page=hw_settings page
        //add_action( self::load_settings_page_hook_slug('hw_settings') , array( $this, 'help_tab' ) ); //get current page
        $this->add_help_content(self::load_settings_page_hook_slug('hw_settings') );    //new way
    }
    static function __init(){

    }
}
endif;