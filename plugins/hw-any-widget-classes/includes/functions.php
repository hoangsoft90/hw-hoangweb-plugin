<?php
#/root>
if(class_exists('HW_HOANGWEB') && !class_exists('HW_String', false)) HW_HOANGWEB::load_class('HW_String');

/**
 * get awc apf option
 * @param string $opt: give name of option want to getting
 * @param string $default: default value    (optional)
 * @param string $group: group section name (optional)
 */
function hwawc_get_option($opt='',$default='',$group = ''){
    if(!$opt) return AdminPageFramework::getOption( 'HW_Sidebar_Settings'); //return all fields value in section
    if($group) return AdminPageFramework::getOption( 'HW_Sidebar_Settings', array($group,$opt), $default );
    else return AdminPageFramework::getOption( 'HW_Sidebar_Settings', $opt, $default );
}


/**
 * return all active sidebars
 */
function hwawc_get_all_active_sidebars() {
    global $wp_registered_sidebars;
    return $wp_registered_sidebars;
}

/**
 * sidebars select data
 */
function hwawc_get_active_sidebars_select(){
    global $wp_registered_sidebars;
    $sidebars_field_data = array();
    foreach($wp_registered_sidebars as $sidebar) {
        if($sidebar['id'] == 'sidebar-data') continue;  //ignore hoangweb built sidebar
        $sidebars_field_data[$sidebar['id']] = $sidebar['name'];
    }
    return $sidebars_field_data;
}

/**
 * return all registered widgets
 * @return array
 */
function hwawc_get_all_widgets() {
    if(isset($GLOBALS['wp_widget_factory'])) {
        $widgets = array();
        foreach ($GLOBALS['wp_widget_factory']->widgets as $class => $widget) {
            $widgets[$widget->id_base] =  $class;
        }
        #$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets );
        return $widgets;
    }
    return array();
}

/**
 * register all widget features
 */
function hwawc_register_widget_features() {
    $group = 'widget-features';
    $features = array(
        'saveconfig' => array(
            'class' => 'AWC_WidgetFeature_saveconfig',
            'alias' => 'save config'
        ),
        'grid_posts' => array(
            'class' =>'AWC_WidgetFeature_grid_posts', 'alias' => 'grid posts'
        ),
        'fancybox' => array(
            'class'=> 'AWC_WidgetFeature_fancybox', 'alias' => 'fancybox'
        ),
        'fonticons' => array(
            'class' => 'AWC_WidgetFeature_fonticons', 'alias' => 'font icons'
        ),
        'title_link' => array(
            'class'=>'AWC_WidgetFeature_title_link', 'alias' => 'Widget title Link'
        ),
        'shortcode_params' => array(
            'class' =>'AWC_WidgetFeature_shortcode_params', 'alias' => 'Shortcode params'
        ),
        'fixed_widget' => array(
            'class'=>'AWC_WidgetFeature_fixed_widget', 'alias' => 'Fixed widget'
        ),
        'hide_widget' => array(
            'class' => 'AWC_WidgetFeature_hide_widget', 'alias' => 'Hide Widget'
        ),
        'export' => array(
            'class' => 'AWC_WidgetFeature_export', 'alias' => 'Export Widget'
        )
    );
    foreach($features as $name => $arg) {
        HW_HOANGWEB::register_class(
            $arg['class'],
            HW_AWC_WidgetFeatures_PATH . "/{$name}/awc-widgetfeature-{$name}.php",
            $arg['alias'], $group);
    }
}

/**
 * add new sidebar registration to db
 * @param $sidebar
 */
function hwawc_register_sidebar($sidebar) {
    //valid
    $sidebar = HW_Module_AWC::_valid_sidebar($sidebar);
    if(!is_array($sidebar) || !isset($sidebar['id'])) return;

    $sidebars = get_option('hw_awc_registers_sidebars');
    if(!($sidebars)) {
        $sidebars = array();
        add_option('hw_awc_registers_sidebars', $sidebars);
    }
    $sidebars[$sidebar['id']] = $sidebar;   //override or add new
    //update sidebars into database
    update_option('hw_awc_registers_sidebars', $sidebars);
    return $sidebars;
}

/**
 * get all registered sidebars
 * @param $id return sidebar param by given id
 * @return array|mixed|void
 */
function hwawc_get_registers_sidebars($id='') {
    global $wp_registered_sidebars;
    $sidebars = get_option('hw_awc_registers_sidebars');
    if(empty($sidebars)) $sidebars = array();
    $sidebars = array_merge($wp_registered_sidebars, $sidebars);

    if($id && $sidebars && isset($sidebars[$id])) return $sidebars[$id];
    return $sidebars? $sidebars : array() ;
}

/**
 * unregister sidebar by id
 * @param $id
 */
function hwawc_unregister_sidebar($id) {
    global $wp_registered_sidebars;
    $sidebars = get_option('hw_awc_registers_sidebars');
    if(!empty($sidebars) && isset($sidebars[$id])) {
        //save to db
        unset($sidebars[$id]);
        update_option('hw_awc_registers_sidebars', $sidebars);

        //update from global $wp_registered_sidebars
        if(isset($wp_registered_sidebars[$id])) unset($wp_registered_sidebars[$id]);
    }

}

/**
 * delete all sidebars
 */
function hwawc_unregister_all_sidebars() {
    delete_option('hw_awc_registers_sidebars');
}
//init plugin
include_once('init.php');
//load template functions
include_once('functions-templates.php');