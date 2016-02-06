<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/06/2015
 * Time: 08:39
 */
/**
 * Class HW_Rewrite_URL
 */
//http://stackoverflow.com/questions/13553932/define-permalinks-for-custom-post-type-by-taxonomy
/**
 * Class HW_Rewrite_URL
 */
class HW_Rewrite_URL extends HW_Core
{
    public static $instance;
    public function __construct(){
        $this->init();
    }

    /**
     * get first of all insntance of this class
     * @return HW_Rewrite_URL
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * initial
     */
    public function init(){
        /*Filtro per modificare il permalink*/
        add_filter('post_link', array($this, '_hw_setup_permalink'), 1, 3);
        add_filter('post_type_link', array($this,'_hw_setup_permalink'), 1, 3);

        //add_action( 'generate_rewrite_rules', array($this, 'eg_add_rewrite_rules' ));

        //add_filter( 'page_rewrite_rules', 'HW_Rewrite_URL::_wpse7243_page_rewrite_rules' );
        //add_filter('rewrite_rules_array','HW_Rewrite_URL::_wp_insertMyRewriteRules');
        //add_filter('query_vars','HW_Rewrite_URL::_wp_insertMyRewriteQueryVars');		#custom query vars

        //add_action('init', array($this, '_rewrite_url'));
        //flush rewrite rules when add new category/taxonomy
        add_action( 'created_category',  'flush_rewrite_rules' );
        // Taxonomy edited
        add_action( 'edited_category',  'flush_rewrite_rules' );
        //taxonomy deleted
        add_action( 'delete_category',  'flush_rewrite_rules' );
    }

    /**
     * rewrite url
     */
    public function _rewrite_url(){
        // add to our plugin init function
        global $wp_rewrite;
        $gallery_structure = '/bank/%year%/%monthnum%/%bank%';
        $wp_rewrite->add_rewrite_tag("%bank%", '([^/]+)', "bank=");
        $wp_rewrite->add_permastruct('bank', $gallery_structure, false);
        //$wp_rewrite->flush_rules();
    }
    /**
     * modify post type link
     * @param $permalink
     * @param $post
     * @param $leavename
     * @return mixed
     */
    public function _hw_setup_permalink($permalink, $post, $leavename) {
        // Get post
        if(is_numeric($post)) $post = get_post($post);

        //set custom rewrite slug for post type
        if($post->post_type == 'xx'){

        }
        //con %brand% catturo il rewrite del Custom Post Type
        if (strpos($permalink, '%brand%') === FALSE) return $permalink;

        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'category');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
            $taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'no-brand';

        return str_replace('%brand%', $taxonomy_slug, $permalink);
    }

    /**
     *
     * @return array
     */
    public function _wpse7243_page_rewrite_rules(){
        // The most generic page rewrite rule is at end of the array
        // We place our rule one before that
        end( $rewrite_rules );
        $last_pattern = key( $rewrite_rules );
        $last_replacement = array_pop( $rewrite_rules );
        $rewrite_rules +=  array(
            '(.+?)/([0-9]+)/([^/]+)/([^/]+)/?$' => 'index.php?pagename=$matches[1]&id=$matches[2]&fname=$matches[3]&lname=$matches[4]',
            $last_pattern => $last_replacement,
        );
        return $rewrite_rules;
    }
    // Adding a new rule
    public function _wp_insertMyRewriteRules($rules)
    {
        $newrules = array();
        $newrules['(profile)/(.*)$'] = 'index.php?pagename=profile&profile=$matches[2]';
        return $newrules + $rules;
    }
    // Adding the id var so that WP recognizes it
    public function _wp_insertMyRewriteQueryVars($vars)
    {
        array_push($vars, 'profile');
        return $vars;
    }
}

HW_Rewrite_URL::getInstance();