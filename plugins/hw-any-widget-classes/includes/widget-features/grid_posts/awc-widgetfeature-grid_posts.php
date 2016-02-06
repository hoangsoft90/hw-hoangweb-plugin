<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/06/2015
 * Time: 07:54
 */
if(class_exists('HW_AWC_WidgetFeature')) :
class AWC_WidgetFeature_grid_posts extends HW_AWC_WidgetFeature{
    /**
     * check whether widget enable fancybox
     * @param $instance widget instance
     */
    public function is_active($instance = '') {
        if(!empty($instance)) $this->widget_instance = $instance;
        return $this->get_field_value('awc_enable_grid_posts') && $this->get_field_value('awc_grid_posts_cols');
    }
    public function do_widget_feature_frontend($widget, $instance) {}
    /**
     * load option grid posts
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        $this->widget_instance = $instance; //maybe update widget instance
        //$id = $this->get_field_id('awc_enable_grid_posts');
        $grid_posts_cols = $this->get_field_value('awc_grid_posts_cols', 2);
        $enable_grid_posts = $this->get_field_value('awc_enable_grid_posts');

        echo '<div class="awc-widget-feature-grid_posts"><fieldset><legend>Grid Posts</legend>';
        echo '<div ><input type="checkbox" name="'.$this->get_field_name('awc_enable_grid_posts').'" id="'.$this->get_field_id('awc_enable_grid_posts').'" '.esc_attr($enable_grid_posts? 'checked="checked"':'').'/>';
        echo '<label for="'.$this->get_field_id('awc_enable_grid_posts').'"><strong>Hiển thị posts dạng grid</strong></label></div>';
        //grids column
        echo '<div><label for="'.$this->get_field_id('awc_grid_posts_cols').'"><strong>Số cột posts grid:</strong></label>';
        echo '<input size="5" type="text" name="'.$this->get_field_name('awc_grid_posts_cols').'" id="'.$this->get_field_id('awc_grid_posts_cols').'" value="'.$grid_posts_cols.'"/></div>';
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
        if( isset($new_wf_instance['awc_enable_grid_posts']) ) $new_wf_instance['awc_enable_grid_posts'] = 1;
        if(! isset($new_wf_instance['awc_grid_posts_cols'])) $new_wf_instance['awc_grid_posts_cols'] = '';
        return $new_wf_instance;
    }
    public function init(WP_Widget $widget){

    }
    public function run(){

    }
}
endif;