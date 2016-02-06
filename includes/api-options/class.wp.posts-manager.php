<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/11/2015
 * Time: 10:37
 */
/**
 * Class HW_WP_Posts_Manager
 */
class HW_WP_Posts_Manager extends HW_Admin_Options {
    /**
     * column image name
     */
    const IMAGE_COLUMN_NAME = 'hw_image';
    /**
     * @var
     */
    var $show_thumb_posttypes;
    /**
     * display post thumbnail in admin column posts list
     * @var null
     */
    private $show_posts_thumbnail_column = null;
    /**
     * @return mixed|void
     */
    public function load() {
        //filter posts with custom criteria
        add_action( 'restrict_manage_posts', array($this, '_admin_posts_filter_restrict_manage_posts' ));
        add_filter( 'parse_query', array($this, '_admin_posts_filter') );
        add_action('init', array($this, '_init'));

        //enable thumbnail column to bellow post types
        $this->show_thumb_posttypes = hw_get_setting(array('my_posttype_settings','show_posts_thumbnail_column'));
        if(empty($this->show_thumb_posttypes)) $this->show_thumb_posttypes = array('post' => 1);

        //custom admin columns. learn more: http://www.hoangweb.com/wordpress-site/tuy-bien-cot-trong-admin-liet-ke-posts-va-custom-post-type
        foreach($this->show_thumb_posttypes as $posttype => $active) {
            if($active) {
                add_filter('manage_'.$posttype.'_posts_columns', array($this, '_manage_columns_head'));
                add_action('manage_'.$posttype.'_posts_custom_column', array($this, '_manage_columns_content'), 10, 2);
            }
        }
    }
    /**
     * regiter acf fields group
     */
    private function implement_acf_fields(){
        $list = hw_get_setting(array('my_taxonomy_settings','allow_taxonomies_image'));
        if(!empty($list)) {
            HW_ACF_API::hw_acf_register_field_group_taxonomy_image($list);
            foreach ($list as $tax) {
                //hiển thị columns taxonomy
                add_filter("manage_edit-{$tax}_columns", array($this, '_custom_taxonomy_admin_columns'));
                add_filter("manage_{$tax}_custom_column", array($this, '_manage_taxonomy_columns_content'), 10, 3); //column content
            }
        }
    }
    /**
     * @hook init
     */
    public function _init() {
        //prepare acf fields
        $this->implement_acf_fields();
    }
    /**
     * adding new column to your list taxonomies page
     * @param $theme_columns
     * @return mixed
     */
    public function _custom_taxonomy_admin_columns($theme_columns){
        $theme_columns[self::IMAGE_COLUMN_NAME] = __('Ảnh');
        return $theme_columns;
    }

    /**
     * display taxonomy column content
     * @param $out
     * @param $column_name
     * @param $term_id
     * @return string
     */
    public function _manage_taxonomy_columns_content($out, $column_name, $term_id) {
        //$term = get_term($term_id, 'category');
        $taxonomy = isset($_REQUEST['taxonomy'])? $_REQUEST['taxonomy'] : 'category';   //get active taxonomy in page
        switch ($column_name) {
            case self::IMAGE_COLUMN_NAME:
                // get header image url
                //$data = maybe_unserialize($theme->image);
                if(function_exists('get_field')) {
                    $img=get_field(self::IMAGE_COLUMN_NAME,"{$taxonomy}_{$term_id}");
                    if(is_array($img)) $img = $img['url'];
                    if(!$img) $img=HW_HOANGWEB_URL.'/images/placeholder.png';

                    $out .= "<img src=\"$img\" width=\"80\" height=\"80\"/>";
                }
                break;

            default:
                break;
        }
        return $out;
    }
    /**
     * ADD NEW COLUMN
     * @hook manage_
     * @param $defaults
     */
    public function _manage_columns_head($defaults) {

        //array_unshift($defaults, array('featured_image' => __('Ảnh')));
        $columns['featured_image'] = __('Ảnh');
        //$defaults = array_merge($columns, $defaults);
        return $columns+$defaults;
    }
    /**
     * SHOW THE FEATURED IMAGE
     * @hook manage_
     * @param $column_name
     * @param $post_ID
     */
    public function _manage_columns_content($column_name, $post_ID) {
        //pre-hook to limit post types that point to this callback in construct method
        //show post thumbnail column in admin posts manager for allowing current post type
        /*if (isset($this->show_thumb_posttypes[get_post_type()]) && $this->show_thumb_posttypes[get_post_type()]
            && $column_name == 'featured_image')
        {

        }
        */
        if($column_name == 'featured_image') {
            $img = get_the_post_thumbnail($post_ID, array('80','80'));
            //$post_featured_image = HW_POST::get_featured_image($post_ID);
            if ($img) {
                echo $img;
            }
            else {
                // NO FEATURED IMAGE, SHOW THE DEFAULT ONE
                echo '<img src="' . HW_HOANGWEB_URL. '/images/placeholder.png" width="80px" height="80px"/>';
            }
        }

    }
    /**
     * First create the dropdown
     * make sure to change POST_TYPE to the name of your custom post type
     *
     * @author Ohad Raz
     * @hook restrict_manage_posts
     *
     * @return void
     */
    public function _admin_posts_filter_restrict_manage_posts(){
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        //only add filter to post type you want
        //if ('post' == $type){
        //change this to the list of values you want to show

        ?>
        ID:
        <input type="text" size="10" name="filter_post_id" value="<?php echo isset($_GET['filter_post_id'])? $_GET['filter_post_id']:''?>"/>
        <?php
        //}
    }
    /**
     * if submitted filter by post meta
     *
     * make sure to change META_KEY to the actual meta key
     * and POST_TYPE to the name of your custom post type
     * @author Ohad Raz
     * @param  (wp_query object) $query
     *
     * @return Void
     */
    public function _admin_posts_filter( $query ){
        global $pagenow;
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ( /*'post' == $type &&*/ is_admin() && $pagenow=='edit.php'
            && !empty($_GET['filter_post_id']) && is_numeric($_GET['filter_post_id']) ) {
            $query->query_vars['p'] = $_GET['filter_post_id'];
        }

        return $query;
    }
}