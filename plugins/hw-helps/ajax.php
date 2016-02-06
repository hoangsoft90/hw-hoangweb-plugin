<?php
//mimic the actuall admin-ajax (see: hw-hoangweb/ajax.php)
define('DOING_AJAX', true);

if (!isset( $_POST['action']))
    die('-1');

//make sure you update this line
//to the relative location of the wp-load.php
require_once(ABSPATH .'/wp-load.php');

//Typical headers
header('Content-Type: text/html');
send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');

/*Next we need to call the actual methods we want to invok*/
$action = esc_attr(trim($_POST['action']));

//A bit of security
$allowed_actions = array(
    //'custom_action1',
    //'custom_action2'
);

//For logged in users
#add_action('HW_custom_action1', 'handler_fun1');
#add_action('HW_custom_action2', 'handler_fun1');

//For guests
#add_action('HW_nopriv_custom_action2', 'handler_fun2');
#add_action('HW_nopriv_custom_action1', 'handler_fun1');

if(!empty($allowed_actions) && in_array($action, $allowed_actions)) {
    if(is_user_logged_in())
        do_action('HW_'.$action);
    else
        do_action('HW_nopriv_'.$action);
} else {
    die('-1');
}

?>