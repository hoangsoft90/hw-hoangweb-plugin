<?php 
/**
 * add sub admin menu under wpcf7 main menu
 */

//hwskin selector field
if(class_exists('HW_SKIN')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_LINKS);

if(class_exists('AdminPageFramework')): //please tun off wpdebug
class HW_Wpcf_form_settings extends AdminPageFramework{
    //tell the framework what page to create
    public function setUp() {
        //$this->setRootMenuPage( 'sdfgfgdfg' );      # set the top-level page, ie add a page to the Settings page
        $this->setRootMenuPageBySlug('wpcf7');      //add submenu page under wpcf7
        #add sub-menu pages
        $this->addSubMenuItem(
        array(
        'title'     => 'Cấu hình',
        'page_slug' => 'hw_wpcf7_form_settings',
        )
        );
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_wpcf7_form_settings() {
        
        
    }
    /**
     * show contact form settings
     */
    public function load_hw_wpcf7_form_settings( $oAdminPage ) {
        if ( $post = wpcf7_get_current_contact_form() ) {
            $post_id = $post->initial() ? -1 : $post->id();
        
        }
        //register section
        $this->addSettingSections(
                'hw_wpcf7_form_settings',    //must match with page slug
                array(
                        'section_id' => 'general',
                        'title' => 'Cấu hình',
                        'description' => 'Cài đặt chung.',
                )
        );
        //get list of pages
		 $pages = get_pages();
		 $list =array(
			'__all__' => 'Tất cả',
			'__home__' => 'Trang Chủ'
		 );
		 foreach($pages as $page){
		     $list[$page->post_name] = $page->post_title;
		 }
		 
        $this->addSettingFields(
                'general',
                array(    // emable wpcf7 css
                        'field_id'      => 'enable_wpcf7_css',
                        'type'          => 'checkbox',
                        'title'         => __('Kích hoạt WPCF7 CSS','hwcf'),
                        'description'   => __('Kích hoạt CSS mặc định.','hwcf'),     #description that will be placed below the input field
                        'value' => '1'    //default checked status
                        
                ),
                array(  //enabled wpcf7 js for ajax feature
                        'field_id' => 'enable_wpcf7_js',
                        'type' => 'checkbox',
                        'title' => __('Kích hoạt WPCF7 JS','hwcf'),
                        'description' => __('Kích hoạt tính năng ajax trong WPCF7','hwcf'),
                        'value' => '1'      //check default
                ),
                array(  //exclude pages
                      'field_id' => 'exclude_pages'  ,
                      'type' => 'select',
                      'title' => __('Loại trừ pages'),
                        'description' => __('Chọn các pages bạn sẽ sử dụng tính năng ajax của Contact Form 7')  ,
                        'is_multiple'=>true,
                        'attributes' => array('select' => array('size' => 5)),
                        'label' =>  $list,
                ),
                array(
                        'field_id' => 'loadingImg',
                        'type' => 'hw_skin_link',
                        'title' => __('Chọn ảnh loading khi sử lý lưu contact form'),
                        'description' => __('Chọn ảnh loading khi sử lý lưu contact form'),
                        'label'=>'',
                    'external_skins_folder' => 'hw_wpcf7_ajax_images',
                    'skin_filename' => 'hw-wcf7-ajax-image.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => HW_WPCF7_PATH,
                    'plugin_url' => HW_WPCF7_URL,
                    'files_skin_folder' => 'ajaxLoading',
                ),
                
                //submit button
                array(
                        'type' => 'submit',
                        'field_id'      => 'submit_button',
                        'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
                )
        );
        //webhook API
        $this->addSettingFields(
                'webhook',
                array(
                        'field_id' => 'webhook_url',
                        'type' => 'text',
                        'title' => 'URL nhận dữ liệu qua POST',
                        'description' => 'Điền URL (bắt đầu với http://) nhận dữ liệu qua phương thức POST sau khi người dùng nhấn submit form.'
                ),
                //submit button
                array(
                        'type' => 'submit',
                        'field_id'      => 'submit_button',
                        'show_title_column' => false,
                )
        );
        
    }
}
if(is_admin()){
    //init custom field type
    if(class_exists('APF_imageSelector_hwskin')) new APF_imageSelector_hwskin('HW_Wpcf_settings');
    new HW_Wpcf_settings;
}
endif;