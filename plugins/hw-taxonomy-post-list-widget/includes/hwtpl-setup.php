<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Taxonomy_Post_List
 */
class HW_Taxonomy_Post_List{
    /**
     * @var array
     */
    private $serialize_data = array();
    //localize data callback
    public $localize_object_callback = null;

    function __construct() {
        $this->serialize_data = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'home_url' => home_url(),
            'images_url' => HWTPL_PLUGIN_URL.('images'),
            'query_type' => array('current_context','filter_query')
        );
        //init actions hooks
        $this->setup_hooks();
    }

    /**
     * register hooks
     */
    private function setup_hooks() {
        /** Tell WordPress to run hw_cplw_scripts_method() when the 'wp_enqueue_scripts' hook is run. */
        add_action('wp_enqueue_scripts', array($this, 'hw_cplw_scripts_method'));
        add_action('admin_enqueue_scripts', array($this, 'hw_cplw_admin_scripts'));

        /** Tell WordPress to run hw_cplw_scripts_method() when the 'hw_cplw_stylesheet' hook is run. */
        add_action( 'wp_enqueue_scripts', array($this, 'hw_cplw_stylesheet' ));

        add_action( 'admin_head', array($this, 'hw_cplw_required_css' ));
    }
    /**
     * hw_cplw_scripts_method() function includes required jquery files.
     *
     */
    function hw_cplw_scripts_method() {
        wp_enqueue_script( 'jquery' );  //enqueue jquery wp core
        wp_enqueue_script('jquery-ui',HWTPL_PLUGIN_URL. ('/js/jquery-ui-1.10.3.custom.min.js'),array('jquery'),0,true);    //jquery-ui
        #wp_enqueue_script('jquery-ui');

        wp_enqueue_script('cycle_js', HWTPL_PLUGIN_URL.('/js/jquery.cycle.all.js'));
        wp_enqueue_script('hwtpl_frontend_js', HWTPL_PLUGIN_URL.('/js/js.js'));
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'hwtpl_frontend_js', '__hwcpl_object', $this->serialize_data);
    }

    /**
     * @param $hook
     */
    function hw_cplw_admin_scripts($hook) {
        if( 'widgets.php' != $hook ) {
            // Only applies to dashboard panel
            return;
        }
        wp_enqueue_style('cplw_admin', HWTPL_PLUGIN_URL.('/css/hwtpl-admin.css'));
        #wp_enqueue_script('cplw_myjs', HWTPL_PLUGIN_URL.('/js/admin_js.js'));
        wp_enqueue_script('hw_cplw_myjs', HWTPL_PLUGIN_URL.('/js/test.js') /*,array('cplw_myjs')*/);

        //ajax link to get custom fields base post type
        $get_cf_pt_ajax = admin_url('admin-ajax.php?action=load_customfields_from_pt&nonce='.wp_create_nonce("load_customfields_from_pt_nonce"));

        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        $this->serialize_data['fetch_customfields_bytype_URL'] = $get_cf_pt_ajax;
        //get more serialize_data
        if(is_callable($this->localize_object_callback ) ) {
            $this->serialize_data = call_user_func($this->localize_object_callback, $this->serialize_data);
        }
        wp_localize_script( 'hw_cplw_myjs', '__hwcpl_object', $this->serialize_data);

        wp_enqueue_script('cplw_myjs', HWTPL_PLUGIN_URL.('/js/admin_js.js'));
    }

    /**
     * hw_cplw_stylesheet() function includes required css files.
     *
     */
    function hw_cplw_stylesheet() {
        wp_register_style( 'main-style', HWTPL_PLUGIN_URL.('/css/main.css') );
        wp_enqueue_style( 'main-style' );
    }


    /**
     * hw_cplw_required_css() function includes required css files for admin side.
     *
     */
    function hw_cplw_required_css() {
        wp_register_style( 'cplw_css', HWTPL_PLUGIN_URL.('/css/basic.css') );
        wp_enqueue_style( 'cplw_css' );

    }
}
