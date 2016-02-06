<?php
/**
 * Class HW_Validation
 */
class HW_Validation {
    /**
     * valid object name
     * @param string $name: string name
     * @return valid object name
     */
    public static function valid_objname($name){
        $delimiter = '_';
        return preg_replace('/[\s,.\[\]\/\\\#\*@$%^\!~\-\+\=]+/',$delimiter,$name);
    }
    /**
     * valid a file name
     * @param unknown $file
     */
    public static function valid_filename($file,$replace ='-'){
        return preg_replace('#[\/,\\|]+#', $replace, $file);
    }
    /**
     * valid url
     * @param $url
     */
    public static function hw_valid_url($url){
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        }
        /*if(preg_match('[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?',$url)){
            return true;
        }*/
    }

    /**
     * clean url
     * @param $url
     * @return string
     */
    public static function  clean_url($url) {
        if(self::hw_valid_url($url)) {
            $data = parse_url($url);
            $new_url = '';
            foreach($data as $info => &$val) {
                if($info == 'path') {
                    $val = preg_replace('#[\/\\\]+#','/', $val);
                }
                if($info == 'scheme') $new_url .= $val. '://';
                elseif($info == 'query') $new_url .= '?'. $val;
                elseif($info == 'fragment') $new_url .= '#'. $val;
                else $new_url .= $val;
            }
            $url = $new_url;
        }
        return $url ;
    }
    /**
     * return valid field name from string
     * @param $str:
     * @return mixed
     */
    static public function valid_apf_slug($str){
        if(is_string($str)) $str = preg_replace('#[\s@\#\$\!%\^\&\*\(\)\-\+\[\]\=\~]#','_',$str);
        //$str = preg_replace('#-{2,}#','_',$str);
        return $str;
    }
    /**
     * format number
     * @param $value: value to format
     * @param $unit: unit in px or %, em...(default px)
     */
    public static function format_unit($value,$unit = 'px'){
        return (is_string($value) && substr(trim($value),-1) == '%')? $value : floatval($value).$unit;
    }
    /**
     * valid classes attribute value
     * @param array|string $classes
     * @return string
     */
    public static function valid_classes_attr($classes) {
        if(is_string($classes)) {
            $classes = preg_split('#[\s]+#', trim($classes));
        }
        $classes = array_flip(array_flip($classes) );

        #return str_replace('#[\s]+#', ' ', join(' ',$classes));
        return $classes;
    }
    /**
     * check if valid URL that ends in .jpg, .png, or .gif
     * @param $file
     * @return int
     */
    public static function valid_image_ext($file) {
        #return preg_match("|(?:([^:/?#]+):)?(?://([^/?#]*))?([^?#]*\.(?:jpg|gif|png))(?:\?([^#]*))?(?:#(.*))?|", $file);
        return preg_match("#(?i)\.(jpg|png|gif)$#", $file);
    }
}