<?php
/**
 * add sub admin menu under hoangweb menu page
 */

if(class_exists('HW_SKIN') && function_exists('hwskin_load_APF_Fieldtype')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
/**
 * Class HW_Livechat_settings
 */
if(class_exists('AdminPageFramework')):
class HW_Livechat_settings extends AdminPageFramework{
    /**
     * root menu page
     */
    const LIVECHAT_ROOT_MENU_PAGE = 'hoangweb-theme-options';
    /**
     * page menu slug
     */
    const page_setting_slug = 'hw_livechat_settings';

    /**
     * tell the framework what page to create
     */
    public function setUp() {
        #self::LIVECHAT_ROOT_MENU_PAGE
        #$this->setRootMenuPage( 'Livechat');      # set the top-level page, ie add a page to the Settings page
        $this->setRootMenuPageBySlug(HW_Module_Settings_page::PAGE_SLUG);      //add submenu page under hoangweb theme options
        #add sub-menu pages
        $this->addSubMenuItem(
            array(
            'title'     => 'Live chat',
            'page_slug' => self::page_setting_slug,
            )
        );

        //modify apf hwskin fieldtype
        add_filter('apf_hwskin', array($this, '_apf_hwskin'),10,2);
        add_filter('hwskin_field_output', array($this, '_apf_hwskin_field_output'),10,3);   //hack field output
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_livechat_settings() {


    }

    /**
     * check whether user visiting this page
     * @return bool
     */
    public static function is_current_screen(){
        $screen = get_current_screen(); //only current this page
        return strpos($screen->id , self::page_setting_slug)!==false;
    }
    /**
     * before output hw_skin apf field
     * @param $skin: HW_SKIN object
     * @param $Output: chunk of string to output
     * @param $field_output: field output in string
     * @hook hwskin_field_output
     */
    public function _apf_hwskin_field_output( $aOutput= array(),$skin,$field_output = ''){
        if(!self::is_current_screen()) return $aOutput;
        if(!preg_match('#<\s?option\s?( [^<>]+)?>(.+?)<\s?\/option\s?>#',$field_output)){
            $aOutput[] = '<div>Lưu ý: Trước khi chọn giao diện, vui lòng chọn dịch vụ chat ở trên và nhấn nút submit bên dưới. Hoặc dịch vụ này chưa có skin.</div>';
        }
        return $aOutput;
    }
    /**
     * modify apf hwskin params
     * @param $setting
     * @param $value
     * @return mixed|null|void
     * @hook apf_hwskin
     */
    public function _apf_hwskin($value, $setting){
        if($setting == 'group'){
            if(self::is_current_screen()){
                $chat_service = hw_livechat_option('chat_service');
                if($chat_service) return $chat_service;
            }
        }
        return $value;
    }
    /**
     * show livechat settings
     * @param $oAdminPage
     */
    public function load_hw_livechat_settings( $oAdminPage ) {
        
        /*register section
        $this->addSettingSections(
                self::page_setting_slug,    //must match with page slug
                array(
                        'section_id' => 'general',
                        'title' => 'Cài đặt',
                        'description' => 'Cài đặt chung.',
                ),
                array(
                        'section_id' => 'webhook',
                        'title' => 'Web hook',
                        'description' => 'Truy xuất dữ liệu ra bên ngoài.',
                )
        );*/
        $chat_list_services = array(
                'olark' => 'Olark',
                'zopim' => 'Zopim',

        );
        $this->addSettingFields(
                //'general',    ->no need specific group
                array(
                    'field_id' => 'enable_livechat',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt livechat',
                    'description' => 'Kích hoạt livechat'
                ),
                array(  //enable chat box on mobile
                    'field_id' => 'active_on_mobile',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt trên di động?',
                    'description' => 'Có kích hoạt boxchat trên di động ? Chú ý: để tính năng này hoạt động, yêu cầu kích hoạt thêm plugin HW Libraries.'
                ),
                array(    // emable wpcf7 css
                        'field_id'      => 'chat_service',
                        'type'          => 'select',
                        'title'         => __('Dịch vụ chat','hwchat'),
                        'description'   => __('Chọn dịch vụ live chat.','hwchat'),     #description that will be placed below the input field
                        'label' => $chat_list_services
                ),
                array(  //enabled wpcf7 js for ajax feature
                        'field_id' => 'chat_embed_code',
                        'type' => 'textarea',
                        'title' => __('Mã live chat','hwchat'),
                        'description' => __('Dán đoạn mã của tài khoản chat của bạn vào đây.','hwchat'),
                ),
                array(  //exclude pages
                        'field_id' => 'chat_skin'  ,
                        'type' => 'hw_skin',
                        'title' => __('Giao diện'),
                    //'label' =>'',
                        'description' => __('Chọn skin cho chatbox, lưu ý: yêu cầu footer.php phải gọi hàm <code>wp_footer()</code>')  ,
                    'enable_skin_condition' => true,
                    'external_skins_folder' => 'hw_livechat_skins',
                    'skin_filename' => 'hw-livechat-skin.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => HW_LIVECHAT_PATH,
                    'plugin_url' => HW_LIVECHAT_URL,
                    //'group' => 'olark' //dynamic
                ),
                
                //submit button
                array(
                        'type' => 'submit',
                        'field_id'      => 'submit_button',
                        'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
                    'label' => 'Lưu lại'
                )
        );

    }
    /**
     * The pre-defined validation callback method.
     *
     * The following hooks are available:
     *	- validation_{instantiated class name}_{field id} – [3.0.0+] receives the form submission value of the field that does not have a section. The first parameter: ( string|array ) submitted input value. The second parameter: ( string|array ) the old value stored in the database.
     *	- validation_{instantiated class name}_{section_id}_{field id} – [3.0.0+] receives the form submission value of the field that has a section. The first parameter: ( string|array ) submitted input value. The second parameter: ( string|array ) the old value stored in the database.
     *	- validation_{instantiated class name}_{section id} – [3.0.0+] receives the form submission values that belongs to the section.. The first parameter: ( array ) the array of submitted input values that belong to the section. The second parameter: ( array ) the array of the old values stored in the database.
     *	- validation_{page slug}_{tab slug} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     *	- validation_{page slug} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     *	- validation_{instantiated class name} – receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     */
    public function validation_HW_Livechat_settings($aInput, $aOldInput) {

        if(isset($aInput['chat_skin']['skin_options'])) {   //make sure exists current skin
            $options = $aInput['chat_skin']['skin_options'];    //user skin options

            //resume hw_skin
            if(isset($aInput['chat_skin']['hash_skin']) && isset($aInput['chat_skin']['hwskin_config'])) {
                $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($aInput['chat_skin']);//

                if($skin && isset($aInput['chat_skin']['hash_skin'])) {
                    $hash_skin = $aInput['chat_skin']['hash_skin'];     //current active skin

                    $skin_setting_file = $skin->instance->get_file_skin_setting($hash_skin); //current skin setting
                    $skin_options = $skin->instance->get_file_skin_options();      //current skin options

                    if(file_exists($skin_setting_file)) include ($skin_setting_file);
                    if(file_exists($skin_options)) include ($skin_options);

                    if(isset($theme) && isset($theme['options'])) $default_options = $theme['options']; //default options
                    else $default_options = array();

                    if( isset($theme_options)) {
                        $options = HW_SKIN::merge_skin_options_values($options, $default_options, $theme_options);

                        //__save_session('y2',$options);
                        /*$_SESSION['y3']=$skin_setting;
                        $_SESSION['y4']=$default_options;*/
                    }
                }
            }

            //get demo live chat embed code
            if(isset($options['enable_demo_chat']) && strtolower($options['enable_demo_chat']) == 'on'
                && isset($options['demo_embedcode'])) {
                $aInput['chat_embed_code'] = $options['demo_embedcode'];
                //clear enable_demo_chat option to able to update 'enable_demo_chat' skin option in next time
                unset($aInput['chat_skin']['skin_options']['enable_demo_chat']);
            }
        }
        //save current skin for enqueue
        if(!empty($aInput['chat_skin'])) {
            HW_SKIN::save_enqueue_skin(array(
                'type'=> 'resume_skin' ,
                'skin' => $aInput['chat_skin'],
                'object' => 'livechat',
                'status' => $aInput['enable_livechat']
            ));
        }

        return $aInput;
    }
}
if(is_admin() ){
    //init custom field type
    /*if(class_exists('APF_hw_skin_Selector_hwskin')) {
        new APF_hw_skin_Selector_hwskin('HW_Livechat_settings');
    }*/
    HW_APF_FieldTypes::apply_fieldtypes('hw_skin', 'HW_Livechat_settings');
    new HW_Livechat_settings;
}
endif;