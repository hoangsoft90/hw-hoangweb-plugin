<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/05/2015
 * Time: 21:01
 */
class HW_WPCF7
{
    /**
     * current class instance
     */
    private static $instance = null;
    /**
     * sample list forms
     */
    public static $form_templates = array();
    /**
     * @var HW_SKIN|null
     */
    public $skin = null;
    /**
     * manage template
     */
    private $template = null;

    /**
     * localize this object
     * @var unknown
     */
    private $hwcf7_localize_obj = array();
    /**
     * send contact form data via http
     * @var unknown
     */
    private $external_storages_hook = array();
    /**
     * special google form fields
     * @var unknown
     */
    static $special_gfields = array();

    public function HW_WPCF7(){
        //setup actions
        $this->setup_actions();
        $this->template = HW_WPCF7_Template::getInstance(); //form template manager
        //init skin
        if(class_exists('HW_SKIN')){

            $this->skin = new HW_SKIN($this,HW_WPCF7_PATH, 'hw_wpcf7_skins','hw-wpcf7-skin.php','themes');
            //$this->skin->skin_name='hw-wpcf7-skin.php';
            //$this->skin->skin_folder='hw_wpcf7_skins';	//folder hold all skins
            $this->skin->plugin_url = HW_WPCF7_URL;
            $this->skin->enable_external_callback = false;
            $this->skin->enable_template_engine(false,true);
        }
        self::$form_templates = array(
            'basic-contact-form' => __('Form liên hệ'),
            'order-form' => __('Form đặt hàng')
        );
        if(is_admin()){
            //initialize something
            //$hwcf_settings = AdminPageFramework::getOption( 'HW_Wpcf_settings','general');
            $enable_wpcf7_js = hw_wpcf7_option('enable_wpcf7_js');

            if(!$enable_wpcf7_js){
                remove_action( 'init', 'wpcf7_enqueue_scripts' );	//remove wpcf7 script
            }
        }

        //avaiable storages service
        $this->external_storages_hook = array(
            '' => __('Lưu mặc định'),
            'url' => __('URL'),
            'google_form' => __('Google Form')
        );
        self::$special_gfields = array(
            'sendEmail' => 'Làm nội dung để gửi mail từ google spreadsheet',
            'admin_email' => 'Địa chỉ email của admin',
            'website' => 'Đến từ địa chỉ website nào.'
        );
        //config localize object for this contact form
        $this->hwcf7_localize_obj['ajax_url'] = admin_url('admin-ajax.php');
        $this->hwcf7_localize_obj['storages_hook'] = $this->external_storages_hook;
    }

    /**
     * setup actions
     */
    private function setup_actions(){
        //modify wpcf7 admin menu
        add_action( 'init', array($this,'_init_something'), 10, 2 ); //
        add_action( 'admin_menu', array($this, '_modify_wpcf7_menu' ));

        add_action('wpcf7_init', array($this, '_hw_wpcf7_init'));   //init wpcf7 hook

        //wpcf7 version 4.0+
        if(defined('WPCF7_VERSION') && WPCF7_VERSION > 3.9) {
            add_action( 'wpcf7_admin_footer', array($this, '_hw_wpcf7_add_meta_box' ) );    //register my metabox where user can pick contact form skin for current
            #add_action('wpcf7_admin_footer',array($this, '_hw_wpcf7_admin_after_form'));   //display my registered metabox bellow 'form' metabox
            add_filter('wpcf7_editor_panels', array($this, '_hw_wpcf7_editor_panels'));
            add_action('wpcf7_contact_form', array($this,'_hw_wpcf7_admin_after_general_settings'));    //initial
        }
        #else { //should init hooks for all version
            //wpcf7 v3.9
            add_action( 'wpcf7_add_meta_boxes', array($this, '_hw_wpcf7_add_meta_box' ));
            add_action('wpcf7_admin_after_form',array($this, '_hw_wpcf7_admin_after_form'));   //display my registered metabox bellow 'form' metabox
            add_action('wpcf7_admin_after_general_settings',array($this,'_hw_wpcf7_admin_after_general_settings'));  //initial
        #}


        add_action( 'admin_enqueue_scripts', array($this, '_hw_admin_enqueue_scripts' ));
        add_action('wpcf7_save_contact_form', array(&$this, '_hw_wpcf7_save_contact_form'));      //save contact form settings

        //register wpcf7_contact_form_properties
        add_filter('wpcf7_contact_form_properties', array($this, '_hw_wpcf7_allow_properties'),10,2);


        //exclude pages for disabling wpcf7
        add_action( 'wp', array($this, '_hw_add_wpcf7_scripts' ));

        //grab google form fields via ajax
        add_action("wp_ajax_hw_catch_gdrive_formfields", array($this, "_hw_catch_gdrive_formfields"));
        add_action("wp_ajax_nopriv_hw_catch_gdrive_formfields", array($this, "_login_require_notice"));
    }

    /**
     * init hook
     */
    public function _init_something(){
        //load plugin help
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('wpcf7', HW_WPCF7_PATH.'helps');
            HW_HELP::set_helps_url('wpcf7', HW_WPCF7_URL. '/helps');
            HW_HELP::register_help('wpcf7');
            HW_HELP::load_module_help('wpcf7');
        }
    }
    /**
     * Modify registered post type menu label
     *
     * @param string $post_type Registered post type name.
     * @param array $args Array of post type parameters.
     */
    public function _modify_wpcf7_posttype($post_type, $args){  //not in use
        global $wp_post_types;
        if ( 'wpcf7_contact_form' === $post_type ) {

            //$wp_post_types[ $post_type ] = $args;
        }
    }

    /**
     * @hook admin_menu
     */
    public function _modify_wpcf7_menu(){
        global $menu;
        global $submenu;
        //find wpcf7 menu page at 26 position, if wpcf7 plugin installed on your wordpress
        if(isset($menu[26]) && $menu[26][0] == 'Contact'){
            $menu[26][0] = __('Quản lý Form');
        }
        if(isset($submenu['wpcf7'])){   //contact form 7 already installed
            $submenu['wpcf7'][0][0] = __('Danh sách Forms');
        }

    }
    /**
     * return current class instance
     */
    static public function get_instance(){
        if(!self::$instance) self::$instance = new HW_WPCF7();
        return self::$instance;
    }

    /**
     * enqueue wpcf7 resource
     */
    public function _hw_add_wpcf7_scripts(){
        $exclude = hw_wpcf7_option('exclude_pages');
        if ( is_page($exclude) && function_exists('wpcf7_enqueue_scripts'))    //wpcf7 js only for thoes pages
            wpcf7_enqueue_scripts();
    }

    /**
     * list allow contact form properties
     * @param $props
     * @param $wpcf7
     */
    public function _hw_wpcf7_allow_properties($props, $wpcf7){
        if(!isset($props['hw_wpcf7_use_skin'])) $props['hw_wpcf7_use_skin'] = '';   //enable wpcf7 skin
        if(!isset($props['hw_wpcf7_skin'])) $props['hw_wpcf7_skin'] = '';   //set default skin property, but need to check if exists field value, don't clear it'value
        if(!isset($props['hw_wpcf7skin_setting'])) $props['hw_wpcf7skin_setting'] = '';   // wpcf7 skin setting
        if(!isset($props['hw_default_gform'])) $props['hw_default_gform'] = ''; //default gform id field
        //form attributes
        if(!isset($props['hw_form_class_attr'])) $props['hw_form_class_attr'] = '';   //form class
        if(!isset($props['hw_form_id_attr'])) $props['hw_form_id_attr'] = '';   // form id
        if(!isset($props['hw_form_name_attr'])) $props['hw_form_name_attr'] = '';   //form name attribute
        if(!isset($props['hw_form_enctype_attr'])) $props['hw_form_enctype_attr'] = '';   // form enctype

        if(!isset($props['hw_custom_css'])) $props['hw_custom_css'] = '';   //custom css
        if(!isset($props['hw_form_template'])) $props['hw_form_template'] = '';   //forms list template
        if(!isset($props['hw_gformID'])) $props['hw_gformID'] = ''; //google form ID
        if(!isset($props['enable_email_by_gapp'])) $props['enable_email_by_gapp'] = ''; //enable send mail by google app
        if(!isset($props['hook_url'])) $props['hook_url'] = ''; //web hook url
        if(!isset($props['hwcf_data_hook'])) $props['hwcf_data_hook'] = ''; //storage hook type
        if(!isset($props['hw_sent_ok_redirect_page'])) $props['hw_sent_ok_redirect_page'] = '';     //sent ok event
        if(!isset($props['hw_on_sent_ok_js_event'])) $props['hw_on_sent_ok_js_event'] = '';     //on_sent_ok js event
        if(!isset($props['hw_on_submit_js_event'])) $props['hw_on_submit_js_event'] = '';       //on_submit js event

        return $props;
    }
    /**
     * add a metabox to edit contact form screen
     * @param $post
     */
    function _hw_wpcf7_add_meta_box( $post){
        add_meta_box( 'skindiv', __( 'Chọn giao diện Form', 'contact-form-7' ),array($this,'_wpcf7_skin_meta_box'), null, 'hw_wpcf7_skin', 'core' );
    }
    /**
     * return current working form
     */
    function current_form(){
        $current = WPCF7_ContactForm::get_current() ;
        return $current;
    }
    /**
     * get google form response (spreadsheet) link
     * @param string $id: google form id
     * @return string
     */
    public static function get_gform_response_link($id){
        #return 'https://docs.google.com/forms/d/'.$id.'/formResponse';
        return 'https://docs.google.com/spreadsheets/d/'.$id;
    }
    /**
     * metabox content for skin selector
     * @param object $contact_form  current wcpf7 object
     */
    public function _wpcf7_skin_meta_box( $contact_form ) {
        //$contact_form = WPCF7_ContactForm::get_instance( $post );    //or call: wpcf7_contact_form($post_id);
        //form enctypes list
        $form_enctypes = array(
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain'
        );

        $properties = $contact_form->get_properties();
        $active_theme = $contact_form->prop('hw_wpcf7_skin');   //get active skin
        $skin_config = $contact_form->prop('hw_wpcf7skin_setting');    //skin settings
        if($active_theme) $skin_config['hash_skin'] = $active_theme;    //save hash skin

        //get current form template
        $temp = $contact_form->prop('hw_form_template');//_print($active_theme);

        //get current storage hook
        $active_hook = $contact_form->prop('hwcf_data_hook');

        //active private skin for wpcf7
        $enable_skin = $contact_form->prop('hw_wpcf7_use_skin');

        //form attributes
        $form_class = $contact_form->prop('hw_form_class_attr');
        $form_id = $contact_form->prop('hw_form_id_attr');
        $form_name = $contact_form->prop('hw_form_name_attr');
        $form_enctype = $contact_form->prop('hw_form_enctype_attr'); //form enctype

        /**
         * form events
         */
        //redirect page option on submit success
        $page_sent_ok = $contact_form->prop('hw_sent_ok_redirect_page');
        $on_sent_ok_js_event = $contact_form->prop('hw_on_sent_ok_js_event');
        $on_submit_js_event = $contact_form->prop('hw_on_submit_js_event');

        //begin localize script
        wp_enqueue_script('hw-wpcf7-js');
        wp_localize_script('hw-wpcf7-js', 'HW_WPCF7', $this->hwcf7_localize_obj);

        ?>
        <div class="hw-wpcf7-settings">
            <div><strong>Yêu cầu:</strong><br/>
                <ul>
                    <li><strong>Shortcode tạo trường</strong>: sử dụng placeholder làm tiêu đề của trường. vd: [text* your-name placeholder "Field title"]</li>
                    <li><strong>Trường đặc biệt</strong>: Bạn nên tạo thêm 3 trường đặc biệt trong google form:
                        <ul>
                            <?php foreach( self::$special_gfields as $name => $desc) echo '<li><u>'.$name.'</u>:'.$desc.'</li>';?>
                        </ul>
                    </li>
                </ul>
            </div><hr/>
            <!-- #contact form skin -->
            <div>
                <table data-width="" width="100%" cellspacing="5px" cellpadding="5px;">
                    <tr>
                    <td valign="top">
            <h2><?php _e('Giao diện');?></h2>
            <?php if(class_exists('HW_SKIN')){?>
                <p>
                    <label for="use_skin"><strong><?php _e( 'Kích hoạt giao diện riêng:' ); echo $enable_skin;?></strong></label><br/>
                    <input type="checkbox" name="hw_wpcf7_use_skin" id="hw_wpcf7_use_skin" <?php checked($enable_skin == 'on'? 1:0)?>/>
                </p>
                <!-- skin selector -->
                <p>
                    <label for="hw_wpcf7_skin"><strong><?php _e( 'Giao diện :' ); ?></strong></label><br/>
                    <?php //$this->skin->get_skins_select_tag('hw_wpcf7_skin',$active_theme)?>
                    <select name="hw_wpcf7_skin" id="hw_wpcf7_skin" onchange="">
                        <?php $options = $this->skin->generate_skin_options_tag($active_theme);
                        echo $options['options'];
                        ?>

                    </select>

                </p>
                <p>
                    <?php
                    //skin condition, skin options
                    echo $this->skin->create_total_skin_selector('hw_wpcf7skin_setting', $skin_config, array(), array(
                        'show_main_skin' =>0,
                        'show_config_field' => 1,
                        'show_condition_field' => 1,
                        'show_skin_options' => 1,

                    ));
                    ?>
                </p>
            <?php }?>
            <p><!-- list forms template -->
                <label for="hw_form_template"><strong><?php _e('Mẫu form')?></strong></label><br/>
                <select name="hw_form_template" id="hw_form_template">
                    <option value="">----Mặc định----</option>
                    <?php foreach(self::$form_templates as $name => $text){?>
                        <option <?php selected($temp == $name? 1:0)?> value="<?php echo $name?>"><?php echo $text;?></option>
                    <?php }?>
                </select><br/>
                <!-- <a class="button" href="#"><?php _e('Dùng mẫu này')?></a><br/> -->
                <span>Chú ý:<em>Các trường form bạn nhập ở trên sẽ bị xóa và thay thế bởi template bạn chọn. Backup trường form & các thộc tính của bạn ở trên, để sửa lại sau khi thay đổi form template mới.</em></span>
            </p>
            <p>
                <label for="hw_form_class_attr"><strong><?php _e('HTML Form class')?></strong></label><br/>
                <input type="text" name="hw_form_class_attr" id="hw_form_class_attr" value="<?php echo $form_class?>"/>
            </p>
            <p>
                <label for="hw_form_id_attr"><strong><?php _e('HTML Form ID')?></strong></label><br/>
                <input type="text" name="hw_form_id_attr" id="hw_form_id_attr" value="<?php echo $form_id?>"/>
            </p>
            <p>
                <label for="hw_form_name_attr"><strong><?php _e('HTML Form Name')?></strong></label><br/>
                <input type="text" name="hw_form_name_attr" id="hw_form_name_attr" value="<?php echo $form_name?>"/>
            </p>
            <p>
                <label for="hw_form_enctype_attr"><strong><?php _e('HTML Form enctype')?></strong></label><br/>
                <select name="hw_form_enctype_attr" id="hw_form_enctype_attr">
                    <option value="">---- Chọn ----</option>
                    <?php foreach($form_enctypes as $enctype){
                        $selected = selected($enctype, $form_enctype, false);
                        printf('<option %s value="%s">%s</option>', $selected, $enctype, $enctype);
                    }?>

                </select>
            </p>

                    </td>
                        <td valign="top">
                            <!-- skin preview -->
                            <div id="preview_skin">
                                <?php if($active_theme){ //display active skin
                                    $data = $this->skin->get_skin_data($active_theme);
                                    ?>
                                    <img src="<?php echo $data['screenshot']?>" onError="this.onerror=null;this.src='<?php echo HW_SKIN::get_image('error.jpg')?>';"/>
                                <?php }?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- #contact form event -->
            <hr/>
            <div >
                <h2>Sự kiện contact form</h2>
                <p>
                    <label for="hw_sent_ok_redirect_page"><strong><?php _e('Trang thành công')?></strong></label><br/>
                    <select name="hw_sent_ok_redirect_page" id="hw_sent_ok_redirect_page">
                        <option value="-1">Trang hiện tại</option>
                        <?php
                        $pages = get_pages();
                        $options = array();
                        foreach($pages as $page) {
                            //$options[$page->ID] = $page->post_title;
                            $selected = ($page_sent_ok == $page->ID)? 'selected="selected"' : '';
                            printf('<option value="%s" %s>%s</option>', $page->ID, $selected, $page->post_title);
                        }

                        ?>
                    </select>
                </p>
                <p>
                    <label for="hw_on_sent_ok_js_event"><strong><?php _e('on_sent_ok')?></strong></label><br/>
                    <textarea name="hw_on_sent_ok_js_event" id="hw_on_sent_ok_js_event"><?php echo $on_sent_ok_js_event?></textarea><br/>
                    <em>Gọi Javascript khi form submit thành công</em>
                </p>
                <p>
                    <label for="hw_on_submit_js_event"><strong><?php _e('on_submit')?></strong></label><br/>
                    <textarea name="hw_on_submit_js_event" id="hw_on_submit_js_event"><?php echo $on_submit_js_event?></textarea><br/>
                    <em>Gọi Javascript sau khi form submit chu dù thất bại.</em>
                </p>

            </div>
            <!-- # contact form DB -->
            <hr/>
            <div>
                <h2><?php _e("Lưu dữ liệu form")?></h2>
                <p>
                    <!-- enable send email via google app -->
                    <input type="checkbox" name="enable_email_by_gapp" id="enable_email_by_gapp" <?php checked($contact_form->prop('enable_email_by_gapp') =='on' ?1:0)?>/>
                    <label for="enable_email_by_gapp"><?php _e('Kích hoạt gửi email bằng google app')?></label><br/>
                    <span><em>Tùy chọn này sẽ tắt chế độ gửi mail mặc định trong Contact Form.</em></span>
                </p>
                <p>
                    <label for="hwcf_data_hook"><strong><?php _e('Web hook')?></strong></label><br/>
                    <select name="hwcf_data_hook" id="hwcf_data_hook" onchange="HW_WPCF7.wpcf_hookdata_change_event(this)">
                        <?php foreach($this->external_storages_hook as $hook => $display):?>
                            <option <?php selected($hook==$active_hook? 1: 0)?> value="<?php echo $hook?>"><?php echo $display?></option>
                        <?php endforeach;?>
                    </select>
                </p>
                <div id="hook_google_form" class="hwcf-hidden"><!-- google form ID -->
                    <p>
                        <label for="hw_gformID"><strong><?php _e('Google form ID/URL')?></strong></label><br/>
                        <input type="text" name="hw_gformID" id="hw_gformID" value="<?php echo $contact_form->prop('hw_gformID')?>"/><br/>
                        <em>Chú ý:
                            <ul>
                                <li>Điền đầy đủ URL nếu có dạng: https://docs.google.com/a/{account}/forms/d/{ID}/viewform</li>
                                <li>Để theme classic mặc định của form để hoạt động đúng.</li>
                            </ul> </em>
                    </p>
                    <p>
                        <label for="hw_default_gform">
                        <input type="checkbox" title="Chú ý: Tùy chọn này được reset sau khi kích hoạt." class="tooltip" name="hw_default_gform" id="hw_default_gform" <?php checked($contact_form->prop('hw_default_gform'),'on')?>/><strong><?php _e('Kích hoạt mặc định')?></strong>
                        </label><br/>
                        <em>Chú ý: Tùy chọn này được reset sau khi kích hoạt.</em>
                    </p>
                    <div>
                        <a class="button" id="grab_gform_fields"><?php _e('Lấy fields')?></a>|
                        <!--
          <a class="button" target="_blank" href="<?php echo self::get_gform_response_link($contact_form->prop('hw_gformID'))?>" id="gform_response"><?php _e('Xem kết quả')?></a>|
           -->
                    </div>
                    <br/>
                    <span><em>Dùng để lưu trữ dữ liệu gửi đi từ người dùng trên website. Chú ý: mỗi shortcode tạo trường contact form khai báo thêm thuộc tính để gán vào mỗi field của google form. VD: <strong>[text* your-name gfield:489yiertrfg]</strong></em></span>
                    <p  class="" id="hw_parse_gform_result"></p>
                </div>
                <div id="hook_url" class="hwcf-hidden">
                    <label for="hook_url"><strong><?php _e('Hook URL')?></strong></label>
                    <input type="text"  id="hook_url" name="hook_url" value="<?php echo $contact_form->prop('hook_url')?>"/>
                </div>

            </div>
            <hr/>
            <div >
                <h2><?php _e("Cài đặt khác")?></h2>
                <!-- custom css -->
                <p>
                    <label for="hw_custom_css"><strong><?php _e('Tùy chỉnh CSS')?></strong></label><br/>
                    <textarea id="hw_custom_css" name="hw_custom_css" cols="100" rows="8"><?php echo esc_textarea( $contact_form->prop( 'hw_custom_css' ) ); ?></textarea><br/>
                    <span><strong>Lưu ý</strong>: biến <em>{form_css}</em> đại diện cho truy cập class từ form này. Nên dùng để tránh xung đột CSS với contact form 7 khác.</span>
                </p>
                <p>
                    <label><strong>Gửi dữ liệu Form vào SMS</strong></label><br/>
                    Xem <a href="#" target="_blank">hướng dẫn gửi SMS từ form</a> sau khi người dùng nhấn nút gửi nội dung trên Contact Form.
                </p>
            </div>
        </div>
        <script>

        </script>
        <br class="clear" />
    <?php
    }
    /**
     * save contact form settings
     * @param object $contact_form: current contact form instance;
     */
    public function _hw_wpcf7_save_contact_form($contact_form){
        $properties = $contact_form->get_properties();
        $additional_settings = array(); //set additional_settings

        //$properties = array();
        //enable private skin
        if ( isset( $_POST['hw_wpcf7_use_skin'] ) ) {
            $properties['hw_wpcf7_use_skin'] = trim( $_POST['hw_wpcf7_use_skin'] );
        }
        else $properties['hw_wpcf7_use_skin'] = 'off';

        //get selected skin
        if ( isset( $_POST['hw_wpcf7_skin'] ) ) {   //contact form skin
            $properties['hw_wpcf7_skin'] = trim( $_POST['hw_wpcf7_skin'] );
        }
        //skin setting
        if( isset( $_POST['hw_wpcf7skin_setting'] ) ) {
            $properties['hw_wpcf7skin_setting'] = $_POST['hw_wpcf7skin_setting'];
        }

        if ( isset( $_POST['hw_custom_css'] ) ) {   //custom css
            $properties['hw_custom_css'] = trim( $_POST['hw_custom_css'] );
        }
        /*form attributes*/
        if ( isset( $_POST['hw_form_class_attr'] ) ) $properties['hw_form_class_attr'] = trim( $_POST['hw_form_class_attr'] );  //form class attr
        if ( isset( $_POST['hw_form_id_attr'] ) ) $properties['hw_form_id_attr'] = trim( $_POST['hw_form_id_attr'] );   //form id attr
        if ( isset( $_POST['hw_form_name_attr'] ) ) $properties['hw_form_name_attr'] = trim( $_POST['hw_form_name_attr'] ); //form name attr
        if ( isset( $_POST['hw_form_enctype_attr'] ) ) $properties['hw_form_enctype_attr'] = trim( $_POST['hw_form_enctype_attr'] ); //form enctype

        //save storage hook
        if ( isset( $_POST['hwcf_data_hook'] ) ) $properties['hwcf_data_hook'] = trim( $_POST['hwcf_data_hook'] );
        /**
         * google form hook
         */
        if ( isset( $_POST['hw_gformID'] ) ) {   //save google form ID
            $properties['hw_gformID'] = trim( $_POST['hw_gformID'] );
        }
        if ( isset( $_POST['enable_email_by_gapp'] ) ) {   //enable send mail through google form ID
            $properties['enable_email_by_gapp'] = trim( $_POST['enable_email_by_gapp'] );
        }
        else $properties['enable_email_by_gapp'] = 'off';
        //enable default gform from hoangweb google acc
        if ( isset( $_POST['hw_default_gform'] ) ) {
            $properties['hw_gformID'] = hw_wpcf7_default_gform();
            unset($_POST['hw_default_gform']);  //clear this option
        }

        //populate form event
        if( isset($_POST['hw_sent_ok_redirect_page'])) {
            $properties['hw_sent_ok_redirect_page'] = trim( $_POST['hw_sent_ok_redirect_page'] );
        }
        //on_sent_ok js event after form success sent data
        if( isset($_POST['hw_on_sent_ok_js_event'])) {
            $properties['hw_on_sent_ok_js_event'] = trim( $_POST['hw_on_sent_ok_js_event'] );
            $additional_settings[] = 'on_sent_ok: "'.esc_attr($properties['hw_on_sent_ok_js_event']) .'"';
        }
        //on_submit js event after form submit whatever event send error
        if( isset($_POST['hw_on_submit_js_event'])) {
            $properties['hw_on_submit_js_event'] = trim( $_POST['hw_on_submit_js_event'] );
            $additional_settings[] = 'on_submit: "'.esc_attr($properties['hw_on_submit_js_event']) .'"';
        }

        //web hook url
        if ( isset( $_POST['hook_url'] ) ) $properties['hook_url'] = trim( $_POST['hook_url'] );
        //$current = WPCF7_ContactForm::get_current();
        //__save_session('properties',$properties);__save_session('temp',$this->template->get_form_template($properties['hw_form_template']));
        if(isset( $_POST['hw_form_template'] ) && $_POST['hw_form_template'] !== $properties['hw_form_template']    //change new template
            && isset($properties['hw_form_template']) && $this->template->get_form_template(trim($_POST['hw_form_template']))){ //form template
            $properties['hw_form_template'] = trim( $_POST['hw_form_template'] );   //save form template

            $new_fields = $this->template->parse_form_template_fields($properties['hw_form_template']);
            //update exists form to new selected template
            $properties['form'] = $new_fields;

            //update mail template
            $properties['mail'] = $this->template->mail_template($properties['hw_form_template']);

        }
        //update additional_settings to this form
        if(count($additional_settings)) {
            $properties['additional_settings'] = join("\n" , $additional_settings);
        }
        $contact_form->set_properties( $properties );   //update cf settings
        #__save_session('_POST',$_POST);
        //save current skin for enqueue
        if(!empty($properties['hw_wpcf7_skin'])) {
            $this->skin->save_skin_assets(array(
                'skin' => array(
                    'hash_skin' => $properties['hw_wpcf7_skin'],
                    'hwskin_condition' => $properties['hw_wpcf7skin_setting']['hwskin_condition'],
                    'theme_options' => $properties['hw_wpcf7skin_setting']['skin_options']
                ),
                'status'=> ($properties['hw_wpcf7_use_skin']=='off')? 0:1,
                'object' => 'hw-wpcf7'
            ));

        }
    }
    /**
     * ajax handle
     */
    public function _hw_catch_gdrive_formfields(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_catch_gdrive_formfields_nonce")) {
            exit("No naughty business please");
        }
        $gform_id = urldecode($_REQUEST['gform_id'] );   //google form ID
        $fields = array();

        //get form content
        $url_action = hw_wpcf7_valid_gform_url($gform_id);

        $html = HW_CURL::curl_get($url_action);

        //parse form fields
        preg_match_all('#<(input|textarea|select).+?\/?>#',$html,$result);
        if(isset($result[0]) && is_array($result[0])){
            foreach ($result[0] as $f){
                preg_match_all('#(name|aria-label)=\"([^"]*)\"#',$f,$atts);
                if(isset($atts[2]) && is_array($atts[2]) && count($atts[2])){
                    $fname = strtolower($atts[2][0]);   //field name should be lowercase
                    if(strrpos($fname, 'entry.', -strlen($fname)) === FALSE)  continue;
                    $fields[$fname] = isset($atts[2][1])? trim($atts[2][1]) : '';    //get fields name & label
                    //detect sendEmail field
                    if(trim($fields[$fname]) == 'sendEmail') $avaiable_fsendEmail = true;
                }
            }

        }
        $out = '<div class="result hwcf-parse-gform-result">';

        if(count($fields)):
            $out .= '<span>Chú ý: tạo thêm thuộc tính "gfield" cho các trường form của bạn bằng cách tên các trường tương ứng dưới đây:
                    </span>';
            $ex_scfield = '';
            $has_special_field = false; //check exists form has special field

            $out .= '<table>';
            //display fields
            foreach($fields as $name => $label){
                //check for special field
                if(isset(self::$special_gfields[$label])){
                    $has_special_field = true;  //marked exists one or more special fields
                    continue;    //ignore special fields
                }
                if(!$ex_scfield){
                    $ex_scfield = '<tr><td colspan="2"><br/><span>VD: <strong>[text* your-name gfield:'.$name.']</strong></span>';
                    $out .= $ex_scfield.'<hr/></td></tr>';
                }
                $out .='<tr>';
                $out .= '<td valign="bottom"><div class="g-field"><span>'.$label.'</span><br/><input type="text" readonly value="gfield:'.$name.'"/></div></td>';
                $out .= '<td valign="bottom"><div class="g-field"><input type="text" value="placeholder \''.$label.'\'" readonly/></div></td>';
                $out .= '</tr>';
            }
            //display special fields
            if($has_special_field):
                $special_fields_shortcode_tag = '[hw_wpcf7_special_fields hwspgf ';
                $reverse_fields = array_flip($fields);
                $out .= '<tr><td colspan="2" class="special-fields-label">Các trường đặc biệt sử dụng bởi Google Script</td></tr>';

                foreach(self::$special_gfields as $flabel => $desc){
                    if(isset($reverse_fields[$flabel])){
                        $special_fields_shortcode_tag .= $flabel.':'.$reverse_fields[$flabel].' ';
                        $out .= '<tr class="gform-special-field">';
                        $out .= '<td valign="bottom" colspan=""><div class="g-field"><span>'.$flabel.'</span><br/><input type="text" readonly value="gfield:'.$reverse_fields[$flabel].'"/></div></td>';
                        $out .= '<td valign="top"><div class="g-field">'.$desc.'</div></td>';
                        $out .= '</tr>';
                    }
                }
                $special_fields_shortcode_tag = trim($special_fields_shortcode_tag);    //truncate space around this variable value
                $special_fields_shortcode_tag .= ']';
            endif;
            $out .= '</table>';

            if(!isset($avaiable_fsendEmail)) $out .= '<span style="color:red">Cảnh báo: không tìm thấy nhãn trường (chứa nội dung gửi email) "sendEmail" trong google form. Để có thể gửi mail từ google spreadsheet vui lòng thêm trường với nhãn "sendEmail" trong google form có liên kết vào spreadsheet đó.</span>';
            else {
                $out .= '<p>Chúc mừng: đã tìm thấy trường lưu nội dung gửi email trong google form của bạn. Nội dung này tự động lấy từ Email template ở box "Email" ngay bên dưới.</p>';
                $out .= '<p>Copy trường shortcode này vào nội dung trường form fields ở trên, đặt phía dưới cùng của form hoặc bất kỳ vị trí nào đều hoạt động.<br/><input type="text" style="width:100%" value="'.$special_fields_shortcode_tag.'" readonly/></p>';
            }
        else:
            $out .= 'Không tìm thấy dữ liệu. Hãy kiểm tra lại google form ID?';
        endif;
        $out .= '</div>';
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            echo $out;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    public function _login_require_notice() {
        echo "^^@";
        die();
    }
    /**
     * register shortcode tag for storing special fields name that you don't want show on contact form
     * note: not show any string for this field on website
     * @param unknown $tag
     */
    public function _wpcf7_shortcode_special_fields($tag){
        if (!is_array($tag)) return '';
        //$name = $tag['name']; ->don't need field name, because grab this field via contact form data by code
        //if (empty($name)) return '';
        return '';
    }
    /**
     * display addition metabox on wpcf7 contact form setting page
     * this alternate to use hook 'wpcf7_enqueue_scripts'
     * @hook wpcf7_admin_after_form
     * @param $post contact form object
     */
    public function _hw_wpcf7_admin_after_form($post){
        do_meta_boxes( null, 'hw_wpcf7_skin', $post );
    }

    /**
     * @hook wpcf7_editor_panels
     * @since wpcf7 v4.0+
     * @param $panels
     */
    public function _hw_wpcf7_editor_panels($panels) {
        $panels['hw-settings'] = array(
            'title' => __( 'Cấu hình chức năng', 'contact-form-7' ),
            'callback' => array($this, '_hw_wpcf7_form_settings') );
        return $panels;
    }

    /**
     * display form settings
     * @since wpcf7 v4.0+
     * @param $post
     */
    public function _hw_wpcf7_form_settings($post) {
        $this->_wpcf7_skin_meta_box($post);
    }
    /**
     * init something
     * @hook wpcf7_admin_after_general_settings
     * @param object $contact_form: current wpcf7 object
     */

    public function _hw_wpcf7_admin_after_general_settings($contact_form){
        //generate ajax link to fetch form fields
        $nonce = wp_create_nonce("hw_catch_gdrive_formfields_nonce");
        $fetch_gfields_url = admin_url('admin-ajax.php?action=hw_catch_gdrive_formfields&form_id='.$contact_form->id().'&nonce='.$nonce);

        $this->hwcf7_localize_obj['current_formID'] = $contact_form->id();
        $this->hwcf7_localize_obj['fetch_gfields_url'] = $fetch_gfields_url;

    }
    /**
     * hook into wpcf7_init
     * @hook init
     */
    public function _hw_wpcf7_init(){
        //add wpcf7 shortcode for special fields
        wpcf7_add_shortcode('hw_wpcf7_special_fields', array($this, '_wpcf7_shortcode_special_fields'),true);
    }

    /**
     * put scripts/styles in wp admin page
     * @hook admin_enqueue_scripts
     * @param $hook_suffix: present current page slug
     */
    public function _hw_admin_enqueue_scripts($hook_suffix){
        if ( false === strpos( $hook_suffix, 'wpcf7' ) )
            return; //only for wpcf7 current page
        wp_enqueue_style('hw-wpcf7-style',HW_WPCF7_URL.('/style.css'));
        //admin js
        wp_register_script('hw-wpcf7-js',HW_WPCF7_URL.('/js/hw-wpcf7-js.js'));
        //wp_enqueue_script('hw-wpcf7-js'); //call with wp_localize together
        wp_enqueue_script('jquery.sticky.js', HW_WPCF7_URL.('/js/jquery.sticky.js'));
    }

}