<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 25/05/2015
 * Time: 20:46
 */
//include YARPP_Meta_Box class if not exists
if(!class_exists('HW_YARPP_Meta_Box') && defined('HW_YARPP_DIR')) include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box.php');

/**
 * Class HWRP_Meta_Box_Settings
 */
if(class_exists('HW_YARPP_Meta_Box')):
class HWRP_Meta_Box_Settings extends HW_YARPP_Meta_Box{
    /**
     * shared first of all instance to this class
     * @var
     */
    private static $instance;
    /**
     * instance of HW_SKIN
     */
    public $skin;
    //private $skin_config;   //save skin config
    /**
     * constructor
     */
    public function __construct(){
        $this->setup_hooks();
        //create skin object
        self::create_skin_manager($this);

    }
    public function check_already(){

    }

    /**
     * create hw_skin instance
     * @param $ref
     */
    public static function create_skin_manager(&$ref) {
        if(class_exists('HW_SKIN')){
            $skin = new HW_SKIN($ref,HWRP_PLUGIN_PATH,'hw_relatedposts_skins','hw-relatedposts-skin.php','skins');
            $skin->plugin_url = HWRP_PLUGIN_URL;          //set plugin url or url to app that use hw_skin
            $skin->enable_external_callback = false;     //turn off/on external callback

            $skin->custom_skins_preview = true; //use own skins viewer
            /*$skin->set_template_header_info(array(    //since all module use common template header: HW Template, importer/exporter that use same
                'name' => 'HWRP Template',
                'description' => 'Description',
                'author' => 'Author'
            ));*/
            $skin->add_skin_name_list(array('hw-category-posts.php'));
            $skin->match_skin_name_list('#hw-yarpp-template-.*#');

            //set migrate data + compatible vars from active skin if found together (new)
            //set migrate data with this skin
            $skin->migrate(array(
                'cat_posts' => 'wp_query',  //array($this, '_get_wp_query'),     //warning: this make infinite loop or too heveay data for hwskin_config,
                'metaFields' => array(),
                'arrExlpodeFields' => array('title','excerpt','comment_num','date','thumb','author'),
                'instance' => array(),
                'hwtpl_wrapper_id' => 'hwtpl_wrapper_id-hw-loop-template',
                'hwtpl_scrollbar_wrapper_class' => 'hwtpl_scrollbar_wrapper_class-hw-loop-template',
                'hwtpl_pagination_class' => 'hwtpl_pagination_class',
                'awc_enable_grid_posts' => false,
                'before_widget' => '',
                'after_widget' => '',
                'open_title_link' => '',
                'close_title_link' => '',
                'before_title' => '',
                'widget_title' => '',
                'after_title' => ''
            ));
            $skin->enable_template_engine();
            if(is_object($ref)) $ref->skin = $skin;
        }
    }
    /**
     * setup hooks
     */
    private function setup_hooks(){
        add_filter('hw_yarpp_settings_save', array($this, '_yarpp_settings_save'));
        //hw_skin
        add_filter('hw_skin_skins_holders', array($this, '_hw_skin_skins_holders'), 10, 2);
        add_filter('hwskin_first_skin_data', array($this, '_hwskin_first_skin_data'), 10, 2);
        //ajax handle
        add_action("wp_ajax_hwrp_load_more_skins", array($this, "_hwrp_load_more_skins"));
        add_action("wp_ajax_nopriv_hwrp_load_more_skins", array($this, "_require_login"));
    }

    /**
     * load more skins
     */
    public function _hwrp_load_more_skins(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hwrp_load_more_skins")) {
            exit("No naughty business please");
        }
        if(class_exists('HW_Taxonomy_Post_List_widget')){
            $inst = HW_Taxonomy_Post_List_widget::get_instance();
            //_print($inst->skin);
        }
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);

        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    function _require_login() {
        echo "hacked !";
        die();
    }
    /**
     * create first of all instance for this class
     * @return HWRP_Meta_Box_Settings
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * filter each skin data for at first
     * @param $temp_skin
     * @param $folder
     * @return mixed
     */
    public function _hwskin_first_skin_data($temp_skin, $folder){
        if(isset($folder['group']) && $folder['group'] == 'HWRP_TPL'){  //extend skins folder
            $temp_skin['holder_url'] = WP_PLUGIN_URL.'/hw-taxonomy-post-list-widget/';
            $temp_skin['skin_url'] = WP_PLUGIN_URL.'/hw-taxonomy-post-list-widget/'.$temp_skin['skin_folder'].'/'.$temp_skin['path'];
            $temp_skin['screenshot_url'] = $temp_skin['skin_url'].'/'.$temp_skin['screenshot'];
        }
        return $temp_skin;
    }
    /**
     * extend skins_folder to get more skins when fetch skins
     * @param $skins_holders
     * @Param $hw_skin: current HW_SKIN instance
     */
    public function _hw_skin_skins_holders($skins_holders, $hw_skin){
        $config = $hw_skin->get_config(false);
        if($config['skin_folder'] == 'hw_relatedposts_skins') {
            //_print($skins_holders);
            $hwtpl_path = WP_PLUGIN_DIR.'/hw-taxonomy-post-list-widget/';
            $hwtpl_url = WP_PLUGIN_URL.'/hw-taxonomy-post-list-widget/';

            /*$skins_folder = (class_exists('HW_Taxonomy_Post_List_widget') ? HW_Taxonomy_Post_List_widget::SKINS_FOLDER : 'wcp_hw_skins');
            $active_theme_dir = rtrim(get_template_directory(), '/').'/'.$skins_folder;*/

            //hw taxonomy post list skins
            if(isset($skins_holders[$hwtpl_path])) {
                $skins_holders[$hwtpl_path]['group'] = 'HWRP_TPL';  //modify group name
            }
            else {
                $skins_holders[$hwtpl_path] = array(
                    'folder' => 'skins',
                    'url' => 'get_ref_plugin_url',//$hwtpl_url,
                    'group' => 'HWRP_TPL'
                );
            }
        }

        return $skins_holders;
    }
    /**
     * save yrapp settings
     * @param $new_options
     * @return mixed
     */
    public function _yarpp_settings_save($new_options){
        $hwrp_options = array();

        if(isset($_POST['hwrp_allow_post_types'])) {
            $hwrp_options['hwrp_allow_post_types'] = $new_options['hwrp_allow_post_types'] = array_keys($_POST['hwrp_allow_post_types']);
        }
        else $new_options['hwrp_allow_post_types'] = array();

        if(isset($_POST['hwrp_disable_yarpp_css'])) {
            $new_options['hwrp_disable_yarpp_css'] = $_POST['hwrp_disable_yarpp_css'] ? 1 : 0;
            $hwrp_options['hwrp_disable_yarpp_css'] = $new_options['hwrp_disable_yarpp_css'];
        }
        if(isset($_POST['hwrp_skins'])) $new_options['hwrp_skins'] = $_POST['hwrp_skins'];

        update_option('hwrp_options', $hwrp_options);   //save hwrp options to wp options
        return $new_options;
    }

    /**
     * post type related data template list
     */
    private function display_table_posttype_templates() {
        include_once(HWRP_PLUGIN_PATH. 'includes/templates/table-posttypes-related-templates.php');
    }
    /**
     * render ouput metabox
     */
    public function display(){
        $pts = get_post_types('','objects');    //list all registered post types
        ?>
        <div>
            <strong>Kích hoạt bài viết liên quan với post types dưới đây:</strong><br/>
        <?php
        //enable yarpp for bellow posts type
        $post_types = hw_yarpp_get_option( 'hwrp_allow_post_types' );
        foreach (/*$yarpp->get_post_types('objects')*/$pts as $post_type) {
            echo "<label for='hwrp_allow_post_type_{$post_type->name}'><input id='hwrp_allow_post_type_{$post_type->name}' name='hwrp_allow_post_types[{$post_type->name}]' type='checkbox' ";
            checked( in_array( $post_type->name, (array)$post_types ) );
            echo "/> {$post_type->labels->name}</label> ";
        }

        ?>
            <p><em>Sau đó tại mục ("Relatedness" options) lựa chọn những post types sẽ sử dụng để truy xuất related posts.</em>
            <em>Lưu ý: thay đổi lại post types hỗ trợ với YARPP bạn cần load lại trang 1 lần nữa sau khi lưu thay đổi cài đặt. Điều này sẽ update lại mục ("Relatedness" options).</em>
            </p>
        </div>

        <?php
        //$this->textbox( 'h1', __( 'Match threshold:', 'hwrp' ) );
        $this->checkbox( 'hwrp_disable_yarpp_css', __( "Không sử dụng CSS mặc định của YARPP?", 'hwrp' ) );

        //display related data post type templates options
        $this->display_table_posttype_templates();
    }

    /**
     * how to display related posts
     */
    public function display_how_to_use(){
        if(file_exists(HWRP_TEMPLATES_PATH.'/shortcode-template.hwtpl')) include(HWRP_TEMPLATES_PATH.'/shortcode-template.hwtpl');
    }
}
endif;