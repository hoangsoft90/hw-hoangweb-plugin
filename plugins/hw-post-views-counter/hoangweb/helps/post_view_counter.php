<?php
if(class_exists('HW_HELP')):
class HW_HELP_POST_VIEW_COUNTER extends HW_HELP{
    function __construct(){
        parent::__construct();  //load parent instance

    }
    /**
     * Add the help tab to the screen.
     */
    public function help_tab(){
        $screen = get_current_screen();

        // documentation tab
        $screen->add_help_tab( array(
                'id'    => 'hw-help-post-view-counter',
                'title' => __( 'Hướng dẫn' ),
                //'content'   => "<p>sdfsgdgdfghfhfgh</p>",
                'callback' => array($this, 'help_callback')
            )
        );
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
        //show help when at options-general.php?page=hw_settings page
        add_action( self::load_settings_page_hook_slug('post-views-counter') , array( $this, 'help_tab' ) ); //get current page
    }
    static function __init(){

    }
}
endif;