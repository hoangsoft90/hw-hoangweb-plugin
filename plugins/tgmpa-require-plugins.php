<?php
#root>hw-install.php
/**
 * Include the TGM_Plugin_Activation class: http://tgmpluginactivation.com/
 */
require_once(dirname(dirname(__FILE__)). '/lib/TGMPA-TGM-required-Plugin/class-tgm-plugin-activation.php');
require_once(dirname(dirname(__FILE__)). '/lib/class-tgm-hw-private-plugin-activation.php');

/**
 * Class HW_Plugins_Manager
 */
class HW_Plugins_Manager {
    /**
     * list Modules page
     */
    const PAGE_SLUG = 'hw-install-hw-modules';
    /**
     * modules data
     * @var
     */
    private static $modules;
    /**
     * main class construct
     */
    public function __construct() {
        add_action('tgmpa_register', array($this, '_hwp_tgmpa_register'));
        add_action('hw_tgmpa_register', array($this, '_hwp_tgmpa_register_modules'),9);

    }

    /**
     * register modules
     * @param $module
     */
    public static function register_module($module) {
        if(isset($module['slug']) ) {
            self::$modules[$module['slug']] = $module;
        }

    }

    /**
     * return an exists module definition
     * @param $slug
     * @return null
     */
    public static function get_module($slug) {
        return isset(self::$modules[$slug])? self::$modules[$slug] : null;
    }

    /**
     * @return SimpleXMLElement
     */
    function load_modules_packages() {

    }
    /**
     * fetch module info from file
     * /utilities/modules.php
     * @param $file
     */
    public static function get_module_info($file) {
        $header = array(
            'name'          => 'Module Name',
            'description'   => 'Description',
            'version' => 'Version',
            'author'        => 'Author',
            'uri'           => 'Author URI',
        );
        if(file_exists($file)) {
            return get_file_data($file, $header);
        }
    }
    /**
     * register hoangweb modules
     */
    public function _hwp_tgmpa_register_modules() {
        $plugins = array();
        $xml = $this->fetch_modules_package() ;
        if(!empty($xml->xml->module))
        foreach ($xml->xml->module as $module) {
            $hw = $module->children($xml->namespaces['hw']);
            $plugins[] = array(
                'name' => (string) $hw->name,
                'slug' => (string) $hw->slug,
                'desc' => (string)$hw->description,
                'version' => (string) $hw->version,
                'require_wp_plugin' => (string) $hw->require_wp_plugin,
                'force_activation' => (string) $hw->force_activation,
                'can_export' => (string) $hw->can_export,
                'position' => (string) $hw->position,
                'required' => (string) $hw->required
            );
        }
        //list all modules
        /*$other_plugins = array(
            array(
                'name' => 'Điều kiện (system)',
                'slug' => 'condition',
                'desc' => 'Tạo ràng buộc điều kiện sử dụng cho các plugins.',
                'force_activation' => true,
                'can_export' => true,
                'position'=> 1
            ),
            array(
                'name' => 'HW HELP',
                'slug' => 'hw-helps',
                'desc' => 'hoangweb help',
                'force_activation' => true,
                'position' => 2,
                'can_export' =>0
            ),
            array(
                'name' => 'HW SKIN',
                'slug' => 'hw-skin',
                'desc' => 'Tạo và quản lý giao diện.',
                'force_activation' => true,
                'position' => 3,
                'can_export' => 0
            ),
            array(
                'name' => 'Quản lý skins',
                'slug' => 'skins-setting',
                'desc' => 'Quản lý stylesheets/scripts cho skins',
                'force_activation' => true,
                'can_export' => true,
                'position' => 4
            ),
            array(
                'name' => 'Tùy biến Sidebar Widget',
                'slug' => 'hw-any-widget-classes',
                'desc' => 'Tùy biến giao diện widget và sidebar.',
                'force_activation' => true,
                'can_export' => true,
                'position' => 'hw-skin'
            ),
            array(
                'name' => 'HW Importer',
                'slug' => 'hw-importer',
                'desc' => 'Import/export data',
                'force_activation' => true,
                'position' => 5,
                'can_export'=> 0
            ),
            array(
                'name' => 'Twig Timber',
                'slug' => 'timber-engine',
                'desc' => 'Template System maintain by Timber',
                'can_export' => false,
                'force_activation' => true,
                'position' => 6,
                'can_export'=> 0
            ),
            array(
                'name' => 'Vị trí hiển thị Modules',
                'slug' => 'positions',
                'desc' => 'Quản lý vị trí hiển thị module',
                'force_activation' => true,
                'position' => 7,
                'can_export' => 1
            ),
            array(
                'name' => 'Theme Options',
                'slug' => 'theme-options',
                'desc' => 'Tùy chỉnh theme options.',
                'force_activation' => true,
                'position' => 8
            ),
            // This is an example of how to include a plugin pre-packaged with a theme.
            array(
                'name'               => 'My Tooltip', // The plugin name.
                'slug'               => 'tooltip', // The plugin slug (typically the folder name).
                'required'           => true,
                'force_activation' => true,
                'source'             => HW_HOANGWEB_PLUGINS . '/tooltip/index.php', // The plugin source.
                'desc' => 'Kích hoạt thư viện Tooltip.',
                'can_export' => 0
            ),
            array(
                'name' => 'jQuery noConflict',
                'slug' => 'jquery-noconflict',
                'desc' => 'Sử lý lỗi jquery undefined.',
                'force_activation' => true,
                'can_export'=> 0
            ),

            array(
                'name' => 'Phân trang',
                'slug' => 'pagination',
                'desc' => 'Module tạo giao diện phân trang, yêu cầu cài đặt plugin '. hw_install_plugin_link('wp-pagenavi', 'wp-pagenavi'),
                'require_wp_plugin' => 'wp-pagenavi/wp-pagenavi.php',
                'can_export' => true
            ),
            array(
                'name' => 'Thanh định hướng (breadcrumb)',
                'slug' => 'breadcrumb',
                'desc' => 'Quản lý giao diện breadcrumb. Yêu cầu cài đặt plugin '. hw_install_plugin_link('breadcrumb-navxt', 'breadcrumb-navxt'),
                'require_wp_plugin' => 'breadcrumb-navxt/breadcrumb-navxt.php',
                'can_export' => true
            ),

            array(
                'name' => 'HW WPCF7',
                'slug' => 'hw-wpcf7',
                'desc' => 'Thêm chức năng cho '.hw_install_plugin_link('contact-form-7', 'contact form 7').'. Hỗ trợ Contact Form 7',
                'require_wp_plugin' => 'contact-form-7/wp-contact-form-7.php',
                'can_export' => true
            ),
            ...
            $plugins = array_merge($plugins, $other_plugins);
        );*/
        $config = array(
            'default_path' => '',                      // Default absolute path to pre-packaged plugins.
            'menu'         => self::PAGE_SLUG, // Menu slug.
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,                   // Automatically activate plugins after installation or not.
            'message'      => 'Hoangweb yêu cầu các Modules sau:',                      // Message to output right before the plugins table.
            'strings'      => array(
                'page_title'                      => __( 'Hoangweb sử dụng các Modules', 'hoangweb' ),
                'menu_title'                      => __( 'HW Modules', 'hoangweb' ),
                'installing'                      => __( 'Đang cài Plugin: %s', 'hoangweb' ), // %s = plugin name.
                #..
            )
        );
        foreach($plugins as $module){
            self::register_module($module);
        }
        hw_tgmpa( $plugins, $config );
    }

    /**
     * fetch modules package
     */
    public static function fetch_modules_package() {
        include_once (HW_HOANGWEB_PLUGINS . '/hw-importer/includes/parsers.php');
        $xml = HW_WXR_Parser_SimpleXML::read_simplexml_object(HW_HOANGWEB_PLUGINS. '/modules-package.xml' );
        return $xml;
    }

    /**
     * fetch wp plugins package
     * @return mixed
     */
    public static function fetch_plugins_package() {
        include_once (HW_HOANGWEB_PLUGINS . '/hw-importer/includes/parsers.php');
        $xml = HW_WXR_Parser_SimpleXML::read_simplexml_object(HW_HOANGWEB_PLUGINS. '/plugins-package.xml' );
        return $xml;
    }
    /**
     * Register the required plugins for this theme.
     *
     * In this example, we register two plugins - one included with the TGMPA library
     * and one from the .org repo.
     *
     * The variable passed to tgmpa_register_plugins() should be an array of plugin
     * arrays.
     *
     * This function is hooked into tgmpa_init, which is fired within the
     * TGM_Plugin_Activation class constructor.
     */

    public function _hwp_tgmpa_register(){
        /**
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array(

            // This is an example of how to include a plugin pre-packaged with a theme.
            /*array(
                    'name'               => 'TGM Example Plugin', // The plugin name.
                    'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
                    'source'             => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
                    'required'           => true, // If false, the plugin is only 'recommended' instead of required.
                    'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
                    'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
                    'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
                    'external_url'       => '', // If set, overrides default API URL and points to an external URL.
            ),*/

            // This is an example of how to include a plugin from a private repo in your theme.
            /*array(
                    'name'               => 'TGM New Media Plugin', // The plugin name.
                    'slug'               => 'tgm-new-media-plugin', // The plugin slug (typically the folder name).
                    'source'             => 'https://s3.amazonaws.com/tgm/tgm-new-media-plugin.zip', // The plugin source.
                    'required'           => true, // If false, the plugin is only 'recommended' instead of required.
                    'external_url'       => 'https://github.com/thomasgriffin/New-Media-Image-Uploader', // If set, overrides default API URL and points to an external URL.
            ),*/

            /* This is an example of how to include a plugin from the WordPress Plugin Repository.*/
            /*array(
                'name'      => 'Wp Favs - Bulk plugin installation',
                'slug'      => 'wpfavs',
                'required'  => false,
            ),
    */

        );
        $xml = $this->fetch_plugins_package() ;
        if(count($xml->xml->xpath('/plugins/plugin')))
            foreach ($xml->xml->xpath('/plugins/plugin') as $plugin) {
                $hw = $plugin->children($xml->namespaces['hw']);
                $plugins[] = array(
                    'name' => (string) $hw->name,
                    'slug' => (string) $hw->slug,
                    'required' => (string)$hw->required,
                    'external_url' => (string) $hw->external_url,
                    'source' => (string) $hw->source
                );
            }
        //other plugins
        $other_plugins = array(
            array(
                'name' => 'Wordpress Ajax Importer',
                'slug' => '',
                'required' => false,
                'external_url' => '',
                'source' => HW_HOANGWEB_URL.'/lib/TGMPA-TGM-required-Plugin/plugins/wordpress-importer-master.zip'
            ),
        );
        $plugins = array_merge($plugins, $other_plugins);

        /**
         * Array of configuration settings. Amend each line as needed.
         * If you want the default strings to be available under your own theme domain,
         * leave the strings uncommented.
         * Some of the strings are added into a sprintf, so see the comments at the
         * end of each line for what each argument will be.
         */
        $config = array(
            'default_path' => '',                      // Default absolute path to pre-packaged plugins.
            'menu'         => 'hw-install-plugins', // Menu slug.
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,                   // Automatically activate plugins after installation or not.
            'message'      => 'Hoangweb yêu cầu các plugins sau:',                      // Message to output right before the plugins table.
            'strings'      => array(
                'page_title'                      => __( 'Hoangweb sử dụng các Plugins', 'hoangweb' ),
                'menu_title'                      => __( 'HW Plugins', 'hoangweb' ),
                'installing'                      => __( 'Đang cài Plugin: %s', 'hoangweb' ), // %s = plugin name.
                'oops'                            => __( 'Something went wrong with the plugin API.', 'hoangweb' ),
                'notice_can_install_required'     => _n_noop( 'Hoangweb yêu cầu cài đặt các plugins %1$s.', 'Hoangweb yêu cầu cài đặt các plugins: %1$s.' ), // %1$s = plugin name(s).
                'notice_can_install_recommended'  => _n_noop( 'Hoangweb đề cử các plugins sau: %1$s.', 'Hoangweb đề cử các plugins sau: %1$s.' ), // %1$s = plugin name(s).
                'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
                'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
                'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
                'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
                'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
                'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
                'install_link'                    => _n_noop( 'Begin installing plugin', 'Bắt đầu cài đặt plugins' ),
                'activate_link'                   => _n_noop( 'Begin activating plugin', 'Bắt đầu kích hoạt các plugins' ),
                'return'                          => __( 'Return to Required Plugins Installer', 'hoangweb' ),
                'plugin_activated'                => __( 'Plugin activated successfully.', 'hoangweb' ),
                'complete'                        => __( 'All plugins installed and activated successfully. %s', 'hoangweb' ), // %s = dashboard link.
                'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
            )
        );

        tgmpa( $plugins, $config );
    }

}
if(is_admin() || is_call_behind()){
    new HW_Plugins_Manager();
}
