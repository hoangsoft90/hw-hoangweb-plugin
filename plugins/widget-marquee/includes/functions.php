<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 08/11/2015
 * Time: 12:29
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * get all metalsliders data
 */
function hwmq_get_all_mlsliders(){
    return HWML_Slider_Settings_Metabox::get_all_mlsliders();
}

/**
 * get mlslides data for specific id
 * @param $id
 * @return WP_Query
 */
function hwmq_get_mlslider_data($id) {
    return HWMLShortcode_Manager::get_mlslider_metaslides($id);
}