<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 08/12/2015
 * Time: 22:50
 */
//https://github.com/rstacruz/nprogress
$theme_options[] = array(
    'name' => 'minimum',
    'type'=>'text',
    'value' => '0.1',
    'description' => ''
);
$theme_options[] = array(
    'name' => 'easing',
    'type'=>'select',
    'options' => array('ease'=> 'ease'),
    'description' => ''
);
$theme_options[] = array(
    'name' => 'speed',
    'type'=>'text',
    'value' => '500',
    'description' => ''
);
$theme_options[] = array(
    'name' => 'trickle',
    'type'=>'checkbox',
    'value' => '0',
    'description' => ''
);
$theme_options[] = array(
    'name' => 'trickleRate',
    'type'=>'text',
    'value' => '0.02',
    'description' => 'how much to increase per trickle'
);
$theme_options[] = array(
    'name' => 'trickleSpeed',
    'type'=>'text',
    'value' => '800',
    'description' => 'how often to trickle, in ms'
);
$theme_options[] = array(
    'name' => 'showSpinner',
    'type'=>'checkbox',
    'value' => '1',
    'description' => 'Turn off loading spinner by setting it to false'
);
