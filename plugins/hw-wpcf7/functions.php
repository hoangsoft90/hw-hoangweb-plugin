<?php 
/**
 * send email using PHPMailer from wp library
 * @param unknown $args: from,FromName,to,toName,subject,body,AltBody
 */
function hw_wpcf7_mail($args){
    $from = isset($args['from'])? $args['from'] : get_option('admin_email');
    $FromName = isset($args['FromName'])? $args['FromName'] : get_bloginfo('name');
    $to = isset($args['to'])? $args['to'] : get_option('admin_email');
    $toName = isset($args['toName'])? $args['toName'] : '';
    $subject = isset($args['subject'])? $args['subject'] : '';
    $body = isset($args['body'])? $args['body'] : '';
    $AltBody = isset($args['AltBody'])? $args['AltBody'] : '';
    //valid
    if(!$to) return;
    
    #require_once '/PHPMailer/PHPMailerAutoload.php';
    #load phpmailer from wordpress system
    require_once ABSPATH . WPINC . '/class-phpmailer.php';
    require_once ABSPATH . WPINC . '/class-smtp.php';
    
    $mail = new PHPMailer(true);
    
    //$mail->SMTPDebug = 3;                               // Enable verbose debug output
    
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.mail.yahoo.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'quachhoang_2005@yahoo.com';                 // SMTP username
    $mail->Password = 'Hoangcode837939';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to
    
    $mail->From = $from;
    $mail->FromName = $FromName;
    $mail->addAddress($to, $toName);     // Add a recipient
    $admin_mail = get_option('admin_email');
    $mail->addReplyTo($admin_mail, get_bloginfo('name'));
    
    
    $mail->isHTML(true);                                  // Set email format to HTML
    
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = $AltBody;
    
    if(!$mail->send()) {
        return false;
    } else {
        /*echo 'Message has been sent';*/
        return true;
    }
}
/**
 * post record into google form response
 * @param string $gform_id: google form ID
 * @param array $posted_data: data present record in google spreadsheet that store for google form
 */
function hw_wpcf7_post_gform($gform_id,$posted_data = array()){

    //save form data
    $url_action = hw_wpcf7_valid_gform_response_url($gform_id);

    return HW_CURL::curl_post($url_action, $posted_data);
}

/**
 * @param $gform_id
 * @return string
 */
function hw_wpcf7_valid_gform_url($gform_id) {
    if(filter_var($gform_id, FILTER_VALIDATE_URL) === false) {
        return 'https://docs.google.com/forms/d/'.$gform_id.'/viewform';
    }
    return $gform_id;
}

/**
 * get form response url
 * @param $url
 * @return mixed|string
 */
function hw_wpcf7_valid_gform_response_url($url) {
    if(!filter_var($url, FILTER_VALIDATE_URL) === false) {
        if(preg_match('%docs\.google\.com\/.+?forms\/d\/(.*)\/viewform%', $url)) {
            return str_replace( '/viewform', '/formResponse',$url);
        }
    }
    else $url= 'https://docs.google.com/forms/d/'.$url.'/formResponse';
    return $url;
}
/**
 * return default google form ID
 * @return string
 */
function hw_wpcf7_default_gform() {
    return '1GzynAtb3hiv6E0mFE0KhxMwARSYGdGSY8oJ5ImGM7m4';
}

/**
 * @param $url
 * @return string
 */
function hw_wpcf7_get_gformID($url) {
    if(filter_var($url, FILTER_VALIDATE_URL) === false) {
        preg_match('%docs\.google\.com\/.+?forms\/d\/(.*)\/%', $url, $results);
        return count($results)? $results[1] : "0";
    }

    return $url;
}
/**
 * get wpcf7 apf option
 * @param string $opt: give name of option want to getting
 * @param string $default: default value
 * @param string $group: group section name
 */
function hw_wpcf7_option($opt,$default='',$group = 'general'){
    if(class_exists('AdminPageFramework')) return AdminPageFramework::getOption( 'HW_Wpcf_settings', array($group,$opt), $default );
}
/**
 * return current page url
 */
function hw_wpcf7_current_page_url(){
    global $wp;
    return home_url(add_query_arg(array(),$wp->request));
}
/**
Email Setting
 */
add_filter( 'wp_mail_content_type', 'set_html_content_type' );
function set_html_content_type() {
    return 'text/html';
}
/*---------------------------practice----------------------------*/

//draft
function hw_msg($t){
    @session_start();
    if(!isset($_SESSION['bb']) || !is_array($_SESSION['bb'])) $_SESSION['bb']=array();
    $_SESSION['bb'][]=$t;

}
