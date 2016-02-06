<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 21:11
 */
//HW_HOANGWEB::load_class('HW_DOMDocument') ;   //already exists
require_once ('class-hw-upgrader-skins.php');
/**
 * Class HW_Upgrader
 * cover from wp-admin/includes/class-wp-upgrader.php
 */
abstract class HW_Upgrader {
    /**
     * The error/notification strings used to update the user on the progress.
     *
     * @since 2.8.0
     * @var string $strings
     */
    public $strings = array();

    /**
     * @var array
     */
    public $result = array();

    /**
     * The upgrader skin being used.
     *
     * @since 2.8.0
     * @var WP_Upgrader_Skin $skin
     */
    public $skin = null;
    /**
     * @var HW_Packages_Upgrader
     */
    public $packages_updater = null;
    /**
     * @param null $skin
     * @param $updater
     */
    public function __construct( $skin = null , $package_updater= null) {
        if ( null == $skin )
            $this->skin = new HW_Upgrader_Skin();
        else
            $this->skin = $skin;

        if($package_updater == null) $this->packages_updater = new HW_Packages_Upgrader('',$this);
        else {
            $this->packages_updater = $package_updater;
            $this->packages_updater->upgrader = $this;
        }
    }
    /**
     * Initialize the upgrader.
     *
     * This will set the relationship between the skin being used and this upgrader,
     * and also add the generic strings to `WP_Upgrader::$strings`.
     *
     * @since 2.8.0
     */
    public function init() {
        $this->skin->set_upgrader($this);
        $this->generic_strings();
    }
    /**
     * Add the generic strings to WP_Upgrader::$strings.
     *
     * @since 2.8.0
     */
    public function generic_strings() {
        $this->strings['bad_request'] = __('Invalid Data provided.');
        $this->strings['fs_unavailable'] = __('Could not access filesystem.');
        $this->strings['fs_error'] = __('Filesystem error.');
        $this->strings['fs_no_root_dir'] = __('Unable to locate WordPress Root directory.');
        $this->strings['fs_no_content_dir'] = __('Unable to locate WordPress Content directory (wp-content).');
        $this->strings['fs_no_plugins_dir'] = __('Unable to locate WordPress Plugin directory.');
        $this->strings['fs_no_themes_dir'] = __('Unable to locate WordPress Theme directory.');
        /* translators: %s: directory name */
        $this->strings['fs_no_folder'] = __('Unable to locate needed folder (%s).');

        $this->strings['download_failed'] = __('Download failed.');
        $this->strings['installing_package'] = __('Installing the latest version&#8230;');
        $this->strings['no_files'] = __('The package contains no files.');
        $this->strings['folder_exists'] = __('Destination folder already exists.');
        $this->strings['mkdir_failed'] = __('Could not create directory.');
        $this->strings['incompatible_archive'] = __('The package could not be installed.');

        $this->strings['maintenance_start'] = __('Enabling Maintenance mode&#8230;');
        $this->strings['maintenance_end'] = __('Disabling Maintenance mode&#8230;');

    }

    /**
     * custom strings
     * @param $aStr
     */
    public function update_strings($aStr) {
        if(is_array($aStr)) $this->strings = array_merge($this->strings, $aStr);
    }
    /**
     * Download a package.
     *
     * @since 2.8.0
     *
     * @param string $package The URI of the package. If this is the full path to an
     *                        existing local file, it will be returned untouched.
     * @return string|WP_Error The full path to the downloaded package file, or a {@see WP_Error} object.
     */
    public function download_package( $package ) {

        /**
         * Filter whether to return the package.
         *
         * @since 3.7.0
         *
         * @param bool        $reply   Whether to bail without returning the package.
         *                             Default false.
         * @param string      $package The package file name.
         * @param WP_Upgrader $this    The WP_Upgrader instance.
         */
        $reply = apply_filters( 'upgrader_pre_download', false, $package, $this );
        if ( false !== $reply )
            return $reply;

        if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
            return $package; //must be a local file..

        if ( empty($package) )
            return new WP_Error('no_package', $this->strings['no_package']);

        $this->skin->feedback('downloading_package', $package);

        $download_file = download_url($package, 0);

        if ( is_wp_error($download_file) )
            return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

        return $download_file;
    }

    /**
     * Install a package.
     * Copies the contents of a package form a source directory, and installs them in
     * a destination directory. Optionally removes the source. It can also optionally
     * clear out the destination folder if it already exists.
     * @param array $args
     */
    public function install_package( $args = array() ) {
        global $wp_filesystem, $wp_theme_directories;
        $defaults = array(
            'source' => '', // Please always pass this
            'destination' => '', // and this
            'clear_destination' => false,
            'clear_working' => false,
            'abort_if_destination_exists' => true,
            'hook_extra' => array()
        );
        $args = wp_parse_args($args, $defaults);
        // These were previously extract()'d.
        $source = $args['source'];
        $remote_source = $source;;
        $local_destination = $destination = $args['destination'];
        $clear_destination = $args['clear_destination'];

        @set_time_limit( 300 );

        if ( empty( $source ) || empty( $destination ) ) {
            return new WP_Error( 'bad_request', $this->strings['bad_request'] );
        }
        $this->skin->feedback( 'installing_package' );

        //Filter the install response before the installation has started.
        $res = apply_filters( 'upgrader_pre_install', true, $args['hook_extra'] );
        if ( is_wp_error( $res ) ) {
            return $res;
        }

        $remote_destination = $wp_filesystem->find_folder( $local_destination );

        //Filter the source file location for the upgrade package.
        $source = apply_filters( 'upgrader_source_selection', $source, $source, $this );
        if ( is_wp_error( $source ) ) {
            return $source;
        }
        /*
		 * Protection against deleting files in any important base directories.
		 * Theme_Upgrader & Plugin_Upgrader also trigger this, as they pass the
		 * destination directory (WP_PLUGIN_DIR / wp-content/themes) intending
		 * to copy the directory into the directory, whilst they pass the source
		 * as the actual files to copy.
		 */
        $protected_directories = array( ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR, HW_HOANGWEB_PLUGINS,WP_CONTENT_DIR . '/themes' ,HW_HOANGWEB_PATH. '/cache/test-modules');
        if ( is_array( $wp_theme_directories ) ) {
            $protected_directories = array_merge( $protected_directories, $wp_theme_directories );
        }
        if ( in_array( $destination, $protected_directories ) ) {   //add source to destination folder
            $remote_destination = trailingslashit( $remote_destination ) . trailingslashit( basename( $source ) );
            $destination = trailingslashit( $destination ) . trailingslashit( basename( $source ) );
        }

        if ( $clear_destination ) {
            //We're going to clear the destination if there's something there
            $this->skin->feedback('remove_old');
            $removed = true;
            if ( $wp_filesystem->exists( $remote_destination ) ) {
                $removed = $wp_filesystem->delete( $remote_destination, true );
            }
            if ( is_wp_error($removed) ) {
                return $removed;
            } elseif ( ! $removed ) {
                return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);
            }
        } elseif ( $args['abort_if_destination_exists'] && $wp_filesystem->exists($remote_destination) ) {
            //If we're not clearing the destination folder and something exists there already, Bail.
            //But first check to see if there are actually any files in the folder.
            $_files = $wp_filesystem->dirlist($remote_destination);
            if ( ! empty($_files) ) {
                $wp_filesystem->delete($source, true); //Clear out the source files.
                return new WP_Error('folder_exists', $this->strings['folder_exists'], $remote_destination );
            }
        }
        //Create destination if needed
        if ( ! $wp_filesystem->exists( $remote_destination ) ) {
            if ( ! $wp_filesystem->mkdir( $remote_destination, FS_CHMOD_DIR ) ) {
                return new WP_Error( 'mkdir_failed_destination', $this->strings['mkdir_failed'], $remote_destination );
            }
        }
        // Copy new version of item into place.
        $result = copy_dir($source, $remote_destination);
        if ( is_wp_error($result) ) {
            if ( $args['clear_working'] ) {
                $wp_filesystem->delete( $source, true );
            }
            return $result;
        }
        //Clear the Working folder?
        if ( $args['clear_working'] ) {
            $wp_filesystem->delete( $remote_source, true );
        }

        $this->result = compact( 'source', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination' );

        return $this->result;
    }
    /**
     * Unpack a compressed package file.
     *
     * @since 2.8.0
     *
     * @param string $package        Full path to the package file.
     * @param bool   $delete_package Optional. Whether to delete the package file after attempting
     *                               to unpack it. Default true.
     * @return string|WP_Error The path to the unpacked contents, or a {@see WP_Error} on failure.
     */
    public function unpack_package( $package, $delete_package = true ) {
        global $wp_filesystem;

        $this->skin->feedback('unpack_package');

        $upgrade_folder = HW_HOANGWEB_PATH . 'cache/upgrade/';

        //Clean up contents of upgrade directory beforehand.
        $upgrade_files = $wp_filesystem->dirlist($upgrade_folder);
        if ( !empty($upgrade_files) ) {
            foreach ( $upgrade_files as $file )
                $wp_filesystem->delete($upgrade_folder . $file['name'], true);
        }

        // We need a working directory - Strip off any .tmp or .zip suffixes
        $working_dir = $upgrade_folder . basename( basename( $package, '.tmp' ), '.zip' );

        // Clean up working directory
        if ( $wp_filesystem->is_dir($working_dir) )
            $wp_filesystem->delete($working_dir, true);

        // Unzip package to working directory
        $result = unzip_file( $package, $working_dir ); //HW_Unzipper::extract($package, $working_dir );

        // Once extracted, delete the package if required.
        if ( $delete_package )
            unlink($package);

        if ( is_wp_error($result) ) {
            $wp_filesystem->delete($working_dir, true);
            if ( 'incompatible_archive' == $result->get_error_code() ) {
                return new WP_Error( 'incompatible_archive', $this->strings['incompatible_archive'], $result->get_error_data() );
            }
            return $result;
        }
        //check package contain package folder with same name
        $list_dirs = array_values(HW_File_Directory::list_folders($working_dir));
        if(count($list_dirs)==1 && $list_dirs[0]== basename( basename( $package, '.tmp' ), '.zip' )) {
            $working_dir .= '/'.basename( basename( $package, '.tmp' ), '.zip' );
        }
        return $working_dir;
    }

    /**
     * update packages file in wxr format
     */
    public function update_packages_wxr($options) {
        if(method_exists($this, 'filter_before_update')) add_filter('hw_update_packages_wxr', array($this, 'filter_before_update'), 10, 2);
        $this->packages_updater->update($options);
        if(method_exists($this, 'filter_before_update')) remove_filter('hw_update_packages_wxr', array($this, 'filter_before_update') );
    }
    /**
     * Connect to the filesystem.
     *
     * @since 2.8.0
     *
     * @param array $directories                  Optional. A list of directories. If any of these do
     *                                            not exist, a {@see WP_Error} object will be returned.
     *                                            Default empty array.
     * @param bool  $allow_relaxed_file_ownership Whether to allow relaxed file ownership.
     *                                            Default false.
     * @return bool|WP_Error True if able to connect, false or a {@see WP_Error} otherwise.
     */
    public function fs_connect( $directories = array(), $allow_relaxed_file_ownership = false ) {
        global $wp_filesystem;

        if ( false === ( $credentials = $this->skin->request_filesystem_credentials( false, $directories[0], $allow_relaxed_file_ownership ) ) ) {
            return false;
        }

        if ( ! WP_Filesystem( $credentials, $directories[0], $allow_relaxed_file_ownership ) ) {
            $error = true;
            if ( is_object($wp_filesystem) && $wp_filesystem->errors->get_error_code() )
                $error = $wp_filesystem->errors;
            // Failed to connect, Error and request again
            $this->skin->request_filesystem_credentials( $error, $directories[0], $allow_relaxed_file_ownership );
            return false;
        }

        if ( ! is_object($wp_filesystem) )
            return new WP_Error('fs_unavailable', $this->strings['fs_unavailable'] );

        if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() )
            return new WP_Error('fs_error', $this->strings['fs_error'], $wp_filesystem->errors);

        foreach ( (array)$directories as $dir ) {
            switch ( $dir ) {
                case ABSPATH:
                    if ( ! $wp_filesystem->abspath() )
                        return new WP_Error('fs_no_root_dir', $this->strings['fs_no_root_dir']);
                    break;
                case WP_CONTENT_DIR:
                    if ( ! $wp_filesystem->wp_content_dir() )
                        return new WP_Error('fs_no_content_dir', $this->strings['fs_no_content_dir']);
                    break;
                case WP_PLUGIN_DIR:
                    if ( ! $wp_filesystem->wp_plugins_dir() )
                        return new WP_Error('fs_no_plugins_dir', $this->strings['fs_no_plugins_dir']);
                    break;
                case get_theme_root():
                    if ( ! $wp_filesystem->wp_themes_dir() )
                        return new WP_Error('fs_no_themes_dir', $this->strings['fs_no_themes_dir']);
                    break;
                default:
                    if ( ! $wp_filesystem->find_folder($dir) )
                        return new WP_Error( 'fs_no_folder', sprintf( $this->strings['fs_no_folder'], esc_html( basename( $dir ) ) ) );
                    break;
            }
        }
        return true;
    } //end fs_connect();
    /**
     * @param $options
     */
    public function run( $options ) {
        $defaults = array(
            'package' => '', // Please always pass this.
            'destination' => '', // And this
            'clear_destination' => false,
            'abort_if_destination_exists' => true, // Abort if the Destination directory exists, Pass clear_destination as false please
            'clear_working' => true,
            'is_multi' => false,
            'hook_extra' => array() // Pass any extra $hook_extra args here, this will be passed to any hooked filters.
        );

        $options = wp_parse_args( $options, $defaults );

        // Connect to the Filesystem first.
        $res = $this->fs_connect( array( WP_CONTENT_DIR, $options['destination'] ) );
        // Mainly for non-connected filesystem.
        if ( ! $res ) {
            /*if ( ! $options['is_multi'] ) {
                $this->skin->footer();
            }*/
            return false;
        }
        //Download the package (Note, This just returns the filename of the file if the package is a local file)
        if(file_exists($options['package'])) $download = $options['package'];
        else $download = $this->download_package( $options['package'] );

        if ( is_wp_error($download) ) {
            $this->skin->error($download);
            //$this->skin->after();

            return $download;
        }
        $delete_package = ( $download != $options['package'] ); // Do not delete a "local" file

        //Unzips the file into a temporary directory
        $working_dir = $this->unpack_package( $download, $delete_package );
        if ( is_wp_error($working_dir) ) {
            $this->skin->error($working_dir);
            $this->skin->after();

            return $working_dir;
        }
        $options['source'] = $working_dir;
        $options['package_folder'] = basename($working_dir);

        //With the given options, this installs it to the destination directory.
        $result = $this->install_package( array(
            'source' => $working_dir,
            'destination' => $options['destination'],
            'clear_destination' => $options['clear_destination'],
            'abort_if_destination_exists' => $options['abort_if_destination_exists'],
            'clear_working' => $options['clear_working'],
            'hook_extra' => $options['hook_extra']
        ) );

        $this->skin->set_result($result);

        if ( is_wp_error($result) ) {
            $this->skin->error($result);
            $this->skin->feedback('process_failed');
        } else {
            //Install Succeeded
            $this->skin->feedback('process_success');

            //update packages xml file
            $result = $this->update_packages_wxr($options);
            if(is_wp_error($result)) {
                $this->skin->error($result);
                $this->skin->feedback('process_failed');
            }
            else {
                $this->skin->feedback('process_success');
            }
        }
        $this->update_packages_wxr($options);

        $this->skin->feedback('go_manage_page');
        $this->skin->after();
        return $result;
    }
}

/**
 * Class HW_Module_Upgrader
 */
class HW_Module_Upgrader extends HW_Upgrader {
    /**
     * @param null $skin
     * @param null $package_upgrader
     */
    public function __construct($skin, $package_upgrader) {
        parent::__construct($skin, $package_upgrader );

    }
    /**
     * Initialize the install strings.
     *
     * @since 2.8.0
     */
    public function install_strings() {
        $this->strings['no_package'] = __('Install package not available.');
        $this->strings['downloading_package'] = __('Downloading install package from <span class="code">%s</span>&#8230;');
        $this->strings['unpack_package'] = __('Unpacking the package&#8230;');
        $this->strings['installing_package'] = __('Installing the plugin&#8230;');
        $this->strings['no_files'] = __('The plugin contains no files.');
        $this->strings['process_failed'] = __('Plugin install failed.');
        $this->strings['process_success'] = __('Plugin installed successfully.');
        $this->strings['go_manage_page'] = __('<a href="'.admin_url('admin.php?page='. HW_Plugins_Manager::PAGE_SLUG).'">Trở về trang danh sách modules.</a>');
    }
    /**
     * @param upload id| file location $package
     * @param array $args
     */
    public function install( $package, $args = array() ) {
        $defaults = array(
            'clear_update_cache' => true,
        );
        $parsed_args = wp_parse_args( $args, $defaults );

        $this->init();
        $this->install_strings();
        $this->packages_updater->load_packages('module');   //you can't put in constructor because it must to done initialiazation

        add_filter('upgrader_source_selection', array($this, 'check_package') );
        add_filter('upgrader_source_selection', array($this, 'check_package') );

        $this->run( array(
            'package' => $package,
            'destination' => HW_HOANGWEB_PATH .'/cache/test-modules',//HW_HOANGWEB_PLUGINS, //do not add slash character
            'clear_destination' => false, // Do not overwrite files.
            'clear_working' => true,
            /*'hook_extra' => array(
                'type' => 'plugin',
                'action' => 'install',
            )*/
        ) );
        remove_filter('upgrader_source_selection', array($this, 'check_package') );

        if ( ! $this->result || is_wp_error($this->result) )
            return $this->result;
        else {

        }
        return true;
    }
    /**
     * valid module package by zip
     * @param $file
     * @return bool
     */
    static function check_module_zipfile($file) {
        $dirs = HW_Unzipper::get_root_dirs_fromzip($file);
        $files = HW_Unzipper::get_root_files_fromzip($file);
        if(!count($dirs) || count($dirs)>1 || count($files)) return false;

        return !in_array($dirs[0], array_values(HW_File_Directory::list_folders(HW_HOANGWEB_PLUGINS)) );
    }

    /**
     * @param $source
     * @return WP_Error
     */
    public function check_package($source) {

        $info = $this->fetch_module_info($source);

        if ( is_wp_error($info) )
            return $info;

        if(!is_array($info) || !count($info))
            return new WP_Error( 'incompatible_archive_no_plugins', $this->strings['incompatible_archive'], __( 'No valid modules were found.' ) );

        return $source;
    }
    /**
     * Deactivates a plugin before it is upgraded.
     *
     * Hooked to the {@see 'upgrader_pre_install'} filter by {@see Plugin_Upgrader::upgrade()}.
     *
     * @since 2.8.0
     * @since 4.1.0 Added a return value.
     *
     * @param bool|WP_Error  $return Upgrade offer return.
     * @param array          $plugin Plugin package arguments.
     * @return bool|WP_Error The passed in $return param or {@see WP_Error}.
     */
    public function deactivate_module_before_upgrade($return, $plugin) {

        if ( is_wp_error($return) ) //Bypass.
            return $return;

        // When in cron (background updates) don't deactivate the plugin, as we require a browser to reactivate it
        if ( defined( 'DOING_CRON' ) && DOING_CRON )
            return $return;

        $plugin = isset($plugin['plugin']) ? $plugin['plugin'] : '';
        if ( empty($plugin) )
            return new WP_Error('bad_request', $this->strings['bad_request']);

        if ( hw_is_active_module($plugin) ) {
            //Deactivate the plugin silently, Prevent deactivation hooks from running.
            hw_deactivate_modules($plugin);
        }

        return $return;
    }

    /**
     * get module info from source
     * @param $source
     */
    private function fetch_module_info($source) {
        global $wp_filesystem;
        if ( is_wp_error($source) )
            return $source;

        $working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit(WP_CONTENT_DIR), $source);
        if ( ! is_dir($working_directory) ) // Sanity check, if the above fails, let's not prevent installation.
            return $source;

        // Check the folder contains at least 1 valid plugin.
        $files = glob( $working_directory . '/*.php' );
        if ( $files ) {
            foreach ( $files as $file ) {
                $info = get_module_data( $file  );
                if ( ! empty( $info['name'] ) ) {
                    if(!isset($info['slug'])) $info['slug'] = basename(dirname($file));
                    return $info;
                    break;
                }
            }
        }
        return $source;
    }
    /**
     * @param array $data
     * @param $source
     * @return array
     */
    public function filter_before_update($data, $source) {
        $info = $this->fetch_module_info($source);
        if( is_array($info)) $data = array_merge($data, $info);
        return $data ;
    }
}

/**
 * Interface HW_Packages_Upgrader
 */
interface HW_Packages_Upgrader_Interface {
    /**
     * @param $options
     * @return mixed
     */
    public function update($options);

}
/**
 * Class HW_Packages_Upgrader
 */
if(class_exists('HWIE_WXR_Manager', false)) :
class HW_Packages_Upgrader extends HWIE_WXR_Manager implements HW_Packages_Upgrader_Interface{
    /**
     * HW_Upgrader
     * @var null
     */
    public $upgrader = null;
    /**
     * @var
     */
    private $packages_xml;
    /**
     * DOMElement
     * @var DOMElement
     */
    public $packages = null;
    private $namespaces;
    /**
     * @var null
     */
    private $parser = null;
    /**
     * @var array
     */
    public $strings = array();
    /**
     * @param $packages
     * @param $skin
     */
    public function __construct($packages = '', $upgrader = null) {
        if($packages) $this->packages_xml = $packages;
        $this->parser = new HW_WXR_Parser();

        if($upgrader)
            $this->upgrader = $upgrader;

        parent::__construct();
        //$this->load_packages(); //parse packages file
    }
    /**
     * Initialize the install strings.
     *
     * @since 2.8.0
     */
    public function install_strings() {
        $this->strings['update_wxr_packages'] = __('Updated all to wxr packages file.');
        $this->strings['update_item_to_wxr_packages'] = __('Updated %s to wxr packages file.');
        $this->strings['update_packages_error'] = __('Update packages wxr file error.');
        $this->strings['update_packages_success'] = __('Update packages wxr file successful.');
        $this->upgrader->update_strings($this->strings);
    }
    /**
     * parse packages
     * @param $tag
     * @param $file
     */
    public function load_packages( $tag='', $file = '') {
        //valid
        if(!$file && $this->packages_xml) $file = $this->packages_xml;
        if($file) $this->packages_xml = $file;  //remind packages file

        if(empty($this->packages_xml) || $this->packages || !$tag || !$file) return ;

        if(!$this->packages) {
            $packages = $this->parser->simplexml_parser->read_simplexml_object($this->packages_xml);
            $this->packages = dom_import_simplexml($packages->xml);
            $this->namespaces = $packages->namespaces;

            $xpath = new DOMXPath($this->packages->ownerDocument);
            if(count($xpath->query('/packages/'.$tag)))
            foreach ($xpath->query('/packages/'.$tag) as $item) {
                //$hw = $item->children($this->namespaces['hw']);
                $slug = $item->getElementsByTagNameNS($this->namespaces['hw'],'slug')->item(0);
                if($slug) $slug = $slug->nodeValue;

                $this->add((string) $slug, $item);
            }
        }
        $this->install_strings();
        $this->upgrader->skin->feedback('update_wxr_packages');
    }

    /**
     * @Param $name
     * @param $ele
     */
    public function add_item($name, $ele) {
        if($ele instanceof SimpleXMLElement ) $ele = dom_import_simplexml($ele);
        $this->packages->appendChild($this->packages->ownerDocument->importNode($ele, true));
        $this->add((string)$name, $ele);    //add to data
        $this->upgrader->skin->feedback('update_item_to_wxr_packages', $name);
    }

    /**
     * save xml content packages
     */
    public function save_back_packages() {
        $xml_content = $this->output_dom_to_string(false, ($this->packages));#__print($xml_content);
        //if(file_exists($this->packages_xml)) file_put_contents($this->packages_xml, $xml_content);
        if($xml_content)
            $this->upgrader->skin->feedback('save_back_packages_file');
        else
            return new WP_Error('update_packages_error', $this->strings['update_packages_error']);
    }
    public function update($options) {}

}
endif;
/**
 * Class HW_Modules_Packages_Upgrader
 */
class HW_Modules_Packages_Upgrader extends HW_Packages_Upgrader{
    /**
     * @param HW_Export $packages
     */
    public function __construct($packages) {
        parent::__construct($packages);
        //$this->load_packages('module');   //note: call in HW_Upgrader from it' child class
    }
    /**
     * @param array $module
     */
    public function add_module_item(array $module) {
        //valid
        if(empty($module['version'])) $module['version']= '1.0';

        $item = $this->createElement('module');
        $item->appendChild($this->createElement('hw:name', $module['name']));
        $item->appendChild($this->createElement('hw:slug', $module['slug']));
        if(isset($module['description'])) $item->appendChild($this->createElement('hw:description', $module['description']));
        $item->appendChild($this->createElement('hw:version', $module['version']));
        if(isset($module['force_activation'])) $item->appendChild($this->createElement('hw:force_activation', $module['force_activation']));
        if(isset($module['can_export'])) $item->appendChild($this->createElement('hw:can_export', $module['can_export']));
        if(isset($module['position'])) $item->appendChild($this->createElement('hw:position', $module['position']));

        $this->add_item($module['slug'], $item);
        return $item;
    }

    /**
     * generic strings
     */
    public function install_strings() {
        parent::install_strings();
        $this->strings['save_back_packages_file'] = __('Save xml back to modules package.');
        $this->upgrader->update_strings($this->strings);
    }
    /**
     * remove item by slug
     * @param $name
     */
    public function remove_module_item($name) {
        $this->remove($name);
    }
    /**
     * update packages
     * @param $options
     */
    public function update($options) {
        $module_path = $options['destination']. '/'. $options['package_folder'];
        $this->install_strings();
        $package_info = apply_filters('hw_update_packages_wxr', array(), $module_path) ;

        $this->add_module_item($package_info);

        $result = $this->save_back_packages();
        if(is_wp_error($result)) $this->upgrader->skin->feedback('update_packages_error', $this->strings['update_packages_error']);
        else
            $this->upgrader->skin->feedback('update_packages_success', $this->strings['update_packages_success']);

    }
}

/**
 * Class HW_Themes_Packages_Upgrader
 */
class HW_Themes_Packages_Upgrader extends HW_Packages_Upgrader {
    public function add_theme_item () {

    }
    public function update($options) {

    }
}
/**
 * Upgrade Skin helper for File uploads. This class handles the upload process and passes it as if it's a local file to the Upgrade/Installer functions.
 *
 * @package WordPress
 * @subpackage Upgrader
 * @since 2.8.0
 */
class HW_File_Upload_Upgrader {

    /**
     * The full path to the file package.
     *
     * @since 2.8.0
     * @var string $package
     */
    public $package;

    /**
     * The name of the file.
     *
     * @since 2.8.0
     * @var string $filename
     */
    public $filename;

    /**
     * Construct the upgrader
     * @param $form
     * @param $urlholder
     */
    public function __construct( $file ) {
        $this->package = $file;
    }
    /**
     * Delete the attachment/uploaded file.
     *
     * @since 3.2.2
     *
     * @return bool Whether the cleanup was successful.
     */
    public function cleanup() {
        if ( $this->id )
            wp_delete_attachment( $this->id );

        elseif ( file_exists( $this->package ) )
            return @unlink( $this->package );

        return true;
    }
}