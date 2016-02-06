<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 22:25
 */
class HW_String {
    /**
     * remove unicode character in string
     * @param $str: target string
     * @return mixed
     */
    public static function vn_str_filter ($str){
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ', 'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        return $str;
    }
    /**
     * tidy html cleaning or valid html tags
     * @param string $string: content of html
     * @param array $tidyConfig: tidy setting (optional)
     */
    public static function tidy_cleaning($string,$tidyConfig = null,$return = ''){
        $out = array ();
        $config = array (
            'indent' => true,
            'show-body-only' => false,
            'clean' => true,
            'output-xhtml' => true,
            'preserve-entities' => true
        );
        if ($tidyConfig == null) {
            $tidyConfig = &$config;
        }
        if(!class_exists('tidy')) {
            return ;
        }
        $tidy = new tidy ();
        $out ['full'] = $tidy->repairString ( $string, $tidyConfig, 'UTF8' );
        unset ( $tidy );
        unset ( $tidyConfig );
        $out ['body'] = preg_replace ( "/.*<body[^>]*>|<\/body>.*/si", "", $out ['full'] );
        $out ['style'] = '<style type="text/css">' . preg_replace ( "/.*<style[^>]*>|<\/style>.*/si", "", $out ['full'] ) . '</style>';
        if($return && isset($out[$return])) return ($out[$return]);
        else return $out;
    }

    /**
     * generate random string with limit length
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * remove empty lines from string
     * @param $string
     * @return mixed
     */
    public static function truncate_empty_lines($string){
        // New line is required to split non-blank lines
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", trim($string));
    }

    /**
     * display limit string
     * @param $str
     * @param int $leng
     */
    public static function limit($str, $leng=100) {
        if(!is_numeric($leng)) $leng = 100; //valid
        if(strlen($str)>$leng) return mb_substr($str,0,$leng-3,'UTF-8'). '..';
        return $str;
    }
}