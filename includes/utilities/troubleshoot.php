<?php
#/root>hw-functions.php

/**----------------------------multilanguage--------------------------------------
* check if qtranslate is installed
*/
if(function_exists('qtrans_convertURL')){
    //fix home_url if qtranslate plugin installed
    function qtrans_convertHomeURL($url, $what) {
        if($what=='/') return qtrans_convertURL($url);
        return $url;
    }
    add_filter('home_url', 'qtrans_convertHomeURL', 10, 2);
}
