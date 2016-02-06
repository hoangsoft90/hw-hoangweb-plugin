<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 28/10/2015
 * Time: 14:44
 */
/**
 * Class HW_Gallery_Widget
 */
if(class_exists('AdminPageFramework_Widget')):
class HW_Gallery_Widget extends AdminPageFramework_Widget {
    /**
     * The user constructor.
     *
     * Alternatively you may use start_{instantiated class name} method.
     */
    public function start() {}
    /**
     * Sets up arguments.
     *
     * Alternatively you may use set_up_{instantiated class name} method.
     */
    public function setUp() {
        $this->setArguments(
            array(
                'description'   =>  __( 'Hiển thị gallery', 'hw-gallery' ),
            )
        );

    }
    /**
     * Sets up the form.
     *
     * Alternatively you may use load_{instantiated class name} method.
     */
    public function load( $oAdminWidget ) {
        //get all hwml shortcodes
        $data = hw_gallery_get_galleries();
        $galleries = array();
        foreach($data as $gallery) {
            $galleries[$gallery['id']] = $gallery['title'];
        }
        HW_UI_Component::empty_select_option($galleries);

        //register form fields
        $this->addSettingFields(
            array(
                'field_id'      => 'title',
                'type'          => 'text',
                'title'         => __( 'Tiêu đề', 'hw-gallery' ),
                'default'       => '',
            ),
            /*array(
                'field_id'      => 'repeatable_text',
                'type'          => 'text',
                'title'         => __( 'Text Repeatable', 'hwml' ),
                'repeatable'    => true,
                'sortable'      => true,
            ),*/
            array(
                'field_id' => 'gallery',
                'type' => 'select',
                'label' => $galleries,
                'title' => __('Chọn Gallery','hw-gallery'),
                'description' => ''
            ),

            array()
        );

    }
    /**
     * Validates the submitted form data.
     *
     * Alternatively you may use validation_{instantiated class name} method.
     */
    public function validate( $aSubmit, $aStored, $oAdminWidget ) {

        // Uncomment the following line to check the submitted value.
        // AdminPageFramework_Debug::log( $aSubmit );

        return $aSubmit;

    }
    /**
     * Print out the contents in the front-end.
     *
     * Alternatively you may use the content_{instantiated class name} method.
     * @param $sContent
     * @param $aArguments
     * @param $aFormData
     */
    public function content( $sContent, $aArguments, $aFormData ) {
        if(!empty($aFormData['gallery'])) {
            return do_shortcode('[hw_gallery id="'.$aFormData['gallery']. '"]');
        }
        /*return $sContent
        . '<p>' . __( 'Hello world! This is a widget created by Admin Page Framework.', 'hwml' ) . '</p>'
        . AdminPageFramework_Debug::get( $aArguments )
        . AdminPageFramework_Debug::get( $aFormData );*/
    }
}
//istall widget
    new HW_Gallery_Widget( __( 'Hiển thị gallery', 'hw-gallery' ) );  // the widget title
endif;