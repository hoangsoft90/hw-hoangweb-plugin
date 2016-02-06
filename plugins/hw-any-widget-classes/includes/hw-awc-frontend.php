<?php
#/root
/**
 * Class HW_AWC_Frontend
 */
class HW_AWC_Frontend{
    /**
     * storing class instance
     * @var
     */
    private static $instance;

    public function __construct(){
        $this->setup_actions();

    }

    /**
     * get active dynamic sidebars setting
     * @return array
     */
    public static function get_active_sidebars_settings(){
        $result = get_transient('hw_dynamic_sidebars_settings');
        if(!$result) {
            $args = array(
                'post_type' => HWAWC_Sidebars_Manager::post_type,
                'showposts' => -1,
                'orderby' => 'menu_order',
                'order' => 'asc',
                'meta_key' => 'enable',
                'meta_query' => array(
                    //list enable sidebar settings
                    array(
                        'key'       => 'enable',
                        'value'     => '1',
                        /*'compare'   => '==',
                        'type'      => 'NUMERIC',*/
                    ),
                )
            );
            $result = array();
            $query = new WP_Query($args);
            while($query->have_posts()){
                $query->the_post();//$query->next_post();
                $query_data_and = get_post_meta(get_the_ID(), 'query_data_and', true);
                $query_data_or = get_post_meta(get_the_ID(), 'query_data_or', true);

                $result[get_the_ID()] = array(
                    'title' =>get_the_title(),
                    'query_data_and' => $query_data_and,
                    'query_data_or' => $query_data_or,
                    'post_ID' => get_the_ID()
                );
            }
            $query->reset_postdata();   //reset query
            set_transient('hw_dynamic_sidebars_settings', $result);     //set cache fetch from database
        }

        return $result;
    }

    /**
     * init hook
     */
    public function _init() {


    }
    /**
     * get current class instance
     * @return HW_AWC_Frontend
     */
    public static function getInstance() {
        if(!self::$instance) self::$instance = new self();  //create first of all instance
        return self::$instance;
    }
    /**
     * setup hooks
     */
    private function setup_actions() {
        //add_filter('sidebars_widgets', array($this, '_current_sidebar_switch') );     //do it in custom dynamic_sidebar
        add_action('init', array($this, '_init'));
        //add_filter('widget_title',array($this, '_widget_title_link'));    //moved to feature: title_link
    }

    /**
     * whether replace a new sidebar
     * @param $bind_data
     * @return bool
     */
    public static function check_sidebar_changing($bind_data, $relation = 'AND') {
        return HW__Template_Condition::check_template_changing($bind_data, $relation);
    }

    /**
     * switching sidebar (no longer use)
     * @param $widgets
     * @return mixed
     */
    public function _current_sidebar_switch($widgets) {

        if(is_admin())
            return $widgets;

        $key = 'sidebar-1'; // the sidebar you want to change!

        if(isset($widgets[$key]) && is_user_logged_in() && isset($widgets['logged-in']))
            $widgets[$key] = $widgets['logged-in'];

        return $widgets;
    }
}
if(!is_admin()){
    new HW_AWC_Frontend();
}
