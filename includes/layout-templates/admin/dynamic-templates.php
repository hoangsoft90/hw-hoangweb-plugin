<?php
#/root>includes/hoangweb-core.php

//register metabox for hw_mysidebar post type edit page
include_once(dirname(__FILE__).'/dynamic-templates-metabox.php');

if(class_exists('AdminPageFramework_PostType')):
/**
 * Class HW_Templates_Manager
 */
class HW_Templates_Manager extends AdminPageFramework_PostType
{
    const post_type = 'hw_pt_template';
    /*public function __construct(){
        //parent::__construct();
    }*/
    public function setUp(){
        $this->setAutoSave( true );

        $this->setArguments(
            array( // argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels' => array(
                    'name' => __( 'Cấu hình Templates' ),
                    'add_new_item' => __( 'Thêm tùy chọn'),
                ),
                'supports' => array( 'title' ,'excerpt'), // e.g. array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
                'public' => true,
                //'taxonomies' => array('category'),
                'menu_icon' => version_compare( $GLOBALS['wp_version'], '3.8', '>=' )
                        ? 'dashicons-format-aside'
                        : HW_HOANGWEB_URL. '/images/icon_16x16.png',
                // (framework specific key) this sets the screen icon for the post type for WordPress v3.7.1 or below.
                'screen_icon' => HW_HOANGWEB_URL. '/images/icon_16x16.png', // a file path can be passed instead of a url, plugins_url( 'asset/image/wp-logo_32x32.png', APFDEMO_FILE )
                //make post type un-queryable
                'exclude_from_search' => true,
                'show_in_admin_bar'   => true,
                'show_in_nav_menus'   => false,
                'publicly_queryable'  => false,
                'query_var'           => false,
                'publicly_queryable'  => false
            )
        );
        //add taxonomy
        /*$this->addTaxonomy(
            'sample_taxonomy', // taxonomy slug
            array(          // argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
                'labels' => array(
                    'name' => 'Genre',
                    'add_new_item' => 'Add New Genre',
                    'new_item_name' => "New Genre"
                ),
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_table_filter' => true,    // framework specific key
                'show_in_sidebar_menus' => true,   // framework specific key
            )
        );*/
    }
    /**
     * Custom Columns of Post Listing Table. define what columns are displayed in the table
     * @param $aHeaderColumns
     * @return array
     */
    public function columns_hw_pt_template( $aHeaderColumns ) {//columns_{post type slug}      #hwml_shortcodes_list
        //_print($aHeaderColumns);
        return array(
            'cb'                => '<input type="checkbox" />', // Checkbox for bulk actions.
            'title'             => __( 'Tên'  ), // Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
            'desc'             => __( 'Mô tả' ),
            'template'      => __('Template'),
            'enable'        => __('Kích hoạt'),
            'author' => __('Tác giả')
        );
            // + $aHeaderColumns    #enable the default columns
    }
    /**
     * Cell Output of Custom Columns
     * listing table processes rendering each row and its column, the framework receives a callback for custom columns
     * callback method name is made up of ”cell_” + “{post type slug}” + _ + “{column key}”.
     * @param $sCell: the HTML cell output.
     * @param $iPostID: parsing post ID.
     * @return string
     */
    public function cell_hw_pt_template_desc( $sCell, $iPostID ) { // cell_{post type}_{column key}
        $post = get_post($iPostID);
        $excerpt = $post->post_excerpt;    //slider from this shortcode
        if($excerpt) {
            echo $excerpt;
        }

        /*return $sCell
            . '<div class="color-sample-container">'
            . '<p style="background-color: ' . esc_attr( $this->aSliders[(int)$iSlider] ) . '">'
            . '</p>'
            . '</div>';*/
    }

    /**
     * display active option
     * @param $sCell
     * @param $iPostID
     */
    public function cell_hw_pt_template_enable( $sCell, $iPostID ) {
        $enable = get_post_meta($iPostID, 'enable', true);
        $class = $enable? 'hw-enable-icon' : 'hw-disable-icon';     //class
        echo '<span class="'.$class.'"></span>';
    }
    /**
     * display template option
     * @param $sCell
     * @param $iPostID
     */
    public function cell_hw_pt_template_template( $sCell, $iPostID ) {
        $out = '<ul>';
        $query_data_and = get_post_meta($iPostID, 'query_data_and', true);
        $query_data_or = get_post_meta($iPostID, 'query_data_or', true);

        if(is_array($query_data_and))
        foreach ($query_data_and as $row)
        {
            if(strtolower($row['act']) == 'templates') {
                $out .= '<li>- Điều kiện AND '. $row['compare'].$row['act_values'] . '</li>';
                break;
            }
        }
        if(is_array($query_data_or)){
            foreach ($query_data_or as $row)
            {
                if(strtolower($row['act']) == 'templates') {
                    $out .= '<li>- Điều kiện OR '. $row['compare'].$row['act_values'] . '</li>';
                    break;
                }
            }
        }
        $out .= '</ul>';
        echo $out;
    }

    /**
     * CSS Rules of Post Listing Page
     * modify the inline CSS rules that the framework inserts
     * @param $sStyle
     * @return string
     */
    public function style_HW_Templates_Manager( $sStyle ) { #style_{instantiated class name}()

        return $sStyle . "
            .color-sample-container {
                height: 3em;
            }
            .color-sample-container p {
                border: solid 1px #CCC;
                width: 3em;
                height: 100%;
            }
            .hw-enable-icon{
                background: url('".HW_AWC_URL."/images/icons/enable.png') no-repeat;
                width: 40px;
                height: 40px;
                  display: block;
            }
            .hw-disable-icon {
                background: url('".HW_AWC_URL."/images/icons/disable.png') no-repeat;
                width: 40px;
                height: 40px;
                  display: block;
            }
        ";
    }
    /**
     * Front-end Article Output
     */
    public function content( $sContent ) {

        return "<h3>" . __( 'Selected Color', 'hwml' ) . "</h3>". "<div style='width: 100%;'>". "<div style='margin:auto; width: 3em; height: 3em; background-color:'></div>". "</div>";

    }

    /**
     * init metaboxs
     */
    public static function init_metabox() {
        if(is_admin() ){
            global $current_user;
            if(class_exists('HW_Templates_Manager') && class_exists('HW_Templates_Metabox')
                && ( current_user_can('administrator') )    //only for administrator
            ){
                new HW_Templates_Manager( HW_Templates_Manager::post_type );  // the post type slug

                HW_Templates_Metabox::setInstance( new HW_Templates_Metabox(
                    null,   // meta box ID - can be null.
                    __( 'Thêm template động' ), // title
                    array( HW_Templates_Manager::post_type ),  // post type slugs: post, page, etc.
                    'normal',   // context
                    'low'   // priority
                ));
                //add new metabox
                new HW_Templates_Metabox(
                    null,   // meta box ID - can be null.
                    __( 'Hướng dẫn' ), // title
                    array( HW_Templates_Manager::post_type ),  // post type slugs: post, page, etc.
                    'normal',   // context
                    'low'   // priority
                );
            }
        }
    }
}
    add_action('hw_plugins_loaded', 'HW_Templates_Manager::init_metabox'); //plugins_loaded
endif;
