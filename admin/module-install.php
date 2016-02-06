<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 06/12/2015
 * Time: 21:45
 */
//cover from wp-admin/plugin-install.php
/**
 * Install plugin administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */
// TODO route this pages via a specific iframe handler instead of the do_action below
if ( !defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'module-information' == $_GET['tab'] ) )
    define( 'IFRAME_REQUEST', true );

/**
 * WordPress Administration Bootstrap.
 */
if(isset($_GET['_root'])) $abspath = (base64_decode($_GET['_root']));
else $abspath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

require_once( $abspath.  '/wp-admin/admin.php' );
//require_once $abspath . '/wp-load.php';
require_once (HW_HOANGWEB_UTILITIES . '/admin/admin.php');

if ( ! current_user_can('install_plugins') )
    wp_die(__('You do not have sufficient permissions to install plugins on this site.'));

//do not allow for multisite

$wp_list_table = hw_get_list_table('HW_Module_Install_List_Table');
//$wp_list_table = _get_list_table('WP_Plugin_Install_List_Table');
$pagenum = $wp_list_table->get_pagenum();

if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
    $location = remove_query_arg( '_wp_http_referer', wp_unslash( $_SERVER['REQUEST_URI'] ) );

    if ( ! empty( $_REQUEST['paged'] ) ) {
        $location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
    }

    wp_redirect( $location );
    exit;
}

$wp_list_table->prepare_items();

$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );

if ( $pagenum > $total_pages && $total_pages > 0 ) {
    wp_redirect( add_query_arg( 'paged', $total_pages ) );
    exit;
}

$title = __( 'Add Plugins' );
$parent_file = 'plugins.php';

wp_enqueue_script( 'plugin-install' );
if ( 'module-information' != $tab )
    add_thickbox();

$body_id = ($tab =='module-information')? 'plugin-information' :$tab;   //for stylesheet by wp

wp_enqueue_script( 'updates' );


/**
 * Fires before each tab on the Install Plugins screen is loaded.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_plugins_pre_module-information'.
 *
 * @since 2.7.0
 */
do_action( "install_plugins_pre_$tab" );

if ( $tab !== 'upload' ) {
    $wp_list_table->views();
    echo '<br class="clear" />';
}
/**
 * WordPress Administration Template Header.
 */
include(ABSPATH . 'wp-admin/admin-header.php');
?>
    <div class="wrap">
        <h2>
        </h2>
    </div>
<?php
wp_print_request_filesystem_credentials_modal();
/**
 * WordPress Administration Template Footer.
 */
include(ABSPATH . 'wp-admin/admin-footer.php');