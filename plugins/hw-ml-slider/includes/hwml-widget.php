<?php
/**
 * Creates a widget.
 *
 * @since   3.2.0
 */
if(class_exists('AdminPageFramework_Widget')):
class HWML_Widget extends AdminPageFramework_Widget {
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
                'description'   =>  __( 'Hiển thị slider nâng cao', 'hwml' ),
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
        $hwml_data = HWMLShortcode_Manager::get_hwml_slideshow_posts();
        HW_UI_Component::empty_select_option($hwml_data);

        //register form fields
        $this->addSettingFields(
            array(
                'field_id'      => 'title',
                'type'          => 'text',
                'title'         => __( 'Tiêu đề', 'hwml' ),
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
                'field_id' => 'slider',
                'type' => 'select',
                'label' => $hwml_data,
                'title' => __('Chọn slider','hwml'),
                'description' => ''
            ),
            array(
                'field_id' => 'use_default_slider',
                'type' => 'checkbox',
                'title' => 'Lấy slider mặc định',
                'description' => 'Sử dụng slider đã thiết lập mặc định <a href="'.HW_NHP_Main_Settings::get_setting_page_url().'" target="_blank">tại đây</a>.'
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
        if(!empty($aFormData['use_default_slider'])) {  //default metaslider
            echo do_shortcode('[metaslider id='.hw_option('main_slider_id',1).']');
        }
        elseif(!empty($aFormData['slider'])) {
            return do_shortcode(hwml_generate_shortcode($aFormData['slider']));
        }
        /*return $sContent
        . '<p>' . __( 'Hello world! This is a widget created by Admin Page Framework.', 'hwml' ) . '</p>'
        . AdminPageFramework_Debug::get( $aArguments )
        . AdminPageFramework_Debug::get( $aFormData );*/

    }
}
//istall widget
new HWML_Widget( __( 'Hiển thị slider nâng cao', 'hwml' ) );  // the widget title
endif;