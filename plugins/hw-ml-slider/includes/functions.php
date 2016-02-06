<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

if(class_exists('HW_SKIN') && function_exists('hwskin_load_APF_Fieldtype')) {
    //custom hw_skin field type for AdminPageFramework
    hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
}
if(is_admin()){
    if(class_exists('HW_HOANGWEB')){
        //HW_HOANGWEB::loadlib('AdminPageFramework'); //load admin page framework ->entrusted by hw-hoangweb/__autoload
    }
}

/**
 * require HW_HOANGWEB plugin
 * make sure ml-slider plugin should be exists before active this plugin.
 */
//register_activation_hook( HWML_PLUGIN_FILE, 'hwml_require_plugins_activate' );
function hwml_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin',
            'ml-slider/ml-slider.php'   //meta slider
        ));
    }
    else wp_die('Xin lỗi, bạn cần kích hoạt plugin hw-hoangweb trước hoặc kích hoạt lại plugin này.');
}


/**
 * generate shortcode
 * @param $id: post id
 */
function hwml_generate_shortcode($id){
    return esc_attr('[hwml_slider id="'.$id.'"]');
}

/**
 * return all hw sliders posts
 * @return array
 */
function hwml_get_all_sliders(){
    if(!class_exists('HWML_Shortcodes_List')) return array();  //valid

    $args = array(
        'post_type' => HWML_Shortcodes_List::hwml_slider_posttype,
        'showposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'asc'
    );
    $result = array();
    $query = new WP_Query($args);
    while($query->have_posts()){
        $query->the_post();//$query->next_post();
        $result[get_the_ID()] = get_the_title();

    }
    $query->reset_postdata();   //reset query
    //wp_reset_postdata();
    return $result;
}

/**
 * get slider setting
 * @param $name: given option
 * @param $id: post id
 */
function hwml_get_option($name, $id = null){
    if(empty($id) && isset($_GET['post']) ) $id = $_GET['post'];
    return get_post_meta($id, $name, true);
}