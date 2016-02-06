<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

if(class_exists('HW_AWC_WidgetFeature')) :
/**
 * AWC_WidgetFeature_hide_widget
 */
class AWC_WidgetFeature_hide_widget extends HW_AWC_WidgetFeature{
    public static $checked = array();
    public $hide_list = array();
    /**
     * check whether widget enable hide option
     * @param $instance widget instance
     */
    public function is_active($instance = '') {
        if(!empty($instance)) $this->widget_instance = $instance;
        return $this->get_field_value('hide_option');
    }
    public function __construct($widget){
        parent::__construct($widget);
        
    }
    /**
     * load option widget
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        HW_HOANGWEB::load_class('HW_UI_Component');

        $this->widget_instance = $instance;
        $is_hide = $this->get_field_value('hide_option');
        $condition = $this->get_field_value('condition');

        #$dynamic_settings = HW_Condition::get_active_conditions_settings();
    ?>
        <fieldset><legend>Ẩn widget</legend>

        <p>
            <input type="checkbox" class="" name="<?php echo $this->get_field_name('hide_option')?>" id="<?php echo $this->get_field_id('hide_option')?>" <?php checked( $is_hide)?>/>
            <label for="<?php echo $this->get_field_id('hide_option')?>"><strong>Kích hoạt ẩn</strong></label>
        </p>
            <p>
                <label for="<?php echo $this->get_field_id('condition')?>"><strong>Điều kiện</strong></label>
                <?php
                echo HW_Condition::get_conditions_select_tag($condition,
                    array('name' => $this->get_field_name('condition'), 'id' => $this->get_field_id('condition'))
                );

                ?>
            </p>
        </fieldset>
    <?php
    }
    public function do_widget_feature_frontend($widget, $instance) {

    }
    /**
     * validation widget instance
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        if(isset($new_wf_instance['hide_option'])) {
            $new_wf_instance['hide_option'] = 1;
        }
        else $new_wf_instance['hide_option'] = 0;
        return $new_wf_instance;
    }
    /**
     * do while create the class instance
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget){
        add_action('admin_enqueue_scripts', array(&$this, '_admin_enqueue_scripts') );  //not work
        add_filter('hw_sidebars_widgets', array(__CLASS__, '_sidebars_widgets'));
        //$this->_admin_enqueue_scripts();
    }

    /**
     * @wp_hook action admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        if(self::is_widgets_page()){
            $this->enqueue_script('xx.js', array('jquery') );

        }
    }

    /**
     * @hook hw_sidebars_widgets
     * @param $sidebars
     */
    public static function _sidebars_widgets($sidebars ) {
        if ( is_admin() ) {
            return $sidebars;
        }
        global $wp_registered_widgets;

        foreach ( $sidebars as $s => $sidebar ) {
            if ( $s == 'wp_inactive_widgets' || strpos( $s, 'orphaned_widgets' ) === 0 || empty( $sidebar ) ) {
                continue;
            }

            foreach ( $sidebar as $w => $widget ) {
                // $widget is the id of the widget
                if ( ! isset( $wp_registered_widgets[ $widget ] ) ) {
                    continue;
                }
                if ( isset( self::$checked[ $widget ] ) ) {
                    $show = self::$checked[ $widget ];
                } else {
                    $widget_info = HW_AWC::get_widget_data($widget);
                    $opts = $wp_registered_widgets[ $widget ];
                    $widget_obj = isset($widget_info['widget'] ) ? $widget_info['widget'] : null;
                    $id_base = isset($widget_info['id_base'])? $widget_info['id_base'] : '';
                    #$id_base = is_array( $opts['callback'] ) ? $opts['callback'][0]->id_base : $opts['callback'];

                    if ( ! $id_base ) {
                        continue;
                    }
                    $feature = HW_AWC::get_widget_feature( $widget, self::_static_option('feature_name') );
                    #
                    if(!$feature || ( HW_AWC_WidgetFeatures::check_widget_feature( $widget, self::_static_option('feature_name') && !$feature->is_active())) ) {
                        continue;
                    }

                    #$instance = get_option( 'widget_' . $id_base );
                    $instance = $widget_info['instance'];

                    if ( ! $instance || ! is_array( $instance ) ) {
                        continue;
                    }
                    //from 'display widgets' plugin
                    if ( isset( $instance['_multiwidget'] ) && $instance['_multiwidget'] ) {
                        $number = $opts['params'][0]['number'];
                        if ( ! isset( $instance[ $number ] ) ) {
                            continue;
                        }

                        $instance = $instance[ $number ];
                        unset( $number );
                    }

                    unset( $opts );

                    $show = self::show_widget($feature, $instance );

                    self::$checked[ $widget ] = ($show ? true : false);
                }
                if ( ! $show ) {
                    unset( $sidebars[ $s ][ $w ] );
                    #$this->hide_list[] = "['$s'][$w]";
                }

                unset( $widget );
            }
            unset( $sidebar );
        }

        return $sidebars;
    }

    /**
     * check widget is show/hide
     * @param $feature
     * @param $instance
     */
    private static function show_widget($feature, $instance ) {
        static $dynamic_settings;
        if(!$dynamic_settings) $dynamic_settings = HW_Condition::get_active_conditions_settings();
        $hide_option = $feature->get_field_value('hide_option', false ,$instance);
        $condition = $feature->get_field_value('condition', false ,$instance);

        if(!$condition || !isset($dynamic_settings[$condition])) return !$hide_option;
        $result = 0;
        if($hide_option) {
            $setting = array($condition => $dynamic_settings[$condition]);
            $setting_conditions = HW__Template_Condition::parse_template_conditions($setting);#_print($setting_conditions);

            foreach($setting_conditions as $pages_condition) {   //and, or condition
                if(isset($pages_condition) && is_array($pages_condition)) {     //get template alternate with AND relation

                    foreach ($pages_condition as $temp => $meet_condition) {
                        if($meet_condition['result']) {
                            return 0;
                            //break;  //first occurence
                        }
                        else $result = 1;
                    }
                }
            }
        }

        return $result;
    }
    public function run(){
        //do base your usage
    }

}

endif;