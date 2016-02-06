<?php
if(class_exists('HW_AWC_WidgetFeature')) :
    class AWC_WidgetFeature_fixed_widget extends HW_AWC_WidgetFeature{

        /**
         * check whether widget enable fixed_widget
         * @param $instance widget instance
         */
        public function is_active($instance = '') {
            if(!empty($instance)) $this->widget_instance = $instance;
            return $this->get_field_value('awc_enable_fixed_widget');
        }
        /**
         * get features settings from current widget
         */
        public function get_options() {
            $settings =  $this->get_options_value();

            return $settings;
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

        /**
         * do widget feature in frondend if it actived
         * @param $t
         * @param $instance
         */
        public function do_widget_feature_frontend($t, $instance) {
            //widget id
            $current_widget_id = "{$t->id_base}-{$t->number}";
            $lib = $this->get_field_value('awc_fixedobj_lib');  //fixed object lib
            $options = $this->get_options();

            $json_config = HW_SKIN_Option::build_json_options($options);    //parse fancybox setting to json data
            //for sticky js
            if($lib == 'sticky') {
                echo '<script>
        jQuery(document).ready(function($) {
            $("#'.$current_widget_id.'").sticky('.$json_config.');

        });
        </script>';
            }
            //for sticky-kit
            else {
                echo '<script>
        jQuery(document).ready(function($) {
            $("#'.$current_widget_id.'").stick_in_parent('.$json_config.');

        });
        </script>';
            }
        }
        /**
         * load options fixed widget feature
         * @param WP_Widget $t: widget object
         * @param array $instance: widget data
         */
        function do_widget_feature($t,$instance = array()) {
            $this->widget_instance = $instance; //maybe update widget instance

            $enable_fixed = $this->get_field_value('awc_enable_fixed_widget');
            $lib = $this->get_field_value('awc_fixedobj_lib');

            echo '<div class="awc-widget-feature-fixed_widget"><fieldset><legend>Fixed widget</legend>';
            if(class_exists('HW_NHP_Main_Settings', false)) {
                echo '<p><a href="' .HW_NHP_Main_Settings::get_setting_page_url(). '" target="_blank">Kích hoạt & cấu hình fixed widget</a></p>';
            }
            echo '<div ><input type="checkbox" name="'.$this->get_field_name('awc_enable_fixed_widget').'" id="'.$this->get_field_id('awc_enable_fixed_widget').'" '.esc_attr( $enable_fixed? 'checked="checked"':'').'/>';
            echo '<label for="'.$this->get_field_id('awc_enable_fixed_widget').'"><strong>Kích hoạt fixed widget</strong></label></div>';
            //grids column
            echo '<label for="'.$this->get_field_id('awc_fixedobj_lib').'"><strong>Chọn thư viện</strong></label>';
            $fixed_libs = array(
                'sticky' => 'Sticky',
                'sticky-kit' => 'Sticky Kit'
            );
            echo HW_UI_Component::build_select_tag($fixed_libs, $lib, array('name' => $this->get_field_name('awc_fixedobj_lib'), 'id' => $this->get_field_id('awc_fixedobj_lib')));

            #echo '<div><label for="'.$t->get_field_id('awc_grid_fancybox').'"><strong>Số cột posts grid:</strong></label>';
            #echo '<input size="5" type="text" name="'.$t->get_field_name('awc_grid_fancybox').'" id="'.$t->get_field_id('awc_grid_fancybox').'" value="'.$instance['awc_grid_fancybox'].'"/></div>';
            /*echo '<div>
            <span>Cài đặt options</span><br/>

        </div>';*/

            if($lib == 'sticky-kit') {
                if(file_exists(plugin_dir_path(__FILE__). '/options-sticky-kit.php')) {
                    include( plugin_dir_path(__FILE__). '/options-sticky-kit.php' );
                }
            }
            elseif(file_exists(plugin_dir_path(__FILE__). '/options.php')) {
                include( plugin_dir_path(__FILE__). '/options.php' );
            }
            //build theme options
            if(isset($theme_options)) {
                echo '<div id="'.$this->get_field_id('fixed_widget-settings').'" class="">';
                echo $this->build_options($theme_options);
                echo '</div>';
            }
            echo '<script>
        jQuery(function($) {

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
            $new_wf_instance['awc_enable_fixed_widget'] = isset($new_wf_instance['awc_enable_fixed_widget']);
            $new_wf_instance['awc_enable_fixed_widget'] = isset($new_wf_instance['awc_enable_fixed_widget']);
            //
            return $new_wf_instance;
        }
        public function init(WP_Widget $widget){
            add_action('wp_enqueue_scripts', array($this, '_wp_enqueue_scripts') );

        }
        public function _wp_enqueue_scripts() {
            if(!is_admin()) {
                //enqueue sticky lib
                #HW_Libraries::enqueue_jquery_libs('sticky');   //best way
                HW_Libraries::get('sticky')->enqueue_scripts('jquery.sticky.js');
                #wp_enqueue_script('jquery.sticky.js', plugins_url('asset/jquery.sticky.js', __FILE__));
            }

        }
    }
endif;