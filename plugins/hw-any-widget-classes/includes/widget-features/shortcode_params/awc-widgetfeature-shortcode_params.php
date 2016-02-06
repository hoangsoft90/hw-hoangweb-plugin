<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/07/2015
 * Time: 13:42
 */
if(class_exists('HW_AWC_WidgetFeature')) :
class AWC_WidgetFeature_shortcode_params extends HW_AWC_WidgetFeature{
    /**
     * widget config group
     */
    const WIDGET_CONFIG_GROUP = 'wf-shortcode';

    /**
     * load option grid posts
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        $this->widget_instance = $instance; //maybe update widget instance
        //$wf_name = $this->get_widget_feature_name('');
        //list registered sidebars
        $sidebars = hwawc_get_active_sidebars_select();
        $sidebar_skins = HW_AWC_Sidebar_Settings::available_widget_skins(true);     //sidebar skins

        //saved widget configs by specifying group
        $widgetconfig_groups = AWC_WidgetFeature_saveconfig::get_widgets_settings_select(' WHERE _group="' . self::WIDGET_CONFIG_GROUP. '"');

        //HW_UI_Component::build_select_tag($sidebars, '', '');

        echo '<div class="form-wf-shortcode-params" id="'. $this->get_field_id('wfshortcode_container').'"><fieldset><legend>Tạo shortcode widget</legend>';
        echo '<input type="hidden" class="widget_class" name="' .$this->get_field_name('widget_class'). '" id="' .$this->get_field_id('widget_class'). '" value="'. get_class($t) .'"/>';
        echo '<input type="hidden" class="widget_instance" name="'. $this->get_field_name('widget_instance') .'" id=" '.$this->get_field_id('widget_instance').' " value="'. base64_encode(serialize($instance)) .'"/>';

        //apply widget to sidebar
        echo '<p><label for="'. $this->get_field_id('sidebar') .'">Chọn Sidebar</label><br/>';
        echo '<select class="hw-select sidebar" name="'. $this->get_field_name('sidebar').'" id="'. $this->get_field_id('sidebar') .'">';
        foreach ($sidebars as $id => $name) {
            $selected = selected( $this->get_field_value('sidebar'), $id, false);
            printf('<option value="%s" %s>%s</option>', $id, $selected, $name);
        }
        echo '</select></p>';

        //choose sidebar skin
        echo '<p><label for="'. $this->get_field_id('sidebar_skin') .'">Chọn giao diện Sidebar</label><br/>';
        echo HW_UI_Component::build_select_tag($sidebar_skins, null, array(
            'name' => $this->get_field_name('sidebar_skin') ,
            'id' => $this->get_field_id('sidebar_skin'),
            'class' => 'sidebar_skin'
        ));
        echo '</select></p>';

        //widget config group
        echo '<p><label for="'. $this->get_field_id('config_group') .'">Chọn widget config</label><br/>';
        echo '<select class="config_group" name=" '.$this->get_field_name('config_group').' " id="' . $this->get_field_id('config_group') .'">';
        foreach($widgetconfig_groups as $id => $text) {
            $selected = selected( $this->get_field_value('config_group'), $id, false);
            printf('<option value="%s" %s>%s</option>', $id, $selected, $text);
        }
        echo '</select>';
        echo '<a href="javascript:void(0)" onclick="__awc_feature_shortcode_params.refresh_saved_widgetsconfig(this,\'' .$this->get_field_id('config_group'). '\')">Refresh</a>';

        //you also should be active saveconfig feature
        if(!HW_AWC_WidgetFeatures::check_widget_feature($t, 'saveconfig') ) {
            echo '<div>Bạn cũng cần kích hoạt feature "<a href="' .admin_url('options-general.php?page=' . HW_HOANGWEB_Settings::HW_SETTINGS_PAGE). '" target="_blank">saveconfig</a>" cho widget này.</div>';
        }
        echo '</p>';

        echo '<a href="javascript:void(0)" onclick="__awc_feature_shortcode_params.generate_shortcode(this,jQuery(\'#'. $this->get_field_id('wfshortcode_container') .'\'))" class="button">Tạo shortcode</a>';
        echo '<div></div>';

        echo '<p><em>Hướng dẫn</em>: Sử dụng tính năng "Lưu cài đặt" ở tại widget này để lưu cấu hình của widget (chọn nhóm "Shortcode Widget")</p>';

        echo '</fieldset></div>';
    }
    public function do_widget_feature_frontend($widget, $instance) {}
    public function is_active(){}
    /**
     * validation widget instance
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        #if( isset($new_wf_instance['awc_enable_grid_posts']) ) $new_wf_instance['awc_enable_grid_posts'] = 1;
        #if(! isset($new_wf_instance['awc_grid_posts_cols'])) $new_wf_instance['awc_grid_posts_cols'] = '';
        return $new_wf_instance;
    }

    /**
     * when instance this widget feature
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget){
        $this->remove_settings = true;  //allow to remove settings of feature from current widget
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts') );
        add_filter('hw_awc_widget_saveconfig_group', array(__CLASS__, '_hw_awc_widget_saveconfig_group'));
    }

    /**
     * @hw_hook filter hw_awc_widget_saveconfig_group
     * @param array $_group group
     * @return array
     */
    public static function _hw_awc_widget_saveconfig_group($_groups) {
        $groups = array(self::WIDGET_CONFIG_GROUP => 'Shortcode Widget');
        AWC_WidgetFeature_saveconfig::set_groups($groups);
        return AWC_WidgetFeature_saveconfig::get_groups();
    }
    public function run(){

    }

    /**
     * remove settings of the feature
     * @param feature $wf_instance
     * @return mixed|void
     */
    public function remove_settings($wf_instance) {
        $fields = array('widget_class', 'sidebar', );
        if(is_array($wf_instance) ) {
            foreach( $fields as $field) {
                if(isset($wf_instance[$field])) unset($wf_instance[$field]);
            }
        }
        return $wf_instance;
    }

    /**
     * enqueue material in admin
     */
    public function _admin_enqueue_scripts() {
        $script_handle = $this->get_widget_feature_name('script-');

        //generate shortcode widget ajax url
        $nonce = wp_create_nonce("hw_generate_shortcode_widget_nonce");
        $ajax_link = admin_url('admin-ajax.php?action=hw_generate_shortcode_widget&nonce='.$nonce);

        //load saveconfig widgets data throught ajax
        $nonce = wp_create_nonce("hw_load_saveconfig_widgets_data_nonce");
        $load_saveconfig_url = admin_url('admin-ajax.php?action=hw_load_saveconfig_widgets_data&nonce='.$nonce);

        if(!wp_script_is($script_handle)) {
            wp_enqueue_script($script_handle , plugins_url('awc-wf-shortcode.js', __FILE__), array('jquery'));
            wp_localize_script($script_handle,  $this->get_widget_feature_name('__awc_feature_'), array(
                'generate_shortcode_url' => $ajax_link,
                'load_saveconfig_data_url' => $load_saveconfig_url,
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }

    }

    /**
     * HW_SKIN::apply_skin_data callback
     * @param array $args
     */
    public static function _hw_skin_resume_skin_data($args) {
        extract($args);
        /**
         * override sidebar param from active skin
         */
        $sidebar_params = &$args['sidebar_params'];
        if(isset($theme['params']) && is_array($theme['params'])){
            $sidebar_params = array_merge($sidebar_params, $theme['params']);
        }
    }

    /**
     * generate shortcode from widget with ajax handle
     */
    public static function _hw_ajax_generate_shortcode_widget() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_generate_shortcode_widget_nonce")) {
            exit("Lỗi! bạn không có quyền hoặc phiên làm việc hết hạn.");
        }
        //valid
        if(!isset($_REQUEST['widget_class'])) return ;

        //get params
        $widget = $_REQUEST['widget_class'];  //widget class
        $sidebar = isset($_REQUEST['sidebar'])? $_REQUEST['sidebar'] : '';  //sidebar id
        $sidebar_skin = isset($_REQUEST['sidebar_skin'])? $_REQUEST['sidebar_skin'] : 'skin_default';  //sidebar skin

        /*$params = isset($_REQUEST['params'])? $_REQUEST['params'] : '';
        $widget_instance = @unserialize(base64_decode($params));*/

        $widget_config = isset($_REQUEST['widget_config'])? $_REQUEST['widget_config'] : '';    //widget config

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            echo '<textarea style="width:100%;height:70px;" disabled="disabled">[hw_widget_content widget="'.$widget.'" sidebar="'. $sidebar.'" skin="'. $sidebar_skin.'" params_config= "'. $widget_config .'"]</textarea>';
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * load saved widgets config data
     */
    public static function _ajax_load_saveconfig_widgets_data() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_load_saveconfig_widgets_data_nonce")) {
            exit("Lỗi! bạn không có quyền hoặc phiên làm việc hết hạn.");
        }
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            $widgetconfig_groups = AWC_WidgetFeature_saveconfig::get_widgets_settings_select(' WHERE _group="' . self::WIDGET_CONFIG_GROUP. '"');
            echo json_encode($widgetconfig_groups);
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    /**
     * initialize
     */
    public static function _init() {

        //ajax handle
        add_action('wp_ajax_hw_generate_shortcode_widget', array(__CLASS__, '_hw_ajax_generate_shortcode_widget') );
        add_action('wp_ajax_nopriv_hw_generate_shortcode_widget', array(__CLASS__, '_hw_ajax_generate_shortcode_widget') );

        add_action('wp_ajax_hw_load_saveconfig_widgets_data', array(__CLASS__, '_ajax_load_saveconfig_widgets_data') );
        add_action('wp_ajax_nopriv_hw_load_saveconfig_widgets_data', array(__CLASS__, '_ajax_load_saveconfig_widgets_data') );
    }
}
AWC_WidgetFeature_shortcode_params::_init();

include ('shortcode.php');
endif;