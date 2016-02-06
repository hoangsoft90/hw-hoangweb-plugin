<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_DA_PLUGIN_URL', plugins_url('', __FILE__));
define('HW_DA_PLUGIN_PATH', plugin_dir_path(__FILE__));

include ('includes/functions.php');
include ('includes/shortcodes.php');

/**
 * Class HW_Downloadattachment
 */
class HW_Downloadattachment extends HW_Core{
    /**
     * sington for the class instance
     * @var null
     */
    public static $instance = null;

    /**
     * @var null
     */
    private $module = null;
    /**
     * module settings
     * @var null
     */
    private $options = null;

    /**
     * Class constructor.
     * @param $module
     */
    public function __construct($module) {
        $this->module = $module;

        // settings
        $this->options = array_merge(
            array( 'general' => get_option( 'download_attachments_general' ) )
        );

        // actions
        add_action( 'hw_plugins_loaded', array( &$this, 'load_textdomain' ) );
        add_action('wp_loaded', array(&$this, '_loaded'));
        // filters
        add_filter( 'the_content', array( &$this, 'add_content' ) );
    }

    /**
     * @hook init
     */
    public function _loaded() {
        $this->options['hoangweb'] = $this->module->get_values();
    }
    /**
     * Add frontend attachments box.
     * @hook the_content
     */
    public function add_content( $content ) {
        if ( ! is_singular() || ! in_array( get_post_type(), array_keys( $this->options['general']['post_types'], true ) ) || $this->options['hoangweb']['download_box_display'] === 'manually' )
            return $content;

        $args = '';

        foreach ( $this->options['general']['frontend_columns'] as $column => $bool ) {
            switch ( $column ) {
                case 'icon':
                case 'size':
                case 'date':
                    $args .= ' display_' . $column . '="' . ( $bool === true ? 1 : 0 ) . '"';
                    break;

                case 'author':
                    $args .= ' display_user="' . ( $bool === true ? 1 : 0 ) . '"';
                    break;

                case 'downloads':
                    $args .= ' display_count="' . ( $bool === true ? 1 : 0 ) . '"';
                    break;
            }
        }

        // after content
        if ( $this->options['general']['download_box_display'] === 'after_content' )
            return $content . do_shortcode( '[hw-download-attachments' . $args . ']' );
        // before content
        else
            return do_shortcode( '[hw-download-attachments' . $args . ']' ) . $content;
    }
    /**
     * Load text domain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'download-attachments', false, HW_DA_PLUGIN_PATH . 'languages/' );
    }
}
