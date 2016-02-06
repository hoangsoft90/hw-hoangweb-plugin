<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * shortcode
 * @param string $socials_btn
 * @param string $button_style
 * @param string $button_size
 * @param array $options
 */
function hwss_socials_share($socials_btn = 'facebook_share,facebook_like,facebook_recommend_button,googleplus,twitter,twitter_follow_button,linkedin,pinterest',$button_style = 'standard',$button_size = 'small',$options = array()){
	if(is_string($socials_btn)) $socials_btn = explode(',',$socials_btn);
	//valid socials buttons
	$socials_btn = array_intersect($socials_btn,array_keys(HW_SocialShare_widget::$socials_button));
	
	$params = array(
		'title' => ' ',
		'sharing_service' => 'addthis',	//default use addthis sharing service
		'addthis_compactbtn_style' => '',
		'button_style' => $button_style,
		'button_size' => $button_size,
        'scale' => '',
		'custom_css' => '',
		'pick_socials' => $socials_btn,
		'save_order_items_pick_socials' => is_string($socials_btn)? $socials_btn : (is_array($socials_btn)? join(',',$socials_btn):'')
		);
	$params = array_merge($params,$options);
	the_widget('HW_SocialShare_widget',$params ,array('before_title'=>'','after_title'=>'','before_widget'=>'','after_widget'=>''));
}

/**
 * Socials sharing for vertical box count style
 * @param string $socials_btn
 * @param array $options
 */
function hwss_vertical_box_count_socials_share($socials_btn = '',$options = array()){
	hwss_socials_share($socials_btn,'vertical-counter', '' , $options);
}

/**
 * Socials sharing for standard buttons
 * @param string $socials_btn
 * @param array $options
 */
function hwss_horizontal_socials_share($socials_btn = '',$options = array()){
	hwss_socials_share($socials_btn,'standard','small',$options);
}

/**
 * group socials sharing with addthis
 * @param string $image
 */
function hwss_compact_socials_share_addthis($image =''){
	$params = array(
		'title' => ' ',
		'sharing_service' => 'addthis',
		'addthis_compactbtn_style' => '',
		'button_style' => '',
		'button_size' => '',
        'scale' => '',
		'custom_css' => '',
		//'pick_socials' => $socials_btn,
		//'save_order_items_pick_socials' => is_string($socials_btn)? $socials_btn : (is_array($socials_btn)? join(',',$socials_btn):''),
		'addthis_compactbtn_style' => plugins_url('images/addthis_compactbtn/'.$image, dirname(__FILE__)),
		'addthis_compactbtn'=>'on'
		);
	the_widget('HW_SocialShare_widget',$params,array('before_title'=>'','after_title'=>'','before_widget'=>'','after_widget'=>''));
}

/**
 * return social shares setting
 * @param string $name setting name
 * @param mixed $default if null get default value
 */
function hwss_option ($name = '', $default = '') {
    if($name) return AdminPageFramework::getOption( 'HW_SocialsShare_Settings', $name, $default);
    return AdminPageFramework::getOption( 'HW_SocialsShare_Settings' );
}

/**
 * require HW_HOANGWEB plugin
 */
register_activation_hook( HW_SOCIALSHARE_PLUGIN_FILE, 'hwshare_require_plugins_activate' );
function hwshare_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');

}
?>