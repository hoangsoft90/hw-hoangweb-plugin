<?php
/**
 * lightbox feature
 */
if(class_exists('HW_AWC_WidgetFeature')) :
class AWC_WidgetFeature_fancybox extends HW_AWC_WidgetFeature{

    /**
     * return fancybox global settings
     * @return mixed|string
     */
    public static function get_global_options() {
        //get global fancybox setting
        return hw_option('fancybox_settings', array());
    }

    /**
     * get fancybox settings from current widget
     */
    public function get_options() {
        $settings = self::get_global_options();
        if($this->get_field_value('use_default_opts') == 'extend') {
            $settings = array_merge($settings, $this->get_options_value());
        }

        return $settings;
    }
    /**
     * check whether widget enable fancybox
     * @param $instance widget instance
     */
    public function is_active($instance = '') {
        if(!empty($instance)) $this->widget_instance = $instance;
        return $this->get_field_value('awc_enable_fancybox');
    }

    /**
     * do on website
     * @param string $selector jquery selector
     * @return mixed|void
     */
    public function run($selector) {
        $json_config = HW_SKIN_Option::build_json_options($this->get_options());    //parse fancybox setting to json data
        echo '<script>
        jQuery(document).ready(function($) {
            /* Apply fancybox to multiple items */

            jQuery("'. $selector .'").fancybox('.$json_config.');

        });
        </script>';
    }
    public function do_widget_feature_frontend($widget, $instance) {}
    /**
     * load option grid posts
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        $this->widget_instance = $instance; //maybe update widget instance

        $enable_fancybox = $this->get_field_value('awc_enable_fancybox');

        echo '<div class="awc-widget-feature-fancybox"><fieldset><legend>Fancybox</legend>';
        if(class_exists('HW_NHP_Main_Settings', false)) {
            echo '<p><a href="' .HW_NHP_Main_Settings::get_setting_page_url(). '" target="_blank">Kích hoạt & cấu hình fancybox</a></p>';
        }
        echo '<div ><input type="checkbox" name="'.$this->get_field_name('awc_enable_fancybox').'" id="'.$this->get_field_id('awc_enable_fancybox').'" '.esc_attr( $enable_fancybox? 'checked="checked"':'').'/>';
        echo '<label for="'.$this->get_field_id('awc_enable_fancybox').'"><strong>Kích hoạt fancybox</strong></label></div>';
        //grids column
        #echo '<div><label for="'.$t->get_field_id('awc_grid_fancybox').'"><strong>Số cột posts grid:</strong></label>';
        #echo '<input size="5" type="text" name="'.$t->get_field_name('awc_grid_fancybox').'" id="'.$t->get_field_id('awc_grid_fancybox').'" value="'.$instance['awc_grid_fancybox'].'"/></div>';
        echo '<div>
            <span>Cài đặt options</span><br/>
            <label><input type="radio" name="'.$this->get_field_name('use_default_opts').'" class="'.$this->get_field_id('use_default_opts').'" id="'.$this->get_field_id('use_default_opts').'" value="default" '.($this->get_field_value('use_default_opts') == 'default'? "checked='checked'":"").'/> Mặc định</label>
            <label><input type="radio" name="'.$this->get_field_name('use_default_opts').'" class="'.$this->get_field_id('use_default_opts').'" id="'.$this->get_field_id('use_default_opts').'" value="extend" '.($this->get_field_value('use_default_opts') == 'extend'? "checked='checked'":"").'/> Mới</label>
        </div>';

        if(file_exists(plugin_dir_path(__FILE__). '/options.php')) {
            include( plugin_dir_path(__FILE__). '/options.php' );
        }
        if(isset($theme_options)) {
            $class = ($this->get_field_value('use_default_opts') == 'default')? 'hw-hidden' : '';
            echo '<div id="'.$this->get_field_id('fancybox-settings').'" class="'.$class.'">';
            echo $this->build_options($theme_options);
            echo '</div>';
        }
        echo '<script>
        jQuery(function($) {
            $(".'.$this->get_field_id('use_default_opts').'").click(function(e) {
                var setting_container = "#'. $this->get_field_id('fancybox-settings') .'";
                if($(this).val() == "default") {
                    $(setting_container).hide();
                }
                else $(setting_container).show().removeClass("hw-hidden");
            });
        });
        </script>
        ';
        echo '</fieldset></div>';
    }
    /**
     * validation widget instance
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        $new_wf_instance['awc_enable_fancybox'] = isset($new_wf_instance['awc_enable_fancybox']);
        //
        return $new_wf_instance;
    }
    public function init(WP_Widget $widget){
        add_action('wp_enqueue_scripts', array($this, '_wp_enqueue_scripts') );

    }
    public function _wp_enqueue_scripts() {
        if(!is_admin()) {
            //enqueue fancybox lib
            HW_Libraries::enqueue_jquery_libs('fancybox');
        }

    }
}
endif;