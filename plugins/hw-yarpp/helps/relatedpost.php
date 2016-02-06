<?php
if(class_exists('HW_HELP')):
class HW_HELP_RELATEDPOST extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance

    }

    /**
     * major help callback
     */
    public function help_callback(){
        if($this->help_static_file()) {
            echo $this->read_help_file($this->help_static_file());
        }
    }
    /**
     * this call by parent class
     */
    function register_admin_menu(){

        //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
        $this->add_help_content( self::load_settings_page_hook_slug('yarpp') );     //new approarch
    }
    static function __init(){

    }
}
endif;