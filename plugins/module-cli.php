<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_HW_Module
 * @cmd wp hw-module
 */
if(class_exists('HW_CLI_Command', false)):
class HW_CLI_HW_Module extends HW_CLI_Command {
    /**
     * active module
     * @param $args
     * @param $assoc_args
     * @cmd wp hw-module active xx
     */
    public function activate($args, $assoc_args) {
        //note run any code from this context is make code of site fully execute such as wp ajax
        $name = $this->get_cmd_arg($assoc_args, 'name');    //get module name
        if($name) {
            #hw_activate_modules(array($name));
            $plugin_table = new HW_TGMPA_List_Table();
            $plugin_table->process_bulk_action(array($name), 'tgmpa-bulk-activate');
        }

        $this->success( ' activate module '.$name.' successful.' );

    }

    /**
     * deactive the module
     * @param $args
     * @param $assoc_args
     * @cmd wp hw-module deactive xx
     */
    public function deactivate($args, $assoc_args) {
        $name = $this->get_cmd_arg($assoc_args, 'name');    //get module name
        if($name) hw_deactivate_modules(array($name));
        $this->result( ' deactivate module '.$name.' successful.' );
    }

    /**
     * list modules
     * @param $args
     * @param $assoc_args
     */
    public function _list_($args, $assoc_args) {
        $status = $this->get_cmd_arg($assoc_args, 'status', 'active');
        $modules = hw_get_modules();
        foreach($modules as $module) {
            echo $module[0].PHP_EOL.', ';
        }
    }

    /**
     * list all modules commands
     * @param $args
     * @param $assoc_args
     */
    public function all_cmds($args, $assoc_args) {
        HW_HOANGWEB::load_class('HW_Ajax');
        $Utilities = HW_CLI_Command_Utilities::get_instance();
        HW_Ajax::result($Utilities->get_clis());
    }
    public function test(){
        //WP_CLI::set_logger('sdff');
        //WP_CLI::log("dfsdf");
        $this->result('sdfsdgasfggdg3454366');
    }
}
//hw_register_module_cli('hw-module', __FILE__,'HW_CLI_HW_Module' ); //not optimized
endif;
/**
 * Class HW_CLI_Hoangweb
 * @cmd hoangweb
 */
if(class_exists('HW_CLI_Command', false)):
class HW_CLI_Hoangweb extends HW_CLI_HW_Module{
    /**
     * reset wp learn from plugin wordpress-reset
     * @param $args
     * @param $assoc_args
     */
    public function resetwp($args, $assoc_args) {
        global $current_user, $user_id;
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        $blogname = get_option( 'blogname' );
        $admin_email = get_option( 'admin_email' );
        $blog_public = get_option( 'blog_public' );

        if ( $current_user->user_login != 'admin' )
            $user = get_user_by( 'login', 'admin' );

        if ( empty( $user->user_level ) || $user->user_level < 10 )
            $user = $current_user;

        global $wpdb, $reactivate_wp_reset_additional;

        $prefix = str_replace( '_', '\_', $wpdb->prefix );
        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE $table" );
        }

        $result = wp_install( $blogname, $user->user_login, $user->user_email, $blog_public );
        extract( $result, EXTR_SKIP );

        $query = $wpdb->prepare( "UPDATE $wpdb->users SET user_pass = %s, user_activation_key = '' WHERE ID = %d", $user->user_pass, $user_id );
        $wpdb->query( $query );

        $get_user_meta = function_exists( 'get_user_meta' ) ? 'get_user_meta' : 'get_usermeta';
        $update_user_meta = function_exists( 'update_user_meta' ) ? 'update_user_meta' : 'update_usermeta';

        if ( $get_user_meta( $user_id, 'default_password_nag' ) )
            $update_user_meta( $user_id, 'default_password_nag', false );

        if ( $get_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' ) )
            $update_user_meta( $user_id, $wpdb->prefix . 'default_password_nag', false );

        if ( defined( 'REACTIVATE_WP_RESET' ) && REACTIVATE_WP_RESET === true )
            @activate_plugin( plugin_basename( __FILE__ ) );

        if ( ! empty( $reactivate_wp_reset_additional ) ) {
            foreach ( $reactivate_wp_reset_additional as $plugin ) {
                $plugin = plugin_basename( $plugin );
                if ( ! is_wp_error( validate_plugin( $plugin ) ) )
                    @activate_plugin( $plugin );
            }
        }

        wp_clear_auth_cookie();
        wp_set_auth_cookie( $user_id );

        wp_redirect( admin_url() . '?reset' );
        //exit();
        $this->result('Reset WP successful.');
    }

    /**
     * delete all posts
     * @param $args
     * @param $assoc_args
     */
    public function delete_all_posts($args, $assoc_args) {
        global $wpdb;
        $this->command_log('Clear all old posts.');
        $removed = array();
        if($wpdb->query("TRUNCATE TABLE $wpdb->posts")) $removed[] = 'Posts removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->postmeta")) $removed[] = 'Postmeta removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->comments")) $removed[] = 'Comments removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->commentmeta")) $removed[] = 'Commentmeta removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->links")) $removed[] = 'Links removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->terms")) $removed[] = 'Terms removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->term_relationships")) $removed[] = 'Term relationships removed';
        if($wpdb->query("TRUNCATE TABLE $wpdb->term_taxonomy")) $removed[] = 'Term Taxonomy removed';
        if($wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient_%')")) $removed[] = 'Transients removed';
        $wpdb->query("OPTIMIZE TABLE $wpdb->options");

        WP_CLI::success('deleted all posts in your blog.');
    }
}
endif;