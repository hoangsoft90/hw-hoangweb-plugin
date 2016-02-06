<?php
/**
 * Uninstall procedure.
 * Last update 2013-12-09
 * @since Version 4.0.7
 * @author Eliezer Vargas
 */

/* Exit if plugin delete hasn't be called */
if (!defined('WP_UNINSTALL_PLUGIN')) exit();

global $wpdb;

/* Yarpp option names */
$optNames = array(
    'hw_yarpp',
    'hw_yarpp_pro',
    'hw_yarpp_fulltext_disabled',
    'hw_yarpp_optin_timeout',
    'hw_yarpp_version',
    'hw_yarpp_version_info',
    'hw_yarpp_version_info_timeout',
    'hw_yarpp_activated',
    'widget_hw_yarpp_widget',
    'hw_yarpp_upgraded'
);

/* Select right procedure for single or multi site */
if(is_multisite()) {

    /* Get sites ids */
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

    /* Get main site id */
    $original_blog_id = get_current_blog_id();

    /* loop through all sites */
    foreach($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        clean($optNames, $wpdb);
    }/*end foreach*/

    switch_to_blog($original_blog_id);

} else {

    clean($optNames, $wpdb);

}/*end if*/


/**
 * Loop through option array and delete the option and clear and drop cache tables.
 * @param array $opts Array of yarpp's options
 * @param object $wpdb Wordpress db global
 */
function clean(Array $opts, $wpdb){

    foreach($opts as $opt){
        delete_option($opt);
    }
 
    /* Truncate, clear and drop yarpp cache */
    $wpdb->query('DELETE FROM `'.$wpdb->prefix.'postmeta` WHERE meta_key LIKE "%hw_yarpp%"');
    $wpdb->query('TRUNCATE TABLE `'.$wpdb->prefix.'hw_yarpp_related_cache`');
    wp_cache_flush();
    $wpdb->query('DROP TABLE `'.$wpdb->prefix.'hw_yarpp_related_cache`');

    /* Delete users yarpp related data */
    $wpdb->query('DELETE FROM `'.$wpdb->prefix.'usermeta` WHERE meta_key LIKE "%hw_yarpp%"');

}/*end clean */