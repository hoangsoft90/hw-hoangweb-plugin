<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//field type for picking socials
include_once ('field-socials_picker.php');

//extending the framework admin factory class

/**
 * Class HW_Socials
 */
class HW_SocialsShare_Settings extends AdminPageFramework {
    /**
     * settings page slug
     */
    const SETTINGS_PAGE_SLUG = 'hw_social_option';


    /**
     * tell the framework what page to create
     */
    public function setUp() {
        $this->setRootMenuPage( 'Chia sẻ' );      # set the top-level page, ie add a page to the Settings page
        #add sub-menu pages
        $this->addSubMenuItem(
            array(
                'title'     => 'Cài đặt chia sẻ',
                'page_slug' => 'hw_social_option',
            )
        );
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_social_option() {

        // Show the saved option value.
        /* The extended class name is used as the option key. This can be changed by passing a custom string to the constructor.
        echo '<h3>Saved Fields</h3>';
        echo '<pre>my_text_field: ' . AdminPageFramework::getOption( 'APF_AddFields', 'my_text_field', 'default text value' ) . '</pre>';
        echo '<pre>my_textarea_field: ' . AdminPageFramework::getOption( 'APF_AddFields', 'my_textarea_field', 'default text value' ) . '</pre>';

        echo '<h3>Show all the options as an array</h3>';
        echo $this->oDebug->getArray( AdminPageFramework::getOption( 'APF_AddFields' ) );
		*/
        ?>
        Cài đặt thanh chia sẻ hiển thị bên sườn website.
<?php
//$data = get_option( 'HW_Socials' ,array());

    }

    /**
     * @param array $socials
     * @return array
     */
    public static function get_sort_socials_selected($socials = array()){
        if(count($socials) == 0) $socials = HW_SocialShare_widget::$socials_button;
        $order_socials = hwss_option( 'socials_button_hidden');
        $order_socials = explode(',', $order_socials);
        $result = $socials;
        return HW_SocialShare_widget::order_assoc_array_base_other($order_socials,$result);
    }
    /**
     * load form settings
     * @param unknown $oAdminPage
     */
    public function load_hw_social_option( $oAdminPage ) {  // load_{page slug}
        $HW_Socials_fields = hwss_option();
        //get list of pages
        $pages = get_pages();
        $list =array(
            '__all__' => 'Tất cả',
            '__home__' => 'Trang Chủ'
        );
        foreach($pages as $page){
            $list[$page->post_name] = $page->post_title;
        }
        //get socials button & also order by user
        $socials = self::get_sort_socials_selected();

        //list avaiable sharing services
        $services = array();
        foreach(HW_SocialShare_widget::$sharing_services as $key=>$item) {
            if(isset($item['enable']) && !$item['enable']) continue;
            $services[$key] = ucfirst($item['text']);
        }

        //default custom css
        $custom_css_def = '';
        if(!empty($HW_Socials_fields['wrap_id'])) $custom_css_def = '#'.$HW_Socials_fields['wrap_id'].'{position:relative;}';

        //help popup
        if(class_exists('HW_HELP')) {
            $guide_link = 'Xem '. HW_HELP::generate_help_popup(array('HW_HELP_SHARE', 'share.html'), 'hướng dẫn chi tiết','hướng dẫn tạo nút chia sẻ');
        }
        else $guide_link = '';

        $this->addSettingSections(
            'hw_social_option',
            array(
                'section_id' => 'my_first_section',
                'title' => 'My First Form Section',
                'description' => 'This section is for text fields.',
            ),
            array(
                'section_id' => 'my_second_section',
                'title' => 'My Second Form Section',
                'description' => 'This section is for selectors.',
            )
        );

        $this->addSettingFields(
        #'my_first_section',
            array(
                'field_id' => 'help',
                'type' => 'title',
                'description' => $guide_link ,
            ),
            array(    // Single text field
                'field_id'      => 'enable_side_share_bar',
                'type'          => 'checkbox',
                'title'         => __('Kích hoạt','hwss'),
                'description'   => __('Kích hoạt thanh chia sẻ ở sườn website.','hwss'),     #description that will be placed below the input field
            ),
            array(
                'field_id' => 'sharing_service',
                'type' => 'select',
                'title' => __('Chọn dịch vụ','hwss'),
                'description' => __('Lựa chọn dịch vụ chia sẻ như addthis, sharethis,..','hwss'),
                'label' => $services
            ),
            array(    // Text Area
                'field_id'      => 'wrap_id',
                'type'          => 'text',
                'title'         => __('Wrap DIV selector','hwss'),
                'description'   => __('ID/class thẻ div bao khung website. Cần chỉ định giá trị này để hiển thị nút chia sẻ ở sườn website.<br/>VD: .site-inner','hwss'),
                'default'       => '',    #default value
            ),
            array(
                'field_id' => 'pages_list',
                'type' => 'select',
                'title' => __('Hiển thị ở trang','hwss'),
                'description' => __('Chọn trang hiển thị.','hwss'),
                'label' =>  $list,
                'is_multiple'=>true,
                'attributes' => array('select' => array('size' => 5)),

            ),
            array(
                'field_id' => 'socials_button',
                'type' => 'socials_button_picker',
                'title' => __('Chọn nút & xắp xếp','hwss'),
                'description' => __('Chọn một hoặc nhiều nút mạng xã hội, bạn có thể di chuyển xắp lại thứ tự hiển thị.','hwss'),
                'label' => $socials,
                'is_multiple' => true,
                'attributes' => array('select'=>array('size' => 10)),
            ),
            array(
                'field_id' => 'custom_css',
                'type' => 'textarea',
                'title' => __('Tùy biến CSS','hwss'),
                'description' => __('Chèn thêm CSS.','hwss'),
                'default' => '/*chèn CSS ở đây*/'.$custom_css_def,
            ),
            array(
                'field_id' => 'button_size',
                'type' => 'text',
                'title' => 'Kích thước',
                'description' => 'Kích thước co dãn. VD: 1 (small), 1.5 (medium),..',
                'default' => '1'
            ),
            array(
                'field_id' => 'socials_button_hidden',
                'type' => 'hidden',
            ),
            array(
                'field_id' => 'enable_standard_buttons_in_post',
                'type' => 'checkbox',
                'title'         => __('Kích hoạt trong bài viết.','hwss'),
                'description'   => __('Kích hoạt nút socials chuẩn trong bài viết chi tiết.','hwss'),
            ),
            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
            )
        );
    }

}

//Instantiate the Class
if(is_admin()){
    new APF_HW_FieldType_socials_button_picker( 'HW_SocialsShare_Settings' );
    new HW_SocialsShare_Settings;
}