<?php
#/root>
/**
 * Class HW_AWC
 */
class HW_AWC extends HW_AWC_WidgetFeatures{
    /**
     * @var array
     */
    private $css_content = array();
    /**
     * only one instance of this class
     */
    static private $instance = null;

    /**
     * class construct method
     */
    public function __construct(){
        if(class_exists('HW_AWC_Sidebar_Settings')){
            $this->setup_actions();
        }
    }

    /**
     * setup actions
     */
    private function setup_actions(){
        add_filter('dynamic_sidebar_params',array($this, '_hw_awc_widget_change_params'));
        add_action('admin_enqueue_scripts',array($this, '_hw_awc_admin_enqueue_scripts'));   //admin enqueue script/css
        //add_action('admin_footer',array($this, '_hw_awc_admin_footer'));
        add_action('wp_footer', array($this, '_hw_awc_footer'));
        add_action('init', array($this, '_hw_awc_init'),2);

        add_action('in_widget_form', array($this, '_hw_awc_in_widget_form'),5,3);
        add_filter('widget_update_callback', array($this,'_hw_awc_in_widget_form_update'),5,3);
        add_filter('widget_display_callback', array($this, '_hw_awc_widget_custom_title'),10,3);
        //add_action('dynamic_sidebar_before', array($this, '_hw_awc_dynamic_sidebar_before'));
        #add_action('widgets_init', array($this, '_widgets_init'));
        add_action('hw_widgets_init', array($this, '_widgets_init'));   //merge this plugin to hoangweb module
        add_action('wp_register_sidebar_widget', array($this, '_wp_register_sidebar_widget'));
        add_action('wp_loaded', array(&$this, '_trigger_widget_checks'));
    }

    /**
     * @hook wp_loaded
     */
    public function _trigger_widget_checks() {
        add_filter( 'sidebars_widgets', array( &$this, '_sidebars_widgets' ) );
    }

    /**
     * @hook sidebars_widgets
     */
    public function _sidebars_widgets($sidebars) {
        return apply_filters('hw_sidebars_widgets', $sidebars);
    }
    /**
     * when load each widget in sidebars
     * @wp_hook action wp_register_sidebar_widget
     * @param $widget Widget instance
     */
    public function _wp_register_sidebar_widget($widget) {//_print($widget);
        $wd = isset($widget['callback'][0])? $widget['callback'][0] : null;
        //setup features for certain widgets
        if($wd instanceof WP_Widget && is_active_widget(false,false,$wd->id_base) && class_exists('APF_WidgetFeatures')) {
            APF_WidgetFeatures::setup_features_widgets($wd);
        }
    }
    /**
     * register sidebars
     * @wp_hook action widgets_init
     */
    public function _widgets_init() {
        //default sidebar for other purpose
        register_sidebar( array(
            'name' => __( 'Lưu trữ' ),
            'id' => 'hw-sidebar-data',
            'description' => __( 'Chứa các widgets với mục đích sử dụng riêng' ),
            'before_widget' => '<div id="%1$s" style="" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<span>',
            'after_title' => '</span>',
        ) );
        $user_sidebars = hwawc_get_registers_sidebars();
        if(is_array($user_sidebars))
            foreach ($user_sidebars as $sidebar) {
                $sidebar['hw_can_delete'] = 1;  //mark user sidebar
                register_sidebar($sidebar);
            }

        if ( empty ( $GLOBALS['wp_widget_factory'] ) )
            return;

        if(empty($GLOBALS['wp_widget_factory']->widgets)) {
            $GLOBALS['wp_widget_factory']->widgets = array();
        }
    }
    /**
     * return the class instance
     * @return HW_AWC
     */
    static public function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param $css: css string
     * @param string $skin: by skin
     */
    public function add_inlineCSS($css, $skin = ''){
        if(!$css) return;
        if(!$skin) $this->css_content[] = $css;
        else $this->css_content[$skin] = $css;
    }
    public function get_all_css(){
        return join("\n",$this->css_content);
    }


    /**
     * load option grid posts (moved to widget-features/grid_posts)
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    /*private function do_widget_feature_grid_posts($t, $instance){
        //$id = $t->get_field_id('awc_enable_grid_posts');
        if(!isset($instance['awc_grid_posts_cols'])) $instance['awc_grid_posts_cols'] = 2;

        echo '<div class="awc-widget-feature-grid_posts"><fieldset><legend>Grid Posts</legend>';
        echo '<div ><input type="checkbox" name="'.$t->get_field_name('awc_enable_grid_posts').'" id="'.$t->get_field_id('awc_enable_grid_posts').'" '.esc_attr(isset($instance['awc_enable_grid_posts']) && $instance['awc_enable_grid_posts']? 'checked="checked"':'').'/>';
        echo '<label for="'.$t->get_field_id('awc_enable_grid_posts').'"><strong>Hiển thị posts dạng grid</strong></label></div>';
        //grids column
        echo '<div><label for="'.$t->get_field_id('awc_grid_posts_cols').'"><strong>Số cột posts grid:</strong></label>';
        echo '<input size="5" type="text" name="'.$t->get_field_name('awc_grid_posts_cols').'" id="'.$t->get_field_id('awc_grid_posts_cols').'" value="'.$instance['awc_grid_posts_cols'].'"/></div>';
        echo '</fieldset></div>';

    }*/

    /**
     * valid color hex string
     * @param $color
     * @return string
     */
    public static function valid_hex_color($color){
        if(preg_match('/^#[a-f0-9]{6}$/i', $color)) //hex color is valid
        {
            //Verified hex color
        }

        //Check for a hex color string without hash 'c1c2b4'
        else if(preg_match('/^[a-f0-9]{6}$/i', $color)) //hex color is valid
        {
            $color = '#' . $color;
        }

        return $color;
    }
    /**
     * this snipet from WP Core (wp-includes/widgets.php) - since version 4.0
     * Substitute HTML id and class attributes into before_widget
     * @param string $widget_id  given widget id
     * @Param string|array $sidebar  sidebar id or params
     * @param array $override
     * @return string of classname
     */
    public static function format_widget_sidebar_params($widget_id, $sidebar = null, $override = array()) {
        global $wp_registered_widgets, $wp_registered_sidebars;
        //validate
        if(!is_array($override)) $override = array();

        //from wp core
        if(! isset($override['classname']) && isset($wp_registered_widgets[$widget_id])) {
            $classname_ = '';
            foreach ( (array) $wp_registered_widgets[$widget_id]['classname'] as $cn ) {
                if ( is_string($cn) )
                    $classname_ .= '_' . $cn;
                elseif ( is_object($cn) )
                    $classname_ .= '_' . get_class($cn);
            }
            $classname_ = ltrim($classname_, '_');
        }

        //override values
        elseif(isset($override['classname'])) $classname_ = $override['classname'];

        //return $classname_;
        //get sidebar setting
        if(is_string($sidebar) && isset($wp_registered_sidebars[$sidebar]) ) {
            $sidebar = $wp_registered_sidebars[$sidebar];
        }
        //override sidebar params
        if(is_array($sidebar) && isset($override['sidebar_params']) && is_array($override['sidebar_params'])) {
            $sidebar = array_merge($sidebar, $override['sidebar_params']);
        }
        if(is_array($sidebar) && isset($sidebar['before_widget'])) {
            if(!isset($classname_)) $classname_ = '';
            if(!empty($widget_id)) {
                $id[] = $widget_id;
            }
            else {
                $a = preg_split('|[\s]+|', $classname_);
                $id[] = reset($a );
            }

            if(isset($override['widget_id']) ) $id[] = ($override['widget_id']);

            $sidebar['before_widget'] = sprintf($sidebar['before_widget'], implode('-', $id), $classname_);
            return $sidebar;
        }
        return $classname_;
    }

    /**
     * apply sidebar skin for dynamic
     * @param $sidebar sidebar id
     * @param $skin sidebar skin style
     * @param $override_params arguments
     * @param $widget_id specific  widget id
     */
    public static function apply_sidebar_skin($sidebar, $skin, $override_params = array(), $widget_id = null) {
        global $wp_registered_sidebars;
        global $wp_registered_widgets;

        //valid
        if(!isset($wp_registered_sidebars[$sidebar])) return;

        $sidebar_params = $wp_registered_sidebars[$sidebar];    //sidebar params

        //get change default sidebar skin, here we create 4 holder: sidebar_default, skin1,skin2,skin3
        $skin_data = HW_AWC_Sidebar_Settings::get_sidebar_setting($skin,$sidebar);

        //valid widget id
        if(empty($widget_id) && !isset($wp_registered_widgets[$widget_id])) {
            list($widget_id, $t) = each($wp_registered_widgets); //get first widget as demo
        }

        //change sidebar params from skin
        HW_SKIN::apply_skin_data($skin_data,  array(__CLASS__, '_hw_skin_resume_skin_data') ,array(
            'sidebar' => $sidebar,
            'sidebar_widget_skin' => $skin,
            'sidebar_params' => &$sidebar_params
        ));

        $params = array(
            'classname' => $widget_id . ' hw-awc-override',
            'widget_id' => $widget_id
        );
        $params = array_merge($params, $override_params);
        //format sidebar variables
        $sidebar_params = HW_AWC::format_widget_sidebar_params($widget_id, $sidebar_params, $params);

        return $sidebar_params;
    }
    /**
     * HW_SKIN::apply_skin_data callback
     * @param $args
     */
    public static function _hw_skin_resume_skin_data($args) {
        extract($args);
        global $wp_registered_sidebars;
        if(isset($sidebar) && isset($skin)) $wp_registered_sidebars[$sidebar]['skin'] = $skin;     //bring skin object into params
        /**
         * override sidebar param from active skin
         */
        $sidebar_params = &$args['sidebar_params'];
        if(isset($theme['params']) && is_array($theme['params'])){
            $sidebar_params = array_merge($sidebar_params,$theme['params']);
        }
    }
    /**
     * put stylesheet & js together on admin head page
     */

    public function _hw_awc_admin_enqueue_scripts(){
        global $wp_customize;
        //get working page
        if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen(array('widgets','hw_sidebar_widgets_settings'))
            || !empty($wp_customize)    //or current of customize.php page
        ){
            wp_enqueue_style('hw-awc-style', HW_AWC_URL.('/style.css'));
            wp_enqueue_script('hw-awc-script', HW_AWC_URL.('/js/hw-awc-admin-js.js'),array('jquery'));
            #wp_enqueue_script('jscolor', HW_AWC_URL.('/js/jscolor/jscolor.js'));    //jscolor lib
            HW_Libraries::enqueue_jquery_libs('colors/jscolor');

            wp_localize_script('hw-awc-script', 'HW_AWC', array()); //create js object for this module

            //load media upload box in admin
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');

        }
        /*if(HW_HOANGWEB::is_current_screen('widgets')){        //wp link popup moved to new widget feature

            // need these styles
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_style('editor.min.css', includes_url('css/editor.min.css'), null);
        }*/
    }


    /**
     * collect inline content in footer section
     */

    public function _hw_awc_footer(){
        $css = HW_AWC::getInstance()->get_all_css();
        //print inline css
        if($css) echo "<style>{$css}</style>";

    }
    /**
     * valid sidebar name
     */

    public function _hw_awc_init(){

        HW_AWC::getInstance();  //initial HW_AWC object

        global $wp_registered_sidebars;
        //auto fix invalid sidebar name
        foreach($wp_registered_sidebars as $name => $param){
            $new_sidebar = $name;   //save sidebar id
            /**
             * valid sidebar
             */
            $wp_registered_sidebars[$name]['name'] .= '(ID:'.$param['id'].')';
            if(!HW_AWC_Sidebar_Settings::check_valid_sidebar_name($name)){
                $wp_registered_sidebars[$name]['old_id'] = $wp_registered_sidebars[$name]['id'];    //save old sidebar id
                $wp_registered_sidebars[$name]['description'] = "Thông báo: Bạn không thể sử dùng Sidebar này do bị lỗi ID quy định bởi hoangweb. Tên sidebar không chứa ký tự đặc biệt và hơn một -, được thay bằng ký tự _";
            }
            if(HW_AWC_Sidebar_Settings::get_sidebar_setting('autofix_sidebar_name',$name)
                && !HW_AWC_Sidebar_Settings::check_valid_sidebar_name($name))
            {
                $new_sidebar = HW_AWC_Sidebar_Settings::valid_sidebar_name($name);
                //register new sidebar directly instead of using function register_sidebar
                $wp_registered_sidebars[$new_sidebar] = $param;   //new sidebar name
                $wp_registered_sidebars[$new_sidebar]['id'] = $new_sidebar; //assign new sidebar id
                $wp_registered_sidebars[$new_sidebar]['old_id'] = $param['id']; //or $name
                $wp_registered_sidebars[$new_sidebar]['name'] .= '(đã sửa)';
                $wp_registered_sidebars[$new_sidebar]['description'] .= PHP_EOL."\n'id sidebar này tạo bởi hàm register_sidebar không hợp lệ, do vậy sidebar này có tên ID mới là: ({$new_sidebar})'";
                //unset($wp_registered_sidebars[$name]);  //don't remove invalid sidebar because wp theme will still use it

            }
            /**
             * apply skin for all widgets in the sidebar
             */
            if(HW_AWC_Sidebar_Settings::get_sidebar_setting('enable_override_sidebar',$name)){  //because still save settings for old sidebar name

                $skin_data = HW_AWC_Sidebar_Settings::get_sidebar_setting('skin_default',$name);
                if(isset($skin_data['hash_skin'])) $hash_skin = $skin_data['hash_skin'];
                if(isset($skin_data['hwskin_config'])) $skin_config = $skin_data['hwskin_config'];

                if(isset($skin_config) && $skin_config && isset($hash_skin) && class_exists('HW_SKIN')){
                    $skin = HW_SKIN::resume_skin($skin_config); //resume HW_SKIN with given config
                    if($skin) $file = $skin->get_skin_file($hash_skin);
                    if(isset($file) && file_exists($file)) {
                        $theme = array();   //valid
                        $css_str = '';
                        //get colors from parent
                        $color_title = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgcolor_title',$name);
                        $color_box = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgcolor_box',$name);
                        $img_title = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgimg_title',$name);
                        $img_box = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgimg_box',$name);

                        $hwawc = HW_AWC::getInstance();

                        include_once($file);

                        $wp_registered_sidebars[$new_sidebar]['skin'] = $skin;  //reference skin object

                        //override sidebar param
                        if(isset($theme['params']) && is_array($theme['params'])){
                            $wp_registered_sidebars[$new_sidebar] = array_merge($wp_registered_sidebars[$new_sidebar],$theme['params']);
                        }
                        //enqueue css & js -> depricated
                        /*if(!isset($theme['styles'])) $theme['styles'] = array();
                        if(!isset($theme['scripts'])) $theme['scripts'] = array();

                        if(count($theme['styles']) || count($theme['scripts'])) {
                            $skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                        }*/
                        //add inline content to footer
                        if(isset($theme['css_title_selector']) ) {
                            if($color_title) $css_str .= $theme['css_title_selector'].'{background-color:'.$color_title.' !important;}';
                            if($img_title) $css_str .= $theme['css_title_selector'].'{background-image:url('.$img_title.') !important;}';
                        }
                        if(isset($theme['css_content_selector']) ) {
                            if($color_box) $css_str .= $theme['css_content_selector'].'{background-color:'.$color_box.' !important;}';
                            if($img_box) $css_str .= $theme['css_content_selector'].'{background-image:url('.$img_box.') !important;}';
                        }
                        $hwawc->add_inlineCSS($css_str, $hash_skin);

                    }
                }
            }

        }

    }

    /**
     * @param $id
     */
    public function _hw_awc_dynamic_sidebar_before($id){

    }

    /**
     * extra fields to any widget
     * @param object $t: widget class object
     * @param $return
     * @param array $instance: widget instance
     */

    public function _hw_awc_in_widget_form($t,$return,$instance){
        /*begin custom sidebar format*/
        static $sw_list;
        if(!$sw_list) $sw_list = get_option('sidebars_widgets');    //get all widgets

        global $wp_registered_sidebars;
        foreach($sw_list as $sidebar=>$widgets){
            //get sidebar where widget nested on it
            if(is_array($widgets) && in_array($t->id,$widgets)){  //make sure you ignore the key 'array_version'
                $found_sidebar = true;
                break;
            }
        }


        if($sidebar == 'array_version'
            || !isset($found_sidebar)
            || !isset($wp_registered_sidebars[$sidebar])
        ) {
            echo '<div style="background:#fafafa;border:1px solid #dadada;padding:5px;"><p>Nhấn nút Lưu để hiển thị công cụ tùy chỉnh widget này hoặc nếu không thấy thì xóa widget và thêm lại.</p></div>';
            return;
        } //first check
        if(isset($wp_registered_sidebars[$sidebar]['old_id'])){
            $old_sidebar = $wp_registered_sidebars[$sidebar]['old_id'];
        }else{
            $old_sidebar = $sidebar;
        }
        if(!HW_AWC_Sidebar_Settings::check_valid_sidebar_name($old_sidebar)){
            echo "<div class='hwawc-message'>Do Sidebar cũ có ID ({$old_sidebar}) không hợp lệ, nên tạo mới sidebar với tên: ({$sidebar})";
            echo '<br/>Hiển thị sidebar:<code>dynamic_sidebar("'.$sidebar.'")</code>';
            echo '</div>';
            // return;  //fixed from callback '_hw_awc_init'
        }

        $params = $wp_registered_sidebars[$sidebar];    //get sidebar param where widget nested on it

        //create feature tog
        if(class_exists('HW_ButtonToggle_widget')) {
            $btn_tog = new HW_ButtonToggle_widget($t,$instance);
        }
        //instance HW_SKIN for widget box title
        $skin = new HW_SKIN($t,HW_AWC_PATH,'hw_awc_skins','hwawc-skin.php','skins');
        $skin->plugin_url = HW_AWC_URL;
        $skin->external_callback = false;
        $skin->init();  //seem no longer use

        ?>
        <?php if(isset($btn_tog)) $btn_tog->set_button_toggle_start_wrapper('Tùy chỉnh widget');

        ?>
        <div style="background:#fafafa;border:1px solid #dadada;padding:5px;">
            <!-- show widget ID on each widget in admin. -->
            <p>
            <div id="<?php echo $t->get_field_id('show_widget_id'); ?>" style="padding:5px;background:pink;border:1px solid red;" name="<?php echo $t->get_field_name('show_widget_id'); ?>" >
                ID: <strong><?php echo $t->id; ?></strong><br/>
                <?php
                if(is_plugin_active('amr-shortcode-any-widget/amr-shortcode-any-widget.php')){
                    echo 'Hiển thị nội dung widget này bởi shortcode:<br/>';
                    echo '<code>[do_widget id='.$t->id.']</code>';
                    echo '<a href="'.HW_AWC_URL.('/docs/amr-shortcode-any-widget.html').'" target="_blank">Hướng dẫn</a>';
                }
                else echo hw_install_plugin_link('amr-shortcode-any-widget','Nhúng widget này bằng shortcode');

                //display widget content with feature "shortcode_params"
                if(!HW_AWC_WidgetFeatures::check_widget_feature($t, 'shortcode_params') && class_exists('HW_HOANGWEB_Settings')) {
                    echo '<p>Kích hoạt feature "<a target="_blank" href="' .admin_url('options-general.php?page='. HW_HOANGWEB_Settings::HW_SETTINGS_PAGE). '">Tạo shortcode widget</a>" để hiển thị nội dung của widget này.</p>';
                }
                ?>
            </div>
            </p>
            <!-- widget features -->
            <?php
            $this->load_widget_features($t,$instance);
            ?>
            <p>

            </p>
            <hr/>
            <!-- sidebar custom after_title & before_title -->
            <?php

            if(isset($params['before_title'])):
                ?>

                <p>
                    <label for="<?php echo $t->get_field_id('awc_clear_widget_title')?>"><strong><?php _e('Xóa tiêu đề widget')?></strong></label><br/>
                    <input type="checkbox" name="<?php echo $t->get_field_name('awc_clear_widget_title'); ?>" id="<?php echo $t->get_field_id('awc_clear_widget_title');?>" <?php checked(isset($instance['awc_clear_widget_title']) && $instance['awc_clear_widget_title'] ? 1: 0)?>/><?php _e('Xóa before_title & after_title')?>
                    <br/>
                    <span style="font-size:12px">Nếu xóa tiêu đề (xóa before_title & after_title) thì check vào đây. <i>(Tùy chọn này sẽ xóa thẻ HTML có trong before_title & after_title và cả before_widget,after_widget nếu cần thiết để có thể hiển thị tốt widget mà không có title.)</i></span>
                    <br/>

                </p><hr/>

                <p>
                    <label><strong><?php _e('Sửa before_title')?></strong></label><br/>
                    <em>Hiện tại: (<font color="blue"><?php echo htmlentities($params['before_title'])?></font>)</em><br/>
                    <em><?php if(!preg_match('#%\d\$s#', $params['before_title'])) echo '<font color="red">Cảnh báo: before_title chưa sẵn sàng.</font>';?></em><br/>
                    <input <?php ?> type="text" name="<?php echo $t->get_field_name('format_before_title'); ?>" id="<?php echo $t->get_field_id('format_before_title');?>" value="<?php echo isset($instance['format_before_title'])? $instance['format_before_title']: ''?>"><br/>
                    <span style="font-size:12px"><i>(mỗi ID or chuỗi class ngăn cách bởi '|')</i></span>
                    <br/>
                    <span><em>ie: before_title=>"<?php echo htmlentities('<h2 id="%1$s" class="%2$s" style="%3$s">')?>"</em></span>
                </p><hr/>
            <?php endif;?>
            <p>
                <label><strong><?php _e('Sửa after_title')?></strong></label><br/>
                <em>Hiện tại: (<font color="blue"><?php echo htmlentities($params['after_title'])?></font>)</em><br/>
                <em><?php if(!preg_match('#%\d\$s#', $params['after_title'])) echo '<font color="red">Cảnh báo: after_title chưa sẵn sàng.</font>';?></em><br/>
                <input <?php ?> type="text" name="<?php echo $t->get_field_name('format_after_title'); ?>" id="<?php echo $t->get_field_id('format_after_title');?>" value="<?php echo isset($instance['format_after_title'])? $instance['format_after_title']: ''?>"><br/>
                <span style="font-size:12px"><i>(mỗi ID or chuỗi class ngăn cách bởi '|')</i></span>
                <br/>
                <span><em>ie: after_title=>"<?php echo htmlentities('</h2><div id="%1$s" class="%2$s" style="%3$s">')?>"</em></span>
            </p><hr/>
            <p>
                <label><strong><?php _e('Sửa before_widget')?></strong></label><br/>
                <em>Thêm class theo thứ tự bằng cách chèn tag (*1,*2,..) vào tham số before_widget: <br/>(Hiện tại:<font color="blue"><?php echo htmlentities($params['before_widget'])?></font>)</em><br/>
                <em><?php if(!preg_match('#\*\d+#', $params['before_widget'])) echo '<font color="red">Cảnh báo: before_widget chưa sẵn sàng.</font>';?></em>
                <input type="text" name="<?php echo $t->get_field_name('preclasses_before_widget'); ?>" id="<?php echo $t->get_field_id('preclasses_before_widget');?>" value="<?php echo isset($instance['preclasses_before_widget'])? $instance['preclasses_before_widget']: ''?>"><br/>
                <span style="font-size:12px"><i>(mỗi chuỗi class tương ứng với *1|*2.. ngăn cách bởi '|'. VD: class1 class2|class3 class4 class5)</i></span>
            </p>
            <hr/>
            <p>
                <label for="<?php echo $t->get_field_id('widget_skin')?>"><strong><?php _e('Giao diện widget')?></strong></label>
            <div>
                <select name="<?php echo $t->get_field_name('widget_skin')?>" id="<?php echo $t->get_field_id('widget_skin')?>">
                    <?php foreach(HW_AWC_Sidebar_Settings::available_widget_skins() as $name=>$field){?>
                        <option <?php selected((isset($instance['widget_skin']) && $name == $instance['widget_skin'])? 1:0)?> value="<?php echo $name?>"><?php echo isset($field['title'])? $field['title'] : $name?></option>
                    <?php }?>
                </select><br/>
                <span>Quản lý skins, nhấn <a target="_blank" href="<?php echo HW_AWC_Sidebar_Settings::get_edit_sidebar_setting_page_link($sidebar)?>">vào đây</a>.</span>
            </div>
            </p>
            <p>
                <strong>Mầu sắc</strong><br/>
                <em>Lưu ý: TH nếu xóa mầu và khi load lại trang cần xóa lại vì mầu khởi đầu là #FFFFFF.<br/> Nếu không xuất hiện hộp chọn mầu thì tự điền mã mầu và lưu lại để khởi tạo lại trình chọn mã mầu.</em>
            </p>
            <p>
                <input type="text" size="5" class="color {hash:true}" name="<?php echo $t->get_field_name('bgcolor_widget')?>" id="<?php echo $t->get_field_id('bgcolor_widget')?>" value="<?php echo isset($instance['bgcolor_widget'])? $instance['bgcolor_widget'] : ''?>"/>
                <label for="<?php echo $t->get_field_id('bgcolor_widget')?>">Mầu nền widget</label>
                <a class="clear-jscolor button" href="javascript:void(0)">Xóa</a>
            </p>
            <p>
                <input type="text" size="5" class="color {hash:true}" name="<?php echo $t->get_field_name('bgcolor_title')?>" id="<?php echo $t->get_field_id('bgcolor_title')?>" value="<?php echo isset($instance['bgcolor_title'])? $instance['bgcolor_title'] : ''?>"/>
                <label for="<?php echo $t->get_field_id('bgcolor_title')?>">Mầu nền tiêu đề</label>
                <a class="clear-jscolor button" href="javascript:void(0)" onclick="">Xóa</a>
            </p>
            <p>
                <input type="text" size="5" class="color {hash:true}" name="<?php echo $t->get_field_name('bgcolor_box')?>" id="<?php echo $t->get_field_id('bgcolor_box')?>" value="<?php echo isset($instance['bgcolor_box'])? $instance['bgcolor_box'] : ''?>"/>
                <label for="<?php echo $t->get_field_id('bgcolor_box')?>">Mầu nền nội dung</label>
                <a class="clear-jscolor button" href="javascript:void(0)">Xóa</a>
            </p>

            <!-- bg title -->
            <p>
                <label><strong><?php _e('Ảnh nền cho tiêu đề widget')?></strong></label>
            <div>
                <?php

                if(strpos($params['before_title'],'{css_title}') === false){
                    echo '<font color="red">Warning: không tìm thấy {css_title} tag trong tham số <em>before_title</em>. Để sử dụng trường này bạn cần thêm tag này vào tham số <em>before_title</em> cho sidebar hiện tại.</font>';
                }
                ?>
                <?php if(isset($instance['bgimg_title'])){?>
                    <img class="user-preview-image" style="max-width:100%;" src="<?php echo $instance['bgimg_title']; ?>"><!-- display image -->
                <?php }?>
                <input type="hidden" name="<?php echo $t->get_field_name('bgimg_title'); ?>" id="<?php echo $t->get_field_id('bgimg_title');?>" value="<?php echo isset($instance['bgimg_title'])? $instance['bgimg_title']: ''?>" class="regular-text" /><!-- show image url -->
                <input type='button' class="button-primary" value="Upload" id="<?php echo $t->get_field_id('hw_awc_uploadimage')?>"/><br /><!-- upload button -->
                <script type="text/javascript">
                    jQuery(function( $ ) {
                        if(typeof hw_awc_btn_upload_image == 'function') {
                            hw_awc_btn_upload_image($( '#<?php echo $t->get_field_id('hw_awc_uploadimage')?>' ), $( '#<?php echo $t->get_field_id('bgimg_title');?>' ));
                        }

                    });
                </script>
                <p>

                    <input type="checkbox" name="<?php echo $t->get_field_name('bgimg_title_css')?>" id="<?php echo $t->get_field_id('bgimg_title_css')?>" onlick="this.value = this.checked" <?php  checked(isset($instance['bgimg_title_css'])? 1:0)?>/>
                    <label for="<?php echo $t->get_field_id('bgimg_title_css')?>"><?php _e('Hiển thị ảnh nền cho tiêu đề widget')?></label>
                </p>
            </div>
            </p>
            <!-- bg box -->
            <p>
                <label for="<?php echo $t->get_field_id('hw_awc_upload_bgimg_box')?>"><strong><?php _e('Ảnh nền cho nội dung widget')?></strong></label><br/>
                <?php
                if(strpos($params['after_title'],'{css_box}') === false
                    && strpos($params['before_widget'],'{css_box}') === false)
                {
                    echo '<div><font color="red">Warning: không tìm thấy {css_box} tag trong tham số <em>after_title</em> hoặc <em>before_widget</em>. Để sử dụng trường này bạn cần thêm tag này cho sidebar hiện tại.</font></div>';
                }
                ?>
                <?php if(isset($instance['bgimg_box'])){?>
                    <img class="user-preview-image" style="max-width:100%;" src="<?php echo $instance['bgimg_box']; ?>"><!-- display image -->
                <?php }?>
                <input type="hidden" name="<?php echo $t->get_field_name('bgimg_box'); ?>" id="<?php echo $t->get_field_id('bgimg_box');?>"
                       value="<?php echo isset($instance['bgimg_box'])? $instance['bgimg_box']: ''?>" class="regular-text" /><!-- show image url -->
                <input type='button' class="button-primary" value="Upload" id="<?php echo $t->get_field_id('hw_awc_upload_bgimg_box')?>"/><br /><!-- upload button -->
            </p>
            <script type="text/javascript">
                jQuery(function( $ ) {
                    if(typeof hw_awc_btn_upload_image == 'function') {
                        hw_awc_btn_upload_image($( '#<?php echo $t->get_field_id('hw_awc_upload_bgimg_box')?>' ),$('#<?php echo $t->get_field_id('bgimg_box')?>'));
                    }

                });
                jQuery(function($){
                    //empty color input
                    var empty_color_input = function(obj){
                        $(obj).attr('value','').css({background:'#fff'});
                        $(obj).empty();
                        //remove color from input
                        //HW_AWC.jscolor_set(obj,'');//wrong it will reset to white color
                    };
                    //init jscolor when ajax response for all input field has 'color' class
                    $('input.color').each(function(i,obj){
                        var color = $(obj).val(),
                            clear_btn = $($(obj).next().next());

                        $(obj).data('clear_btn', clear_btn);
                        //bind clear color button
                        if(!$(obj).data('clear_color_btn')){
                            $(obj).data({
                                clear_color_btn:function(){
                                    empty_color_input(obj);
                                    //remove color from input
                                    //HW_AWC.jscolor_set(obj,'');//wrong it will reset to white color
                                }
                            });
                        }
                        clear_btn.bind('click',$(obj).data('clear_color_btn'));
                        //update color
                        if(color && $(obj).attr('value')){
                            HW_AWC.jscolor_set(obj,color);  //resume color in input tag
                        }
                        else {
                            setTimeout(function(){
                                empty_color_input(obj); //clear default white color
                                $(obj).data('clear_btn').trigger('click');
                            },1000);
                        }

                    });
                });

            </script>
        </div>
        <?php
        if(isset($btn_tog)) $btn_tog->set_button_toggle_end_wrapper(); //close feature tog
    }
    /**
     * Callback function for options update (priorität 5, 3 parameters)
     * @param array $instance: current widget instance data
     * @param array $new_instance: new widget instance data
     * @param array $old_instance: old widget instance data
     */

    public function _hw_awc_in_widget_form_update($instance, $new_instance, $old_instance){

        //please: see update method in your widget to confirm that all fields automatically update, otherwise all checkbox fields bellow will not saving
        if(!empty($new_instance['format_before_title'])) {
            $instance['format_before_title'] = ($new_instance['format_before_title']);  //format before_title
        }
        if(!empty($new_instance['format_after_title'])) {
            $instance['format_after_title'] = $new_instance['format_after_title'];  //format after title
        }
        if(!empty($new_instance['preclasses_before_widget'])) {
            $instance['preclasses_before_widget'] = $new_instance['preclasses_before_widget'];  //format before_widget
        }
        //css widget title
        if(!empty($new_instance['bgimg_title'])) {
            $instance['bgimg_title'] = $new_instance['bgimg_title'];  //use widget image
        }
        if(!empty($new_instance['bgimg_title_css'])) {
            $instance['bgimg_title_css'] = $new_instance['bgimg_title_css'];    //make bg widget title
        }
        //css widget content
        if(!empty($new_instance['bgimg_box'])) {
            $instance['bgimg_box'] = $new_instance['bgimg_box'];    //make bg widget content
        }
        //clear widget title
        if(!empty($new_instance['awc_clear_widget_title'])) {
            $instance['awc_clear_widget_title'] = ($new_instance['awc_clear_widget_title'] == 'on')? 1:0;  //clear widget title
        }
        else $instance['awc_clear_widget_title'] = 0;
        if(!empty($new_instance['widget_skin'])) {
            $instance['widget_skin'] = $new_instance['widget_skin'];  // widget skin
        }

        if(isset($new_instance['bgcolor_widget'])) {
            $instance['bgcolor_widget'] = self::valid_hex_color($new_instance['bgcolor_widget']);  //background color widget
        }
        if(isset($new_instance['bgcolor_title'])) {
            $instance['bgcolor_title'] = self::valid_hex_color($new_instance['bgcolor_title']);  //background color widget title
        }
        if(isset($new_instance['bgcolor_box'])) {
            $instance['bgcolor_box'] = self::valid_hex_color($new_instance['bgcolor_box']);  // background color widget content
        }
        //widget feature update fields
        $instance = $this->valid_widget_feature($instance,$new_instance,$old_instance);
        //some time need to remove heavy data for reseting db because try many time durring got error
//if(isset($instance['hw_widopt_setting'])) unset($instance['hw_widopt_setting']);
        $instance = apply_filters('hwawc_widget_form_update', $instance,$new_instance,$old_instance);#__save_session('hwawc_widget_form_update', $instance);
        return $instance;
    }

    /**
     * format sidebar params
     * @param string $sidebar_id  sidebar id
     * @param array $params  sidebar params
     * @param array $current_widget  widget custom sidebar params
     */
    public static function format_sidebar_params($sidebar_id, $params, $current_widget) {
        //parse settings
        if(isset($current_widget['bgcolor_widget'])) $color_widget = self::valid_hex_color($current_widget['bgcolor_widget']);

        //inline css
        if(strpos($params['before_title'], '{css_title}') !==false
            || strpos($params['after_title'], '{css_box}') !==false
            || strpos($params['before_widget'], '{css_box}') !==false)
        {
            if(isset($current_widget['bgcolor_title'])) $color_title = self::valid_hex_color($current_widget['bgcolor_title']);
            if(isset($current_widget['bgimg_title'])) $img_title = ($current_widget['bgimg_title']);
            if(isset($current_widget['bgcolor_box'])) $color_box = self::valid_hex_color($current_widget['bgcolor_box']);
            if(isset($current_widget['bgimg_box'])) $img_box = ($current_widget['bgimg_box']);

            //if not found, get schema colors from parent
            if(!isset($color_title)) $color_title = self::valid_hex_color(HW_AWC_Sidebar_Settings::get_sidebar_setting('bgcolor_title',$sidebar_id));
            if(!isset($color_box)) $color_box = self::valid_hex_color(HW_AWC_Sidebar_Settings::get_sidebar_setting('bgcolor_box',$sidebar_id));
            if(!isset($img_title)) $img_title = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgimg_title',$sidebar_id);
            if(!isset($img_box)) $img_box = HW_AWC_Sidebar_Settings::get_sidebar_setting('bgimg_box',$sidebar_id);  //not use in the context

            /**
             * inline content bring to footer such as css,js,html
             */
            //css title
            $css_title = ''; $css_box = '';
            if($color_title && $color_title != 'transparent') $css_title .= ";background-color:{$color_title} !important;";
            if($img_title && isset($current_widget['bgimg_title_css'])) {
                $css_title .= "background-image:url($img_title) !important;";
            }
            //css box
            if($color_box && $color_box != 'transparent') $css_box .= ";background-color:{$color_box} !important;";
            if($img_box) $css_box .= "background-image:url($img_box) !important;";

            $params['before_title'] = str_replace('{css_title}', $css_title, $params['before_title']);

            $params['after_title'] = str_replace('{css_box}', $css_box, $params['after_title']);
            $params['before_widget'] = str_replace('{css_box}', $css_box, $params['before_widget']);

        }
        /*custom format before_title & after_title*/
        if(isset($current_widget['format_before_title'])){
            $format = explode('|', $current_widget['format_before_title']);
            $params['before_title'] = vsprintf($params['before_title'],$format);
        }
        if(isset($current_widget['format_after_title'])){
            $format = explode('|', $current_widget['format_after_title']);
            $params['after_title'] = vsprintf($params['after_title'],$format);
        }
        /*end*/
        /*pre-classes before_widget*/
        if (isset($current_widget['preclasses_before_widget'])){
            $classes = $current_widget['preclasses_before_widget'];
            foreach(explode('|',$classes) as $i=>$c){
                $params['before_widget'] = str_replace('*'.($i+1),$c, $params['before_widget']);
            }
            #$params['before_widget'] = preg_replace('/class="/', 'class="'.$classes.' ',  $params['before_widget'], 1);
        }
        /*widget bg title*/
        /*if(strpos($params[0]['before_title'], '{widget_bg}') !==false){
            if(isset($current_widget['bgimg_title']) && isset($current_widget['bgimg_title_css']) ){
                $bg = $current_widget['bgimg_title'];
                $params[0]['before_title'] = str_replace('{widget_bg}',"style=\"background:url($bg)\"",$params[0]['before_title']);
            }
            else $params[0]['before_title'] = str_replace('{widget_bg}','',$params[0]['before_title']);
        }*/
        //make widget color
        if(isset($color_widget) && $color_widget) {
            if(preg_match('#style(\s+)?=#', $params['before_widget'])) { //exists style attr
                $params['before_widget'] = preg_replace('#(style(\s+)?=(\s+)?("|\'))#',"$1background-color:{$color_widget} !important;",$params['before_widget']);
            }
            else{   //assign to class attribute
                $params['before_widget'] = preg_replace('#(class(\s+)?=(\s+)?("|\'))#',"style='background-color:{$color_widget} !important;' $1", $params['before_widget']);
            }
        }

        return $params;
    }
    /**
     * check you theme exists function bind to this hook 'dynamic_sidebar_params' before use this.
     * @param array $params: sidebar widgets params
     */
    public function _hw_awc_widget_change_params($params) {
        global $wp_registered_widgets;
        global $wp_registered_sidebars;

        $this_id = $params[0]['id']; // Get the id for the current sidebar we're processing
        $widget_id = $params[0]['widget_id'];
        $widget_obj = $wp_registered_widgets[$widget_id];
        $widget_opt = get_option($widget_obj['callback'][0]->option_name);
        $widget_num = $widget_obj['params'][0]['number'];
        $arr_registered_widgets = wp_get_sidebars_widgets(); // Get an array of ALL registered widgets

        if(!isset($arr_registered_widgets[$this_id]) || !is_array($arr_registered_widgets[$this_id])) { // Check if the current sidebar has no widgets
            return $params; // No widgets in this sidebar... bail early.
        }
        if(!is_active_sidebar($this_id)) return $params;    //this sidebar should be actived

        $current_widget = $widget_opt[$widget_num]; //get current widget
        //check if this sidebar have any modified from user setting
        $enable_override_sidebar = HW_AWC_Sidebar_Settings::get_sidebar_setting('enable_override_sidebar',$this_id);
        //parse settings
        if(isset($current_widget['bgcolor_widget'])) $color_widget = self::valid_hex_color($current_widget['bgcolor_widget']);

        //callback
        if(!is_admin()) {
            $wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
            $wp_registered_widgets[ $widget_id ]['callback'] = array($this, '_hw_custom_widget_callback_function');
        }

        /**
         * apply skin for specific widget
         */
        if($enable_override_sidebar && isset($current_widget['widget_skin'])){
            //get widget skin
            $skin_data = HW_AWC_Sidebar_Settings::get_sidebar_setting($current_widget['widget_skin'],$this_id);

            if(isset($skin_data['hash_skin'])) $hash_skin = $skin_data['hash_skin'];
            if(isset($skin_data['hwskin_config'])) $skin_config = $skin_data['hwskin_config'];

            if(isset($skin_config) && $skin_config && isset($hash_skin)){
                $skin = HW_SKIN::resume_skin($skin_config); //resume HW_SKIN with given config
                if(method_exists($skin,'get_skin_file')) $file = $skin->get_skin_file($hash_skin);
                if(isset($file) && file_exists($file)) {


                    $theme = array();   //valid
                    include($file); //don't use include_one or require_once, and don't create function in skin file because will cause duplicate function

                    $wp_registered_sidebars[$this_id]['skin'] = $skin;     //bring skin object into params
                    /**
                     * override sidebar param
                     */
                    if(isset($theme['params']) && is_array($theme['params'])){
                        $params[0] = array_merge($params[0],$theme['params']);
                    }
                    /**
                     * this snipet from WP Core (wp-includes/widgets.php) - since version 4.0
                     * Substitute HTML id and class attributes into before_widget
                     */
                    $classname_ = self::format_widget_sidebar_params($widget_id);
                    $params[0]['before_widget'] = sprintf($params[0]['before_widget'], $widget_id, $classname_);
                    /**
                     * enqueue css & js -> depricated
                     */
                    /*if(!isset($theme['styles'])) $theme['styles'] = array();
                    if(!isset($theme['scripts'])) $theme['scripts'] = array();

                    if(count($theme['styles']) || count($theme['scripts'])) {
                        $skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                    }*/

                }
            }
        }
        $params[0] = /*$new_params = */self::format_sidebar_params($this_id, $params[0], $current_widget);
        //$params[0] = array_merge($params[0], $new_params);

        /*clear widget title*/
        if(isset($current_widget['awc_clear_widget_title']) && $current_widget['awc_clear_widget_title']){
            //clear before_title & after_title
            $params[0]['before_title'] = '';
            $params[0]['after_title'] = '';

            //valid HTML combine from before_widget + after_widget
            $tidyconfig = array(
                'indent' => false,
                'show-body-only' => true,
                'clean' => false,
                'output-xhtml' => true,
                'input-xml'=>true,
            );
            $sign_str = '_______';
            $html = $params[0]['before_widget'].$sign_str.$params[0]['after_widget'];
            $html = HW_String::tidy_cleaning($html,$tidyconfig,'body');  //hwawc_tidy_cleaning($html,$tidyconfig,'body');
            $t = explode($sign_str,$html);
            if(count($t) == 2){
                $params[0]['before_widget'] = $t[0];  //get new before widget
                $params[0]['after_widget'] = $t[1];  //get new after_widget param
            }

        }

        /**
         * show widgets with order number
         */
        if($enable_override_sidebar && HW_AWC_Sidebar_Settings::get_sidebar_setting('alphabe_widgets',$this_id)){
            global $hwawc_widget_num; // Global a counter array
            if(!$hwawc_widget_num) {// If the counter array doesn't exist, create it
                $hwawc_widget_num = array();
            }

            if(isset($hwawc_widget_num[$this_id])) { // See if the counter array has an entry for this sidebar
                $hwawc_widget_num[$this_id] ++;
            } else { // If not, create it starting with 1
                $hwawc_widget_num[$this_id] = 1;
            }
            $class_id = 'widget-'.$hwawc_widget_num[$this_id];
            $class = 'class="';
            // Add a widget number class for additional styling options
            //if(!$this->exists_class($class_id, $params[0]['before_widget'])) {
                $class .= $class_id . ' ';
            //}

            if($hwawc_widget_num[$this_id] == 1) { // If this is the first widget
                //if(!$this->exists_class('widget-first', $params[0]['before_widget']))
                    $class .= 'widget-first ';
            } elseif($hwawc_widget_num[$this_id] == count($arr_registered_widgets[$this_id])) { // If this is the last widget
                //if(!$this->exists_class('widget-last', $params[0]['before_widget']))
                    $class .= 'widget-last ';
            }
            $params[0]['before_widget'] = str_replace('class="', $class, $params[0]['before_widget']); // Insert our new classes into "before widget"

        }

        //finally
        if($enable_override_sidebar){
            //mark modify before_widget class by adding new class for this plugin to prevent conflict other css in your theme
            $params[0]['before_widget'] = str_replace('class="', 'class="hw-awc-override ', $params[0]['before_widget']);
        }

        return $params;
    }

    /**
     * get widget data
     * @param $widget_id
     */
    public static function get_widget_data($widget_id) {
        global $wp_registered_widgets;
        if(isset($wp_registered_widgets[$widget_id])) {
            $data = array('widget'=> null);
            $widget_obj = $wp_registered_widgets[$widget_id];
            $widget_num = $widget_obj['params'][0]['number'];

            //widget object
            if(!empty($widget_obj['original_callback'])) {
                $callback = $widget_obj['original_callback'];
            }
            elseif(!empty($widget_obj['callback'])){
                $callback = $widget_obj['callback'];

            }
            if(isset($callback)) {
                $data['widget'] = $callback[0];
                $widget_opt = get_option($callback[0]->option_name);
                $data['id_base'] = $callback[0]->id_base;

                $data['instance'] = $instance = $widget_opt[$widget_num]; //get current widget instance
                //from 'display widgets' plugin
                if ( isset( $instance['_multiwidget'] ) && $instance['_multiwidget'] ) {

                    if ( isset( $instance[ $widget_num ] ) ) {
                        $data['instance'] = $instance[ $widget_num ];
                        unset($widget_num);
                    }
                }
            }
            return $data;
        }

    }
    /**
     * custom widget callback
     */
    public function _hw_custom_widget_callback_function() {
        global $wp_registered_widgets;
        $original_callback_params = func_get_args();
        $widget_id = $original_callback_params[0]['widget_id'];
        $widget_obj = $wp_registered_widgets[$widget_id];

        $original_callback = $widget_obj['original_callback'];
        $wp_registered_widgets[$widget_id]['callback'] = $original_callback;
        //widget object
        $widget = $widget_obj['original_callback'][0];
        $widget_opt = get_option($widget_obj['original_callback'][0]->option_name);
        $widget_num = $widget_obj['params'][0]['number'];
        $instance = $widget_opt[$widget_num]; //get current widget instance

        $widget_id_base = $wp_registered_widgets[$widget_id]['callback'][0]->id_base;

        //do widget features
        #$features = array('fixed_widget');

        /*$feature = HW_AWC::get_widget_feature($widget, 'fancybox');
        if($feature && HW_AWC::check_widget_feature($widget, 'fancybox') && $feature->is_active($instance) ) {
            if(method_exists($feature, 'do_widget_feature_frontend')) {
                $feature->do_widget_feature_frontend($widget, $instance);
            }
        }*/
        $all_features = HW_AWC_WidgetFeatures::get_all_features();
        $widgets_fields = HW_AWC_WidgetFeatures::get_features_data();
        foreach ($all_features as $name => $text) {
            if(isset($widgets_fields[$name]) && is_array($widgets_fields[$name])) {
                foreach ($widgets_fields[$name] as $widg) {
                    if($widget == $widg['widget']
                        || ($widg['widget']->id_base == $widget->id_base && $widg['widget']->number == $widget->number)
                    ) {
                        $feature = $widg['class'];
                        $active = true;
                        if(method_exists($feature, 'is_active') ) { //check active widget feature
                            $active = $feature->is_active($instance);
                        }
                        if( $active && method_exists($feature, 'do_widget_feature_frontend')) {
                            $feature->do_widget_feature_frontend($widget, $instance);
                        }
                    }
                }
            }
        }
        if ( is_callable( $original_callback ) ) {

            ob_start();
            call_user_func_array( $original_callback, $original_callback_params );
            $widget_output = ob_get_clean();

            echo apply_filters( 'hw_widget_output', $widget_output, $widget_id_base, $widget_id );

        }
    }
    /**
     * check for exists class name in HTML element
     * @param $class: class name to check
     * @param string $ele: element with class attribute
     */
    private function exists_class($class,$ele = ''){
        preg_match_all('#class(\s+)?=(\s+)?(\'|")(.+)(\'|")#',$ele,$res);   //extract names of classes on element by HTML
        if(isset($res[4]) && count($res[4])) {
            foreach($res[4] as $classes){
                if(in_array(trim($class), preg_split('/[\s]+/', $classes))) {
                    return true;
                };
            }
        }
    }
    /**
     * modify widget content (ie: title) to show img tag
     * @param array $instance: widget instance
     * @param object $widget: widget class object
     * @param array $args: widget params
     */

    function _hw_awc_widget_custom_title($instance, $widget, $args){
        //On a single post.
        $title = get_the_title();
        if(isset($instance['bgimg_title']) && $instance['bgimg_title'] && !isset($instance['bgimg_title_css'])){	#if disabled bg css
            remove_filter( 'widget_title', 'esc_html' );	#this allow to display html code in widget title
            $instance['title'] = '<img class="'.$widget->id_base.'_bgimg_title_'.$widget->number.'" src="'.$instance['bgimg_title'].'" />';
        }
        return $instance;
    }

}