<?php
//mimic the actuall admin-ajax
define('DOING_AJAX', true);
define('WP_DEBUG', false);  //turn off debug

if (!isset( $_REQUEST['action']))
    die('-1');

//make sure you update this line
//to the relative location of the wp-load.php
//require_once('../../../wp-load.php');

#$_SERVER['HTTP_HOST'] = 'yourdomain.com';

ini_set('display_errors', true);
include ('classes/class-core.php');
HW_WP::load_wp();

function switch_to_blog_cache_clear( $blog_id, $prev_blog_id = 0 ) {
    if ( $blog_id === $prev_blog_id )
        return;

    wp_cache_delete( 'notoptions', 'options' );
    wp_cache_delete( 'alloptions', 'options' );
}
add_action( 'switch_blog', 'switch_to_blog_cache_clear', 10, 2 );
/**
 * END LOAD WORDPRESS
 */

#switch_to_blog( 31 );
#var_dump(get_bloginfo('name'));
#restore_current_blog();

//switch to blog
if(isset($_REQUEST['blog']) && is_numeric($_REQUEST['blog'])) {
    #switch_to_blog($_REQUEST['blog']);
    #restore_current_blog($_REQUEST['blog']);
    //global $wpdb;
    //$wpdb->blogid
}
/**
 * do before show content ajax
 */
function hw_ajax_before_content() {
    $allow_head = isset($_REQUEST['include_head']) && $_REQUEST['include_head']? true : false;  //call wp_head()
    if($allow_head) wp_head();
}

/**
 * clean wp_head
 */
if(!function_exists('hw_clean_wp_head')){
function hw_clean_wp_head() {
    if(!defined('DOING_AJAX')) return;
    /* This will remove Really Simple Discovery link from the header */
    remove_action('wp_head', 'rsd_link');

    /* This will remove the Wordpress generator tag  */
    remove_action('wp_head', 'wp_generator');

    /* This will remove the standard feed links */
    remove_action( 'wp_head', 'feed_links', 2 );

    /* This will remove the extra feed links */
    remove_action( 'wp_head', 'feed_links_extra', 3 );

    /* This will remove index link */
    remove_action('wp_head', 'index_rel_link');

    /* This will remove wlwmanifest */
    remove_action('wp_head', 'wlwmanifest_link');

    /* This will remove parent post link */
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);

    /*This will remove start post link */
    remove_action('wp_head', 'start_post_rel_link');

    /* This will remove the prev and next post link */
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

    /* This will remove shortlink for the page */
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

    //remove all hook assigned to wp_head
    global $wp_filter;
    foreach($wp_filter['wp_enqueue_scripts'] as $periror => $cb){
        foreach($cb as $name => $d){
            //if(strpos($name,'wpcf7')===false)
            remove_action('wp_enqueue_scripts',$name);      //remove scripts from frontend
            remove_action('admin_enqueue_scripts',$name);   //remove scripts from admin
        }
    }
}
}
hw_clean_wp_head();

//register new hooks
add_action('wp_footer', function() {
    echo '
    <script type="text/javascript">
    //SyntaxHighlighter
    if(typeof SyntaxHighlighter != "undefined") SyntaxHighlighter.all();
    </script>
    ';
});


//Typical headers
header('Content-Type: text/html');
send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');

/*Next we need to call the actual methods we want to invok*/
$action = esc_attr(trim($_REQUEST['action']));
$allow_footer = isset($_REQUEST['include_footer']) && $_REQUEST['include_footer']? true : false;  //call wp_footer()

//A bit of security
$allowed_actions = array(
    //'custom_action1',
    //'custom_action2'
);

//For logged in users
#add_action('HW_custom_action1', 'handler_fun1');
#add_action('HW_custom_action2', 'handler_fun1');

//For guests
#add_action('HW_nopriv_custom_action2', 'handler_fun2');
#add_action('HW_nopriv_custom_action1', 'handler_fun1');


if(empty($allowed_actions) || in_array($action, $allowed_actions)) {
    //load main ajax content
    if(is_user_logged_in()){
        do_action('HW_'.$action, 'hw_ajax_before_content', false);
    }else{
        do_action('HW_nopriv_'.$action, 'hw_ajax_before_content', false);
    }
    if($allow_footer) wp_footer();  //load wp_footer()

} else {
    die('-1');
}

?>