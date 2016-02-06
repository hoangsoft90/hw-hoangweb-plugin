<?php
/**
 * @Class HW_Product
 */
class HW_Product extends HW_UI_Component{
    /**
     * @var array
     */
    var $attrs = array();

    /**
     * @var array
     */
    #static $products = array();

    /**
     * set product attributes
     * @param array $arr
     */
    public function set_attributes($arr = array()) {
        if(is_array($arr)) $this->attrs = $arr;
    }

    /**
     * return attributes data for certain product
     * @param $type
     */
    /*public function get($type) {
        if(is_string($type) && isset(self::$products[$type])) {
            return self::$products[$type];
        }
    }*/
    /**
     * format price
     * @param $price
     * @return string
     */
    public static function format_price($price, $currency = 'VNĐ') {
        return is_numeric($price)? number_format($price) .' '.trim($currency) : $price;
    }
    /**
     * get attributes
     * @param array $override
     */
    public function get_attributes($override= array()) {
        if(empty($this->attrs)) return;

        $atts = apply_filters('hw_product_attributes', $this->attrs);
        //merge user attrs
        if(is_array($override) && count($override)) {
            $atts = array_merge($atts ,$override);
        }
        return $atts ;
    }

    /**
     * @param callable $callback
     * @param callable $value_callback
     */
    public function loop_attr_callback($callback, $value_callback) {
        $atts = $this->get_attributes();
        add_filter('hw_attr_value', array($this, '_filter_product_price'), 10,2);

        if(is_array($atts) && count($atts)) {
            foreach ($atts as $name=> $text){
                $val = $name;
                //get attribute value
                if(is_callable($value_callback)) {
                    $val = call_user_func($value_callback, $name);
                }
                $val = apply_filters('hw_attr_'.$name, $val, $name);
                $val = apply_filters('hw_attr_value', $val, $name);
                //valid
                if(!$val) continue;

                //render loop
                if(is_callable($callback)) {
                    call_user_func($callback, array('name'=>$name, 'value'=>$val,'text'=> $text) );
                }
            }
        }
    }

    /**
     * @hook hw_attr_value
     * @param $value
     * @param $name
     * @return int|string
     */
    public function _filter_product_price($value, $name){
        if($name == 'price' || $name == 'gia') {
            $value = is_numeric($value)? number_format($value) : $value;
        }
        return $value;
    }
}