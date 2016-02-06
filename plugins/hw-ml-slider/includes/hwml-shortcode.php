<?php
/**
 * Class HWMLShortcode_Manager
 */
class HWMLShortcode_Manager{
    /**
     * construct
     */
    function __construct(){
        if($this->check_already()) $this->setup_actions();
    }

    /**
     * already work
     * @return bool
     */
    public function check_already(){
        return class_exists('APF_hw_skin_Selector_hwskin');
    }

    /**
     * setup hooks
     */
    public function setup_actions(){
        //add hwml_slider shortcode
        add_shortcode('hwml_slider', array($this, '_hwml_shortcode_slider'));
    }
    /**
     * add shortcode to display slider on website
     * @param array $attsa
     */
    public function _hwml_shortcode_slider($atts = array()){
        $default = array(
            'id' => false,'restrict_to' => false
        );
        extract(shortcode_atts($default,$atts,'hwml_slider'));
        if ( ! $id ) {
            return false;
        }
    // handle [hwml_slider id=123 restrict_to=home]
        if ($restrict_to && $restrict_to == 'home' && ! is_front_page()) {  //front page
            return;
        }
        if ($restrict_to && $restrict_to != 'home' && ! is_page( $restrict_to ) ) { //for page
            return;
        }
        $id = trim($id,'&quot;') ;
        $post =  get_post($id); //_print(get_post_custom($id));
        $slider_id = get_post_meta($id,'pick_slider',true);    //meta slider id
        $slider_theme = get_post_meta($id, 'slider_theme', true);   //slider theme
        $source = get_post_meta($id, 'slideshow_source', true); //choose slideshow source

        if(!$slider_id && !$slider_theme){
            throw new Exception('Lỗi custom field (hwml-shortcode)?');
            return ;
        }
        //instance HW_SKIN
        $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($slider_theme); //current skin file
        $file = ($skin->instance->get_skin_file($skin->hash_skin));
        $skin_config = $skin->instance->get_config(false);
        $skin_info = $skin->instance->get_active_skin_info();
        $options_config = $skin->instance->get_file_skin_options($skin->hash_skin); //theme options configuration
        $theme_setting = $skin->instance->get_file_skin_setting(); //(new HW_SKIN)->get_file_skin_setting()

        //theme options
        $user_theme_options = isset($slider_theme['skin_options']) && is_array($slider_theme['skin_options'])?  $slider_theme['skin_options']: array();
        /*
        //load skin resource
        if(file_exists($options_config)) include($options_config);   //include define theme options
        if(file_exists($theme_setting)) include($theme_setting);        //theme setting

        if( isset($theme_options) && isset($theme)){
            $default = isset($theme['options']) ? $theme['options'] : array();

            $result = HW_SKIN::get_skin_options($user_theme_options, $default,$theme_options);
            $user_theme_options = array_merge($user_theme_options, $result);
        }
        */
        //new way
        $user_theme_options = HW_SKIN::merge_skin_options_values($user_theme_options, $theme_setting, $options_config);

        //get default template for this skin base slideshow source
        $default_template = $skin_info[0].'/'.$skin_info[1].trim($skin_config['skin_name'], '.php').'_template_'.$source.'.php';      //default template
        if(file_exists($default_template) && isset($user_theme_options['template_file']) && $user_theme_options['template_file'] == APF_hw_skin_Selector_hwskin::DEFAULT_TEMPLATE){
            $file  = $default_template; //change template file
        }
        //get user choose template for this skin
        elseif(isset($user_theme_options['template_file']) && $user_theme_options['template_file'] != APF_hw_skin_Selector_hwskin::DEFAULT_TEMPLATE){
            $file = base64_decode($user_theme_options['template_file']);
        }

        if(file_exists($file)){
            //get data
            $show_title = get_post_meta($id, 'show_title', true);
            $data = array();
            //valid data
            if(isset($user_theme_options['template_file'])) unset($user_theme_options['template_file']) ;

            //get sides from metaslider
            if($source == 'metaslider'){
                $query = $this->get_mlslider_metaslides($slider_id);
                $data['posts'] = Timber::get_posts($query->query_vars);
            }
            //get sides from posts
            if($source == 'posttype'){
                //get query post types
                $posttype = get_post_meta($id, 'source_posttype', true);     //get active post types
                $current_post = get_post_meta($id, 'current_post', true);     //get active post types
                $only_attachment = get_post_meta($id, 'only_attachments', true);     //get only attachments

                $args = array(
                    'order' => 'ASC',
                    'posts_per_page' => -1
                );
                $query = null;
                $images = array();

                if($current_post) {
                    global $post;
                    if(is_single())
                    $images = get_children( array (
                        'post_parent' => $post->ID,
                        'post_type' => 'attachment',
                        'post_mime_type' => 'image'
                    ));
                }
                else {
                    //get all selected terms assign to post type
                    $pt_terms = get_post_meta($id, 'post_type_terms', true);
                    $tax_query_relation = get_post_meta($id, 'tax_query_relation', true);

                    $terms_tax = array();   //prepare tax query
                    $tax_query = array();   //tax_query param
                    if($tax_query_relation) $tax_query['relation'] = $tax_query_relation;

                    if(is_array($pt_terms))
                        foreach( $pt_terms as $t => $enable) {
                            if($enable) {
                                $tax = base64_decode($t);
                                $tax = explode('|', $tax);
                                if(!isset($terms_tax[$tax[1]]) ) $terms_tax[$tax[1]] = array();
                                $terms_tax[$tax[1]][] = $tax[0];

                            }
                        }
                    //build tax_query param
                    foreach($terms_tax as $tax => $terms) {
                        $tax_query[] = array(
                            'taxonomy' => $tax,
                            'field' => 'slug',
                            'terms' => $terms,
                            'operator' => 'IN'
                        );
                    }

                    //$terms = wp_get_post_terms($id,'category',array("fields" => "ids"));
                    $args['post_type'] = $posttype;
                    $args['tax_query'] = $tax_query;

                    $query = new WP_Query($args);
                    $data['posts'] = Timber::get_posts($args);
                    //get only attachments image
                    if($only_attachment) {  //refercence http://wordpress.stackexchange.com/questions/52315/get-attachments-for-all-posts-of-particular-post-type

                        if($query->have_posts()):
                        while($query->have_posts()) {
                            $query->the_post();
                            // arguments for get_posts
                            $attachment_args = array(
                                'post_type' => 'attachment',
                                'post_mime_type' => 'image',
                                'post_status' => null, // attachments don't have statuses
                                'post_parent' => get_the_ID()
                            );
                            // get the posts
                            $this_posts_attachments = get_posts( $attachment_args );
                            // append those posts onto the array
                            $images[get_the_ID()] = $this_posts_attachments; // make an array with the post_id as the key, just in case that's useful
                        }
                        endif;

                    }
                }

            }
            //skin data
            $options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options, 'template_file');
            $slider_theme = !empty($user_theme_options['theme'])? $user_theme_options['theme'] : 'default';
            $data = compact('slider_id','options', 'slider_theme', 'show_title');
            $data['context'] = $this;
            /**
             * parse theme
             */
            $content = $skin->instance->render_skin_template(($data),false);  //implement skin template
            if($content!==false) echo $content;

            $theme = array();   //valid
            if($skin->instance->allow_skin_file()) include_once($file);
            /*if(!isset($theme['styles'])) $theme['styles'] = array();
            if(!isset($theme['scripts'])) $theme['scripts'] = array();

            if(count($theme['styles']) || count($theme['scripts'])) {
                $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
            }*/
            //enqueue stuff from skin
            HW_SKIN::enqueue_skin_assets(array(
                'instance' => $skin->instance,
                'hash_skin' => $skin->hash_skin,
                'skin_file' => $file,
                'theme_settings' => $theme,
                'theme_options' => $user_theme_options
            ));
        }
    }

    /**
     * get all hwml shortcodes
     * @return array of sliders
     */
    public static function get_hwml_slideshow_posts(){
        $data = array();
        $args = array(
            'post_type' => HWML_Shortcodes_List::hwml_slider_posttype,
            'post_status' => 'publish',

        );
        $query = new WP_Query($args);
        while($query->have_posts()){
            $query->the_post();
            $data[get_the_ID()] = get_the_title();
        }
        return $data;
    }
    /**
     * get mlslides data
     * @param $slider_id: metaslider id
     */
    public static function get_mlslider_metaslides($slider_id){
        /**
         * query args extract slides from slideshow
         * from method get_slides in file plugins\ml-slider\inc\slider\metaslider.class.php
         */
        $args = array(
            'force_no_custom_order' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'lang' => '', // polylang, ingore language filter
            'suppress_filters' => 1, // wpml, ignore language filter
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'hw-ml-slider',
                    'field' => 'slug',
                    'terms' => $slider_id
                )
            )
        );
        $query = new WP_Query( $args );
        return $query;
    }
}

new HWMLShortcode_Manager();
/**
 * metaslider hook api
 * execute before get posts by instance WP_Query class
 *
 * @since metaslider v3.3
 */
//add_filter('metaslider_populate_slides_args', 'hwml_metaslider_populate_slides_args', 10,3);
function hwml_metaslider_populate_slides_args($args, $id, $settings){

}
