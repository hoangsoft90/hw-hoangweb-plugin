<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/12/2015
 * Time: 10:23
 */
/**
 * Class HW_TimberTerm
 */
if(class_exists('TimberTerm')):
class HW_TimberTerm extends TimberTerm {
    /**
     * @var
     */
    private $term_class;

    /**
     * @param $class
     */
    function add_class($class) {
        if(is_string($class)) $this->term_class = $class;
    }

    /**
     * get term classes
     */
    public function term_class() {
        return $this->term_class;
    }
}
endif;