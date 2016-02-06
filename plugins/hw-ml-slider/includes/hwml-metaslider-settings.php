<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/05/2015
 * Time: 15:43
 */
/**-------------------------------------------------------------------------------
metaslider basic settings
- get a setting: $this->slider->get_setting('<name>')
 */

if(class_exists('HW_WP_NOTICES')):
    class HW_MLSlider #extends HW_WP_NOTICES
    {
        /**
         * notice class
         * @var null
         */
        protected $msg = null;
        /**
         * current class instance
         */
        static $instance = null;

        /**
         * main class constructor
         */
        public function HW_MLSlider(){
            //parent::__contruct();
            $this->msg = HW_WP_NOTICES::get_instance();

            $this->setup_actions(); //setup hooks

            //setup metaslider themes
            if(class_exists('HW_SKIN') /*&& !isset($this->skin)*/){
                $this->skin = new HW_SKIN($this,HWML_PLUGIN_PATH,'hw_mlslider_skins','mlslider-skin.php','themes');
                //$this->skin->skin_name='mlslider-skin.php';
                //$this->skin->skin_folder='hw_mlslider_skins';	//you will see js object 'hw_skin_hw_mlslider_skins'
                $this->skin->plugin_url = HWML_PLUGIN_URL;
                $this->skin->create('private_slider','group-others');    //create theme with own slider js
                $this->skin->enable_template_engine(true, false);
            }
            else $this->msg->put_msg('Vui lòng kích hoạt lại plugin HW_SKIN trước khi sử dụng plugin này.','error');
        }

        /**
         * init hook
         */
        function setup_actions(){
            add_filter('hw_metaslider_menu_title', array($this, '_metaslider_menu_title'));
            add_filter('hw_metaslider_basic_settings',array(&$this,'_hwmlslider_metaslider_basic_settings'),10,2);
            //metaslider advanced settings
            add_filter('hw_metaslider_advanced_settings',array(&$this, '_hwmlslider_metaslider_advanced_settings'),10,2);
            //theme
            add_filter('hw_metaslider_get_available_themes',array(&$this, '_hw_metaslider_get_available_themes'),10,2);
            //navination option
            add_filter('hw_metaslider_navigation_options',array(&$this, '_hw_metaslider_navigation_options'),10,2);
            #scripts & styles
            add_action('hw_metaslider_register_admin_scripts',array(&$this, '_hwmlslider_metaslider_register_admin_scripts'));
            add_action('hw_metaslider_register_admin_styles', array(&$this, '_hwmlslider_metaslider_register_admin_styles'));
            add_action('admin_enqueue_scripts', array(&$this, '_admin_enqueue_scripts'));

            //add_action('admin_print_scripts-hw-metaslider', array($this, 'hwmlslider_print_scripts'));
            //add_action('admin_print_styles-hw-metaslider', array($this, 'hwmlslider_print_styles'));

            add_action('admin_menu', array($this, '_hwmlslider_admin_menu') ,10000);

            /*render slideshow*/
            add_filter('hw_metaslider_slideshow_output', array(&$this, '_hw_metaslider_slideshow_output'),20,3);
            add_filter('hw_metaslider_container_id', array(&$this, '_metaslider_container_id'),10,3);
            //show default settings
            add_filter('hw_metaslider_default_parameters', array(&$this,'_metaslider_default_parameters'));

            //load plugin textdomain
            add_action( 'init', array( $this, '_load_plugin_data' ) ,1000);
        }
        /**
         * @hook admin_menu
         * admin_menu
         */
        function _hwmlslider_admin_menu(){
            //remove metaslider pro page
            remove_submenu_page('hw-metaslider','metaslider-go-pro');
        }
        /**
         * filter slider menu page
         * @param $title
         * @return mixed
         */
        public function _metaslider_menu_title($title) {
            return 'Slides ảnh';
        }
        /**
         * Initialise translations & something
         */
        public function _load_plugin_data(){
            //textdomain
            load_plugin_textdomain( 'metaslider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            if(class_exists('HW_HELP')){
                HW_HELP::set_helps_path('slider', HWML_PLUGIN_PATH.'helps');
                HW_HELP::set_helps_url('slider', HWML_PLUGIN_URL .'/helps' );
                HW_HELP::register_help('slider');
                HW_HELP::load_module_help('slider');
            }
        }
        /**
         * return current class instance
         */
        static public function get_instance(){
            if(!self::$instance) self::$instance = new HW_MLSlider();
            return self::$instance;
        }
        /**
         * modify default slider params
         * @param array $params: default params for all slider
         */
        public function _metaslider_default_parameters($params){
            $params['opacity'] = 0.8;
            return $params;
        }
        /**
         * render metaslider slideshow, i will put skin content after this
         * @param $slideshow: slider output
         * @param int $id: slider id
         * @param array $settings: current slider settings data
         */
        function _hw_metaslider_slideshow_output($slideshow, $id, $settings){
            if(isset($this->skin)){
                //load actived skin
                $file = $this->skin->get_skin_file($settings['theme']);
                if(file_exists($file)) {
                    ob_start();
                    include($file);
                    //valid
                    if(!isset($theme['styles'])) $theme['styles'] = array();
                    if(!isset($theme['scripts'])) $theme['scripts'] = array();
                    /*
                    //enqueue stuff from skin
                    if(count($theme['styles']) || count($theme['scripts'])) {
                        $this->skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                    }*/
                    //enqueue stuff from skin
                    HW_SKIN::enqueue_skin_assets(array(
                        'instance' => $this->skin,
                        'hash_skin' => $settings['theme'],
                        'skin_file' => $file,
                        'skin_settings' => $theme
                    ));
                    $content = ob_get_contents();
                    ob_clean();
                    $slideshow .= $content;
                }
            }

            return $slideshow;
        }
        /**
         * change metaslider container DIV id
         * @param int $container_id: container id
         * @param int $id: slider id
         * @param array $settings: current slider settings
         * @hook hw_metaslider_container_id
         */
        public function _metaslider_container_id($container_id, $id, $settings){
            $container_id = !empty($settings['hw_mlcontainer_id'])? $settings['hw_mlcontainer_id'] : $container_id;	//override default container id
            return $container_id;
        }
        /**
         * callback the hook 'admin_enqueue_scripts' to enqueue stuff to backend in head
         * @hook admin_enqueue_scripts
         */
        public function _admin_enqueue_scripts(){
            if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen(array('post','metaslider', 'hwml_shortcode'))){
                wp_enqueue_style('hwml-admin-css',HWML_PLUGIN_URL.('/css/admin-style.css'));
            }

            //wp_localize_script('hw-skin-js-', 'hw_skinxx',$this->skins_data);
        }
        /**
         * load extra styles & scripts in 2 hooks 'admin_print_styles-metaslider' and 'admin_print_scripts-metaslider'. it can be replace above two hooks.
         * @hook hw_metaslider_register_admin_scripts
         */
        public function _hwmlslider_metaslider_register_admin_scripts(){
            wp_enqueue_script('hwml-js',HWML_PLUGIN_URL.('/js/hwml-js.js'),array('jquery'));

        }
        /**
         * load styles for admin
         * @hook hw_metaslider_register_admin_styles
         */
        public function _hwmlslider_metaslider_register_admin_styles(){
            wp_enqueue_style('hwml-style', HWML_PLUGIN_URL.('/css/hwml-style.css'));
        }
        /**
         * add custom fields to current slider settings
         * @param array $aFields: basic options
         * @param object $slider: current slider data
         * @hook hw_metaslider_basic_settings
         */
        public function _hwmlslider_metaslider_basic_settings($aFields,$slider){

            //get current theme screenshot
            $theme = $slider->get_setting('theme');
            $data = $this->skin->get_skin_data($theme);

            if($data){
                $thumb = '<img src="'.$data['screenshot'].'" onError="this.onerror=null;this.src=\''.HW_SKIN::get_image('error.jpg').'\'"/>';
            }
            else $thumb = '';
            //save current skin to db
            if(isset($_POST['settings']['theme'])) {
                $this->skin->save_skin_assets(array(
                    'skin' => array('hash_skin' => $theme),
                    'object' => 'hw-override-mlslider-'.$slider->id
                ));
            }

            //override exists 'theme' field
            $aFields['theme'] = array(
                'priority' => 40,
                'type' => 'theme',
                'value' => $theme,
                'label' => __( "Theme", "metaslider" ),
                'class' => 'effect coin flex responsive nivo',
                'helptext' => __( "Slideshow theme", "metaslider" ),
                'options' => array(),	//clear all default themes
            );

            //add theme preview holder
            $aFields['hw_theme_thumbnail'] = array(
                'priority' => 41,
                'type' => 'navigation',
                'label' => __( "<div id='ml_theme_thumb'>{$thumb}</div>", "hwml" ),
                'class' => 'option coin flex nivo responsive',
                'value' => '',
                'helptext' => __( "Giao diện theme", "hwml" ),
                'options' => array()
            );
            //modify metaslider container id
            $aFields['hw_mlcontainer_id'] = array(
                'priority' => 42,
                'type' => 'text',
                'value' => $slider->get_setting( 'hw_mlcontainer_id' ),
                'label' => __( "div ID chứa slider", "hwml" ),
                'class' => 'effect coin flex responsive nivo',
                'helptext' => __( "<p class='hwml-tipsy-tooltip'>Thay đổi thuộc tính <strong>ID</strong> của thẻ DIV chứa slider<br/><img src='".HWML_PLUGIN_URL.('/images/metaslider-container-id.jpg')."'/></p>", "hwml" ),
            );
            return $aFields;
        }
        /**
         * metaslider advanced settings section for current slider
         * @param array $aFields: advanced options
         * @param object $slider: current slider data
         * @hook hw_metaslider_advanced_settings
         */
        public function _hwmlslider_metaslider_advanced_settings($aFields,$slider){

            return $aFields;
        }
        /**
         * create option tag in themes selector with select tag
         * @param string $themes: html string for option tag present default themes by metaslider
         * @param string $current_theme: saved current theme
         * @hook hw_metaslider_get_available_themes
         */
        public function _hw_metaslider_get_available_themes($themes, $current_theme){
            $t = '';
            $data = $this->skin->generate_skin_options_tag($current_theme);
            $t .= $data['options'];
            return $themes.$t;
        }
        /*custom setting field*/
        //navination option
        public function _hw_metaslider_navigation_options($navigation_row, $slider){
            return $navigation_row;
        }
    }


endif;