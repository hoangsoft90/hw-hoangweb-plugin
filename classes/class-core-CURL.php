<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 09:53
 */
class HW_CURL{
    /**
     * fetch url content through GET method
     * @param $url
     * @param $curl_opt_array: curl extra option
     * @return mixed
     */
    public static function curl_get($url, $curl_opt_array = array()){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER , 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  //ssl
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  //ssl
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if(is_array($curl_opt_array) && count($curl_opt_array)){
            curl_setopt_array($curl, $curl_opt_array);
        }
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

    /**
     * send post request to url
     * @param $url
     * @param $posted: posted data
     * @param $curl_opt_array: curl extra option
     * @param $posted
     */
    public static function curl_post($url, $posted, $curl_opt_array = array()){
        // Get cURL resource
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            //CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $posted
        ));
        if(is_array($curl_opt_array) && count($curl_opt_array)){
            curl_setopt_array($curl, $curl_opt_array);
        }
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return $resp;
    }
}