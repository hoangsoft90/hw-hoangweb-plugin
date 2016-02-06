<?php
/*
http://admin-page-framework.michaeluno.jp/tutorials/13-add-a-meta-box-for-a-custom-post-type/
http://admin-page-framework.michaeluno.jp/tutorials/12-create-a-custom-post-type-and-custom-taxonomy/
http://admin-page-framework.michaeluno.jp/tutorials/03-create-a-page-group/
 */
//add metabox in post type content
include_once('hwml-shortcode-metabox.php');

/**
 * Class HWML_Shortcodes_List
 * create a new Custom Post Type to manage slider shortcodes
 */
if(class_exists('AdminPageFramework_PostType')):
class HWML_Shortcodes_List extends AdminPageFramework_PostType
{
    const hwml_slider_posttype = 'hwml_shortcode';
    /**
     * sliders names
     * @var array
     */
    private $aSliders = array();
    /**
     * Sets up necessary settings.
     */


    public function setUp(){


        $this->setArguments(
            array( // argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels' => array(
                    'name' => __( 'HW Sliders', 'hwml' ),
                    'add_new_item' => __( 'Thêm slider', 'hwml' ),
                ),
                'supports' => array( 'title' ,'excerpt'), // e.g. array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
                'public' => true,
                //'taxonomies' => array('category'),        //show taxonomy column in admin
                'menu_icon' => version_compare( $GLOBALS['wp_version'], '3.8', '>=' )
                        ? 'dashicons-slides'
                        : 'https://lh4.googleusercontent.com/-z7ydw-aPWGc/UwpeB96m5eI/AAAAAAAABjs/Brz6edaUB58/s800/demo04_16x16.png',
                // (framework specific key) this sets the screen icon for the post type for WordPress v3.7.1 or below.
                'screen_icon' => HWML_PLUGIN_URL.'/images/wp-logo_32x32.png', // a file path can be passed instead of a url, plugins_url( 'asset/image/wp-logo_32x32.png', APFDEMO_FILE )
                //make post type un-queryable
                'exclude_from_search' => true,
                'show_in_admin_bar'   => true,
                'show_in_nav_menus'   => false,
                'publicly_queryable'  => false,
                'query_var'           => false,
                'publicly_queryable'  => false
            )
        );
        $this->aSliders = HWML_Slider_Settings_Metabox::get_all_mlsliders();   //get all meta sliders info
    }

    /**
     * Custom Columns of Post Listing Table. define what columns are displayed in the table
     * @param $aHeaderColumns
     * @return array
     */
    public function columns_hwml_shortcode( $aHeaderColumns ) {//columns_{post type slug}      #hwml_shortcodes_list

        return array(
            'cb'                => '<input type="checkbox" />', // Checkbox for bulk actions.
            'title'             => __( 'Tên', 'hwml'  ), // Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
            'pick_slider'             => __( 'Slider', 'hwml' ),
            'slider_theme' => __('Theme','hwml'),
            'shortcode' => __('Shortcode'),
            'author' => __('Tác giả')
        )
            // + $aHeaderColumns    #enable the default columns
            ;

    }

    /**
     * Cell Output of Custom Columns
     * listing table processes rendering each row and its column, the framework receives a callback for custom columns
     * callback method name is made up of ”cell_” + “{post type slug}” + _ + “{column key}”.
     * @param $sCell: the HTML cell output.
     * @param $iPostID: parsing post ID.
     * @return string
     */
    public function cell_hwml_shortcode_pick_slider( $sCell, $iPostID ) { // cell_{post type}_{column key}

        $iSlider = get_post_meta( $iPostID, 'pick_slider',true);    //slider from this shortcode
        if($iSlider) {
            $edit_link = admin_url('admin.php?page=hw-metaslider&id='.$iSlider);
            if(isset($this->aSliders[(int)$iSlider])) {
                $title = $this->aSliders[(int)$iSlider];
            }
            else $title = 'No title';
            echo '<a href="'.$edit_link.'" target="_blank">'.esc_attr( $title ).'</a>';
        }

        /*return $sCell
            . '<div class="color-sample-container">'
            . '<p style="background-color: ' . esc_attr( $this->aSliders[(int)$iSlider] ) . '">'
            . '</p>'
            . '</div>';*/
    }

    /**
     * show slider theme screenshot
     * @param $sCell
     * @param $iPostID
     */
    public function cell_hwml_shortcode_slider_theme( $sCell, $iPostID ) {
        $skin_info = get_post_meta( $iPostID, 'slider_theme',true);
        $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($skin_info);

        if(count($skin)){
            $data = $skin->instance->get_skin_data($skin->hash_skin);
            echo '<img class="hwml-shortcode-list hwml-skin-thumb" src="'.$data['screenshot'].'"/>';
        }

    }

    /**
     * generate shortcode tag
     * @param $sCell
     * @param $iPostID
     */
    public function cell_hwml_shortcode_shortcode( $sCell, $iPostID ) {
        #$iSlider = get_post_meta( $iPostID, 'pick_slider',true);
        #$aSkin = get_post_meta( $iPostID, 'slider_theme',true);
        echo '<input type="text" size="40" class="input" value="'.hwml_generate_shortcode($iPostID).'" readonly/>';
    }

    /**
     * CSS Rules of Post Listing Page
     * modify the inline CSS rules that the framework inserts
     * @param $sStyle
     * @return string
     */
    public function style_HWML_Shortcodes_List( $sStyle ) { #style_{instantiated class name}()

        return $sStyle . "
            .color-sample-container {
                height: 3em;
            }
            .color-sample-container p {
                border: solid 1px #CCC;
                width: 3em;
                height: 100%;
            }
        ";
    }
    /**
     * Front-end Article Output
     */
    public function content( $sContent ) {

        $_sSelectedColor = get_post_meta( $GLOBALS['post']->ID, 'my_custom_color', true );
        $_sSelectedColor = $_sSelectedColor ? $_sSelectedColor : 'transparent';
        return "<h3>" . __( 'Selected Color', 'hwml' ) . "</h3>". "<div style='width: 100%;'>". "<div style='margin:auto; width: 3em; height: 3em; background-color:" . $_sSelectedColor . "'></div>". "</div>";

    }

    /**
     * init class
     */
    static public function init(){
        if(is_admin()){
            if(class_exists('HWML_Shortcodes_List') && class_exists('HWML_Slider_Settings_Metabox')
                && current_user_can('administrator') ){
                new HWML_Shortcodes_List( HWML_Shortcodes_List::hwml_slider_posttype );  // the post type slug

                //main metabox
                if(class_exists('HWML_Slider_Settings_Metabox')) {
                    HWML_Slider_Settings_Metabox::setInstance( new HWML_Slider_Settings_Metabox(
                        null,   // meta box ID - can be null.
                        __( 'Tạo dữ liệu shortcode cho slider', 'hwml' ), // title
                        array( HWML_Shortcodes_List::hwml_slider_posttype ),  // post type slugs: post, page, etc.
                        'normal',   // context
                        'low'   // priority
                    ));
                }
                //second metabox
                if(class_exists('HWML_Setting_Shortcode_Metabox')) {
                    new HWML_Setting_Shortcode_Metabox(
                        null,
                        __('Nội dung Shortcode','hwml'),
                        array( HWML_Shortcodes_List::hwml_slider_posttype ),
                        'normal',   // context
                        'low'   // priority
                    );
                }
                /*new HWML_Setting_Shortcode_Metabox( //test for existing post type
                    null,
                    __('Nội dung','hwml'),
                    array( 'post' ),
                    'normal',   // context
                    'low'   // priority
                );*/
            }
        }
    }
}
    #add_action('plugins_loaded', 'HWML_Shortcodes_List::init');
    add_action('hw_plugins_loaded', 'HWML_Shortcodes_List::init');
    endif;

