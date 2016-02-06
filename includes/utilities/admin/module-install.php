<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/12/2015
 * Time: 17:15
 */
include_once(HW_HOANGWEB_UTILITIES. '/modules.php');
/**
 * @param $action
 * @param null $args
 */
function modules_api($action, $args = null) {
    if ( is_array( $args ) ) {
        $args = (object) $args;
    }
    $url = $http_url = 'http://api.hoangweb.com/modules/info/1.0/';
    if($action == 'module_information') {
        return HW_XMLRPC_API::current()->modules_info($args->slug);
    }
}
/**
 * Display search form for searching plugins.
 *
 * @since 2.7.0
 */
function _install_search_form( $type_selector = true ) {
    $type = isset($_REQUEST['type']) ? wp_unslash( $_REQUEST['type'] ) : 'term';
    if(HW_SESSION::get_session('search_module')) {  //because i using APF form to list modules from repository, so we not recommend to search via form method thourhg post by default
        $term = HW_SESSION::get_session('search_module');
        HW_SESSION::del_session('search_module');
    }
    else if(isset($_REQUEST['sm'])) $term = wp_unslash($_REQUEST['sm']);
    else $term = '';

    $input_attrs = '';
    $button_type = 'button screen-reader-text';

    // assume no $type_selector means it's a simplified search form
    if ( ! $type_selector ) {
        $input_attrs = 'class="wp-filter-search" placeholder="' . esc_attr__( 'Search Plugins' ) . '" ';
    }

    ?>
    <!-- <form class="search-form search-plugins" method="get">
    <input type="hidden" name="tab" value="search" /> -->
    <?php /*if ( $type_selector ) : ?>
        <select name="type" id="typeselector">
            <option value="term"<?php selected('term', $type) ?>><?php _e('Keyword'); ?></option>
            <option value="author"<?php selected('author', $type) ?>><?php _e('Author'); ?></option>
            <option value="tag"<?php selected('tag', $type) ?>><?php _ex('Tag', 'Plugin Installer'); ?></option>
        </select>
    <?php endif;*/ ?>
    <label><span class="screen-reader-text"><?php _e('Search Plugins'); ?></span>
        <input type="text" name="sm" id="hw_module_search_text" value="<?php echo esc_attr($term) ?>" <?php echo $input_attrs; ?>/>
    </label>
    <?php submit_button( __( 'Search Plugins' ), $button_type, false, false, array( 'id' => 'search-submit' ) ); ?>
    <label>
    <input type="button" id="search-submit" onclick="_search_module(this, $('#hw_module_search_text').val())" class="button " value="Tìm module">
    </label>
    <!-- </form> -->
    <?php
}
/**
 * Determine the status we can perform on a plugin.
 *
 * @since 3.0.0
 */
function _install_plugin_install_status($api, $loop = false) {
    // This function is called recursively, $loop prevents further loops.
    if ( is_array($api) )
        $api = (object) $api;

    // Default to a "new" plugin
    $status = 'install';
    $url = false;
    $update_file = false;

    /*
     * Check to see if this plugin is known to be installed,
     * and has an update awaiting it.
     */
    $update_plugins = get_site_transient('update_plugins');
    if ( isset( $update_plugins->response ) ) {
        foreach ( (array)$update_plugins->response as $file => $plugin ) {
            if ( $plugin->slug === $api->slug ) {
                $status = 'update_available';
                $update_file = $file;
                $version = $plugin->new_version;
                if ( current_user_can('update_plugins') )
                    $url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $update_file), 'upgrade-plugin_' . $update_file);
                break;
            }
        }
    }

    if ( 'install' == $status ) {
        if ( is_dir( WP_PLUGIN_DIR . '/' . $api->slug ) ) {
            $installed_plugin = get_plugins('/' . $api->slug);
            if ( empty($installed_plugin) ) {
                if ( current_user_can('install_plugins') )
                    $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
            } else {
                $key = array_keys( $installed_plugin );
                $key = reset( $key ); //Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
                $update_file = $api->slug . '/' . $key;
                if ( version_compare($api->version, $installed_plugin[ $key ]['Version'], '=') ){
                    $status = 'latest_installed';
                } elseif ( version_compare($api->version, $installed_plugin[ $key ]['Version'], '<') ) {
                    $status = 'newer_installed';
                    $version = $installed_plugin[ $key ]['Version'];
                } else {
                    //If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
                    if ( ! $loop ) {
                        delete_site_transient('update_plugins');
                        wp_update_plugins();
                        return install_plugin_install_status($api, true);
                    }
                }
            }
        } else {
            // "install" & no directory with that slug
            if ( current_user_can('install_plugins') )
                $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
        }
    }
    if ( isset($_GET['from']) )
        $url .= '&amp;from=' . urlencode( wp_unslash( $_GET['from'] ) );

    $file = $update_file;
    return compact( 'status', 'url', 'version', 'file' );
}
/**
 * valid module package by zip
 * @param $file
 * @return bool
 */
function hw_valid_module_zipfile($file) {
    $dirs = HW_Unzipper::get_root_dirs_fromzip($file);
    $files = HW_Unzipper::get_root_files_fromzip($file);
    if(!count($dirs) || count($dirs)>1 || count($files)) return false;

    return !in_array($dirs[0], array_values(HW_File_Directory::list_folders(HW_HOANGWEB_PLUGINS)) );
}

/**
 * Display plugin information in dialog box form.
 * @hook install_plugins_pre_plugin-information
 */
function hw_install_module_information() {
    global $tab;
    if ( empty( $_REQUEST['module'] ) ) {
        return;
    }
    $api = modules_api( 'module_information', array(
        'slug' => wp_unslash( $_REQUEST['module'] ),
        'is_ssl' => is_ssl(),
        'fields' => array(
            'banners' => true,
            'reviews' => true,
            'downloaded' => false,
            'active_installs' => true
        )
    ) );
    //for testing
    $api = (object)array(
        'name' => 'HW YARPP',
        'version' => '1.0',
        'author' => 'hoangweb',
        'slug' => 'hw-yarpp',
        'last_updated' => time()
    );
    if ( is_wp_error( $api ) ) {
        wp_die( $api );
    }
    $plugins_section_titles = array(
        'description'  => _x( 'Description',  'Plugin installer section title' ),
        'installation' => _x( 'Installation', 'Plugin installer section title' ),
    );
    $section = 'description';// Default to the Description tab,
    $_tab = 'plugin-information';//esc_attr( $tab ); because avaiable exists css for 'plugin-information'

    iframe_header( __( 'HW Module Install' ) );

    echo '<div id="plugin-information-scrollable">';
    echo "<div id='{$_tab}-title' class=''><div class='vignette'></div><h2>{$api->name}</h2></div>";
    //tabs
    echo "<div id='{$_tab}-tabs' class=''>\n";
    echo "<a href='#' class=''>Tab</a>";
    echo "<a href='#' class=''>Tab 1</a>";
    echo "</div>\n";

    $date_format = __( 'M j, Y @ H:i' );
    $last_updated_timestamp = strtotime( $api->last_updated );
    ?>
    <div id="<?php echo $_tab; ?>-content" class='<?php  ?>'>
        <!-- right info -->
        <div class="fyi">
            <ul>
    <?php if ( ! empty( $api->version ) ) { ?>
        <li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
    <?php } if ( ! empty( $api->author ) ) { ?>
        <li><strong><?php _e( 'Author:' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?></li>
    <?php } if ( ! empty( $api->last_updated ) ) { ?>
        <li><strong><?php _e( 'Last Updated:' ); ?></strong> <span title="<?php echo esc_attr( date_i18n( $date_format, $last_updated_timestamp ) ); ?>">
				<?php printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) ); ?>
			</span></li>
    <?php } if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
        <li><a target="_blank" href="https://develop.hoangweb.com/plugins/<?php echo $api->slug; ?>/"><?php _e( 'Hoangweb.com Plugin Page &#187;' ); ?></a></li>
    <?php } ?>
            </ul>
        </div>
        <!-- tabs content -->
        <div id="section-holder" class="wrap">
            <?php
            if(!empty($api->sections))
            foreach ( (array) $api->sections as $section_name => $content ) {
                $content = links_add_base_url( $content, 'https://develop.hoangweb.com/modules/' . $api->slug . '/' );
                $content = links_add_target( $content, '_blank' );

                $san_section = esc_attr( $section_name );

                $display = ( $section_name === $section ) ? 'block' : 'none';

                echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
                echo $content;
                echo "\t</div>\n";
            }
            ?>
        </div>
    </div>

<?php
    echo '</div>';#plugin-information-scrollable
    echo "<div id='$tab-footer'>\n";
    if ( ! empty( $api->download_link ) && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
        $status = _install_plugin_install_status( $api );
        switch ( $status['status'] ) {
            case 'install':
                if ( $status['url'] ) {
                    echo '<a class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Now' ) . '</a>';
                }
                break;
            case 'update_available':
                if ( $status['url'] ) {
                    echo '<a data-slug="' . esc_attr( $api->slug ) . '" id="plugin_update_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Update Now' ) .'</a>';
                }
                break;

        }
    }
    echo "</div>\n";
    iframe_footer();
    exit;
}
add_action('install_plugins_pre_module-information', 'hw_install_module_information');