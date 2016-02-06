<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 21:26
 */
class HW_Upgrader_Skin {
    /**
     * HW_Upgrader
     * @var
     */
    public $upgrader;
    /**
     * @var bool
     */
    public $result = false;
    /**
     * @var array
     */
    public $options = array();

    public function __construct($args = array()) {
        $defaults = array( 'url' => '', 'nonce' => '', 'title' => '', 'context' => false );
        $this->options = wp_parse_args($args, $defaults);
    }
    /**
     * @param HW_Upgrader $upgrader
     */
    public function set_upgrader(&$upgrader) {
        if ( is_object($upgrader) )
            $this->upgrader =& $upgrader;

    }
    public function set_result($result) {
        $this->result = $result;
    }

    /**
     * @param $string
     */
    public function feedback($string) {
        if ( isset( $this->upgrader->strings[$string] ) )
            $string = $this->upgrader->strings[$string];

        if ( strpos($string, '%') !== false ) {
            $args = func_get_args();
            $args = array_splice($args, 1);
            if ( $args ) {
                $args = array_map( 'strip_tags', $args );
                $args = array_map( 'esc_html', $args );
                $string = vsprintf($string, $args);
            }
        }
        if ( empty($string) )
            return;
        show_message($string);
    }

    /**
     * @param $errors
     */
    public function error($errors) {
        /*if ( ! $this->done_header )
            $this->header();*/
        if ( is_string($errors) ) {
            $this->feedback($errors);
        } elseif ( is_wp_error($errors) && $errors->get_error_code() ) {
            foreach ( $errors->get_error_messages() as $message ) {
                if ( $errors->get_error_data() && is_string( $errors->get_error_data() ) )
                    $this->feedback($message . ' ' . esc_html( strip_tags( $errors->get_error_data() ) ) );
                else
                    $this->feedback($message);
            }
        }
    }

    /**
     * @param bool $error
     * @param bool $context
     * @param bool $allow_relaxed_file_ownership
     * @return bool
     */
    public function request_filesystem_credentials( $error = false, $context = false, $allow_relaxed_file_ownership = false ) {
        $url = $this->options['url'];
        if ( ! $context ) {
            $context = $this->options['context'];
        }
        if ( !empty($this->options['nonce']) ) {
            $url = wp_nonce_url($url, $this->options['nonce']);
        }

        $extra_fields = array();

        return request_filesystem_credentials( $url, '', $error, $context, $extra_fields, $allow_relaxed_file_ownership );
    }
    public function header() {}
    public function footer() {}
    public function before() {}
    public function after() {}
}
/**
 * Plugin Installer Skin for WordPress Plugin Installer.
 *
 * @package WordPress
 * @subpackage Upgrader
 * @since 2.8.0
 */
class HW_Module_Installer_Skin extends HW_Upgrader_Skin {
    /**
     * @param array $args
     */
    public function __construct($args = array()) {
        $defaults = array( 'type' => 'web', 'url' => '', 'plugin' => '', 'nonce' => '', 'title' => '' );
        $args = wp_parse_args($args, $defaults);

        parent::__construct($args);
    }
    public function before() {
        $this->upgrader->strings['process_success'] = sprintf( __('Successfully installed the plugin <strong>%s %s</strong>.'));
    }
    public function after() {
        #$this->feedback();
    }
}