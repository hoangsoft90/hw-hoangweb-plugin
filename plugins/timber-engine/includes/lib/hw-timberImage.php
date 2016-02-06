<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 09/12/2015
 * Time: 16:52
 */
/**
 * Class HW_TimberImage
 */
class HW_TimberImage extends  TimberImage{
    /**
     * class attribute for image
     * @var
     */
    public $class;
    /**
     * @param $attachment_id
     */
    public function __construct($attachment_id) {
        parent::__construct($attachment_id);
    }
    public function get_image_thumbnail() {

    }
    public function image_thumbnail() {
        $this->get_image_thumbnail();
    }
}