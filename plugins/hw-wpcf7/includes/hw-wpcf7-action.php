<?php
/**
 * Class HWCF7_Events
 */
class HWCF7_Events{
    /**
     * constructor
     */
    public function __construct() {
        $this->setup_actions();     //init hooks
    }

    /**
     * setup actions
     */
    public function setup_actions() {
        add_action("wpcf7_before_send_mail", array($this, "_hw_wpcf7_do_something"));
        add_action('wpcf7_mail_sent', array($this, '_redirect_after_form_sent'));
        //add_filter('hwwpcf7_field_value', array($this, '_hwwpcf7_field_value'),10,2);
    }

    /**
     * mail sent event
     * @param $form: current contact form object
     */
    public function _redirect_after_form_sent($form) {
        $properties = $form->get_properties();  //get form properties
        $sent_ok_redirect = $form->prop('hw_sent_ok_redirect_page');
        if($sent_ok_redirect != '-1') {
            //redirect to page after form sent mail
            $page_url = get_permalink($sent_ok_redirect);
            if($page_url) {
                header('Location: '.$page_url);
                die();
            }

        }
    }
    /**
     * contact form submision
     * @param object $WPCF7_ContactForm: current contact form object
     */

    public function _hw_wpcf7_do_something ($WPCF7_ContactForm) {
        /* Use WPCF7_Submission object's get_posted_data() method to get it. */
        $properties = $WPCF7_ContactForm->get_properties();
        //get google form ID of this form
        $gformID = $properties['hw_gformID'];

        // get the contact form object
        $wpcf7 = WPCF7_ContactForm::get_current();

        if(isset($properties['enable_email_by_gapp']) && $properties['enable_email_by_gapp']){
            //  do not send the email
            $wpcf7->skip_mail = true;       //turn off default send mail by wpcf7, use google drive instead
        }
        $atts = $WPCF7_ContactForm->form_scan_shortcode();

        $fields_title = array();    //fields title
        $data =array(); //fields value

        //submission data
        $submission = WPCF7_Submission::get_instance();
        if ( $submission ) {
            //get storage service
            $storage_hook = $WPCF7_ContactForm->prop('hwcf_data_hook');

            //get posted form data
            $posted_data = $submission->get_posted_data();

            //parse email template into user data
            $mail_temp = $WPCF7_ContactForm->prop( 'mail' );
            $result = wpcf7_mail_replace_tags( $mail_temp );
            $admin_email = !empty($mail_temp['recipient'])? $mail_temp['recipient'] : get_option('admin_email');

            //set special field value
            $special_fields_value['sendEmail'] = $result['body'];
            $special_fields_value['admin_email'] = $admin_email;   #admin email
            $special_fields_value['website'] = hw_wpcf7_current_page_url();   #site url

            foreach($atts as $field){     //loop each field
                $tag = new WPCF7_Shortcode( $field );
                $name = $tag->name; //get field name
                if($tag->has_option('gfield') && $tag->type != 'hw_wpcf7_special_fields'){
                    if($tag->get_option('gfield','',true))
                    {
                        $name = $tag->get_option('gfield','',true);
                        //modify field value
                        $data[$name] = (apply_filters('hwwpcf7_field_value', $posted_data[$tag->name], array('name'=>$name,'tag'=>$tag, 'data'=>&$data, 'wpcf7'=>$wpcf7)));
                        /*if(isset($_POST['product_id']) && $tag->name=='order_detail'){
                            $sp=get_post($_POST['product_id']);
                            $data[$name] = '[ID='.$sp->ID.']'.PHP_EOL.$sp->post_title.PHP_EOL.get_permalink($sp->ID);
                        }*/
                        //else $data[$name] = $posted_data[$tag->name];
                    }
                }
                /**
                 * get field title
                 */
                if($tag->has_option('placeholder') && $tag->type != 'hw_wpcf7_special_fields'){
                    $fields_title[$name] = (string) reset( $tag->values );#$tag->get_option('placeholder','',true);
                }
                /**
                 * special tag to get special fields
                 */
                if($tag->type == 'hw_wpcf7_special_fields'){
                    foreach(HW_WPCF7::$special_gfields as $fname => $desc){
                        if($tag->has_option($fname) && isset($special_fields_value[$fname])) {
                            $data[$tag->get_option($fname,'',true)] = $special_fields_value[$fname];    //add special field value to data
                        }
                    }
                }
            }
            //storage
            if($storage_hook == 'google_form'){
                //get google form id
                $gform_id = $WPCF7_ContactForm->prop('hw_gformID');
                //from google spreadsheet as responses that link to google form. Create event onSubmitForm. you can send mail using google script.
                hw_wpcf7_post_gform($gform_id, $data);
            }
            elseif($storage_hook == 'url'){
                $hook_url = $WPCF7_ContactForm->prop('hook_url');  //web hook url
                $data['labels'] = serialize($fields_title);    //nest labels for all fields in one data together
                HW_CURL::curl_post($hook_url,$data);
            }
            /*hw_mail(array(
             'subject'=>'Khách hàng liên hệ từ '.home_url(),
             'body'=>$body
            ));*/
        }
    }
    /**
     * more contact form fields modification
     * @param string $name: google form field
     * @param object $tag: instance of WPCF7_Shortcode
     * @param unknown $data: data of fields value that will send to storage hook
     * @param unknown $wpcf7: contact form object
     * @return boolean: if you want using new fields then return true
     */
    function _hwwpcf7_field_value($value, $args){
        $tag = $args['tag'];
        $name = $args['name'];
        //$data = &$args['data'];
        $wpcf7 = $args['wpcf7'];

        if(isset($_POST['product_id']) && $tag->name=='order_detail'){
            $sp=get_post($_POST['product_id']);
            $value = '[ID='.$sp->ID.']'.PHP_EOL.$sp->post_title.PHP_EOL.get_permalink($sp->ID);
        }
        return $value;
    }
}

if(!is_admin()) {
    new HWCF7_Events();
}
//see: hw-cf-action.php
