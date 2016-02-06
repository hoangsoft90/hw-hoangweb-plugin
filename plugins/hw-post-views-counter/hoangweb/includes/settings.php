<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Post_Views_Counter_Settings
 * used for /includes/settings.php extending: class Post_Views_Counter_Settings extends HW_Post_Views_Counter_Settings{
 */
class HW_Post_Views_Counter_Settings {
    /**
     * continue private propery from Post_Views_Counter_Settings class
     * important: pre-define property
     * @var
     */
    private $tabs = null;
    private $options = null;
    private $positions = null;

    /**
     * construct method
     */
    public function __construct( $options = array()) {
        //$this->tabs = &$tabs;
        //$this->options = $options;
        foreach ($options as $name => &$data) {
            if(!is_numeric($name)) $this->$name = &$data;
        }

        //actions
        add_action( 'after_setup_theme', array( &$this, '_hw_load_defaults' ) );
        add_action( 'admin_init', array( &$this, '_hw_register_settings' ) );
    }

    /**
     * load settings
     */
    public function _hw_load_defaults(){
        //add new tab
        if(isset($this->tabs)) {
            $this->tabs['hoangweb'] = array(
                'name'	 => __( 'Firebase', 'post-views-counter' ),
                'key'	 => 'post_views_counter_settings_hoangweb',
                'submit' => 'save_pvc_firebase',
                'reset'	 => 'reset_pvc_firebase'
            );
        }
        //add new options
        if(isset($this->positions)) {
            $this->positions['hw_before_content'] = __('Hiển thị trước nội dung (hoangweb)');
            $this->positions['hw_after_content'] = __('Hiển thị sau nội dung (hoangweb)');
        }
    }
    /**
     * Validate general settings.
     */
    public function _hw_validate_settings($input) {//session_start();$_SESSION['z1']=($_POST);
        if ( isset( $_POST['save_pvc_firebase'] ) ) {

        }
        return $input;
    }
    /**
     * register settings
     */
    public function _hw_register_settings() {
        //hoangweb
        register_setting('post_views_counter_settings_hoangweb','post_views_counter_settings_hoangweb', array( &$this, '_hw_validate_settings' ));
        add_settings_section( 'post_views_counter_settings_hoangweb', __( 'Cài đặt Post views sử dụng Firebase', 'post-views-counter' ), '', 'post_views_counter_settings_hoangweb' );
        //register fields
        add_settings_field( 'hw_pvc_active_external_views_db', __( 'Kích hoạt Firebase', 'post-views-counter' ), array( &$this, 'hw_firebase_field' ), 'post_views_counter_settings_hoangweb', 'post_views_counter_settings_hoangweb' );

        add_settings_field( 'hw_pvc_firebase_db', __( 'Firebase DB', 'post-views-counter' ), array( &$this, 'hw_firebase_db_field' ), 'post_views_counter_settings_hoangweb', 'post_views_counter_settings_hoangweb' );

        add_settings_field( 'hw_pvc_firebase_path', __( 'Firebase Path', 'post-views-counter' ), array( &$this, 'hw_firebase_path_field' ), 'post_views_counter_settings_hoangweb', 'post_views_counter_settings_hoangweb' );
    }
    /**
    * enable firebase post views for real-time
    */
    public function hw_firebase_field(){
        $use_firebase = esc_attr( HW_Post_Views_Counter()->get_attribute( 'options', 'hoangweb', 'use_firebase' ) );

        echo '
		<div id="hw_pvc_active_external_views_db">
			<fieldset>
				<input id="pvc-hw-firebase" type="checkbox" name="post_views_counter_settings_hoangweb[use_firebase]" '.($use_firebase == 'on'? 'checked="checked"':'').'/><label for="pvc-hw-firebase">' . esc_html__( 'Nhấn vào đây nếu bạn muốn đếm post views sử dụng firebase thay thế.', 'post-views-counter' ) . '</label>
			</fieldset>
		</div>';
    }

    /**
     * display field for firebase db name
     */
    public function hw_firebase_db_field() {
        $firebase_db = esc_attr( HW_Post_Views_Counter()->get_attribute( 'options', 'hoangweb', 'firebase_db' ) );
        echo '
		<div id="hw_pvc_firebase_db">
			<fieldset>
				<span>https://</span><input id="pvc-hw-firebase-db" type="text" name="post_views_counter_settings_hoangweb[firebase_db]" value="'.$firebase_db.'"/><label for="pvc-hw-firebase-db"><span>.firebaseio.com</span></label> <p>'. esc_html__( 'Nhập tên firebase database bạn muốn lưu trữ bộ đếm post views. Đăng ký firebase <a href="https://www.firebase.com/" target="_blank">tại đây</a>.', 'post-views-counter' ) .'</p>
			</fieldset>
		</div>';
    }
    /**
     * display field for firebase path
     */
    public function hw_firebase_path_field() {
        $firebase_path = esc_attr( HW_Post_Views_Counter()->get_attribute( 'options', 'hoangweb', 'firebase_path' ) );
        echo '
		<div id="hw_pvc_firebase_path">
			<fieldset>
				<input id="pvc-hw-firebase-path" type="text" name="post_views_counter_settings_hoangweb[firebase_path]" value="'.$firebase_path.'"/><label for="pvc-hw-firebase-path"></label> <p>'. esc_html__( 'Nhập địa chỉ firebase bạn muốn lưu trữ bộ đếm post views. VD: /hoangwebcom/.', 'post-views-counter' ) .'</p>
			</fieldset>
		</div>';
    }

}
//new HW_Post_Views_Counter_Settings();