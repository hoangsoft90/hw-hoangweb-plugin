<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 11:31
 */
/**
 * Class NHP_Options_article
 */
class NHP_Options_article extends HW_NHP_Options{
    public function __construct(&$sections) {
        parent::__construct($sections);

    }

    /***
     * get nhp fields
     * @return array
     */
    public function get_fields (&$sections) {

        $sections['article'] = array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_151_edit.png',
            'title' => 'Bài viết',
            'fields' => array(
                'excerpt_more'=>array(
                    'id' =>'excerpt_more',
                    'type' => 'text',
                    'title' => 'Ký tự hiển thị mô tả đoạn cho bài viết (excerpt).',
                    'sub_desc' => 'Ký tự hiển thị mô tả đoạn cho bài viết (excerpt).',
                    'std' => '...'
                ),
                'clean_p_excerpt' => array(
                    'id' =>'clean_p_excerpt',
                    'type' => 'checkbox',
                    'title' => 'Xóa thẻ p bao nội dung excerpt của mỗi post',
                    'desc' => 'Xóa thẻ p bao nội dung excerpt của mỗi post.',
                    'std' => 1
                ),
                'morelink_text' => array(
                    'id' =>'morelink_text',
                    'type' => 'text',
                    'title' => 'Sửa link "đọc tiếp" bên cạnh nội dung excerpt.',
                    'sub_desc' => 'Sửa link "đọc tiếp" bên cạnh nội dung excerpt.',
                    'std' => 'Đọc tiếp'
                ),
                'excerpt_leng' => array(
                    'id' =>'excerpt_leng',
                    'type' => 'text',
                    'title' => 'Số ký tự hiển thị nội dung excerpt.',
                    'sub_desc' => 'Số ký tự hiển thị nội dung excerpt.',
                    'std' => '200'  //default 200 excerpt
                ),
                'wpautop_content' => array(
                    'id' => 'wpautop_content',
                    'type' => 'checkbox',
                    'title' => 'Tự động thêm thẻ xuống dòng cho bài viết.',
                    'desc' => 'Tự động thêm thẻ xuống dòng cho bài viết.'
                ),
                'wpautop_excerpt' => array(
                    'id' => 'wpautop_excerpt',
                    'type' => 'checkbox',
                    'title' => 'Tự động thêm thẻ xuống dòng cho mô tả excerpt.',
                    'desc' => 'Tự động thêm thẻ xuống dòng cho mô tả excerpt.'
                ),
                'divide' => array(
                    'type'=>'hw_divide',
                    'id' => 'divide',
                    'title' => ''
                ),

            )
        );

    }
    /**
     * First create the dropdown
     * make sure to change POST_TYPE to the name of your custom post type
     *
     * @author Ohad Raz
     *
     * @return void
     */
    public static function _admin_posts_filter_restrict_manage_posts(){
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        //only add filter to post type you want
        if ('post' == $type){
            //change this to the list of values you want to show

            ?>
            ID:
            <input type="text" size="10" name="filter_post_id" value="<?php echo isset($_GET['filter_post_id'])? $_GET['filter_post_id']:''?>"/>
        <?php
        }
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
    public static function _posts_filter( $query ){
        global $pagenow;
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ( 'post' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['filter_post_id']) && is_numeric($_GET['filter_post_id']) && $_GET['filter_post_id'] != '') {
            $query->query_vars['p'] = $_GET['filter_post_id'];
        }

        return $query;
    }
    /**
     * setup actions
     */
    public static function setup_actions() {
        add_action( 'restrict_manage_posts', 'NHP_Options_article::_admin_posts_filter_restrict_manage_posts' );
        add_filter( 'parse_query', 'NHP_Options_article::_posts_filter' );

    }

}
/**
 * post types
 */
NHP_Options_article::setup_actions();