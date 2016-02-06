<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/05/2015
 * Time: 21:10
 */
if(class_exists('AdminPageFramework_MetaBox')):
class HWML_Slider_Settings_Metabox extends AdminPageFramework_MetaBox {
    /**
     * @var array
     */
    private $data = array();
    /**
     * current class instance
     */
    static private $instance;

    /*public function __construct($id, $title, $slug, $context, $priority) {
        parent::__construct($id, $title, $slug, $context, $priority);

        //get all post type terms
        $select_posttype = hwml_get_option('source_posttype');

        //prepare terms list for active post type
        $data = array();
        $terms = HW_POST::get_all_terms_taxonomies($select_posttype);    //get all terms from post type
        foreach($terms as $t) {
            $this->data[base64_encode($t->slug.'|'.$t->taxonomy)] = $t->name;
        }
    }*/
    /**
     * set class instance
     * @param $inst: an object instanceof this class
     */
    static public function setInstance($inst){
        if($inst instanceof HWML_Slider_Settings_Metabox){
            self::$instance = $inst;
        }
    }

    /**
     * edit /add new slider link
     * @return string|void
     */
    static function get_edit_sliders_url(){
        return admin_url('admin.php?page=hw-metaslider');
    }

    /**
     * manage hwml_shortcode post type for hw sliders & meta slider
     * @return string|void
     */
    static function get_edit_hw_sliders_url(){
        if(class_exists('HWML_Shortcodes_List')) return admin_url('edit.php?post_type='.HWML_Shortcodes_List::hwml_slider_posttype);
    }

    /**
     * return once instance for this class
     * @return mixed
     */
    static public function getInstance(){
        return self::$instance;
    }

    /**
     * get all metalsliders data
     */
    public static function get_all_mlsliders(){
        $sliders = array();
        //get all sliders from hoangweb custom metaslider
        $posts = get_posts( array(
            'post_type' => 'hw-ml-slider',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'ASC',
            'posts_per_page' => -1
        ) );

        foreach ( $posts as $post ) {
            $sliders[$post->ID] =  $post->post_title;
        }
        return $sliders;
    }
    /*public function start() {

    }*/

    /**
     * display something
     */
    public function do_HWML_Slider_Settings_Metabox() {
        echo '<p>Chọn nguồn slider và giao diện rồi nhấn nút Lưu lại ở bên phải.</p>';

    }

    /**
     * pre-defined validation callback method
     * @param $sInput
     * @param $sOldInput
     * @return mixed
     */
    public function validation_HWML_Slider_Settings_Metabox( $sInput, $sOldInput ) {

        if(!isset($_SESSION['skin_options']) && isset($sInput['skin_options'])) unset($sInput['skin_options']); //disable old skin options & choose other skin options
        /*$skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($sInput['slider_theme']);
        $skin_options_file = $skin->instance->get_file_skin_options($sInput['slider_theme']['hash_skin']);
        $theme_setting = $skin->instance->get_file_skin_setting();
        $skin_options = HW_SKIN::merge_skin_options_values($sInput['slider_theme']['skin_options'], $theme_setting, $skin_options_file);
        */
        //save current skin to db for this widget
        if(!empty($sInput['slider_theme'])) {
            if(isset($sInput['slider_theme']['hash_skin']) && isset($sInput['slider_theme']['hwskin_config'])) {
                $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($sInput['slider_theme']);//
                $skin->instance->save_skin_assets(array(
                    'skin' => $sInput['slider_theme'],
                    'object' => 'hw-mlslider-'. $sInput['post_ID']
                ));
            }
        }

        /* Set a flag
        $_fIsValid = true;

        // Prepare an field error array.
        $_aErrors = array();

        // Use the debug method to see what are passed.
        // $this->oDebug->logArray( $sInput );

        // Check if a url is passed
        if ( ! filter_var( $sInput, FILTER_VALIDATE_URL ) ) {

            $_fIsValid = false;
            // $variable[ 'field_id' ]
            $_aErrors['url'] = __( 'The value must be a url:', 'admin-page-framework-tutorials' ) . ' ' . $sInput;

        }

        // An invalid value is found.
        if ( ! $_fIsValid ) {

            // Set the error array for the input fields.
            $this->setFieldErrors( $_aErrors );
            $this->setSettingNotice( __( 'There was something wrong with your input.', 'admin-page-framework-tutorials' ) );
            return $sOldInput;

        }*/

        return $sInput;

    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {

        $sliders = self::get_all_mlsliders();
        //_print($this->__get('oForm')->getValue());

        $fields = array();  //fields setting

        /*get all post type terms*/
        $select_posttype = hwml_get_option('source_posttype');
        if(!is_string($select_posttype)) $select_posttype = 'post';  // default post type

        //prepare terms list for active post type
        $data = array();
        $terms = HW_POST::get_all_terms_taxonomies($select_posttype);    //get all terms from post type
        foreach($terms as $t) {
            $data[base64_encode($t->slug.'|'.$t->taxonomy)] = $t->name;
        }

        $this->addSettingFields(
        //'general',
            array(
                'field_id' => 'slideshow_source',
                'type' => 'select',
                'title' => __('Chọn nguồn slides'),
                'description' => 'Lấy dữ liệu cho slideshow.',
                'label' => array(
                    'metaslider' => 'Slider',
                    'posttype' => 'Post Type & Taxonomies'
                )
            ),
            array(
                'field_id' => 'current_post',
                'type' => 'checkbox',
                'title' => __('Post hiện tại'),
                'description' => 'Lấy ảnh gán vào post hiện tại.'
            ),
            array(
                'field_id' => 'source_posttype',
                'type' => 'select',  //'posttype',      //sory, choose single post type
                'title' => 'Chọn Post Types',
                #'select_none_button' => false,
                #'select_all_button' => false
                'label' => get_post_types(),
                'description' => __('Lựa chọn post type & nhấn cập nhật/xuất bản để lọc danh mục.')
            ),
            array(
                'field_id' => 'only_attachments',
                'type' => 'checkbox',
                'title' => __('Lấy ảnh gán vào post'),
                #'label' => '',
                'description' => 'Lấy toàn bộ ảnh gán vào post.'
            )
            /*array(
                'field_id' => 'source_taxonomy',
                'type' => 'taxonomy',
                'title' => 'Chọn taxonomy',
                'label_no_term_found' => 'Không tìm thấy.',
                'taxonomy_slugs' => 'category',
                'query' => array(
                    'hide_empty' => 0,
                )
            ),*/
        );
        //if(count($this->data) > 0){   //warning: don't put any condition to add setting field , it will not work!
        $this->addSettingFields(
            array(  //show terms of post type
                'field_id' => 'post_type_terms',
                'type' => 'checkbox',
                'title' => 'Danh mục của ('.$select_posttype.')',
                'description' => (!count($data)? ' Không có dữ liệu !' : 'Chọn danh mục của post type đã chọn.'),
                'label' => $data,
            ),
            array(
                'field_id' => 'tax_query_relation',
                'type' => 'select',
                'title' => 'Mối quan hệ AND/OR cho danh mục',
                'description' => '',
                'label' => array('AND'=>'AND', 'OR'=>'OR')
            )
        );
        //}
        $this->addSettingFields(

            array(
                'field_id'      => 'pick_slider',
                'type'          => 'select',
                'title'         => __('Chọn nguồn slider','hwslider'),
                'description'   => __('Chọn dữ liệu sliders. Thêm/Sửa slides <a target="_blank" href="'.self::get_edit_sliders_url().'">tại đây</a>.','hwslider'),     #description that will be placed below the input field
                'label' => $sliders

            ),
            array(  //show slide title
                'field_id' => 'show_title',
                'type' => 'checkbox',
                'title' => 'Hiển thị tiêu đề slide',
                'description' => 'Hiển thị tiêu đề slide'
            ),
            array(
                'field_id' => 'slider_theme',
                'type' => 'hw_skin',
                'title' => __('Chọn slider theme','hwslider'),
                'description' => __('Chọn theme riêng cho slider','hwslider'),
                //'value' => '',
                'enable_skin_condition' => true,
                'external_skins_folder' => 'hw_mlslider_skins',
                'skin_filename' => 'mlslider-skin.php',
                'enable_external_callback' => false,
                'skins_folder' => 'themes',
                'apply_current_path' => HWML_PLUGIN_PATH,
                'plugin_url' => HWML_PLUGIN_URL,
                'group' => 'group-others', //skins group
                'hwskin_field_output_callback' => array($this,'_hwskin_field_output'),

            )
            //submit button
            /*,array(
                'type' => 'submit',
                'label' => 'Lưu lại',
                'field_id'      => 'submit_button',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            )*/
        );

        //add dynamic apf fields
        if(count($fields) > 0){
            foreach($fields as $opt){
                $this->addSettingField($opt);
            }
        }
        // content_{page slug}_{tab slug}
        //add_filter( 'content_hw_sidebar_widgets_settings', array( $this, 'replyToInsertContents' ) );

    }
    public function _hwskin_field_output($_aOutput,$skin, $field_output){
        return $_aOutput;
    }

    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );
        $btn ='sdfdgdfg';
        return $sContent. $btn;
    }
}

/**
 * Class HWML_Setting_Shortcode_Metabox
 */
class HWML_Setting_Shortcode_Metabox extends AdminPageFramework_MetaBox {
    public function setUp() {
        global $wp_query;
        if(isset($_GET['post'])) {
            $p_ID = $_GET['post'];
            $sc_tag = hwml_generate_shortcode($p_ID);   //hwml shortcode string
        }
        else $sc_tag = '';

        $this->addSettingFields(
        //'general',
            array(
                'field_id'      => 'shortcode_tag',
                'type'          => 'label',
                'title'         => __('Shortcode','hwslider'),
                'description'   => __('Copy đoạn shortcode sau vào theme để hiển thị slider.','hwslider'),     #description that will be placed below the input field
                'label' => '<input type="text" value="'.esc_attr($sc_tag).'" readonly/>'

            ),
            //preview
            array(
                'field_id' => 'preview',
                'type' => 'label',
                'title' => 'Xem trước',
                'description' => 'Xem trước'
            )
        );
    }

}
endif;
if(is_admin()){
    if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HWML_Slider_Settings_Metabox');

    /*new HWML_Slider_Settings_Metabox_page( //this class instance extend from  AdminPageFramework_MetaBox
        null,                                           // meta box id - passing null will make it auto generate
        __( 'Cài đặt cho sidebar ', 'hwawc' ), // title
        //array( 'hw_sidebar_widgets_settings' =>  array( 'hw_sidebar_widgets_settings' ) ),    //syntax: {page slug}=>{tab slug}
        array('hw_slider_settings'),  //apply for this page slug
        'normal',                                         // context
        'default'                                       // priority
    );*/

}


/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function hwml_save_shortcode_meta( $post_id, $post, $update ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'hwml_shortcode';

    // If this isn't a 'book' post, don't update it.
    if ( $slug != $post->post_type ) {
        return;
    }

    // - Update the post's metadata.
//_print($_REQUEST);
    if ( isset( $_REQUEST['book_author'] ) ) {
        update_post_meta( $post_id, 'book_author', sanitize_text_field( $_REQUEST['book_author'] ) );
    }

}
//add_action( 'save_post', 'hwml_save_shortcode_meta', 10, 3 ); //not wprk