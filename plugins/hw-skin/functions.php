<?php

/**
* order plugins to be loaded before all other plugins
*/
function _hwskin_move_at_first_when_activation(){
    if(!is_admin()) return;
    if(function_exists('hw_reorder_actived_plugins')){
        hw_reorder_actived_plugins('hw-skin/hw-skin.php','hw-hoangweb/hoangweb.php');   //move this plugin at first list
    }
    else{
        wp_die('Sory, Bạn cần kích hoạt plugin hw-hoangweb trước.');
        /*
        // ensure path to this file is via main wp plugin path
        $this_plugin = HW_SKIN::get_this_plugin();
        $active_plugins = get_option('active_plugins');
        $this_plugin_key = array_search($this_plugin, $active_plugins);
        if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
            array_splice($active_plugins, $this_plugin_key, 1);
            array_unshift($active_plugins, $this_plugin);
            update_option('active_plugins', $active_plugins);
        }*/
    }

}
//trigger when activate other plugin and because hw-skin/hw-skin.php migrated to hoangweb module (mean not exists)
#add_action( 'activated_plugin', '_hwskin_move_at_first_when_activation');
/**
 * @function register_activation_hook
 */
function _hwskin_activation_hook() {
    global $wpdb;
    $sql = 'CREATE TABLE IF NOT EXISTS `hw_skin_settings` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `blog_id` int(11) NOT NULL,
 `object` int(11) NOT NULL,
 `type` int(11) NOT NULL,
 `skin` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

    $wpdb->query($sql);
}
register_activation_hook( HW_SKIN_PLUGIN_FILE, '_hwskin_activation_hook' );
//require HW_HOANGWEB plugin
#register_activation_hook( HW_SKIN_PLUGIN_FILE, 'hw_skin_require_plugins_activate' );
function hw_skin_require_plugins_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'hw-hoangweb/hoangweb.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Xin lỗi, yêu cầu cài đặt & kích hoạt plugin "hw-hoangweb". <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}

/**
 * enqueue all active skins asset
 */
/**
 *
 * enqueue all active skins resource
 * @hook wp_enqueue_scripts
 */
function _hwskin_wp_enqueue_scripts() {
    //validation
    if(!class_exists('HW_Condition')) return ;

    $skins = hwskin_get_actived_skins();
    #$dynamic_settings = HW__Template_Condition::get_active_templates_settings();
    $dynamic_settings = HW_Condition::get_active_conditions_settings();

    foreach($skins as $id => $skin) {
        if(!empty($skin->instance)) {

            if(isset($skin->hwskin_condition) && isset($dynamic_settings[$skin->hwskin_condition])) {
                $setting = array($skin->hwskin_condition => $dynamic_settings[$skin->hwskin_condition]);
                $setting_conditions = HW__Template_Condition::parse_template_conditions($setting);

                foreach($setting_conditions as $pages_condition) {   //and, or condition
                    if(isset($pages_condition) && is_array($pages_condition)) {     //get template alternate with AND relation

                        foreach ($pages_condition as $temp => $meet_condition) {
                            if($meet_condition['result']) {
                                #$_name = HW_Validation::valid_objname($file);
                                HW_SKIN::enqueue_skins($skin);
                                break;  //first occurence
                            }
                        }
                    }
                }
            }
            else {
                HW_SKIN::enqueue_skins($skin);
            }

        }

    }
}
add_action('wp_enqueue_scripts',  '_hwskin_wp_enqueue_scripts');

//load skin selector Field type for AdminPageFramework
/**
 * load APF filetype for hw_skin
 * @param string $type: skin type
 */
function hwskin_load_APF_Fieldtype($type = HW_SKIN::SKIN_FILES){
    if(!class_exists('AdminPageFramework_Registry')) include_once('lib/admin-page-framework.min.php'); //load admin page framework
    if($type == HW_SKIN::SKIN_FILES){
        if(!class_exists('APF_hw_skin_Selector_hwskin')) include_once('APF_Fields/hw_skin_FieldType.php');
    }
    if($type == HW_SKIN::SKIN_LINKS){
        if(!class_exists('APF_imageSelector_hwskin')) include_once('APF_Fields/hw_skin_link_FieldType.php');
    }
}
/**
 * register skin
 * @param unknown $name: skin name
 * @param unknown $instance: skin instance
 */
function hwskin_register_skin($name,$instance){
    global $HW_Skins;
    if(!$HW_Skins) $HW_Skins = array();
    if(!isset($HW_Skins[$name])){
        $HW_Skins[$name] = $instance;
    }
    
}

/**
 * parse $theme_options in file skin option : options.php
 * @param array $options
 */
function hwskin_parse_theme_options($options = array()){
    if(is_array($options)){
        foreach($options as $f => $arr){
            if(is_numeric($f)){
                if(isset($arr['name'])) {
                    $options[$arr['name']] = $arr;
                    unset($options[$f]);
                }
            }
        }
    }
    return $options;
}

/**
 * save current any skins to db
 * @param $args
 * @param string $name
 */
function hwskin_save_enqueue_skin($args, $name ='') {
    global $wpdb;
    //valid
    if(!isset($args['type'])) $args['type'] = 'resume_skin' ;   //default
    if(!isset($args['object'])  ) $args['object'] = $name;
    if(!isset($args['status'])  ) $args['status'] = 1;

    $where = array(
        'blog_id' => get_current_blog_id(),
        'object' => $args['object'],
        'type' => $args['type'],
    );
    $data = array(
        'blog_id' => get_current_blog_id(),
        'object' => $args['object'],
        'type' => $args['type'],
        'skin' => is_string($args['skin'])? $args['skin'] : serialize($args['skin']),
        'status' => $args['status']
    );

    $exist = $wpdb->get_row($wpdb->prepare(
        'SELECT * FROM '.HW_SKIN::SKINS_SETTINGS_DB.'  WHERE blog_id = %s and object = %s',
         $data['blog_id'], $data['object']
    ));
    if($exist && count($exist)) {
        $wpdb->update(HW_SKIN::SKINS_SETTINGS_DB, $data, $where);
    }
    else $wpdb->insert(HW_SKIN::SKINS_SETTINGS_DB, $data);
}

/**
 * get all actived skins
 * @param $where
 */
function hwskin_get_actived_skins($where= array()) {
    $where['status'] = '1';
    return hwskin_get_skins_enqueues($where);
}

/**
 * get skins setting
 * @param array $where
 * @return array
 */
function hwskin_get_skins_enqueues($where= array('status'=>1)) {
    global $wpdb;
    $data = array();
    $where_mark = '';
    $where_value= array();
    foreach($where as $field => $val) {
        $where_mark .= "{$field} = %s and ";
        $where_value[] = $val;
    }
    //valid
    $where_mark = trim($where_mark, 'and ');

    $query = "SELECT * FROM ".HW_SKIN::SKINS_SETTINGS_DB." ".($where_mark? 'WHERE '.$where_mark : '');
    $result = $wpdb->get_results($wpdb->prepare($query, $where_value));
    //loop all skins
    foreach($result as $skin) {
        $key = $skin->blog_id.'_'. $skin->object;
        if(isset($data[$key])) continue;

        $data[$key]= array();

        $skin_config = unserialize($skin->skin);
        /*if($skin['type'] == 'resume_skin') {

            //parse hw_skin instance
            if(isset($skin_config['hw_skin_config']) && isset($skin_config['hw_skin'])) {   //for old version
                $skin_obj = HW_SKIN::resume_skin($skin_config['hw_skin_config']);

            }
        }
        elseif($skin['type'] == 'APF_field' || $skin['type'] == 'NHP_field') {

        }*/
        if(isset($skin_config['hash_skin']) && isset($skin_config['hwskin_config'])) {
            $skin_obj = HW_SKIN::resume_hwskin_instance($skin_config);//
            $data[$key] = $skin_obj;

        }

        #if(isset($skin_obj)) $data[$key]['instance'] = $skin_obj;
    }
    return $data;
}

/**
 * update skin enqueue status
 * @param $id
 * @param int $status
 */
function hwskin_update_skin_enqueue_status($id, $status=1) {
    global $wpdb;
    $wpdb->update(HW_SKIN::SKINS_SETTINGS_DB, array('status' => $status? 1:0), array('id' => $id));
}
/**
 * delete skin setting
 * @param array $where
 */
function hwskin_delete_skins_enqueue($where= array()) {
    global $wpdb;
    if(!is_array($where)) $where = array();
    return $wpdb->delete(HW_SKIN::SKINS_SETTINGS_DB, $where);
}
