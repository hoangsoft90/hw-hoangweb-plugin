<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/06/2015
 * Time: 07:32
 */

/**
 * Class AWC_WidgetFeature_export
 */
if(class_exists('HW_AWC_WidgetFeature')):
class AWC_WidgetFeature_export extends HW_AWC_WidgetFeature{

    /**
     * init feature
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget) {

        $this->remove_settings = true;
    }
    public function is_active(){}


    /**
     * widget feature form
     * yes, since saveconfig purpose for HW Taxonomy post list widget
     * @param $widget
     * @param $instance
     */
    public function do_widget_feature($t,$instance = array()) {
        include('template/widget_feature-saveconfig.php');
    }
    public function do_widget_feature_frontend($widget, $instance) {

    }
    /**
     * remove this widget features params
     * @param $instance: widget instance
     */
    public function remove_settings($instance) {
        $fields = array('hw_widopt_group', 'hw_widopt_name', 'hw_widopt_desc', 'hw_widopt_setting');
        if(is_array($instance) ) {
            foreach( $fields as $field) {
                if(isset($instance[$field])) unset($instance[$field]);
            }
        }
        return $instance;
    }
    /**
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed|void
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        $new_wf_instance = $this->remove_settings($new_wf_instance); //do not save this feature data to current working widget
        //if(isset($new_wf_instance['hw_widopt_setting'])) unset($new_wf_instance['hw_widopt_setting']);
        return $new_wf_instance;
    }
}
endif;