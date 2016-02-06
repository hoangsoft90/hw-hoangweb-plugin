<?php
//array utilities
include_once ('class-core-Array.list_items_path.php');
include_once ('class-core-Array.replace_array.php');
/**
 * @class hwArray
* Array utilities
*/
class hwArray {
    /**
     * @param $source
     * @param $insert
     * @param $key
     * @param bool $after
     * @return array
     */
    public static function array_insert_by_key ($source, $insert,$key,$after=true) {
        if(!is_array($source)) return;
        $pos = array_search($key, array_keys($source));
        if($after) $pos+=1;
        $seg1 = array_slice($source,0,$pos);
        $seg2 = array_slice($source,$pos);
        $seg1 = array_merge($seg1, $insert);
        return $seg1+$seg2;
    }
    /**
     * clone array of object
     * @param $arr
     * @return array
     */
    public static function cloneArray($arr) {
        $new = array();

        foreach ($arr as $k => $v) {
            if(is_object($v)) $new[$k] = clone $v;
            else $new[$k] = $v;
        }
        return $new;
    }
    /**
    * convert object to array
    * @param object $d: object,ie: object(array("a"=>"b"))
    */
    function objectToArray($d){
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
 
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }
    /**
    * json decode
    * @param string $s: json string
    */
    static function _json_decode($s){
        $s= utf8_encode($s);
        return json_decode(stripslashes($s),true);
    }
    /**
    * list all array keys, note: keys not contain dot '.' character
    * @param array $array: array
    */
    function list_all_keys($array)
    {
        if(is_array($array))
        {
            //result
            $result=array();    //instance DRW
            $paths=new list_items_path($array);    //instance list_items_path class
            $paths=$paths->get();    //get paths to items of array
            foreach($paths as $path)
            {
                $result[$path]='';
            }
            return array_keys($result);    //return all keys in array
        }
    }

    /**
     * remove unicode sequences from json string
     * @param $struct
     * @return mixed
     */
    public static function jsonRemoveUnicodeSequences($struct) {
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($struct));
    }

    /**
     * convert json from array
     * @param $data
     */
    public static function json_output($data) {
        return htmlspecialchars_decode(json_encode($data));
        //return htmlspecialchars(json_encode($data), ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * determine is multi-dimension array
     * @param $a
     * @return bool
     */
    public static function is_multi($a) {
        if(!is_array($a)) return false;
        $rv = array_filter($a,'is_array');
        if(count($rv)>0) return true;
        return false;
    }

    /**
     * convert multi-dimen array to single
     * @param $data
     * @return array
     */
    public static function multi2single($data) {
        $result=  array();
        if(self::is_multi($data) || is_array($data)) {
            foreach($data as $key=> $val) {
                if(is_array($val)) {
                    $result=array_merge($result,self::multi2single($val));
                }
                else $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * @param array $data
     * @param string $key
     * @param $val
     * @param int|string $pos
     * @return array
     */
    public static function add_item_keyval($data, $key, $val, $pos = 1000, $before=false,$search_value=false) {

        $first_key = array_search($key, $data);
        if(!is_numeric($pos)) $second_key = array_search($pos, $search_value? $data: array_keys($data));
        else $second_key = $pos;
        if(!is_numeric($pos) /*&& $second_key == 0*/ && $before == false) $second_key++; //don't index should start from 0
        //array_splice($data, $first_key, 1);    //remove first plugin from list
        if(is_numeric($second_key))
        {
            $data = (array_slice($data, 0, $second_key, true)
                + array($key=>$val)
                + array_slice($data, $second_key, count($data) , true)); //tobe sure get all plugin num ->don't -1

        }
        return $data;
    }
    /**
     * @param $d
     * @return string
     */
    public static function drwOfPath($d){
        $a='';
        if(is_array($d))
        foreach($d as $v) $a.='['.(is_numeric($v)? $v : '"'.$v.'"').']';
        return $a;
    }

    /**
     * sort array by other array
     * @param array $array
     * @param array $orderArray
     * @return array
     */
    public static function sortArrayByArray(Array $array, Array $orderArray) {
        $ordered = array();
        foreach($orderArray as $key) {
            if(array_key_exists($key,$array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    /**
     * split loop into segments
     * @param $per
     * @param $total
     * @return array
     */
    public static function split_loop_segments($per, $total) {
        $data=array();
        $step= (int)$per;
        $last=0;
        $max= (int) $total;
        for($i=0;$i<$max;$i++){
            if($i%$step==0) {
                $start= ($i?$i+1:0);
                $end = $i+$step;
                if($end>$max) $last=$end=$max;
                else $last=$i;

                if($start!=$end) $data[]=($start).'-'.($end);
            }

        }
        if($last<$max && $end <$max) $data[]=($last+1).'-'. ($max);
        return $data;
    }

    /**
     * convert array to html attributes
     * @param $data
     * @return string
     */
    public static function array_to_attrs($data) {
        $str = '';
        if(is_array($data))
        foreach ($data as $key=> $value) {
            $str .= $key .'="'. addslashes($value) .'" ';
        }
        return trim($str);
    }
}
?>