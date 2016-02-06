<?php
if(class_exists('HW_HELP')):
class HW_HELP_NAVMENU extends HW_HELP{
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
     * help for qtranslate plugin
     */
    public function help_qtrans_callback() {

        echo self::read_help_file($this->help_static_file('mqtranslate.html'));

    }
    /**
     * this call by parent class
     */
    function register_admin_menu(){
        global $pagenow;
        //$my_admin_page = add_options_page(__('My Admin Page', 'map'), __('My Admin Page', 'map'), 'manage_options', 'map');
        //new approarch
        if($pagenow == 'admin.php' && isset($_GET['page'])
            && ($_GET['page'] == 'hw_navmenu_settings_page' ) ) {
            $this->add_help_content(self::load_current_page_hook_slug() );  //please check seem to not work
        }
        $this->add_help_content(self::load_admin_page_hook_slug('hoangweb-theme-options'), 'qtrans', 'Hướng dẫn Menu');
    }
    static function __init(){

    }
}
endif;