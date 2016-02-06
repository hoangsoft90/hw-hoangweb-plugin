<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 1/3/16
 * Time: 2:38 PM
 */
function get_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}