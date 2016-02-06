<?php
/**
 * Class HW_POST
 */
class HW_POST{
    /**
     * count items when render taxonomy list in template
     * @var
     */
    static $iCount = 0;

    /**
     * return classes in array base item index
     * @param int $col: set total column
     * @param array $classes: continue classes
     */
    public static function get_item_class($col,$classes = array()){
        if(!is_numeric($col)) $col = 2;
        if( 0 == self::$iCount || 0 == self::$iCount % $col )
            $classes[] = 'hw-first';
        if((self::$iCount+1) % $col == 0) $classes[]='hw-last';
        $classes[] = self::$iCount%2? 'hw-even' : 'hw-odd';   //even, odd row
        self::$iCount ++;   //count next item
        return array_unique($classes);
    }

    /**
     * reset counter for listing terms taxonomy
     */
    public static function reset_item_counter(){
        self::$iCount = 0;
    }
    /**
     * parse classes for current item in loop
     * @param array $classes: classes
     * @param bool $attr
     * @return string
     */
    public static function item_class($classes = array(), $attr=true){
        if(is_numeric($classes)) {
            $classes = self::get_item_class($classes);
        }
        $classes = implode(' ',(array)$classes);
        return $attr? 'class="'.$classes.'"' : $classes;
    }
    /**
     * get post by name
     * @param $post_name
     * @param $posttype specific post type
     * @param $output
     * @return null
     */
    public static function get_post_by_name($post_name, $posttype='post', $output = OBJECT) {
        global $wpdb;
        $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='".$posttype."'", $post_name ));
        if ( $post )
            return get_post($post, $output);

        return null;
    }
    /**
     * get all post terms
     * @param $post: post id or single post data
     * @param $args: inherit argument param from get_object_taxonomies method
     */
    public static  function get_all_post_terms($post = '',$args = array()){
        if(is_numeric($post)) $post = get_post($post);
        if(empty($post) || !is_object($post)){
            global $post;
        }
        //valid post data
        if(empty($post)) return;        //not single post page or can't find post data
        $_args = array('fields' => 'names');
        if(is_array($args)) $_args = array_merge($_args, $args);

        $taxes = get_object_taxonomies($post->post_type,'names');
        return wp_get_post_terms($post->ID, $taxes, $_args);
    }

    /**
     * check exists post terms
     * @param $post
     * @param array $allow_terms
     * @param bool $match_all;
     */
    public static function check_post_terms($post, $allow_terms = array(), $match_all = false) {
        if(!empty($allow_terms) && is_array($allow_terms)) {
            $terms = self::get_all_post_terms($post, array('fields'=> 'slugs'));
            if(is_array($terms)){
                foreach($terms as $slug){
                    if(count($allow_terms) && $match_all == true && !in_array($slug,$allow_terms)) return false;   //not match requirement
                    if(count($allow_terms) && !$match_all && in_array($slug,$allow_terms)) return true;
                }
            }
        }
        return true;
    }
    /**
     * return all terms taxonomies base post type
     * @param $post_type: post type name
     * @param $args: addition arguments
     */
    public static function get_all_terms_taxonomies($post_type, $args  = array()){
        $taxes = get_object_taxonomies($post_type,'names');
        $_args = array(
            'hide_empty' => false
        );
        if(is_array($args)) $_args = array_merge($_args, $args);
        return get_terms($taxes, $_args);
    }

    /**
     * extract taxonomies from post types
     * @param mixed $post_types: list of post types
     * @param bool $pure data for select tag
     */
    public static function get_posttypes_taxonomies($post_types, $pure = true) {
        $data = array();
        if(is_string($post_types)) $post_types = preg_split('#[\s,]+#',$post_types);
        foreach ($post_types as $pt) {
            $taxes = get_object_taxonomies($pt, 'objects');
            foreach($taxes as $name => $tax) {
                $data[$name] = $pure? $tax->labels->name ." ($name)" : $tax;
            }
        }
        return $data;
    }
    /**
     * return list avaiable of taxonomies
     * @param array $excludes: exclude addition this taxonomies beside which taxonomies that you nerver in use
     * @return array
     */
    public static function hw_list_taxonomies($excludes = array()){
        //valid
        if(is_string($excludes)) $excludes = explode(',',$excludes);

        //list taxonomies
        $tax_data = array();
        $_excludes= 'nav_menu,link_category,post_format';    //excludes those taxonomy
        if(is_array($excludes)) $_excludes = array_merge(explode(',',$_excludes), $excludes);
        $taxes = get_taxonomies();
        foreach($taxes as $tax) {
            if(in_array($tax,$_excludes)) continue;
            $tax_data[$tax] = $tax;
        }
        return $tax_data;
    }
    /**
     * list terms taxonomy
     * @param $tax
     * @param $args
     * @param $field default get term id as select value
     * @return array
     */
    public static function list_tax_terms($tax, $args = array(), $field = 'id') {
        $options = array();
        //validation
        if(is_array($args)) $args=array();
        if(!in_array($field, array('id', 'slug'))) $field = 'id';

        $args['order'] = 'ASC';
        $args['fields'] = 'all';
        $args['hide_empty'] = 0;

        $terms_data = get_terms($tax,$args);
        if(is_wp_error($terms_data)) {
            return array('' => 'ERROR');
        }
        foreach($terms_data as $item){

            if($field == 'slug') $id = $item->slug;
            else $id=  $item->term_id;

            $options[$id] =  $item->name;
        }
        return $options;
    }
    /**
     * return all posts by post types
     * @param $posttypes list of post types in array or string with point dilimiter
     */
    static function get_all_posts_by_posttypes($posttypes) {
        if(is_string($posttypes)) $posttypes = preg_split('#[\s,]+#',$posttypes);

        //get all posts by post types
        $posts_result =  array();
        $posts_data = new WP_Query(array(
            'post_type' => $posttypes,
            'showposts' => -1
        ));
        $currentLang = '';  //if mqtranslate plugin actived

        while($posts_data->have_posts() ) {
            $posts_data->the_post();
            if(function_exists('qtrans_use')) {
                if(!$currentLang) $currentLang = qtrans_getLanguage();
                $title = qtrans_use($currentLang, get_the_title(), false);
            }
            else $title = get_the_title();

            $posts_result[get_the_ID()] =  $title;

        }
        return $posts_result;
    }

    /**
     * return all register post types
     * @return array
     */
    public static function get_post_types() {
        global $wp_post_types;
        $result = array();
        $ignores = 'revision,wp-types-group,wp-types-user-group,acf,envira,wpcf7_contact_form,hw_pt_condition,hw_pt_template,hw_mysidebar,hw-ml-slider,hwml_shortcode,nav_menu_item';
        $ignores = explode(',', $ignores);
        $all = get_post_types();
        foreach($all as $pt) {
            if(in_array($pt, $ignores)) continue;   //ignore these post type
            $result[$pt] = $wp_post_types[$pt] ->label;
        }
        return $result;
    }

    /**
     * @param $title
     * @param string $post_type
     * @return int|null
     */
    public static function post_exists($title, $post_type ='post') {
        $item =get_page_by_title($title, OBJECT, $post_type);
        if(!$item) $item = self::get_post_by_name($title, $post_type);
        return $item? $item->ID : null;
    }
    /**
     * get post thumbnail url
     * @param $post_ID
     * @return mixed
     */
    public static  function get_featured_image($post_ID) {
        $post_thumbnail_id = get_post_thumbnail_id($post_ID);
        if ($post_thumbnail_id) {
            $post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
            return $post_thumbnail_img[0];
        }
    }
    /**
     * return all meta keys from post type
     * @param string|array $pt: specify some post types name that exists meta key. if give string separate each post type with comma
     * @return array
     */
    static function generate_posttypes_meta_keys($pt = array('post')){
        global $wpdb;

        if(is_string($pt)) $pt = explode(',', $pt);
        if(is_array($pt)) {
            $pt = array_map(function($s){ return "'{$s}'";}, $pt);
            $pt = join(',',$pt);
        }
        $transient_id = base64_encode($pt);
        // check cache first
        /*$cache = get_transient('foods_meta_keys');
        if($cache) return $cache;*/

        $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key)
        FROM $wpdb->posts
        LEFT JOIN $wpdb->postmeta
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type in ($pt)
        AND $wpdb->postmeta.meta_key != ''
        #AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
        #AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
    ";
        $meta_keys = $wpdb->get_col($query);
        //set_transient($transient_id, $meta_keys, 60*60*24); # 1 Day Expiration
        return $meta_keys;
    }
}
