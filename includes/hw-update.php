<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/12/2015
 * Time: 16:54
 */
if(empty($_data)) $_data = $_GET;
if(empty($_request)) $_request = $_REQUEST;

include_once (HW_HOANGWEB_CLASSES_PATH. '/admin/class-hw-upgrader.php');
//main wp update
include_once (ABSPATH.'/wp-admin/update.php');

if ( isset($_request['action']) ) {
    $plugin = isset($_request['module']) ? trim($_request['module']) : '';
    $theme = isset($_request['theme']) ? urldecode($_request['theme']) : '';
    $action = isset($_request['action']) ? $_request['action'] : '';
    $modules_package = HW_HOANGWEB_PLUGINS . '/modules-package.xml';

    //install hw module
    if ( 'install-module' == $action ) {
        if ( ! current_user_can('install_plugins') )
            wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );

        include_once( HW_HOANGWEB_UTILITIES . '/admin/module-install.php' );
        check_admin_referer('install-module_' . $plugin);

        $api = (object) modules_api('module_information', array('slug' => $plugin));
        if ( is_wp_error($api) )
            wp_die($api);

        $title = __('Module Install');
        $parent_file = 'plugins.php';
        $submenu_file = 'plugin-install.php';

        $title = sprintf( __('Installing Module: %s'), $api->name . ' ' . $api->version );
        $nonce = 'install-plugin_' . $plugin;
        $url = 'hw-update.php?action=install-module&plugin=' . urlencode( $plugin );
        if ( isset($_data['from']) )
            $url .= '&from=' . urlencode(stripslashes($_data['from']));

        $type = 'web'; //Install plugin type, From Web or an Upload.

        $upgrader = new HW_Module_Upgrader(
            new HW_Module_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) ,
            new HW_Modules_Packages_Upgrader($modules_package)
        );
        if(!empty($api->download_link)) $upgrader->install('http://localhost/wp2/wp-content/plugins/hw-hoangweb/data/uploads/test-module.zip');

    }
    //activate module
    elseif($action == 'activate-module') {
        if ( ! current_user_can('update_plugins') )
            wp_die(__('You do not have sufficient permissions to update plugins for this site.'));

        check_admin_referer('activate-module_' . $plugin);
        wp_redirect( admin_url('hw-update.php?action=activate-plugin&failure=true&plugin=' . urlencode( $plugin ) . '&_wpnonce=' . $_data['_wpnonce']) );
        hw_activate_modules($plugin);
        wp_redirect( admin_url('hw-update.php?action=activate-plugin&success=true&plugin=' . urlencode( $plugin ) . '&_wpnonce=' . $_data['_wpnonce']) );
        die();
    }
    //upload module
    elseif ( 'upload-module' == $action ) {
        if ( ! current_user_can( 'upload_plugins' ) ) {
            wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );
        }
        check_admin_referer('module-upload');

        $file_upload = new HW_File_Upload_Upgrader($_data['package']);

        $title = sprintf( __('Installing Module from uploaded file: %s'), esc_html( basename( $_data['package'] ) ) );
        $nonce = 'module-upload';
        $url =  add_query_arg(array('file' => base64_encode($_data['package'])), 'hw-update.php?action=upload-module' );
        $type = 'upload'; //Install plugin type, From Web or an Upload.

        $upgrader = new HW_Module_Upgrader(
            new HW_Module_Installer_Skin( compact('type', 'title', 'nonce', 'url') ),
            new HW_Modules_Packages_Upgrader($modules_package)
        );
        $result = $upgrader->install( ($_data['package']) );

        if ( $result || is_wp_error($result) ) {
            $file_upload->cleanup();
        }

    }
}