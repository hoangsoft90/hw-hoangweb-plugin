<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 09:51
 */
/**
 * Class HW_Gmap
 */
class HW_Gmap extends HW_Core{
    /**
     * sington for the class instance
     * @var null
     */
    public static $instance = null;
    /**
     * main class contructor
     */
    public function __construct() {

    }
    /**
     * generate geo code
     */
    const geocode = 'http://maps.google.com/maps/api/geocode/json?address=%s&sensor=false';
    /**
     * parse location into latitude and longitude
     * @param $location: address
     */
    public static function getLocationFromAddress($location){
        $url = sprintf(self::geocode, urlencode($location));
        HW_HOANGWEB::load_class('HW_CURL');

        $resp = HW_CURL::curl_get($url);
        //other way
        #$resp = @file_get_contents($url);
        $json = json_decode($resp);
        if(isset($json->results[0])) return $json->results[0]->geometry->location;
    }

    /**
     * render map
     * @param $atts
     */
    public function render_googlemap($atts = array()){
        HW_HOANGWEB::load_class('HW_String');
        $module = $this->_option('module') ;
        $id = HW_String::generateRandomString();
        //options
        $width = HW_Validation::format_unit(isset($atts['width'])? $atts['width'] : ($module? $module->get_field_value('width'):'') );
        $height = HW_Validation::format_unit(isset($atts['height'])? $atts['height']: ($module? $module->get_field_value('height'): '') );
        $show_searchbox = isset($atts['show_searchbox'])? (int)$atts['show_searchbox'] : ($module? $module->get_field_value('show_searchbox'): '' );
        if(!empty($atts['location'])) {
            $location = $atts['location'];
            if(is_array($location)) $location = json_encode($location);
        }
        elseif(!empty($atts['address'])) {
            $location = '"'.$atts['address'].'"';
        }
        else $location = '""';

        #$atts['location'] = isset($atts['location'])? HW_Gmap::getLocationFromAddress($atts['location']) : array();
        #$location_json = json_encode($atts['location']);

        $input_box_id = 'pac-input-'. $id;  //input box search location
        $map_canvas_id = 'map-canvas-'. $id;  //map canvas

        $out = '<div class="hw-module-map-container">';
        if($show_searchbox) $out .= '<input id="'.$input_box_id.'" class="controls hw-pac-input" type="text" placeholder="Tìm kiếm">';
        $out .= '<div id="'.$map_canvas_id.'" class="hw-map-canvas"></div>';

        $out .= '
        <style>
            .hw-map-canvas{
                '.($width? "width:{$width};" : '').'
                '.($height? "height:{$height};" : '').'
            }
        </style>
        <script>
        jQuery(function($){
            google.maps.event.addDomListener(window, "load", function(){
                __hw_module_map.map_initialize("#'.$map_canvas_id.'","#'. $input_box_id .'",'.$location.');
            });
        });

        </script>
        ';
        $out .= '</div>';
        return $out;
    }
    public static function __init() {

    }
}