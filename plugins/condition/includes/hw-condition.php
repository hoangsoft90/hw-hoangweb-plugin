<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Condition
 * @used awc-widgetfeature-hide_widget.php
 */
class HW_Condition extends HW_Core{
    /**
     * whether condition is changing
     * @param $bind_data
     * @return bool
     */
    public static function check_condition($bind_data, $relation = 'AND') {
        return APF_hw_condition_rules::check_fields_condition($bind_data, $relation);
    }
    /**
     * get active dynamic conditions setting
     * @return array|mixed
     */
    public static function get_active_conditions_settings(){
        $result = get_transient('hw_dynamic_conditions_settings');
        if(!$result) {
            $args = array(
                'post_type' => HW_Conditions_Manager::post_type,
                'showposts' => -1,
                'orderby' => 'menu_order',
                'order' => 'asc',
                'meta_key' => 'enable',
                'meta_query' => array(
                    //list enable sidebar settings
                    array(
                        'key'       => 'enable',
                        'value'     => '1',
                        /*'compare'   => '==',
                        'type'      => 'NUMERIC',*/
                    ),
                )
            );
            $result = array();
            $query = new WP_Query($args);
            while($query->have_posts()){
                $query->the_post();//$query->next_post();
                $query_data_and = get_post_meta(get_the_ID(), 'query_data_and', true);
                $query_data_or = get_post_meta(get_the_ID(), 'query_data_or', true);

                $result[get_the_ID()] = array(
                    'title' =>get_the_title(),
                    'query_data_and' => $query_data_and,
                    'query_data_or' => $query_data_or,
                    'post_ID' => get_the_ID()
                );
            }
            $query->reset_postdata();   //reset query
            set_transient('hw_dynamic_conditions_settings', $result);     //set cache fetch from database
        }

        return $result;
    }

    /**
     * buid condition select tag
     * @param $value
     * @param array $atts
     * @return string
     */
    public static function get_conditions_select_tag($value, $atts = array()) {
        HW_HOANGWEB::load_class('HW_UI_Component');
        $options = array();
        $dynamic_settings = self::get_active_conditions_settings();
        foreach($dynamic_settings as $id => $item){
            $options[$id] = $item['title'];
        }
        if(class_exists('HW_Conditions_Manager', false)) {
            $guide_link = '(<a href="'.HW_Conditions_Manager::admin_condition_mananger_link().'" target="_blank">Thêm điều kiện</a>)';
        }
        else $guide_link = '';
        return HW_UI_Component::build_select_tag($options, $value, $atts ,true). $guide_link;
    }
}