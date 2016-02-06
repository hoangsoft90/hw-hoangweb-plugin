<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Envira_Gallery for Envira gallery core
 */

/**
 * Class HW_Gallery
 */
class HW__Gallery_Lite extends HW_Envira_Gallery{

    /**
     * Plugin textdomain.
     * @since 1.0.0
     * @var string
     */
    public  $domain = 'hw-gallery';

    /**
     * store class instance
     * @var
     */
    public static $instance = null;

    /**
     * Primary class constructor
     */
    function __construct(){
        parent::__construct();
        //load all hooks
        $this->setup_actions();
        //init skin
        //create skin selector
        if(class_exists('HW_SKIN')) {
            // instance skin, note: if use create instance from class you should use property 'skin'
            $this->skin = new HW_SKIN($this,HW_GALLERY_PLUGIN_PATH, 'hw_gallery_skins','hw-gallery-skin.php','skins');
            $this->skin->plugin_url = HW_GALLERY_PLUGIN_URL;          //set plugin url or url to app that use hw_skin
            $this->skin->enable_external_callback = false;     //turn off/on external callback
            $this->skin->enable_template_engine(true, true);
        }
    }

    /**
     * get class instance
     * @return HW_Gallery
     */
    /*public static function get_instance() {
        if(empty(self::$instance)) self::$instance = new self();
        return self::$instance;
    }*/
    /**
     * setup actions/filters
     */
    private function setup_actions() {
        //Envira_Gallery_Lite::__constructor
        add_action('hw_gallery_pre_init', array($this, '_hw_gallery_pre_init'));
        add_action('hw_gallery_init', array($this, '_hw_gallery_init'));

        // Load the plugin.
        add_action( 'init', array( $this, '_init' ), 2 );

        // Load the plugin textdomain.
        add_action( 'hw_plugins_loaded', array( $this, 'load_plugin_textdomain' ), 11 );

        //modify tabs nav
        add_filter('hw_gallery_tab_nav', array($this, '_hw_gallery_tab_nav'));
        add_filter('hw_gallery_skipped_posttypes', array($this, '_hw_gallery_skipped_posttypes'));

        //save settings
        add_action( 'save_post', array( $this, '_save_meta_boxes' ), 10, 2 );
        add_filter('hw_gallery_save_settings', array($this, '_hw_gallery_save_settings'), 10,3);
        //tabs
        add_action( 'hw_gallery_tab_hwskin', array( &$this, '_skin_tab' ) );

        //frontend
        add_filter('hw_gallery_custom_gallery_data', array($this, '_gallery_custom_gallery_data'));
    }

    /**
     * @wp_hook init
     */
    public function _init() {
        if(class_exists('HW_Gallery_Metaboxes_Lite')) {

        }

    }
    /**
     *
     * @hook hw_gallery_pre_init
     */
    public function _hw_gallery_pre_init() {

    }

    /**
     * @hook hw_gallery_init
     */
    public function _hw_gallery_init() {

    }

    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( HW_GALLERY_PLUGIN_FILE ) ) . '/languages' );
    }
    /**
     * Callback for displaying the UI for setting gallery lightbox options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function _skin_tab($post) {
        if(empty($this->skin)) return;

        // or: $this->get_config
        $current_skin = $this->gallery_metaboxes->get_config( 'hw_skin', $this->gallery_metaboxes->get_config_default( 'hw_skin' ) );
        //current skin value
        $hash_skin = isset($current_skin['hash_skin'])? $current_skin['hash_skin'] : '';

        //get hw_skin config
        $hw_skin_config = $this->gallery_metaboxes->get_config( 'hw_skin_config', $this->gallery_metaboxes->get_config_default( 'hw_skin_config' ) );
        if(empty($hw_skin_config)) {
            $hwskin_config = $this->skin->get_config(true);
        }
        else $hwskin_config = $hw_skin_config;

        ?>
        <div id="hw-gallery-skin">
            <p class="hw-gallery-intro"><?php _e( 'Cài đặt giao diện.' ); ?></p>
            <table class="form-table">
                <tbody>
                <tr id="hw-gallery-config-skin-box">
                    <th scope="row">
                        <label for="hw-gallery-config-skin"><?php _e( 'Giao diện', 'hw-gallery' ); ?></label>
                    </th>
                    <td>
                        <?php
                        echo $this->skin->get_skins_select_tag('hw_skin',$hash_skin ,array('name' => '_hw_gallery[hw_skin][hash_skin]','class'=>'hw-skin-selector'),false);
                        ?>
                        <!-- skin config -->
                        <?php echo $this->skin->create_total_skin_selector('_hw_gallery[hw_skin]', $current_skin,null, array(
                            'show_main_skin' =>0, 'show_config_field' => 1, 'show_condition_field' => 1,
                            'show_skin_options' => 1
                        ));?>
                        <!-- <input type="hidden" name="hw_skin_config" id="hw_skin_config" value="<?php #echo $hwskin_config;?>"/> -->
                        <span class="description"><?php _e( 'Chọn giao diện.', 'hw-gallery' ); ?></span>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php ?>
            <?php do_action( 'hw_gallery_hw_skin_box', $post ); ?>
        </div>
        <?php
    }

    /**
     * @hook hw_gallery_tab_nav
     * @param $tabs
     * @return mixed
     */
    public function _hw_gallery_tab_nav($tabs) {
        $tabs['hwskin'] = __('Giao diện');

        return $tabs;
    }
    /**
     * Callback for saving values from Envira metaboxes.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param object $post The current post object.
     */
    public function _save_meta_boxes($post_id, $post) {

    }

    /**
     * gallery save settings hook
     * @hook hw_gallery_save_settings
     * @param $settings
     * @param $post_id
     * @param $post
     */
    public function _hw_gallery_save_settings($settings, $post_id, $post ) {
        if(isset($_POST['_hw_gallery']['hw_skin'])) {
            $settings['config']['hw_skin'] = $_POST['_hw_gallery']['hw_skin'];
        }
        return $settings;
    }
    /**
     * Returns the post types to skip for loading Envira metaboxes.
     *
     * @since 1.0.7
     *
     * @return array Array of skipped posttypes.
     */
    public function _hw_gallery_skipped_posttypes($posttypes) {
        $exclude = hw_option('hw_exclude_posttypes', array());
        $posttypes = array_merge($posttypes, $exclude);
        return array_flip(array_flip($posttypes));
    }
}