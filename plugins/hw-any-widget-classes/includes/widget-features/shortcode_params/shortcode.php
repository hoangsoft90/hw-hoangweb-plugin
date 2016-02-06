<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/07/2015
 * Time: 13:43
 */
//add shortcode to display any widget
add_shortcode('hw_widget_content',  '_hw_awc_feature_widget_content_shortcode');
/**
 * @shortcode hw_widget_content shortcode to display any widget content
 * @param $prop
 * @param $content
 */
function _hw_awc_feature_widget_content_shortcode($prop, $content) {
    $args=array('widget' => '', 'sidebar' => '', 'params_config' => '', 'skin' => 'skin_default');
    $d=shortcode_atts($args,$prop);
    extract($d);
    //valid
    if(empty($widget)) return ;

    global $wp_registered_sidebars;
    global $wp_registered_widgets;

    //default sidebar params
    $sidebar_params = array('before_title' => '','after_title'=>'','before_widget'=>'','after_widget'=>'');

    if(!empty($sidebar) && !empty($skin) && isset($wp_registered_sidebars[$sidebar])) {
        $sidebar_params = HW_AWC::apply_sidebar_skin($sidebar, $skin, array(
            'widget_id' => 'hw-shortcode-widget',
            'classname' => strtolower($widget) . ' hw-awc-override',
        ));

    }

    //get widget instance by saved widget config
    if(!empty($params_config) && is_numeric($params_config)) {
        $config = AWC_WidgetFeature_saveconfig::get_widget_setting($params_config);
        if($config) {
            $widget_params = /*http_build_query*/(AWC_WidgetFeature_saveconfig::decode_config($config->setting));
        }
    }

    if(isset($widget) && class_exists($widget, false) && !empty($widget_params)) {

        //display widget content
        the_widget($widget, $widget_params , $sidebar_params);
    }

}