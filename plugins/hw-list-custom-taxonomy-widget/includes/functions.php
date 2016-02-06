<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * list terms taxonomy
 * @param $tax
 * @param $args
 * @param $field default get term id as select value
 * @return array
 */
function hwlct_list_tax_terms($tax, $args = array(), $field = 'id') {
    HW_HOANGWEB::load_class('HW_POST');
    return HW_POST::list_tax_terms($tax, $args, $field);
}