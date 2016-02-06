<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/10/2015
 * Time: 15:52
 */
//class attr
$theme_options[] = array(
    'name' => 'form_class_attr',
    'type' => 'text',
    'description' => 'Thuộc tính class cho thẻ form.',
    'method' => 'append',
    'value' => "hw-wpcf7-class hw-wpcf-default"
);
//id attr
$theme_options[] = array(
    'name' => 'form_id_attr',
    'type' => 'text',
    'description' => 'Thuộc tính id cho thẻ form.',
    'method' => 'override'
);
//name attr
$theme_options[] = array(
    'name'=> 'form_name_attr',
    'type' => 'text',
    'description' => 'Thuộc tính name cho thẻ form.',
    'method' => 'override'
);
//enctype attr
$theme_options[] = array(
    'name' => 'form_enctype_attr',
    'type' => 'select',
    'description' => 'Thuộc tính enctype cho thẻ form.',
    'options' => array(
        '' => '---- Chọn ----',
        'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
        'multipart/form-data' => 'multipart/form-data',
        'text/plain' => 'text/plain'
    ),
    'method' => 'override'
);