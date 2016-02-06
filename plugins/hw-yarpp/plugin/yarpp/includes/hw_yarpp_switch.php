<?php
if (!isset($_GET['go']) || trim($_GET['go']) === '') die();

include_once(realpath('../../../../').'/wp-config.php');
$switch = htmlentities($_GET['go']);

function switchYarppPro($status){
    $yarppPro   = get_option('hw_yarpp_pro');
    $yarpp      = get_option('hw_yarpp');

    if($status){
        $yarppPro['optin']                   = (bool) $yarpp['optin'];
        $yarpp['optin']                      = false;
    } else {
        $yarpp['optin']                   = (bool) $yarppPro['optin'];
    }

    $yarppPro['active'] = $status;
    update_option('hw_yarpp',$yarpp);
    update_option('hw_yarpp_pro',$yarppPro);

    header("HTTP/1.1 200");
    header("Content-Type: text/plain; charset=UTF-8");
    die('ok');
}

switch ($switch){
    case 'basic':
        switchYarppPro(0);
        break;
    case 'pro':
        switchYarppPro(1);
        break;
}