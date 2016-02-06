<?php
#/root>includes/functions.php
/**
 * custom dynamic sidebar for dynamic_sidebar() function
 * @param $name: sidebar id
 */
function hw_dynamic_sidebar($name) {
    $name = apply_filters('hw_dynamic_sidebar', $name);
    $dynamic_settings = HW_AWC_Frontend::get_active_sidebars_settings();//_print($dynamic_settings);
    $pages_condition_and = array();
    $pages_condition_or = array();

    //match occurence condition
    $match_occurence = hw_get_setting('match_occurence', 'first_occurence');

    //get cache result for first
    $detect_addition_sidebar = false;
    $data = get_transient('hw_dynamic_sidebar');
    if(empty($data) ) $data = array();
    #see: other way in includes/layout-templates/theme.php/HW__Template_Condition
    foreach ($dynamic_settings as $id => $setting) {
        $result = $result_or = array();
        //$and = APF_hw_condition_rules::parseQuery($setting['query_data_and']); //AND relation
        //$or = APF_hw_condition_rules::parseQuery($setting['query_data_or']);  //OR relation
        if(!empty($setting['query_data_and'])) {
            $_result = HW_AWC_Frontend::check_sidebar_changing($setting['query_data_and'], 'AND');
            list($k, $v) = each($_result);
            $result[] = array('template' => $k,'result' => $v, 'setting' => $setting);  //$result[$k]

            $pages_condition_and = array_merge($pages_condition_and, $result);      //override page result for AND relation
        }
        if(!empty($setting['query_data_or'])) {
            $_result_or = HW_AWC_Frontend::check_sidebar_changing($setting['query_data_or'], 'OR');
            list($k, $v) = each($_result_or);
            $result_or[] = array('template' => $k, 'result' => $v, 'setting' => $setting);

            $pages_condition_or = array_merge($pages_condition_or, $result_or);     //override page result for OR relation
        }
    }

    foreach(array($pages_condition_and, $pages_condition_or) as $pages_condition)
    if(isset($pages_condition) && is_array($pages_condition)) {     //get sidebar alternate with AND relation
        $key = base64_encode(serialize($pages_condition));
        if(!empty($data[$key])) $name = $data[$key];
        else{
            foreach ($pages_condition as $temp => $meet_condition) {
                if($meet_condition['result']) {
                    $_name = HW_Validation::valid_objname($name);
                    //get active sidebars
                    $sidebar = get_post_meta($meet_condition['setting']['post_ID'], $_name, true);
                    if(is_active_sidebar($sidebar)) {   //make sure sidebar not empty
                        if(($match_occurence == 'first_occurence' && !$detect_addition_sidebar)
                            || $match_occurence=='last_occurence') {
                            $name = $sidebar;   //rename sidebar
                            $data[$key] = $name;     //save redirect sidebar name
                            $detect_addition_sidebar = true;    //detect append
                        }

                    }
                }
            }
        }

    }
    /*
    if(isset($pages_condition_and) && is_array($pages_condition_and)) {     //get sidebar alternate with AND relation
        $and_key = base64_encode(serialize($pages_condition_and));
        if(!empty($data[$and_key])) $name = $data[$and_key];
        else{
            foreach ($pages_condition_and as $temp => $meet_condition) {
                if($meet_condition['result']) {
                    $_name = HW_Validation::valid_objname($name);
                    //get active sidebars
                    $sidebar = get_post_meta($meet_condition['setting']['post_ID'], $_name, true);
                    if(is_active_sidebar($sidebar)) {   //make sure sidebar not empty
                        $name = $sidebar;   //rename sidebar
                        $data[$and_key] = $name;     //save redirect sidebar name
                        $detect_addition_sidebar = true;    //detect append
                    }
                }
            }
        }

    }
    if(isset($pages_condition_or) && is_array($pages_condition_or)) {   //get sidebar alternate with OR relation
        $or_key = base64_encode(serialize($pages_condition_or));
        if(!empty($data[$or_key])) {
            $name = $data[$or_key];
        }
        else {
            foreach ($pages_condition_or as $temp => $meet_condition) {
                if($meet_condition['result']) {
                    $_name = HW_Validation::valid_objname($name);
                    //get active sidebars
                    $sidebar = get_post_meta($meet_condition['setting']['post_ID'], $_name, true);
                    if(is_active_sidebar($sidebar)){    //make sure sidebar not empty
                        $name = $sidebar;   //rename sidebar
                        $data[$or_key] = $name;     //save redirect sidebar name
                        $detect_addition_sidebar = true;        //detect append
                    }
                }
            }
        }

    }
    */
    //cache result to database
    if($detect_addition_sidebar == true) {
        set_transient('hw_dynamic_sidebar', $data);
    }
    dynamic_sidebar($name); //load sidebar
}

