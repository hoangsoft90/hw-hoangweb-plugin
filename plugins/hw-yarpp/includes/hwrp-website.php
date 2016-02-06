<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 26/05/2015
 * Time: 15:41
 */
/**
 * Class HW_RelatedPosts_Frontend
 */
class HW_RelatedPosts_Frontend{
    /**
     * store instance of the class
     * @var null
     */
    static $instance = null;

    /**
     * migrate vars to skin file
     * @var array
     */
    private $compatible_vars = array();

    public function __construct(){
        if(!$this->check_already()) return;
        //prepare hooks
        $this->setup_actions();

        $this->compatible_vars = array(
            //skin file in hw-taxonomy-post-list plugin
            'cat_posts' => 'wp_query',
            'metaFields' => array(),
            'arrExlpodeFields' => array('title')
        );
    }

    /**
     * check environment already
     * @return bool
     */
    private function check_already() {
        if(function_exists('hw_yarpp_get_option')) return true;
    }
    /**
     * add actions hook
     */
    private function setup_actions() {
        add_action('wp_print_styles',array($this, '_disable_yarpp_stylesheet'));
        add_action('get_footer',array($this, '_disable_yarpp_stylesheet_dequeue_footer_styles'));
        add_filter('hw_yarpp_results', array($this, '_yarpp_results'), 10, 2);
    }
    /**
     * return this class instance
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * get related posts template by current post or specific post ID
     * @param $post_id: post ID or single post data
     */
    public function get_relatedposts_template_by_post($post_id = ''){
        if(!empty($post_id) && is_numeric($post_id)) {
            $post = get_post($post_id);
        }
        if(!isset($post)){
            global $post;   //get current post
        }
        if(empty($post)) return;    //post not found (not single post page)

        //get post type
        $post_type = ($post->post_type);
        $skins = hw_yarpp_get_option('hwrp_skins');
        if(!isset($skins[$post_type]) || !isset($skins[$post_type]['active'])) {
            return; //current post not support related posts skin
        }

        //get post terms
        if(!isset($skins[$post_type]['filter_terms']) || !$skins[$post_type]['filter_terms']){  //filter skin with terms
            $allow_terms = $skins[$post_type]['terms'];
            if(!HW_POST::check_post_terms($post, $allow_terms, false)) return;     //not match requirement

            /*$terms = hwrp_get_all_post_terms($post, array('fields'=> 'slugs'));
            if(is_array($terms))
            foreach($terms as $slug){
                if( !in_array($slug,$allow_terms)) return;   //not match requirement
            }*/
        }
        //override template by checking detail term
        if(class_exists('APF_Related_templates')  ) {
            $result = APF_Related_templates::get_relatedposts_template_by_post($post);  //from HW_HOANGWEB plugin
            if(!empty($result['template']['instance'])) {
                $skins[$post_type]['instance'] = $result['template']['instance'];
                //change active skin
                if(isset($result['template']['hash_skin'] )) $skins[$post_type]['skin'] = $result['template']['hash_skin'];

                $skins[$post_type]['options_data'] = $result;   //detail related data
                //override
                if(isset($result['title'])) $skins[$post_type]['title'] = $result['title'];
                //override skin
                if(isset($result['widget_config'])) $skins[$post_type]['widget_config'] = $result['widget_config'];
                if(isset($result['sidebar'])) $skins[$post_type]['box_skin'] = $result['sidebar'];
                if(isset($result['sidebar_widget_skin'])) $skins[$post_type]['box_widget_skin'] = $result['sidebar_widget_skin'];

                return (object)$skins[$post_type];
            }
        }
        //get related posts skin for current post
        if(!isset($skins[$post_type]['instance'])
            && isset($skins[$post_type]['skin']) && !empty($skins[$post_type]['hwskin_config']) && class_exists('HW_SKIN'))
        {
            $skins[$post_type]['instance'] = HW_SKIN::resume_skin($skins[$post_type]['hwskin_config']); //resume hw_skin instance
            return (object)$skins[$post_type];
        }
    }


    /**
     * since yarpp v4.2.5
     * @param $posts: related posts result
     * @param array $args: keys 'function','args','related_ID'
     */
    public function _yarpp_results($posts, $args){
        global $wp_query;
        $wp_query->posts = $posts;

        /*$cat_posts = $wp_query;
        $arrExlpodeFields = array('title');*/

        /*if(isset($args['args']['compatible_vars']) && is_array($args['args']['compatible_vars'])){
            $this->compatible_vars = array_merge($this->compatible_vars, $args['args']['compatible_vars']);
        }
        $this->compatible_vars = array_merge($this->compatible_vars, $args['args']['skin']->instance->get_migrate());
        foreach($this->compatible_vars as $var => $val){
            $$var = is_string($val) && isset($$val)? $$val : $val;
        }
        */
        //default sidebar params
        $sidebar_params = array(
            'before_widget' => '<div id="%1$s" class="hw-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        );

        //valid posts
        if(!isset($args['args']['skin'] )) {
            return $posts;
        }
        $hwrp = $skin = $args['args']['skin'];
        if(isset($args['args']['hwrp_current'])) {
            $hwrp = (object)$args['args']['hwrp_current'];      //get current post type related data
        }

        //migrate vars
        $migrate_data = $skin->instance->get_migrate();
        extract( $migrate_data);

        //get sidebar wrapper
        if(isset($skin->box_skin) ) {
            global $wp_registered_sidebars;
            $sidebar = $args['args']['skin']->box_skin ;
            $sidebar_widget_skin = $args['args']['skin']->box_widget_skin;  //override sidebar skin box with other
            $sidebar_params = array();

            //extract($sidebar_params);   //extract sidebar param
            if($sidebar_widget_skin) {
                //get change default sidebar skin, here we create 4 holder: sidebar_default, skin1,skin2,skin3
                $skin_data = HW_AWC_Sidebar_Settings::get_sidebar_setting($sidebar_widget_skin,$sidebar);

                HW_SKIN::apply_skin_data($skin_data, array($this, '_hw_skin_resume_skin_data'),array(
                    'sidebar' => $sidebar,
                    'sidebar_widget_skin' => $sidebar_widget_skin,
                    'sidebar_params' => &$sidebar_params
                ));
                $sidebar_params = HW_AWC::format_widget_sidebar_params(null, $sidebar, array(
                    'classname' => 'hw-related-posts hw-awc-override',
                    'sidebar_params' => $sidebar_params
                ));
            }

        }
        //check related content from widget config
        if(!empty($skin->widget_config)) {
            $widget_instance = AWC_WidgetFeature_saveconfig::get_widget_setting_instance($skin->widget_config,
                array(
                    'widget' => 'hw_taxonomy_post_list_widget',
                    'group' => 'main_loop'
                ));
            //valid data
            if(! isset($widget_instance['query_data'])) $widget_instance['query_data'] = 'current_context';     //get global $wp_query
            //override sidebar params from awc feature assign to widget
            if(isset($sidebar_params) && isset($sidebar)) {
                $sidebar_params = HW_AWC::format_sidebar_params($sidebar, $sidebar_params, $widget_instance);
            }
        }

        /**
         * display related content
         */
        if(!isset($widget_instance)) extract($sidebar_params);   //extract sidebar param if not use HWTPL config

        if(isset($before_widget)) echo $before_widget;
        //widget title
        if(isset($before_title)) echo $before_title;
        echo apply_filters('hw_related_posts_title', $hwrp->title);
        if(isset($after_title)) echo $after_title;

        //do action before
        do_action ('hoangweb_before_loop');
        do_action('hw_yarpp_results_before', $hwrp);

        if(isset($widget_instance)) {

            /**
             * output widget, when using the_widget to show widget content,
             * note that you set up & enable sidebar skin at /admin.php?page=hw_sidebar_widgets_settings this mean sidebar apply to yarpp it work for that skin
             * And no related to active sidebar that using on website, which call by function 'dynamic_sidebar' /hw_dynamic_sidebar
             */
            the_widget('HW_Taxonomy_Post_List_widget',($widget_instance), $sidebar_params);

        }
        else {
            if(isset($args['args']['template'])) include($args['args']['template']);

        }
        //do action after
        do_action('hw_yarpp_results_after', $hwrp);
        do_action('hoangweb_after_loop');

        if(isset($after_widget)) echo $after_widget;


        return false;   //disable behavior as default $yarpp->display_related do
    }

    /**
     * HW_SKIN::apply_skin_data callback
     * @param $params
     */
    public function _hw_skin_resume_skin_data($args) {
        extract($args);
        global $wp_registered_sidebars;
        if(isset($sidebar) && isset($skin)) $wp_registered_sidebars[$sidebar]['skin'] = $skin;     //bring skin object into params
        /**
         * override sidebar param
         */
        $sidebar_params = &$args['sidebar_params'];
        if(isset($theme['params']) && is_array($theme['params'])){
            $sidebar_params = array_merge($sidebar_params,$theme['params']);
        }
        $sidebar_params['xxxxx']= '1';  //just test
    }

    /**
     * display related
     * cover from /yet-another-related-posts-plugin/classes/YARPP_Widget.php
     * @param null $reference_ID
     * @param array $args
     * @param bool $echo
     */
    public function hwrp_display_related(){
        global $hw_yarpp;
        if(empty($hw_yarpp)) return;   //valid object

        $instance['domain'] = 'widget';

        /*if ($hw_yarpp->get_option('cross_relate')){
            $instance['post_type'] = $hw_yarpp->get_post_types();
        } else */if (in_array(get_post_type(), (array)$hw_yarpp->get_option('hwrp_allow_post_types'))) {
            $instance['post_type'] = array(get_post_type());
        } else {
            $instance['post_type'] = array('post'); //default allow post type
        }
        //load skin base current post
        $skin = $this->get_relatedposts_template_by_post(get_post_type());
        $instance['skin'] = $skin;      //hw_skin object
        $instance['hwrp_current'] = $skin;

        if(!empty($skin) && empty($skin->widget_config)) {   //not
            $file = $skin->instance->get_skin_file($skin->skin);

            $theme_setting = $skin->instance->get_file_skin_setting();  //don't need pass skin hash string because you invoke active skin file by method get_skin_file
            if(file_exists($file)) {
                $theme = array();
                $theme['styles'] = array();
                $theme['scripts'] = array();

                if(file_exists($theme_setting)) include($theme_setting);

                $instance['template'] = $file;  //change template for displaying yarpp related

                //get skin compatible with other skin, update from HW_SKIN::get_migrate
                //if(isset($theme['compatible_vars'])) $instance['compatible_vars'] = $theme['compatible_vars'];

                //valid
                if(!is_array($theme['styles'])) $theme['styles'] = array();
                if(!is_array($theme['scripts'])) $theme['scripts'] = array();

                if(count($theme['styles']) || count($theme['scripts'])) {
                    $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                }
            }
        }

        $hw_yarpp->display_related(null, $instance, false);    //we don;t print output of this method from yarpp
    }

    /**
     * disable yarpp stylesheet from header styles
     */
    public function _disable_yarpp_stylesheet(){
        $disable_yarpp_css =  hw_yarpp_get_option('hwrp_disable_yarpp_css');
        if($disable_yarpp_css) {
            wp_dequeue_style('hw-yarppWidgetCss');
            wp_deregister_style('hw-yarppRelatedCss');
        }
    }

    /**
     * dequeue footer styles & scripts
     */
    public function _disable_yarpp_stylesheet_dequeue_footer_styles(){
        $disable_yarpp_css =  hw_yarpp_get_option('hwrp_disable_yarpp_css');
        if($disable_yarpp_css) wp_dequeue_style('hw-yarppRelatedCss');
    }
    /**
     * compatible with other skin file, if using $this keyword
     * @param $func
     * @param $arg
     */
    public function __call($func, $arg){

    }
}