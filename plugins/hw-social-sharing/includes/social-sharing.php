<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Class HW_SocialShare
 */
class HW_SocialShare {
    /**
     * div ID wrapper
     */
    const class_socials_button_wrapper = 'hwss-float-socials-share';

    function __construct() {
        $this->setup_actions();
    }

    /**
     * setup actions
     */
    private function setup_actions() {
        add_action('init', array($this, '_hwss_init') );
        add_action('wp_enqueue_scripts', array($this, '_hwss_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, '_hwss_admin_enqueue_scripts'));
        add_action( 'admin_print_scripts', array($this, '_hwss_admin_inline_js' ) );

        add_action('wp_footer', array($this, '_hwss_footer_init'));

        add_action('the_content', array($this, '_hwss_display_socials_share_in_post') ,10);
    }

    /**
     * @wp_hook action init
     */
    public function _hwss_init(){
        wp_register_script('hwss-js',HW_SOCIALSHARE_URL. ('/js/main.js' ), array('jquery'));
        wp_register_script('hwss-admin-js',HW_SOCIALSHARE_URL.('/js/hwss-admin-js.js'),array('jquery'));
        //create help
        if(class_exists('HW_HELP')){
            HW_HELP::set_helps_path('share', HW_SOCIALSHARE_PATH . 'helps');
            HW_HELP::set_helps_url('share', HW_SOCIALSHARE_URL . '/helps' );
            HW_HELP::register_help('share');
            HW_HELP::load_module_help('share');
        }
    }
    /**
     * put script/css to frontend
     * @wp_hook action wp_enqueue_scripts
     */
    public function _hwss_enqueue_scripts(){
        wp_enqueue_script('hwss-js');

        if(is_admin()) return;	//for frontend
        if(self::is_show_float_sharebar()){
            wp_enqueue_style('hwss-socials-share',HW_SOCIALSHARE_URL.('/css/style.css'));
            wp_enqueue_script('jquery.sticky.js',HW_SOCIALSHARE_URL. ('/js/jquery.sticky.js'), array( 'jquery' ));
        }
    }
    /**
     * put script/css to backend
     * @wp_hook action admin_enqueue_scripts
     */
    public function _hwss_admin_enqueue_scripts(){
        wp_enqueue_script('hwss-admin-js');
    }
    /**
     * admin print inline js
     * @wp_hook action admin_print_scripts
     */
    public function _hwss_admin_inline_js(){

    }
    /**
     * part of website
     * @wp_hook action wp_footer
     */
    public function _hwss_footer_init(){
        //display float socials share bar
        self::show_float_sharebar();
    }
    /**
     * display share buttons on single post after post title
     * @wp_hook action the_content
     * @param $content current post content
     */
    public function _hwss_display_socials_share_in_post($content=''){
        if(is_single()) {
            self::show_standard_socials_button_in_post();
            return $content;
        }
        return $content;
    }
    /**
     * show standard socials button in single post
     */
    public static function show_standard_socials_button_in_post(){
        if(self::is_show_socials_share_in_post()) {
            $HW_Socials_fields = hwss_option() ;
            $pick_socials = hwss_option('socials_button', array());
            $final_socials = HW_SocialsShare_Settings::get_sort_socials_selected($pick_socials);
            //additionl options
            $options = array(
                'class_socials_button_wrapper'=>'horizontal-socials-share-buttons',
                'sharing_service' => $HW_Socials_fields['sharing_service'],

            );
            if(isset($HW_Socials_fields['button_size'])) {  //button size
                $options['button_size'] = $HW_Socials_fields['button_size'];
            }

            if(isset($HW_Socials_fields['custom_css'])) {
                $options['custom_css'] = $HW_Socials_fields['custom_css'];
            }
            hwss_horizontal_socials_share($final_socials, $options);

        }
    }
    /**
     * is visible socials button on single post
     */
    public static function is_show_socials_share_in_post(){
        return hwss_option('enable_standard_buttons_in_post', false ) ;
    }
    /**
     * whether active side float share bar
     */
    public static function is_show_float_sharebar(){
        static $display;
        if(empty($display)){
            $display = true;
            $restrict = hwss_option('pages_list', array() );
            if(!in_array('__all__',$restrict) && !is_page($restrict)) $display = false;
            if(! hwss_option('enable_side_share_bar', false ) ) $display = false;
        }
        return $display;
    }
    /**
     * show float share bar
     */
    public static function show_float_sharebar(){
        $HW_Socials_fields = hwss_option();
        $pick_socials = hwss_option('socials_button', array()) ;
        $display = self::is_show_float_sharebar();

        if($display) {
            $id = rand();
            echo '<div id="'.$id.'">';
            if(isset($HW_Socials_fields['wrap_id']) && $HW_Socials_fields['wrap_id']) {
                echo '<style>'.$HW_Socials_fields['wrap_id'].'{position:relative !important;}</style>';
            }
            echo '<script>
			  jQuery(document).ready(function($){
				$(".'.self::class_socials_button_wrapper.'").sticky({topSpacing:0});
				if(jQuery("#'.$id.'").length) {
					$("'.$HW_Socials_fields['wrap_id'].'").prepend(jQuery("#'.$id.'").html());
					jQuery("#'.$id.'").remove();
				}
			  });
			</script>';
            $final_socials = HW_SocialsShare_Settings::get_sort_socials_selected($pick_socials);
            //additionl options
            $options = array(
                'class_socials_button_wrapper' => self::class_socials_button_wrapper,
                'sharing_service' => $HW_Socials_fields['sharing_service']
            );
            if(isset($HW_Socials_fields['button_size'])) {  //button size
                $options['button_size'] = $HW_Socials_fields['button_size'];
            }
            if(isset($HW_Socials_fields['custom_css'])) {
                $options['custom_css'] = $HW_Socials_fields['custom_css'];
            }
            hwss_vertical_box_count_socials_share ($final_socials, $options);
            echo '</div>';

        };
    }
}
new HW_SocialShare();