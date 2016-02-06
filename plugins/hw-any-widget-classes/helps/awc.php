<?php
if(class_exists('HW_HELP')):
class HW_HELP_AWC extends HW_HELP{

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
     * show help content
     */
    public function help_dynamic_sidebar_callback(){
        if($this->help_static_file()) {
            echo self::read_help_file($this->help_static_file('dynamic-sidebars.html'));
        }
    }
    /**
     * this call by parent class
     */
    public function register_admin_menu(){
       global $pagenow;
        //if(HW_HOANGWEB::is_current_screen('widgets')){    //not working
            //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
            $this->add_help_content(self::load_core_page_hook_slug('widgets'));     //add help tab in core widgets page
            if($pagenow == 'post.php' && isset($_GET['post']) && is_numeric($_GET['post'])
                && get_post_type($_GET['post']) == /*HWAWC_Sidebars_Manager::post_type*/'hw_mysidebar')
            {
                $this->add_help_content( self::load_core_page_hook_slug('post'), 'dynamic_sidebar','Tạo sidebar động');
            }
        //}

    }

    static function __init(){

    }
}
endif;