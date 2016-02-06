<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 20/10/2015
 * Time: 16:40
 */
/**
 * @param $path
 * @param string $exclude
 * @param bool $recursive
 * @return array
 */
function hw_acak($path, $exclude = ".|..|.svn|.DS_Store", $recursive = true) {
    $path = rtrim($path, "/") . "/";
    $folder_handle = opendir($path) or die("Eof");
    $exclude_array = explode("|", $exclude);
    $result = array();
    $done= array();
    while(false !== ($filename = readdir($folder_handle))) {
        if(!in_array(strtolower($filename), $exclude_array)) {
            if(is_dir($path . $filename . "")) {
                if($recursive) $result[] = hw_acak($path . $filename . "", $exclude, true);
            } else {
                if ($filename === '0.gif') {
                    if (!isset($done[$path])) {
                        $result[] = $path;
                        $done[$path] = 1;
                    }
                }
            }
        }
    }
    return $result;
}
