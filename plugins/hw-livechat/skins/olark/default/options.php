<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 25/06/2015
 * Time: 09:05
 */
$theme_options[] = array(
    'name' => 'enable_demo_chat',
    'type' => 'checkbox',
    'description' => 'Kích hoạt form chat mẫu.'
);
$theme_options[] = array(
    'name' => 'inline',
    'type' => 'checkbox',
    'description' => 'box.inline'
);
//demo embed code
$theme_options[] = array(
    'name' => 'demo_embedcode',
    'type' => 'hidden',
    'value' => file_get_contents(plugin_dir_path(__FILE__).'/includes/embed.txt'),  //default value
    'method' => 'append',
    'description' => '' //unused
);
$theme_options[] = array(
    'type' => 'string',
    'description' => '<h3>Sự kiện</h3>'
);
$theme_options[] = array(
    'name' => 'api_box_onShow',
    'type' => 'textarea',
    'description' => 'event occur when chat box visible',
    //'value' => 'twesdf'
);
$theme_options[] = array(
    'name' => 'api_box_onHide',
    'type' => 'textarea',
    'description' => 'when a visitor closes the chat box'
);
$theme_options[] = array(
    'name' => 'api_box_onExpand',
    'type' => 'textarea',
    'description' => 'Watch for visitors clicking the chat box'
);
$theme_options[] = array(
    'name' => 'api_box_onShrink',
    'type' => 'textarea',
    'description' => 'Track when visitors minimize your Olark widget'
);
$theme_options[] = array(
    'name' => 'api_chat_onMessageToVisitor',
    'type' => 'textarea',
    'description' => 'On message to visitor.'
);
$theme_options[] = array(
    'name' => 'api_chat_onMessageToOperator',
    'type' => 'textarea',
    'description' => 'On message to operator. Track messages to operators'
);
$theme_options[] = array(
    'name' => 'api_chat_onBeginConversation',
    'type' => 'textarea',
    'description' => 'used to Notify an operator when a visitor closes the chat box'
);