<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/12/2015
 * Time: 22:49
 */
include_once ('autoload.php');
/**
 * Class HW_Widget_Features_Setting
 * cover from modules-manager.php
 */
if(class_exists('AdminPageFramework')):
class HW_Widget_Features_Setting extends HW_AdminPageFramework {
    /**
     * page slug constant
     */
    const PAGE_SLUG = 'hw_widget_features';
    /**
     * @var array
     */
    private $tabs = array();
    /**
     * store menu data for feature setting tab
     * @var
     */
    public static  $menus = array();
    /**
     * @var array
     */
    private static $widgets_feature = array();
    /**
     * main class constructor
     */
    public function __construct() {
        parent::__construct();
        add_action('do_before_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug('modules-config'), array(&$this, '_do_before_tab_hook'));
    }
    /**
     * @hook do_before_{page-slug}_{modules-config tab}
     */
    public function _do_before_tab_hook() {
        //HW_Libraries::enqueue_jquery_libs('menus/superfish');
    }
    /**
     * setup form fields
     */
    public function setUp() {
        $this->setRootMenuPage('(H) Widgets' , HW_HOANGWEB_URL. '/images/ie-gear-icon.png');
        // Add the sub menus and the pages/ addSubMenuItems
        $this->addSubMenuItem( //addSubMenuItems
            array(
                'title'    =>    'Tính năng Widgets',        // the page and menu title
                'page_slug'    =>    self::PAGE_SLUG         // the page slug
            )
        );
        //define tabs, for main tab
        $this->addInPageTabs(
            self::PAGE_SLUG,    // set the target page slug so that the 'page_slug' key can be omitted from the next continuing in-page tab arrays.
            array(
                'tab_slug'    =>    'general',    // avoid hyphen(dash), dots, and white spaces
                'title'        =>    __( 'Tổng quan' ),
            )

        );
        $register_features = HW_AWC_WidgetFeatures::get_all_features();
        foreach(HW_AWC_WidgetFeatures::get_features_data() as $name => $widgets) {
            foreach($widgets as $id=>$widget) {
                $feature = $widget['class'];    //widget feature object
                if($feature && $feature->_get_option('enable_tab_settings')) {
                    $this->addInPageTab( array(
                        'tab_slug' => $name,
                        'title' => $register_features[$name],//.'('.$widget['widget']->id_base.'-'.$widget['widget']->number.')'
                    ));
                    // load + page slug + tab slug
                    add_action( 'load_' . self::PAGE_SLUG . '_' . self::valid_tab_slug($name), array( $feature, '_replyToAddFormElements' ) );
                    //triggered before rendering the page.
                    add_action('do_before_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($feature, 'do_before_tab_hook'));

                    // triggered in the middle of rendering the page.
                    add_action('do_' .  self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($feature, 'do_tab_hook'));

                    //triggered after rendering the page
                    add_action('do_after_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($feature, 'do_after_tab_hook'));
                    //receives the output of the middle part of the page including form input fields.
                    add_filter('content_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($feature, 'content_tab_filter'));

                    //receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
                    add_filter('validation_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($feature, '_validation_tab_filter'));
                }
                break;  //use same setting  tab for all of widget that own this feature
            }
        }
        //get all features
        /*foreach(HW_AWC_WidgetFeatures::get_all_features() as $feature => $item){

        }*/
        $this->setInPageTabTag( 'h3' );        // sets the tag used for in-page tabs
    }

    /**
     * add module
     * @param $name
     * @param $inst
     */
    public static function add_widget_feature($name, $inst) {
        self::$widgets_feature[$name] = $inst;
        $feature_name = $inst->option('feature_alias');
        $capability = 'manage_options';
        //add submenu under global settings menu
        if($inst->option('enable_tab_settings')) {
            self::add_feature_tab_submenu_page($name, array(
                empty($feature_name)? $name : $feature_name,
                $capability,
                self::get_feature_setting_page($name)
            ));
        }

    }
    /**
     * get all modules or specific module by slug or class
     * @param $name
     * @return array
     */
    public static  function get_widget_feature($name='') {
        if($name && isset(self::$widgets_feature[$name])) { //get feature obj by slug
            return self::$widgets_feature[$name];
        }
    }
    /**
     * return setting page for module
     * @param $module
     */
    public static function get_feature_setting_page($feature) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'tab'       => self::valid_tab_slug($feature),
                    'page'          => urlencode( self::PAGE_SLUG ),
                    'w-feature'        => urlencode( $feature ),
                    #'module_name'   => urlencode( $item['sanitized_plugin'] ),
                    #'module_source' => urlencode( $item['url'] ),
                    'tab-nonce' => urlencode( wp_create_nonce( 'tab-nonce' ) ),
                ),
                #network_admin_url( 'options-general.php' )
                admin_url( 'admin.php' )
            ),
            'w-feature-settings'
        );
    }
    /**
     * add module tab menu
     * @param $module
     * @param $menu
     */
    public static function add_feature_tab_submenu_page($feature, $menu) {
        //valid
        if(!HW_AWC_WidgetFeatures::get_features_data($feature)) return ;    //module not found
        if(!isset(self::$menus[$feature]['features_tab_menus'])) {
            self::$menus[$feature]['features_tab_menus']= array();
        }
        if(!isset(self::$menus[$feature]['features_tab_menus'][$feature])) {
            self::$menus[$feature]['features_tab_menus'][$feature] = $menu;
        }

    }

    /**
     * auto hook callback for this admin page class
     * @hook script_{page-slug}_{modules-config tab}
     */
    public function script_hw_widget_features_general() {

        echo "<script>

            </script>";
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     * @hook do_{page-slug}
     */
    public function do_hw_widget_features() {

    }
    /**
     * The pre-defined validation callback method.
     *
     * Notice that the method name is validation_{instantiated class name}_{field id}. You can't print out inside callback but stored in session variale instead
     *
     * @param    string|array    $sInput        The submitted field value.
     * @param    string|array    $sOldInput    The old input value of the field.
     */
    public function validation_HW_Widget_Features_Setting( $sInput, $sOldInput ) {
        return $sInput;
    }
}
if(is_admin() /*|| class_exists('HW_CLI_Command', false)*/) {
    new HW_Widget_Features_Setting;
}
endif;