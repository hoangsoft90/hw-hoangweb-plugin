<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once (dirname(__FILE__). '/includes/parsers.php');
include_once (dirname(__FILE__). '/includes/exporter.php');
/**
 * Class HW_Import_Export
 */
class HW_Import_Export {
    /**
     * HW_Export
     * @var
     */
    protected $exporter;
    /**
     * @var string
     */
    private $menu = 'hw_wxr_settings';
    /**
     * main class construct method
     */
    public function __construct() {
        $this->exporter = new HW_Export(null);
        $this->setup_actions();
    }

    /**
     * setup actions
     */
    public function setup_actions() {
        //wp core export screen
        //add_action('export_filters', array($this, '_wxr_export_custom'));
        add_action( 'admin_init', array($this, '_wxr_register_settings' ));
        add_action( 'admin_menu', array($this, '_wxr_settings_menu' ));

        add_action( 'admin_init', array($this, '_wxr_process_settings_export'), 1000); //admin_init
        add_action( 'admin_init', array($this, '_wxr_process_settings_import') );
    }

    /**
     * get module object by slug
     * @param $name
     * @return array
     */
    public function get_module($name) {
        return HW_Module::get_module_by_name($name, 1);
    }
    /**
     * location: wp-admin/export.php
     * @hook export_filters
     */
    public function _wxr_export_custom(){
        echo 'sdgdg';
    }
    /**
     * Register the plugin options
     * @hook admin_init
     */
    function _wxr_register_settings() {
        register_setting( 'hw_wxr_settings_group', 'hw_wxr_settings' );
    }
    /**
     * Register the settings page
     * @hook admin_menu
     */
    public function _wxr_settings_menu() {
        //add_options_page( __( 'HW Settings Import and Export' ), __( 'HW Import/Export' ), 'manage_options', 'hw_wxr_settings', array($this, '_wxr_settings_page') );
        add_submenu_page(
            'admin.php', #HW_HOANGWEB_Settings
            __( 'HW Settings Import and Export' ),
            __( 'HW Import/Export' ),
            'manage_options',
            $this->menu,//'admin.php?page='.$this->menu,
            array($this, '_wxr_settings_page')
        );
        HW_HOANGWEB_Settings::add_custom_submenu_page('hw-import-export', array(
            __('HW Import/Export'),
            'manage_options',
            admin_url('admin.php?page='. $this->menu)
        ));
    }
    /**
     * Render the settings page
     */
    public function _wxr_settings_page() {
        $options = get_option( 'hw_wxr_settings' ); ?>
        <div class="wrap">
            <div id="icon-tools" class="icon32"><br /></div>
            <h2><?php screen_icon(); _e('HOANGWEB Settings'); ?></h2>

            <form method="post" action="options.php" class="options_form">
                <?php settings_fields( 'hw_wxr_settings_group' ); ?>
                <table class="form-table">
                    <!--
                    <tr valign="top">
                        <th scop="row">
                            <label for="hw_wxr_settings[text]"><?php _e( 'Plugin Text' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" id="hw_wxr_settings[text]" style="width: 300px;" name="hw_wxr_settings[text]" value="<?php if( isset( $options['text'] ) ) { echo esc_attr( $options['text'] ); } ?>"/>
                            <p class="description"><?php _e( 'Enter some text for the plugin here.'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scop="row">
                            <label for="hw_wxr_settings[label]"><?php _e( 'Label Text' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" id="hw_wxr_settings[label]" style="width: 300px;" name="hw_wxr_settings[label]" value="<?php if( isset( $options['label'] ) ) { echo esc_attr( $options['label'] ); } ?>"/>
                            <p class="description"><?php _e( 'Enter some text for the label here.' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scop="row">
                            <span><?php _e( 'Enable Feature' ); ?></span>
                        </th>
                        <td>
                            <input class="checkbox" type="checkbox" id="hw_wxr_settings[enabled]" name="hw_wxr_settings[enabled]" value="1" <?php checked( 1, isset( $options['enabled'] ) ); ?>/>
                            <label for="hw_wxr_settings[enabled]"><?php _e( 'Enable some feature in this plugin?' ); ?></label>
                        </td>
                    </tr>
                    -->
                </table>
                <?php #submit_button(); ?>
            </form>

            <div class="metabox-holder">
                <div class="postbox">
                    <h3><span><?php _e( 'Export Settings' ); ?></span></h3>
                    <div class="inside">
                        <form method="post">
                            <p><label><input type="radio" name="module" value="all" /> Tất cả</label></p>
                        <?php foreach(HW_TGM_Module_Activation::get_modules() as $slug => $plugin){
                            if(empty($plugin['can_export']) || !HW_Module::is_active($slug)) continue;
                            ?>
                            <p><label><input type="radio" name="module" value="<?php echo esc_attr( $plugin['slug'] ); ?>" /> <?php echo esc_html( $plugin['name'] ); ?></label></p>
                        <?php }?>

                        <p><?php _e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.' ); ?></p>

                            <p><input type="hidden" name="hw_wxr_action" value="export_settings" /></p>
                            <p>
                                <?php wp_nonce_field( 'hw_wxr_export_nonce', 'hw_wxr_export_nonce' ); ?>
                                <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
                            </p>
                        </form>
                    </div><!-- .inside -->
                </div><!-- .postbox -->

                <div class="postbox">
                    <h3><span><?php _e( 'Import Settings' ); ?></span></h3>
                    <div class="inside">
                        <p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.' ); ?></p>
                        <!--
                        <form method="post" enctype="multipart/form-data">
                            <p>
                                <input type="file" name="import_file"/>
                            </p>
                            <p>
                                <input type="hidden" name="hw_wxr_action" value="import_settings" />
                                <?php wp_nonce_field( 'hw_wxr_import_nonce', 'hw_wxr_import_nonce' ); ?>
                                <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
                            </p>
                        </form>
                        -->
                        Nhấn <a href="<?php echo admin_url('admin.php?import=hw-wordpress')?>">vào đây</a> để tiến hành Import dữ liệu.
                    </div><!-- .inside -->
                </div><!-- .postbox -->
            </div><!-- .metabox-holder -->
        </div>
    <?php
    }
    /**
     * Process a settings export that generates a .json file of the shop settings
     * @hook admin_init
     */
    public function _wxr_process_settings_export() {
        if( empty( $_POST['hw_wxr_action'] ) || 'export_settings' != $_POST['hw_wxr_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['hw_wxr_export_nonce'], 'hw_wxr_export_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;
        //module to export
        $module_slug = hw__post('module');
        if($module_slug) {
            $module = $this->get_module($module_slug);
            //get module options
            if($module) $settings = $module->export();
        }

        if(empty($settings)){
            $settings = array();
        }

        //output data to file
        ignore_user_abort( true );
        nocache_headers();
        $this->exporter->export();

        /*header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=hw-settings-export-'.$module_slug.'-' . date( 'm-d-Y' ) . '.json' );
        header( "Expires: 0" );
        echo json_encode( $settings );*/
        exit;
    }

    /**
     * Process a settings import from a json file
     * @hook admin_init
     */
    public function _wxr_process_settings_import() {
        if( empty( $_POST['hw_wxr_action'] ) || 'import_settings' != $_POST['hw_wxr_action'] )
            return;
        if( ! wp_verify_nonce( $_POST['hw_wxr_import_nonce'], 'hw_wxr_import_nonce' ) )
            return;
        if( ! current_user_can( 'manage_options' ) )
            return;

        $extension = end( explode( '.', $_FILES['import_file']['name'] ) );
        $file_size = $_FILES['import_file']['size'];
        //valid
        if( $extension != 'json' ) {
            wp_die( __( 'Please upload a valid .json file' ) );
        }
        $import_file = $_FILES['import_file']['tmp_name'];
        if( empty( $import_file ) ) {
            wp_die( __( 'Please upload a file to import' ) );
        }
        if ($_FILES['import_file']['error'] > 0) {
            wp_die("Error happens");
        }
        if($file_size > 500000) {
            wp_die("Invalid file or file size too big.");
        }
        // Retrieve the settings from the file and convert the json object to an array.
        $settings = (array) json_decode( file_get_contents( $import_file ) );
        update_option( 'hw_wxr_settings', $settings );
        wp_safe_redirect( admin_url( 'options-general.php?page=hw_wxr_settings' ) ); exit;
    }
}

if(is_admin()) new HW_Import_Export();