<?php

/**
 * Class HW_Screen_Option
 */
class HW_Screen_Option extends HW_Core{
    /**
     * @var array
     */
    protected static $options_manager = array();
    /**
     * class instance
     */
    static $instance;

    /**
     * @var string
     */
    protected $option_group = '';

    /**
     * screen options definition
     * @var null
     */
    private $options = array();
    /**
     * @var null
     */
    private $page_slug = null;

    /**
     * class constructor
     */
    public function __construct( $option_group= null, $page_slug='') {

        if($page_slug) $this->page_slug = $page_slug;   //apply screen option to this page
        //note option_group should be valid as variable name
        if($option_group) {
            $this->option_group = HW_Validation::valid_apf_slug($option_group);     //set screen option group
            self::$options_manager[$option_group] = $this;  //add to array
        }

        add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
        add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
        add_filter('screen_settings', array($this,'_screen_settings'), 10, 2);
        /*
        //access option
        $screen = get_current_screen();
        // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option');
        // get the default value if none is set
        $per_page = $screen->get_option( 'per_page', 'default' );
        */
    }

    /**
     * return screen options group
     * @param $group
     * @return mixed
     */
    public static function get($group) {
        if(isset(self::$options_manager[$group])) return self::$options_manager[$group];
    }
    /**
     * get option screen
     * @param $option
     * @param $default default value
     * @return string
     */
    public function get_option($option, $default='') {
        // get the current user ID
        $user = get_current_user_id();

        //access option
        $screen = get_current_screen();
        // retrieve the "" option
        $screen_option = $screen->get_option( $option, 'option');
        // retrieve the value of the option stored for the current user
        $value = get_user_meta($user, $screen_option, true);
        if ( is_null ( $value)  ) {
            // get the default value if none is set
            $value = $screen->get_option( $option, 'default' );
        }
        return $value? $value : $default;
        #return $default;
    }
    /**
     * @hook set-screen-option
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public  function set_screen( $status, $option, $value ) {
        if ( $this->option_group == $option )
            return $value;
    }

    /**
     * admin menu
     * @hook admin_menu
     */
    public function plugin_menu() {

        /*$hook = add_menu_page(
            'Sitepoint WP_List_Table Example',
            'SP WP_List_Table',
            'manage_options',
            'wp_list_table_class',
            array( $this, 'plugin_settings_page' )
        );*/
        if(!$this->page_slug && class_exists('HW_HELP')) {
            $this->page_slug = HW_HELP::load_current_page_hook_slug('');
        }
        if($this->page_slug) add_action( "load-{$this->page_slug}", array($this, '_screen_option' ) );

    }

    /**
     * set options
     * @param $options
     */
    public function add_options($options) {
        if(!is_array($this->options)) $this->options = array();
        if(!empty($options)) $this->options = array_merge($this->options,$options);
    }

    /**
     * Screen options
     * @hook load-{$page_slug}
     */
    public function _screen_option() {
        add_screen_option('hw_screen_name', $this->option_group);   //save screen name as identifier
        /*
        ->sample
        $option = 'per_page';
        $args   = [
            'label'   => 'Module Status',
            'default' => 5,
            'option'  => 'hw_module_status'
        ];*/
        foreach($this->options as $option_name => $args) {
            $args['option'] = ($this->option_group);  //add option screen group
            add_screen_option( $option_name, $args );
        }
        do_action('hw-add-screen-options');
        #$this->customers_obj = new Customers_List();
    }

    /**
     * @hook screen_settings
     * @param $current
     * @param $screen
     * @return string
     */
    public function _screen_settings($current, $screen) {
        //only for current screen options
        if($screen->get_option('hw_screen_name') !== $this->option_group) return $current;
        /*$desired_screen = convert_to_screen('plugins.php');
        if ( $screen->id == $desired_screen->id ){
            $current .= "Hello WordPress!";
        }*/
        return $current.'<br/>'. $this->_option('addition_text');
    }

    /**
     * adding more text to screen option
     * @param $text
     */
    public function addition_text($text) {
        $this->_option('addition_text', $text);
    }
    /**
     * Singleton instance
     * @param $page_slug
     * @param $options
     */
    public static function get_instance( $options= null, $page_slug='') {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self( $options, $page_slug);

        }

        return self::$instance;
    }
}