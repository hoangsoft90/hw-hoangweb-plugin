<?php
/**
 * help class for this plugin
 */
if(class_exists('HW_HELP')):
/**
 * HW_HELP_HWTPL class
 */
class HW_HELP_HWTPL extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance

    }

    /**
     * help callback
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file());
        }
    }
    /**
     * this call by parent class
     */
    public function register_admin_menu(){
        //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
        //add_action( self::load_settings_page_hook_slug('breadcrumb-navxt') , array( $this, 'help_tab' ) );    //old method
        $this->add_help_content(self::load_core_page_hook_slug('widgets') ,'hwtpl',"HWTPL help");
    }
    static function __init(){

    }
}
endif;