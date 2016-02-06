<?php
/**
 * Class HW_WPCF7_Template
 */
class HW_WPCF7_Template{
    /**
     * store this instance
     */
    static $instance;

    /**
     * form templates
     * @var array
     */
    static $form_templates = array();

    /**
     * current template
     * @var
     */
    private $current_template;

    /**
     * constructor
     */
    public function __construct(){
        //must match HW_WPCF7::$form_templates
        //simple contact form
        self::$form_templates['basic-contact-form'] = array(
            'your-name' => array(
                'field'=> '[text* your-name placeholder "Tên của bạn"]',
                'label' => __('Tên của bạn (*)'),
                'mail-sender' => true
            ),
            'your-email' => array(
                'field'=> '[email* your-email placeholder "Email"]',
                'label' => __('Email(*)'),
                'mail' => true
            ),
            'your-subject' => array(
                'field'=>'[text your-subject placeholder "Chủ đề"]',
                'label' => __('Chủ đề'),
                'mail-subject' => true
            ),
            'your-message'=> array(
                'field' => '[textarea your-message placeholder "Tin nhắn"] ',
                'label' => __('Nội dung')
            ),
            'submit' => array(
                'field' => '[submit "Gửi đi"]'
            )
        );
        //order form
        self::$form_templates['order_form'] = array(
            'your-name' => array(
                'field'=> '[text* your-name placeholder "Tên của bạn"]',
                'label' => __('Tên của bạn (*)'),
                'mail-sender' => true
            ),
            'your-email' => array(
                'field'=> '[email* your-email placeholder "Email"]',
                'label' => __('Email(*)'),
                'mail' => true
            ),
            'your-company' => array(
                'field'=>'[text your-company placeholder "Công ty"]',
                'label' => __('Công ty'),
                'mail-subject' => true
            ),
            'your-product' => array(
                'field'=>'[text your-product placeholder "Sản phẩm"]',
                'label' => __('Sản phẩm'),
                'mail-subject' => true
            ),
            'productID' => array(
                'field' => '[dynamichidden product id:product "CF7_GET key=\'order_id\'"]'
            ),
            'your-message'=> array(
                'field' => '[textarea your-message placeholder "Tin nhắn"] ',
                'label' => __('Nội dung')
            ),
            'submit' => array(
                'field' => '[submit "Gửi đi"]'
            )
        );
    }

    /**
     * return your form templates
     * @param string $temp: form name
     */
    public function get_form_template($temp){
        if(isset (self::$form_templates[$temp])) {
            $this->current_template = self::$form_templates[$temp];
            $this->current_template = apply_filters('hwcf7_get_form_template', $this->current_template);
            return $this->current_template;    //self::$form_templates[$temp];
        }
    }

    /**
     * add new form type
     * @Param string$name: form name
     * @param Array $form: form fields definition
     */
    public static function register_form_fields($name, $form = array()){
        if(is_string($name) && is_array($form)) {
            self::$form_templates[$name] = $form;
        }
    }
    /**
     * parse form template fields
     * @param string $temp
     */
    public function parse_form_template_fields($temp = ''){
        if(is_string($temp) && $temp) $temp = $this->get_form_template($temp);
        else $temp = $this->current_template;

        if(is_array($temp)){
            $html = '';
            foreach($temp as $field){
                $html .= '<p>';
                if(isset($field['label'])) $html .= $field['label']."<br/>\n";
                $html .= str_repeat("\t",1).$field['field'];
                $html .= "</p>\n\n";
            }
            return $html;
        }
    }

    /**
     * generate mail template
     * @param $temp
     */
    public function mail_template($temp = ''){
        if(is_string($temp) && $temp) $temp = $this->get_form_template($temp);
        else $temp = $this->current_template;

        if(is_array($temp)){
            $mail = array(
                'recipient' => get_bloginfo('admin_email')
            );
            $fullname = '';
            $email = '';

            $field_values = "<ul>\n";

            foreach($temp as $name => $field){
                if(strpos($field['field'],"[submit") === false) {
                    $field_values .= "<li>{$field['label']}<br/>\n\t[{$name}]</li>\n";    //field value
                }

                //mail addition header
                if(isset($field['mail']) && $field['mail'] ==  true){
                    $mail['additional_headers'] = "Reply-To: [{$name}]";
                    $email = '['.$name.']';
                }
                //mail subject
                if(isset($field['mail-subject'])){
                    $mail['subject'] = "[{$name}]";
                }
                //mail sender
                if(isset($field['mail-sender'])){
                    $mail['sender'] = "[{$name}] <{$mail['recipient']}>";
                    $fullname = '['.$name.']';
                }

            }
            //mail body
            $body = "Gửi đến từ: {$fullname} <{$email}>\nTiêu đề: [your-subject]\n";
            $body .= $field_values.'</ul>';

            $mail['body'] =  $body;

            return $mail;
        }
    }
    /**
     * create & return this class instance
     * @return HW_WPCF7_Template
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}