<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 08/11/2015
 * Time: 15:56
 */
// No direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Available widgets
 *
 * Gather site's widgets into array with ID base, name, etc.
 * Used by export and import functions.
 *
 * @since 0.4
 * @global array $wp_registered_widget_updates
 * @return array Widget information
 */
function hw_wie_available_widgets() {

    global $wp_registered_widget_controls;

    $widget_controls = $wp_registered_widget_controls;

    $available_widgets = array();

    foreach ( $widget_controls as $widget ) {

        if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[$widget['id_base']] ) ) { // no dupes

            $available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
            $available_widgets[$widget['id_base']]['name'] = $widget['name'];

        }

    }

    return apply_filters( 'wie_available_widgets', $available_widgets );

}

/**
 * extract id base from widget instance id
 * @param $widget_instance_id
 * @return mixed
 */
function hw_wie_get_widget_id_base($widget_instance_id) {
    // Get id_base (remove -# from end) and instance ID number
    $id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
    return $id_base;
}

/**
 * remove all widgets
 */
function hw_wie_remove_all_widgets() {
    $available_widgets = hw_wie_available_widgets();

    // Get all existing widget instances
    $widget_instances = array();
    foreach ( $available_widgets as $widget_data ) {
        delete_option( 'widget_' . $widget_data['id_base'] );
    }
    update_option( 'sidebars_widgets', array() );
}
/**
 * Add mime type for upload
 *
 * Make sure the WordPress install will accept .wie uploads.
 *
 * @since 0.1
 * @param array $mime_types Currently uploadable mime types
 * @return array Mime types with additions
 */
function _hw_wie_add_mime_types( $mime_types ) {

    $mime_types['wie'] = 'application/json';

    return $mime_types;

}

add_filter( 'upload_mimes', '_hw_wie_add_mime_types' );


//test
add_action('admin_init', '_admin_init');
function _admin_init(){
    #$a=hw_wie_available_widgets();
    #hw_wie_remove_all_widgets();return;
    $available_widgets = hw_wie_available_widgets();

    // Get all existing widget instances
    $widget_instances = array();
    foreach ( $available_widgets as $widget_data ) {
        if(1||'hw_taxonomy_post_list_widget'==$widget_data['id_base']) $widget_instances[$widget_data['id_base']] = get_option( 'widget_' . $widget_data['id_base'] );
    }
    #__print($widget_instances);
}