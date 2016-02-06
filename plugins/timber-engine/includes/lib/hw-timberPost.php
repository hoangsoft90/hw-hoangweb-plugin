<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/12/2015
 * Time: 10:27
 */
if(class_exists('TimberPost')):
class HW_TimberPost extends TimberPost {
    /**
     * @param $post
     */
    public function __construct($post){
        if(is_numeric($post)) $pid = $post;
        elseif(/*$post instanceof WP_Post*/isset($post->ID)) $pid = $post->ID;

        if(isset($pid)) $this->init($pid);
    }


}
endif;