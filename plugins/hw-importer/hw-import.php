<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/10/2015
 * Time: 10:21
 */
/*if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
    return;
*/

/** Display verbose errors */
if(!defined('IMPORT_DEBUG')) define( 'IMPORT_DEBUG', false );
//load widget importer
include ('plugin/widget-importer-exporter/import.php');

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) )
        require $class_wp_importer;
}

// include WXR file parsers
require_once (dirname( __FILE__ ) . '/includes/parsers.php');

// include main class from wordpress-importer plugin
if(!class_exists('WP_Import')) {    //if wordpress-importer plugin not actived
    require_once (dirname(__FILE__). ('/plugin/wordpress-importer/wordpress-importer.php'));
}

/**
 * Class HW_Import
 * from plugins/wordpress-importer/wordpress-importer.php
 */
#if ( class_exists( 'WP_Importer' ) ) {
if ( class_exists( 'WP_Import' ) ) {
class HW_Import extends WP_Import { //WP_Importer
    /**
     * singleton
     * @var
     */
    public static $instance;

    /**
     * Page slug
     */
    const REGISTER_IMPORT = 'hw-wordpress';

    // mappings from old information to new
    var $processed_authors = array();
    var $author_mapping = array();
    var $processed_terms = array();
    /**
     * posts result
     * @var array
     */
    var $processed_posts = array();
    var $hw_processed_posts = array();

    var $post_orphans = array();
    /**
     * menu items
     */
    var $processed_menu_items = array();
    var $menu_item_orphans = array();
    var $missing_menu_items = array();
    /**
     * attachments
     */
    var $fetch_attachments = false;
    var $url_remap = array();
    var $featured_images = array();
    /**
     * HW_Module_Export
     * @var
     */
    var $module_exporter;
    /**
     * @var null
     */
    var $logger =null;
    /**
     * HW_WXR_Parser
     * @var
     */
    var $parser;
    /**
     * track processing of installation
     * HW_Track_Installation
     * @var
     */
    var $tracker;
    /**
     * main class constructor
     * @param $module_exporter
     */
    public function HW_Import($module_exporter=null) {
        HW_HOANGWEB::load_class('HW_File_Directory');
        $this->logger = HWIE_Logger::get_instance();    //logger, run after installer
        if(!empty($module_exporter)) {
            $this->module_exporter = $module_exporter; //importer known module who using it to import it' data
            $this->parser = $module_exporter;
        }
        //get wxr parser
        else $this->parser = HW_WXR_Parser::get_instance($this);
        //installation tracker
        $this->tracker = HW_Track_Installation::get_instance($this);

        add_filter('import_allow_fetch_attachments', '__return_true');
        add_filter('hw_import_allow_fetch_attachments', '__return_true');
        add_filter('import_allow_create_users', '__return_true');
    }
    /**
     * create instance of this class
     * @param $module_exporter
     * @return HW_Import
     */
    public static  function get_instance($module_exporter= null) {
        if(empty(self::$instance)) self::$instance = new self($module_exporter);
        return self::$instance;
    }
    /**
     * dispath import app
     */
    public function _dispatch() {
        $this->header();
        $step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
        //get demo WXR file
        if(isset($_GET['enable_demo'])) {
            $step = 2;
            $file = dirname( __FILE__ ). '/cli/test1.xml';
            #$file='E:\HoangData\xampp_htdocs\wp2\wp-content\plugins\hw-hoangweb\plugins/theme-options/demo-data/dienthoai/data.xml';
        }
        switch ( $step ) {
            case 0:
                $this->greet();
                break;
            case 1:
                check_admin_referer( 'import-upload' );
                if ( $this->_handle_upload() )
                    $this->_import_options();
                break;
            case 2:
                #check_admin_referer( 'hw-import-wordpress' );
                $this->fetch_attachments = ( $this->_allow_fetch_attachments() );
                if(!empty($_POST['import_id']) && !isset($file)) {
                    $this->id = (int) $_POST['import_id'];
                    $file = get_attached_file( $this->id );
                }

                set_time_limit(0);
                $this->_import( $file );
                break;
        }
        $this->footer();
    }
    /**
     * import data
     * @param $wxr_file
     * @param $num_posts
     * @param $page
     * @param $fetch_attachments
     */
    public function import_file($wxr_file, $num_posts=0, $page=0, $fetch_attachments = true) {
        $this->fetch_attachments = ( ! empty( $fetch_attachments ) && $this->_allow_fetch_attachments() );

        set_time_limit(0);
        $this->_import( $wxr_file , $num_posts, $page);
    }

    /**
     * The main controller for the actual import stage.
     *
     * @param string|SimpleXMLElement $file Path to the WXR file for importing
     * @param $num_posts
     * @param $page
     */
    private function _import( $file ,$num_posts=0, $page=0) {
        add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
        add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

        $this->_import_start( $file, $num_posts, $page);

        $this->_get_author_mapping();

        wp_suspend_cache_invalidation( true );
        $this->_process_categories();
        $this->_process_tags();
        $this->_process_terms();

        $this->processed_sidebars_registration();   //register sidebars
        $this->_process_posts();    //import posts
        $this->processed_options(); //import options
        $this->processed_widgets(); //import widgets
        wp_suspend_cache_invalidation( false );

        // update incorrect/missing information in the DB
        $this->backfill_parents();
        $this->backfill_attachment_urls();
        $this->_remap_featured_images();

        $this->_import_end();
    }
    /**
     * @param string $item
     * @return string
     */
    public function get_import_results($item='') {
        $results['posts'] = $this->hw_processed_posts;
        $results['terms'] = $this->processed_terms ;
        $results['menu_items'] = $this->processed_menu_items ;
        $results['authors'] = $this->processed_authors;
        return $item? (isset($results[$item])? $results[$item]:'') : $results;
    }
    /**
     * Parses the WXR file and prepares us for the task of processing parsed data
     *
     * @param string|SimpleXMLElement $file Path to the WXR file for importing
     */
    private function _import_start( $file ,$num_posts=0, $page=0) {
        if (is_string($file) && ! is_file($file) ) {
            $this->logger->add_log( '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />');
            $this->logger->add_log( __( 'The file does not exist, please try again.', 'wordpress-importer' ) . '</p>');
            $this->footer();
            die();
        }

        $import_data = $this->_parse( $file ,$num_posts, $page);

        if ( is_wp_error( $import_data ) ) {
            $this->logger->add_log( '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />');
            $this->logger->add_log( esc_html( $import_data->get_error_message() ) . '</p>');
            $this->footer();
            die();
        }

        $this->version = $import_data['version'];
        $this->get_authors_from_import( $import_data );
        $this->posts = $import_data['posts'];
        $this->terms = $import_data['terms'];
        $this->categories = $import_data['categories'];
        $this->tags = $import_data['tags'];
        $this->authors_data = $import_data['authors'];   //get import authors

        $this->options = $import_data['options'];   //wp options data

        $this->widgets = $import_data['widgets'];   //widgets data
        $this->sidebars = $import_data['sidebars'];   //get sidebars data
        $this->base_url = esc_url( $import_data['base_url'] );

        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );

        do_action( 'import_start' );
    }
    /**
     * Create options based on import information
     */
    public function processed_options() {
        $this->options = apply_filters( 'hw_import_options', $this->options );
#__print($this->widgets);return;
        $context_data = $this->parser->_get_option('data', array());    //get update variables from context
        if(is_array($this->options) && count($this->options)) {
            foreach ( $this->options as &$option ) {
                //parse import result value
                if(!empty($option['import_results'])) {
                    foreach($option['import_results'] as &$value) {
                        if($value instanceof HWIE_Module_Import_Results) {
                            $value->init($this->parser->importer, $context_data);
                            $value = $value->parse_data($context_data)->value;
                        }
                    }
                }
                $option = apply_filters( 'hw_import_option_data_raw', $option );#__print($option['value']);
                hw_add_wp_option($option['name'], $option['value'], ($option['method'] == 'override'? false: true));
                //update_option($option['name'], $option['value']);
                $this->logger->add_log(sprintf('<div>Đã thêm option <strong>%s</strong></div>', $option['name']));
            }
        }

    }
    /**
     * import widgets data
     */
    public function processed_widgets() {
        $this->widgets = apply_filters( 'hw_import_widgets', $this->widgets );
        if(!empty($this->widgets)) {
            $wie = HW_Widget_Import::get_instance();
            $wie->hw_wie_process_import((object)$this->widgets);
            if($wie->have_import_results()) {
                $wie->hw_wie_show_import_results();
            }
        }
    }
    /**
     * register sidebar
     */
    public function processed_sidebars_registration() {
        foreach($this->sidebars as $name => $sidebar) {
            hwawc_register_sidebar($sidebar);
        }
    }
    /**
     * If fetching attachments is enabled then attempt to create a new attachment
     *
     * @param array $post Attachment post details from WXR
     * @param string $url URL to fetch attachment from
     * @return int|WP_Error Post ID on success, WP_Error otherwise
     */
    function _process_attachment( $post, $url ) {
        if ( ! $this->fetch_attachments )
            return new WP_Error( 'attachment_processing_error',
                __( 'Fetching attachments is not enabled', 'wordpress-importer' ) );

        // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
        if ( preg_match( '|^/[\w\W]+$|', $url ) )
            $url = rtrim( $this->base_url, '/' ) . $url;

        $upload = $this->fetch_remote_file( $url, $post );
        if ( is_wp_error( $upload ) )
            return $upload;

        if ( $info = wp_check_filetype( $upload['file'] ) )
            $post['post_mime_type'] = $info['type'];
        else
            return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'wordpress-importer') );

        $post['guid'] = $upload['url'];

        // as per wp-admin/includes/upload.php
        $post_id = wp_insert_attachment( $post, $upload['file'] );
        usleep(10000); //sleep a bit
        wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );
        usleep(10000);   //sleep wait for update media metadata

        // remap resized image URLs, works by stripping the extension and remapping the URL stub.
        if ( preg_match( '!^image/!', $info['type'] ) ) {
            $parts = pathinfo( $url );
            $name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

            $parts_new = pathinfo( $upload['url'] );
            $name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

            $this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
        }
        return $post_id;
    }
    /**
     * Performs post-import cleanup of files and the cache
     */
    private function _import_end() {
        #wp_import_cleanup( $this->id );

        wp_cache_flush();
        foreach ( get_taxonomies() as $tax ) {
            delete_option( "{$tax}_children" );
            _get_term_hierarchy( $tax );
        }

        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );

        $this->logger->add_log('<p>' . __( 'All done.', 'wordpress-importer' ) . ' <a href="' . admin_url() . '">' . __( 'Have fun!', 'wordpress-importer' ) . '</a>' . '</p>');
        $this->logger->add_log( '<p>' . __( 'Remember to update the passwords and roles of imported users.', 'wordpress-importer' ) . '</p>');

        do_action( 'import_end' );
    }
    /**
     * Create new posts based on import information
     *
     * Posts marked as having a parent which doesn't exist will become top level items.
     * Doesn't create a new post if: the post type doesn't exist, the given post ID
     * is already noted as imported or a post with the same title and date already exists.
     * Note that new/updated terms, comments and meta are imported for the last of the above.
     */
    private function _process_posts() {
        $this->posts = apply_filters( 'hw_import_posts', $this->posts );
        $_id_meta_key = '_hw_id';
        //hwArray::split_loop_segments(5, count($this->posts));
        foreach ( $this->posts as $post ) {
            $post = apply_filters( 'hw_import_post_data_raw', $post );

            if ( ! post_type_exists( $post['post_type'] ) ) {
                $this->logger->add_log(sprintf( __( 'Failed to import &#8220;%s&#8221;: Invalid post type %s', 'wordpress-importer' ),
                    esc_html($post['post_title']), esc_html($post['post_type']) ));

                do_action( 'wp_import_post_exists', $post );
                continue;
            }

            if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
                continue;

            if ( $post['status'] == 'auto-draft' )
                continue;

            if ( 'nav_menu_item' == $post['post_type'] ) {
                $this->_process_menu_item( $post );
                continue;
            }

            $post_type_object = get_post_type_object( $post['post_type'] );
            $post_id= 0;

            //$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );    //by wp
            $post_exists = HW_POST::post_exists( $post['post_title'], $post['post_type'] );
            if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] &&
                (!isset($post['hw_attributes']['update']) || !$post['hw_attributes']['update']) //not allow to update this post
            ) {
                $this->logger->add_log(sprintf( __('%s &#8220;%s&#8221; đã tồn tại và không cho phép cập nhật.', 'wordpress-importer'), $post_type_object->labels->singular_name, esc_html($post['post_title']) ));

                $comment_post_ID = $post_id = $post_exists;
                $post['post_id'] = $post['ID'] = $post_id;
                //get _id meta value
                $_id = get_post_meta($post_id, $_id_meta_key, true);
                if($_id) $post['_id'] = $_id;
            } else {
                if($post_exists) {
                    $comment_post_ID = $post_id = $post_exists;  //exists post
                    $this->logger->add_log(sprintf( __('Cập nhật vào post "%s" cho %s.', 'wordpress-importer'), esc_html($post['post_title']),$post_type_object->labels->singular_name ) );
                }
                else $this->logger->add_log(sprintf( __('Thêm mới post "%s" cho %s.', 'wordpress-importer'), esc_html($post['post_title']),$post_type_object->labels->singular_name ));

                $post_parent = (int) $post['post_parent'];
                if ( $post_parent ) {
                    // if we already know the parent, map it to the new local ID
                    if ( isset( $this->processed_posts[$post_parent] ) ) {
                        $post_parent = $this->processed_posts[$post_parent];
                        // otherwise record the parent for later
                    } else {
                        if(!empty($post['post_id'])) $this->post_orphans[intval($post['post_id'])] = $post_parent;  //by hoang, because we not use import_id feature
                        $post_parent = 0;
                    }
                }

                // map the post author
                $author = sanitize_user( $post['post_author'], true );
                if ( isset( $this->author_mapping[$author] ) )
                    $author = $this->author_mapping[$author];
                else
                    $author = (int) get_current_user_id();
                //valid
                if($post['post_content'] instanceof HWIE_Module_Import_Results) {   //import result in post content
                    $post['post_content']->init($this->parser->importer, $this->parser->_get_option('data', array()));
                    $post['post_content'] = $post['post_content']->parse_data()->value;
                    //HW_Logger::log_file($post['post_content']);
                }

                $postdata = array(
                    'post_author' => $author,
                    'post_date' => $post['post_date'] ,
                    'post_date_gmt' => $post['post_date_gmt'],
                    'post_content' => $post['post_content'],
                    'post_excerpt' => $post['post_excerpt'],
                    'post_title' => $post['post_title'],
                    'post_status' => $post['status'],
                    'post_name' => $post['post_name'],
                    'comment_status' => $post['comment_status'],
                    'ping_status' => $post['ping_status'],
                    'guid' => $post['guid'],
                    'post_parent' => $post_parent,
                    'menu_order' => $post['menu_order'],
                    'post_type' => $post['post_type'],
                    'post_password' => $post['post_password']
                );
                if(!empty($post['post_id'])) $postdata['import_id'] = $post['post_id']; //import id,because we never  use import_id feature
                if(!empty($post_id)) {
                    $post['ID'] = $postdata['ID'] = $post_id; //exists post ID
                }

                $original_post_ID = $post['post_id'];
                $postdata = apply_filters( 'hw_import_post_data_processed', $postdata, $post );

                if ( 'attachment' == $postdata['post_type'] ) {
                    $remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

                    // try to use _wp_attached file for upload folder placement to ensure the same location as the export site
                    // e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
                    $postdata['upload_date'] = $post['post_date'];
                    if ( isset( $post['postmeta'] ) ) {
                        foreach( $post['postmeta'] as $meta ) {
                            if ( $meta['key'] == '_wp_attached_file' ) {
                                if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
                                    $postdata['upload_date'] = $matches[0];
                                break;
                            }
                        }
                    }
                    //fix
                    if(empty($postdata['upload_date'])) $postdata['upload_date'] = date('Y').'/'. date('m');
                    if(trim($remote_url)) {
                        $comment_post_ID = $post_id = $this->_process_attachment( $postdata, $remote_url );#__print($post_id);
                        $post['ID'] = $post_id; //save attachment id
                    }
                } else {

                    //by hoang
                    if($post_exists && isset($post['hw_attributes']['update']) && $post['hw_attributes']['update']) {
                        $comment_post_ID = $post_id = wp_update_post($postdata, true);  //update exists post
                    }
                    else $comment_post_ID = $post_id = wp_insert_post( $postdata, true );   //add new post
                    $post['ID'] = $post_id; //set post id
                    do_action( 'wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post );
                }
                if(empty($post_id)) continue;   //nothing
                if ( is_wp_error( $post_id ) ) {
                    $this->logger->add_log(sprintf( __( 'Failed to import %s &#8220;%s&#8221;', 'wordpress-importer' ),
                        $post_type_object->labels->singular_name, esc_html($post['post_title']) ));
                    if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                        echo ': ' . $post_id->get_error_message();

                    continue;
                }

                if ( $post['is_sticky'] == 1 )
                    stick_post( $post_id );
            }

            //set postthumbnail
            if(!empty($post['hw_thumbnail_id']) /*&& !empty($this->hw_processed_posts[$post['hw_thumbnail_id']['post_id']])*/
                ) {
                $thumb_id = (int)$this->hw_processed_posts[$post['hw_thumbnail_id']]['ID'];
                //set_post_thumbnail((int)$post['ID'], $thumb_id);
                update_post_meta((int)$post['ID'], '_thumbnail_id', $thumb_id);
                #__print($this->hw_processed_posts);
            }

            // map pre-import ID to local ID
            /*if(isset($post['_id'])) $post_key = $post['_id'];   //<hw:attachment><hw:_id></hw:_id>...
            elseif(!empty($post['post_id'])) $post_key = intval($post['post_id']);
            elseif(!empty($post['hw_attributes']['name'])) {
                $post_key = $post['hw_attributes']['name'];
            }
            elseif($post['post_title']) $post_key = sanitize_title($post['post_title']);
            */
            $post_key = HW_WXR_Parser::get_import_id($post);
            //$this->hw_processed_posts[$post_key] = array('ID'=>$post['ID']);
            $this->tracker->add_import('post', $post_key, $post);
            $this->processed_posts[intval($post['post_id'])] = (int) $post_id;

            if ( ! isset( $post['terms'] ) )
                $post['terms'] = array();

            $post['terms'] = apply_filters( 'hw_import_post_terms', $post['terms'], $post_id, $post );

            // add categories, tags and other terms
            if ( ! empty( $post['terms'] ) ) {
                $terms_to_set = array();
                foreach ( $post['terms'] as $term ) {
                    // back compat with WXR 1.0 map 'tag' to 'post_tag'
                    $taxonomy = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
                    $term_exists = term_exists( $term['slug'], $taxonomy );
                    $term_id = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
                    if ( ! $term_id ) {
                        $t = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'] ) );
                        if ( ! is_wp_error( $t ) ) {
                            $term_id = $t['term_id'];
                            do_action( 'wp_import_insert_term', $t, $term, $post_id, $post );
                        } else {
                            $this->logger->add_log(sprintf( __( 'Failed to import %s %s', 'wordpress-importer' ), esc_html($taxonomy), esc_html($term['name']) ));
                            if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                                $this->logger->add_log( ': ' . $t->get_error_message());
                            #echo '<br />';
                            do_action( 'wp_import_insert_term_failed', $t, $term, $post_id, $post );
                            continue;
                        }
                    }
                    $terms_to_set[$taxonomy][] = intval( $term_id );
                }

                foreach ( $terms_to_set as $tax => $ids ) {
                    $tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
                    do_action( 'wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post );
                }
                unset( $post['terms'], $terms_to_set );
            }

            if ( ! isset( $post['comments'] ) )
                $post['comments'] = array();

            $post['comments'] = apply_filters( 'hw_import_post_comments', $post['comments'], $post_id, $post );

            // add/update comments
            if ( ! empty( $post['comments'] ) ) {
                $num_comments = 0;
                $inserted_comments = array();
                foreach ( $post['comments'] as $comment ) {
                    $comment_id	= $comment['comment_id'];
                    $newcomments[$comment_id]['comment_post_ID']      = $comment_post_ID;
                    $newcomments[$comment_id]['comment_author']       = $comment['comment_author'];
                    $newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
                    $newcomments[$comment_id]['comment_author_IP']    = $comment['comment_author_IP'];
                    $newcomments[$comment_id]['comment_author_url']   = $comment['comment_author_url'];
                    $newcomments[$comment_id]['comment_date']         = $comment['comment_date'];
                    $newcomments[$comment_id]['comment_date_gmt']     = $comment['comment_date_gmt'];
                    $newcomments[$comment_id]['comment_content']      = $comment['comment_content'];
                    $newcomments[$comment_id]['comment_approved']     = $comment['comment_approved'];
                    $newcomments[$comment_id]['comment_type']         = $comment['comment_type'];
                    $newcomments[$comment_id]['comment_parent'] 	  = $comment['comment_parent'];
                    $newcomments[$comment_id]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
                    if ( isset( $this->processed_authors[$comment['comment_user_id']] ) )
                        $newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
                }
                ksort( $newcomments );

                foreach ( $newcomments as $key => $comment ) {
                    // if this is a new post we can skip the comment_exists() check
                    if ( ! $post_exists || ! comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
                        if ( isset( $inserted_comments[$comment['comment_parent']] ) )
                            $comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
                        $comment = wp_filter_comment( $comment );
                        $inserted_comments[$key] = wp_insert_comment( $comment );
                        do_action( 'wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post );

                        foreach( $comment['commentmeta'] as $meta ) {
                            $value = maybe_unserialize( $meta['value'] );
                            add_comment_meta( $inserted_comments[$key], $meta['key'], $value );
                        }

                        $num_comments++;
                    }
                }
                unset( $newcomments, $inserted_comments, $post['comments'] );
            }

            if ( ! isset( $post['postmeta'] ) )
                $post['postmeta'] = array();

            $post['postmeta'] = apply_filters( 'hw_import_post_meta', $post['postmeta'], $post_id, $post );
            if(!is_array($post['postmeta'])) {
                $post['postmeta'] = array();
            }
            //save _id key as post meta, alway update this value
            if(!empty($post['_id']))
            $post['postmeta'][] = array(
                'key' => $_id_meta_key,
                'value' => $post['_id']
            );
            // add/update post meta
            if ( ! empty( $post['postmeta'] ) ) {
                foreach ( $post['postmeta'] as $meta ) {
                    $key = apply_filters( 'hw_import_post_meta_key', $meta['key'], $post_id, $post );
                    $value = false;

                    if ( '_edit_last' == $key ) {
                        if ( isset( $this->processed_authors[intval($meta['value'])] ) )
                            $value = $this->processed_authors[intval($meta['value'])];
                        else
                            $key = false;
                    }

                    if ( $key ) {

                        // export gets meta straight from the DB so could have a serialized string
                        if ( ! $value ) {
                            if($meta['value'] instanceof HWIE_Module_Import_Results) {
                                $meta['value']->init($this->parser->importer, $this->parser->_get_option('data'));
                                $value = $meta['value']->parse_data()->value;#__print($value);
                            }
                            else $value = maybe_unserialize( $meta['value'] );
                        }

                        /*if($value instanceof DOMElement) {
                            $parse = new HWIE_Module_Import_Results($value);
                            $parse->init($this);
                            //$value = $parse->parse_import_thumbnail();
                            $value = $parse->parse_data()->value;
                            $this->logger->add_log('parse import thumbnail: '. $value);
                        }*/

                        //modify by hoang
                        add_post_meta( $post_id, $key, $value );
                        if(isset($post['hw_attributes']['update']) && $post['hw_attributes']['update']) {
                            update_post_meta($post_id, $key, $value );
                        }
                        do_action( 'import_post_meta', $post_id, $key, $value );

                        // if the post has a featured image, take note of this in case of remap
                        if ( '_thumbnail_id' == $key && is_numeric($value))
                            $this->featured_images[$post_id] = (int) $value;

                    }
                }
            }
        }

        unset( $this->posts );
    }
    //test import result filter
    public function test_filter_attachment($value, $id) {
        #return 'filter->'. $value;
        return $value;
    }
    /**
     * Parse a WXR file
     *
     * @param string|SimpleXMLElement $file Path to WXR file for parsing
     * @return array Information gathered from the WXR file
     */
    private function _parse( $file, $num_posts=0, $page=0) {
        //$parser = new HW_WXR_Parser();
        return $this->parser->parse( $file ,$num_posts,$page);
    }
    /**
     * Handles the WXR upload and initial parsing of the file to prepare for
     * displaying author import options
     *
     * @return bool False if error uploading or invalid file, true otherwise
     */
    private function _handle_upload() {
        $file = wp_import_handle_upload();

        if ( isset( $file['error'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />';
            echo esc_html( $file['error'] ) . '</p>';
            return false;
        } else if ( ! file_exists( $file['file'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />';
            $this->logger->add_log(sprintf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wordpress-importer' ), esc_html( $file['file'] ) ));
            echo '</p>';
            return false;
        }

        $this->id = (int) $file['id'];
        $import_data = $this->parse( $file['file'] );
        if ( is_wp_error( $import_data ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />';
            echo esc_html( $import_data->get_error_message() ) . '</p>';
            return false;
        }

        $this->version = $import_data['version'];
        if ( $this->version > $this->max_wxr_version ) {
            echo '<div class="error"><p><strong>';
            $this->logger->add_log(sprintf( __( 'This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'wordpress-importer' ), esc_html($import_data['version']) ));
            echo '</strong></p></div>';
        }

        $this->get_authors_from_import( $import_data );

        return true;
    }


    /**
     * Attempt to associate posts and menu items with previously missing parents
     *
     * An imported post's parent may not have been imported when it was first created
     * so try again. Similarly for child menu items and menu items which were missing
     * the object (e.g. post) they represent in the menu
     */
    public function backfill_parents() {
        global $wpdb;

        // find parents for post orphans
        foreach ( $this->post_orphans as $child_id => $parent_id ) {
            $local_child_id = $local_parent_id = false;
            if ( isset( $this->processed_posts[$child_id] ) )
                $local_child_id = $this->processed_posts[$child_id];
            if ( isset( $this->processed_posts[$parent_id] ) )
                $local_parent_id = $this->processed_posts[$parent_id];

            if ( $local_child_id && $local_parent_id )
                $wpdb->update( $wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d' );
        }

        // all other posts/terms are imported, retry menu items with missing associated object
        $missing_menu_items = $this->missing_menu_items;
        foreach ( $missing_menu_items as $item )
            $this->_process_menu_item( $item );

        // find parents for menu item orphans
        foreach ( $this->menu_item_orphans as $child_id => $parent_id ) {
            $local_child_id = $local_parent_id = 0;
            if ( isset( $this->processed_menu_items[$child_id] ) )
                $local_child_id = $this->processed_menu_items[$child_id];
            if ( isset( $this->processed_menu_items[$parent_id] ) )
                $local_parent_id = $this->processed_menu_items[$parent_id];

            if ( $local_child_id && $local_parent_id )
                update_post_meta( $local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id );
        }
    }
    /**
     * Update _thumbnail_id meta to new, imported attachment IDs
     */
    function _remap_featured_images() {
        // cycle through posts that have a featured image
        foreach ( $this->featured_images as $post_id => $value ) {
            if ( isset( $this->processed_posts[$value] ) ) {
                $new_id = $this->processed_posts[$value];
                // only update if there's a difference
                if ( $new_id != $value )
                    update_post_meta( $post_id, '_thumbnail_id', $new_id );
            }
        }
    }
    /**
     * Create new categories based on import information
     *
     * Doesn't create a new category if its slug already exists
     */
    function _process_categories() {
        $this->categories = apply_filters( 'wp_import_categories', $this->categories );

        if ( empty( $this->categories ) )
            return;

        foreach ( $this->categories as $cat ) {
            $cat['taxonomy'] = 'category';   //by hoang, since we save term in result table & i need to know what taxonomy for the current term
            // if the category already exists leave it alone
            $term_id = term_exists( $cat['category_nicename'], 'category' );
            if ( $term_id ) {
                if ( is_array($term_id) ) $term_id = $term_id['term_id'];
                $cat['term_id'] = (int) $term_id;   //by hoang, because we not use import_id feature,
                if ( !empty($cat['term_id']) ) {
                    $this->tracker->add_import('term', HW_WXR_Parser::get_import_id($cat), $cat);
                    $this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
                }
                continue;
            }
            if(!empty($cat['category_parent']) && $cat['category_parent'] instanceof HWIE_Module_Import_Results) {   //import result
                $cat['category_parent']->init($this->parser->importer, $this->parser->_get_option('data'));
                $cat['category_parent'] = $cat['category_parent']->parse_data()->value; //parse category parent id
                if(is_array($cat['category_parent'])) $cat['category_parent'] = $cat['category_parent']['term_id'];
            }
            $category_parent = empty( $cat['category_parent'] ) ? 0 : category_exists( $cat['category_parent'] );
            $category_description = isset( $cat['category_description'] ) ? $cat['category_description'] : '';
            $catarr = array(
                'category_nicename' => $cat['category_nicename'],
                'category_parent' => $category_parent,
                'cat_name' => $cat['cat_name'],
                'category_description' => $category_description
            );

            $id = wp_insert_category( $catarr );
            if ( ! is_wp_error( $id ) ) {
                $cat['term_id'] = $id;  //by hoang, because we not use import_id feature,
                if ( !empty($cat['term_id']) ) {
                    $this->tracker->add_import('term', ($cat), $cat);
                    $this->processed_terms[intval($cat['term_id'])] = $id;
                }
            } else {
                printf( __( 'Failed to import category %s', 'wordpress-importer' ), esc_html($cat['category_nicename']) );
                if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                    echo ': ' . $id->get_error_message();
                echo '<br />';
                continue;
            }
        }

        unset( $this->categories );
    }
    /**
     * Create new post tags based on import information
     *
     * Doesn't create a tag if its slug already exists
     */
    function _process_tags() {
        $this->tags = apply_filters( 'wp_import_tags', $this->tags );

        if ( empty( $this->tags ) )
            return;

        foreach ( $this->tags as $tag ) {
            $cat['taxonomy'] = 'post_tag';   //by hoang, since we save term in result table & i need to know what taxonomy for the current term
            // if the tag already exists leave it alone
            $term_id = term_exists( $tag['tag_slug'], 'post_tag' );
            if ( $term_id ) {
                if ( is_array($term_id) ) $term_id = $term_id['term_id'];
                $tag['term_id'] = (int)$term_id; //by hoang, because we not use import_id feature
                if ( !empty($tag['term_id']) ) {
                    $this->tracker->add_import('term', ($tag), $tag);
                    $this->processed_terms[intval($tag['term_id'])] = (int) $term_id;

                }
                continue;
            }

            $tag_desc = isset( $tag['tag_description'] ) ? $tag['tag_description'] : '';
            $tagarr = array( 'slug' => $tag['tag_slug'], 'description' => $tag_desc );

            $id = wp_insert_term( $tag['tag_name'], 'post_tag', $tagarr );
            if ( ! is_wp_error( $id ) ) {
                $tag['term_id'] = $id['term_id'];  //by hoang, because we not use import_id feature
                if ( !empty($tag['term_id']) ) {
                    $this->tracker->add_import('term', ($tag),$tag );
                    $this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
                }

            } else {
                printf( __( 'Failed to import post tag %s', 'wordpress-importer' ), esc_html($tag['tag_name']) );
                if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                    echo ': ' . $id->get_error_message();
                echo '<br />';
                continue;
            }
        }

        unset( $this->tags );
    }
    /**
     * Create new terms based on import information
     *
     * Doesn't create a term its slug already exists
     */
    function _process_terms() {
        $this->terms = apply_filters( 'wp_import_terms', $this->terms );

        if ( empty( $this->terms ) )
            return;

        foreach ( $this->terms as $term ) {
            $cat['taxonomy'] = $term['term_taxonomy'];   //by hoang, since we save term in result table & i need to know what taxonomy for the current term
            // if the term already exists in the correct taxonomy leave it alone
            $term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
            if ( $term_id ) {
                if ( is_array($term_id) ) $term_id = $term_id['term_id'];
                $term['term_id'] = (int)$term_id;    //by hoang, because we not use import_id feature
                if ( !empty($term['term_id']) ) {
                    $this->tracker->add_import('term', ($term), $term);
                    $this->processed_terms[intval($term['term_id'])] = (int) $term_id;

                }
                //bind menu location
                if(!empty($term['menu_location']) && class_exists('HW_NAVMENU', false)) {
                    HW_NAVMENU::set_menu_location($term['menu_location'], $term_id);
                }
                continue;
            }
            if(!empty($term['term_parent']) && $term['term_parent'] instanceof HWIE_Module_Import_Results) {   //import result for term_parent
                $term['term_parent']->init($this->parser->importer, $this->parser->_get_option('data', array()));
                $term['term_parent'] = $term['term_parent']->parse_data()->value; //parse category parent id
                if(is_array($term['term_parent'])) $term['term_parent'] = $term['term_parent']['term_id'];
            }
            if ( empty( $term['term_parent'] ) ) {
                $parent = 0;
            } else {
                $parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );
                if ( is_array( $parent ) ) $parent = $parent['term_id'];
            }
            $description = isset( $term['term_description'] ) ? $term['term_description'] : '';
            $termarr = array( 'slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent) );

            $id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );
            if ( ! is_wp_error( $id ) ) {
                $term['term_id'] = $id['term_id'];    //by hoang, because we not use import_id feature
                if ( !empty($term['term_id']) ){
                    $this->tracker->add_import('term', ($term), $term );
                    $this->processed_terms[intval($term['term_id'])] = $id['term_id'];

                }
                //bind menu location
                if(!empty($term['menu_location']) && class_exists('HW_NAVMENU', false)) {
                    HW_NAVMENU::set_menu_location($term['menu_location'], $term['term_id']);
                }
            } else {
                printf( __( 'Failed to import %s %s', 'wordpress-importer' ), esc_html($term['term_taxonomy']), esc_html($term['term_name']) );
                if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                    echo ': ' . $id->get_error_message();
                echo '<br />';
                continue;
            }
        }

        unset( $this->terms );
    }
    /**
     * Attempt to create a new menu item from import data
     *
     * Fails for draft, orphaned menu items and those without an associated nav_menu
     * or an invalid nav_menu term. If the post type or term object which the menu item
     * represents doesn't exist then the menu item will not be imported (waits until the
     * end of the import to retry again before discarding).
     *
     * @param array $item Menu item details from WXR file
     */
    function _process_menu_item( $item ) {
        // skip draft, orphaned menu items
        if ( 'draft' == $item['status'] )
            return;

        $menu_slug = false;
        if ( isset($item['terms']) ) {
            // loop through terms, assume first nav_menu term is correct menu
            foreach ( $item['terms'] as $term ) {
                if ( 'nav_menu' == $term['domain'] ) {
                    $menu_slug = $term['slug'];
                    break;
                }
            }
        }

        // no nav_menu term associated with this menu item
        if ( ! $menu_slug ) {
            _e( 'Menu item skipped due to missing menu slug', 'wordpress-importer' );
            echo '<br />';
            return;
        }

        $menu_id = term_exists( $menu_slug, 'nav_menu' );
        if ( ! $menu_id ) {
            printf( __( 'Menu item skipped due to invalid menu slug: %s', 'wordpress-importer' ), esc_html( $menu_slug ) );
            echo '<br />';
            return;
        } else {
            $menu_id = is_array( $menu_id ) ? $menu_id['term_id'] : $menu_id;
        }

        foreach ( $item['postmeta'] as $meta )
            $$meta['key'] = $meta['value'];

        //since you know, because we not use import_id feature
        /*if (isset($_menu_item_object_id) && 'taxonomy' == $_menu_item_type && isset( $this->processed_terms[intval($_menu_item_object_id)] ) ) {
            $_menu_item_object_id = $this->processed_terms[intval($_menu_item_object_id)];
        } else if (isset($_menu_item_object_id) && 'post_type' == $_menu_item_type && isset( $this->processed_posts[intval($_menu_item_object_id)] ) ) {
            $_menu_item_object_id = $this->processed_posts[intval($_menu_item_object_id)];
        } else if ( 'custom' != $_menu_item_type ) {
            // associated object is missing or not imported yet, we'll retry later
            $this->missing_menu_items[] = $item;
            return;
        }*/

        if ( !empty($_menu_item_menu_item_parent) && isset( $this->processed_menu_items[intval($_menu_item_menu_item_parent)] ) ) {
            $_menu_item_menu_item_parent = $this->processed_menu_items[intval($_menu_item_menu_item_parent)];
        } else if ( !empty($_menu_item_menu_item_parent) && !empty($item['post_id'])) { //for import id
            $this->menu_item_orphans[intval($item['post_id'])] = (int) $_menu_item_menu_item_parent;
            $_menu_item_menu_item_parent = 0;
        }
        else $_menu_item_menu_item_parent = 0;

        // wp_update_nav_menu_item expects CSS classes as a space separated string
        if(isset($_menu_item_classes)) $_menu_item_classes = maybe_unserialize( $_menu_item_classes );
        else $_menu_item_classes = array();
        if ( is_array( $_menu_item_classes ) )
            $_menu_item_classes = implode( ' ', hwArray::multi2single($_menu_item_classes ));

        $args = array(
            'menu-item-object-id' => isset($_menu_item_object_id)? $_menu_item_object_id:'',
            'menu-item-object' => $_menu_item_object,
            'menu-item-parent-id' => $_menu_item_menu_item_parent,
            'menu-item-position' => intval( $item['menu_order'] ),
            'menu-item-type' => $_menu_item_type,
            'menu-item-title' => $item['post_title'],
            'menu-item-url' => isset($_menu_item_url)? $_menu_item_url:'',
            'menu-item-description' => $item['post_content'],
            'menu-item-attr-title' => $item['post_excerpt'],
            'menu-item-target' => isset($_menu_item_target)? $_menu_item_target:'',
            'menu-item-classes' => $_menu_item_classes,
            'menu-item-xfn' => isset($_menu_item_xfn)? $_menu_item_xfn:'',
            'menu-item-status' => $item['status']
        );
        $id = wp_update_nav_menu_item( $menu_id, 0, $args );
        if ( $id && ! is_wp_error( $id ) ) {
            $this->tracker->add_import('menu_item',($item), (int) $id);
            $this->processed_menu_items[intval($item['post_id'])] = (int) $id;

        }
    }
    /**
     * Map old author logins to local user IDs based on decisions made
     * in import options form. Can map to an existing user, create a new user
     * or falls back to the current user in case of error with either of the previous
     */
    function _get_author_mapping() {
        /*if ( ! isset( $_POST['imported_authors'] ) )  //modified by hoang
            return;*/
        $create_users = $this->allow_create_users();

        //foreach ( (array) $_POST['imported_authors'] as $i => $old_login ) {  #change $this->authors to $this->authors_data
        foreach ( (array) $this->authors_data as $old_login => $_user ) {
            // Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
            $santized_old_login = sanitize_user( $old_login, true );
            $old_id = !empty( $this->authors_data[$old_login]['author_id'] ) ? intval($this->authors_data[$old_login]['author_id']) : false;

            if ( /*! empty( $_POST['user_map'][$i] )*/username_exists($old_login) || get_user_by('login', $old_login) ) {
                $user = get_user_by('login', $old_login);   //get_userdata( intval($_POST['user_map'][$i]) );
                if ( isset( $user->ID ) ) {
                    $this->tracker->add_import('author', $user->user_login, $user);
                    if ( $old_id ){
                        $this->processed_authors[$old_id] = $user->ID;
                    }
                    $this->author_mapping[$santized_old_login] = $user->ID;
                }
            } else if ( $create_users ) {
                if ( /*! empty($_POST['user_new'][$i])*/$this->version=='1.0' ) {
                    $user_id = wp_create_user( /*$_POST['user_new'][$i]*/$old_login, wp_generate_password() );
                } else if ( $this->version != '1.0' ) {
                    $user_data = array(
                        'user_login' => $old_login,
                        'user_pass' => wp_generate_password(),
                        'user_email' => isset( $this->authors_data[$old_login]['author_email'] ) ? $this->authors_data[$old_login]['author_email'] : '',
                        'display_name' => $this->authors_data[$old_login]['author_display_name'],
                        'first_name' => isset( $this->authors_data[$old_login]['author_first_name'] ) ? $this->authors_data[$old_login]['author_first_name'] : '',
                        'last_name' => isset( $this->authors_data[$old_login]['author_last_name'] ) ? $this->authors_data[$old_login]['author_last_name'] : '',
                    );
                    $user_id = wp_insert_user( $user_data );
                }

                if ( ! is_wp_error( $user_id ) ) {
                    if ( $old_id ) {
                        $this->tracker->add_import('author', $old_login, $user_data);
                        $this->processed_authors[$old_id] = $user_id;
                    }

                    $this->author_mapping[$santized_old_login] = $user_id;
                    printf(__('Created user %s'), esc_html($this->authors_data[$old_login]['author_display_name']));
                } else {
                    printf( __( 'Failed to create new user for %s. Their posts will be attributed to the current user.', 'wordpress-importer' ), esc_html($this->authors_data[$old_login]['author_display_name']) );
                    if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
                        echo ' ' . $user_id->get_error_message();
                    echo '<br />';
                }
            }

            // failsafe: if the user_id was invalid, default to the current user
            if ( ! isset( $this->author_mapping[$santized_old_login] ) ) {
                if ( $old_id )
                    $this->processed_authors[$old_id] = (int) get_current_user_id();
                $this->author_mapping[$santized_old_login] = (int) get_current_user_id();
            }
        }
    }
    /**
     * Use stored mapping information to update old attachment URLs
     */
    public function backfill_attachment_urls() {
        global $wpdb;
        // make sure we do the longest urls first, in case one is a substring of another
        uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

        foreach ( $this->url_remap as $from_url => $to_url ) {
            // remap urls in post_content
            $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url) );
            // remap enclosure urls
            $result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url) );
        }
    }

    /**
     * Display introductory text and file upload form
     */
    public function greet() {
        echo '<div class="narrow">';
        echo '<p>'.__( 'HOangweb! Upload your WordPress eXtended RSS (WXR) file and we&#8217;ll import the posts, pages, comments, custom fields, categories, and tags into this site.', 'wordpress-importer' ).'</p>';
        echo '<p>'.__( 'Chọn file (.xml) của bạn và nhấn nút để cài đặt.', 'wordpress-importer' ).'</p>';
        wp_import_upload_form( 'admin.php?import='.self::REGISTER_IMPORT.'&amp;step=1&amp;enable_demo=1' );

        echo '</div>';
    }
    /**
     * Decide if the given meta key maps to information we will want to import
     *
     * @param string $key The meta key to check
     * @return string|bool The key if we do want to import, false if not
     */
    public function is_valid_meta_key( $key ) {
        // skip attachment metadata since we'll regenerate it from scratch
        // skip _edit_lock as not relevant for import
        if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) )
            return false;
        return $key;
    }
    /**
     * Display pre-import options, author importing/mapping and option to
     * fetch attachments
     */
    private function _import_options() {
        $j = 0;
        ?>
        <form action="<?php echo admin_url( 'admin.php?import='.self::REGISTER_IMPORT.'&amp;step=2' ); ?>" method="post">
            <?php wp_nonce_field( 'hw-import-wordpress' ); ?>
            <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />

            <?php if ( ! empty( $this->authors ) ) : ?>
                <h3><?php _e( 'Assign Authors', 'wordpress-importer' ); ?></h3>
                <p><?php _e( 'To make it easier for you to edit and save the imported content, you may want to reassign the author of the imported item to an existing user of this site. For example, you may want to import all the entries as <code>admin</code>s entries.', 'wordpress-importer' ); ?></p>
                <?php if ( $this->allow_create_users() ) : ?>
                    <p><?php printf( __( 'If a new user is created by WordPress, a new password will be randomly generated and the new user&#8217;s role will be set as %s. Manually changing the new user&#8217;s details will be necessary.', 'wordpress-importer' ), esc_html( get_option('default_role') ) ); ?></p>
                <?php endif; ?>
                <ol id="authors">
                    <?php foreach ( $this->authors as $author ) : ?>
                        <li><?php $this->author_select( $j++, $author ); ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if ( $this->allow_fetch_attachments() ) : ?>
                <h3><?php _e( 'Import Attachments', 'wordpress-importer' ); ?></h3>
                <p>
                    <input type="checkbox" value="1" name="fetch_attachments" id="import-attachments" />
                    <label for="import-attachments"><?php _e( 'Download and import file attachments', 'wordpress-importer' ); ?></label>
                </p>
            <?php endif; ?>

            <p class="submit"><input type="submit" class="button" value="<?php esc_attr_e( 'Submit', 'wordpress-importer' ); ?>" /></p>
        </form>
    <?php
    }
    // Display import page title
    function header() {
        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>' . __( 'Nhập dữ liệu hoangweb', 'wordpress-importer' ) . '</h2>';

        /*$updates = get_plugin_updates();
        $basename = plugin_basename(__FILE__);
        if ( isset( $updates[$basename] ) ) {
            $update = $updates[$basename];
            echo '<div class="error"><p><strong>';
            printf( __( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'wordpress-importer' ), $update->update->new_version );
            echo '</strong></p></div>';
        }*/
        $this->admin_enqueue_styles();
    }
    /**
     * Enqueue stylesheets for import/export page
     *
     * @since 0.1
     */
    private function admin_enqueue_styles() {
        wp_enqueue_style( 'hw-ie-main', HW_IE_PLUGIN_URL . '/assets/css/style.css', false );
    }
    // Close div.wrap
    function footer() {
        echo '</div>';
    }
    /**
     * Decide whether or not the importer is allowed to create users.
     * Default is true, can be filtered via import_allow_create_users
     *
     * @return bool True if creating users is allowed
     */
    function _allow_create_users() {
        return apply_filters( 'hw_import_allow_create_users', true );
    }

    /**
     * Decide whether or not the importer should attempt to download attachment files.
     * Default is true, can be filtered via import_allow_fetch_attachments. The choice
     * made at the import options screen must also be true, false here hides that checkbox.
     *
     * @return bool True if downloading attachments is allowed
     */
    function _allow_fetch_attachments() {
        return apply_filters( 'hw_import_allow_fetch_attachments', true );
    }

    /**
     * Decide what the maximum file size for downloaded attachments is.
     * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
     *
     * @return int Maximum attachment file size to import
     */
    function _max_attachment_size() {
        return apply_filters( 'hw_import_attachment_size_limit', 0 );
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     * @return int 60
     */
    function _bump_request_timeout() {
        return 60;
    }
}
function hw_wordpress_importer_init() {
    load_plugin_textdomain( 'wordpress-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    /**
     * WordPress Importer object for registering the import callback
     * @global WP_Import $wp_import
     */
    $GLOBALS['hw_import'] = HW_Import::get_instance();
    register_importer( HW_Import::REGISTER_IMPORT, 'Hoangweb', __('Import <strong>posts, pages, comments, custom fields, categories, and tags</strong> from a WordPress export file.', 'wordpress-importer'), array( $GLOBALS['hw_import'], '_dispatch' ) );
}
add_action( 'admin_init', 'hw_wordpress_importer_init' );

}
/**
 * Class HWIE_Logger
 */
class HWIE_Logger extends HW_Core{
    /**
     * singleton
     * @var
     */
    public static $instance = null;
    /**
     * @var array
     */
    protected $data = array();

    /**
     * main class constructor
     * @param bool $reset
     */
    public function __construct($reset= true) {
        @session_start();
        $this->data = &HW_SESSION::get_data_group('logs_installer', $reset);
    }

    /**
     * add message
     * @param $msg
     * @param $level
     */
    public function add_log($msg, $level='success') {
        if(empty($level)) $level = 'success';
        $time = time();
        $this->data[$time] = array('message'=> $msg, 'time' => $time, 'level' =>$level);
    }

    /**
     * save logs to sessions data
     */
    /*protected function save_logs() {
        HW_SESSION::__save_session('logs', $this->data, true);
    }*/
    /**
     * get logs data
     * @return array|mixed
     */
    public function get_logs() {
        return $this->data ;
    }

    /**
     * clear logs (test)
     */
    public function clear_logs() {
        $this->data = array();
    }
}

/**
 * Class HW_Track_Installation
 */
class HW_Track_Installation extends HWIE_Logger{
    /**
     * tracking table
     */
    const TRACKING_SQL_TABLE ='hw_installation';
    /**
     * singleton
     * @var
     */
    public static $instance = null;
    /**
     * @var
     */
    protected $importer;
    /**
     * main class constructor of this class
     * @param HW_Import $importer
     */
    public function __construct($importer) {
        $this->importer = $importer;
        $this->prepare_data_table();
    }

    /**
     * create table for tracking import
     */
    public static function prepare_data_table() {
        global $wpdb;
        $wpdb->query('CREATE TABLE IF NOT EXISTS `hw_installation` (
 `_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `value` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    /**
     * add import result
     * @param $type
     * @param $name
     * @param $value
     */
    public function add_import($type, $name, $value) {
        global $wpdb;
        if(is_array($name)) $name = HW_WXR_Parser::get_import_id($name);
        if(!trim($name)) return;
        if(!is_string($value) && !is_numeric($value)) $value = base64_encode(maybe_serialize($value));
        $row = array('_type' => $type, 'name' => $name, 'value' => ($value));
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM ".self::TRACKING_SQL_TABLE. " where name='{$name}' and _type='{$type}'" );
        if($count ==0) {
            $wpdb->insert(self::TRACKING_SQL_TABLE , $row);
            /*$wpdb->replace(self::TRACKING_SQL_TABLE,
                $row,
                array(
                    '%s',
                    '%s',
                    '%s',
                )
            );*/
        }
        else $wpdb->update(self::TRACKING_SQL_TABLE , $row, array('name' => $name, '_type' => $type));

        if($type =='post') $this->importer->hw_processed_posts[$name] = $value;
        elseif($type =='term') $this->importer->processed_terms[$name] = $value;
        elseif($type =='menu_item') $this->importer->processed_menu_items[$name] = $value;

    }

    /**
     * reset installation
     */
    public function reset_table() {
        global $wpdb;
        $wpdb->query('truncate table '. self::TRACKING_SQL_TABLE);
    }

    /**
     * get import item result
     * @param $field
     * @param string $name
     * @return string
     */
    public function get_results( $field ='', $type ='',$name='') {
        global $wpdb;
        static $result = array();

        $sql = 'select * from '. self::TRACKING_SQL_TABLE;
        if($type) $sql .= ' where _type="'.$type.'"';#HW_Logger::log_file($sql);

        if(empty($result[$sql]) ) {
            //$data = !HW_Cache::_get_transient('hw_importer_results'); //alway be get new data
            //if(!$data) {

                $data = $wpdb->get_results($sql, ARRAY_A);
                foreach($data as $id => $row) {
                    if(/*is_serialized($row['value'])*/ HW_Encryptor::is_serialize_base64($row['value'])) {   //for type not is string
                        $row['value'] = HW_Encryptor::decode64($row['value']);//unserialize($row['value']);
                    }
                    $data[$row['name']] = ($field && isset($row[$field]) )? $row[$field] :$row;
                    unset($data[$id]);
                }
            $result[$sql] = $data;
                //HW_Cache::_set_transient('hw_importer_results', $data);
            //}
        }
        return $name? (isset($result[$sql][$name])? $result[$sql][$name] : '') : $result[$sql];
    }
}