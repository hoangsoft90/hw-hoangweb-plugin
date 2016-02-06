<?php
if(class_exists('HW_HELP')):
class HW_HELP_LIVECHAT extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance

    }


    /**
     * show help content
     */
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
        //add_action( ('load-admin_page_hoangweb-theme-options_hw_livechat_settings') , array( $this, '_help_tab' ) );    //try but not work
        //add_action( self::load_current_page_hook_slug() , array( $this, 'help_tab' ) ); //get current page
        $this->add_help_content(('load-admin_page_hoangweb-theme-options_hw_livechat_settings'));   //new method
    }
    static function __init(){

    }
}
endif;