<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/10/2015
 * Time: 16:46
 */
/**
 * Class HW_WPCF7_Frontend
 */
class HW_WPCF7_Frontend {
    /**
     * save current working contact form 7 object
     */
    private $current = null;

    /**
     * @var HW_WPCF7|null
     */
    protected $setting = null;
    /**
     * keep filters' handles
     */
    private $keep_filter_handles = array();

    /**
     * main constructor
     */
    public function __construct() {
        $this->setting = HW_WPCF7::get_instance();
        //init hooks
        $this->setup_actions();
    }

    /**
     * setup actions
     */
    public function setup_actions() {
        /*form render*/
        //generate form shortcode into HTML
        add_filter('wpcf7_form_elements', array($this,'_hw_wpcf7_form_elements'));

        //form class
        add_filter('wpcf7_form_class_attr', array($this, '_hw_wpcf7_form_class_attr'));
        add_filter('wpcf7_form_id_attr', array($this, '_hw_wpcf7_change_form_id'));
        add_filter('wpcf7_form_name_attr', array($this, '_hw_wpcf7_change_name'));
        add_filter('wpcf7_form_enctype', array($this, '_hw_wpcf7_change_form_enctype'));

        add_filter('wpcf7_ajax_loader', array($this, '_hw_wpcf7_change_ajax_loader'));  //enable ajax

        add_action('wp_enqueue_scripts', array($this, '_hw_wpcf7_enqueue_scripts')); //remove wpcf7 stylesheet
        add_action('wpcf7_contact_form', array($this, '_hw_wpcf7_contact_form'));    //instance contact form hook
    }
    /**
     * format css string
     * @param string $css_str: css text
     */
    public function format_custom_css($css_str){
        $class = isset($this->form_class)? '.'.$this->form_class.' ' : '';
        return str_replace('{form_class}',$class,$css_str);
    }
    /**
     * remove wpcf7 stylesheet, Remove the default Contact Form 7 Stylesheet
     * @hook wp_enqueue_scripts
     */
    public function _hw_wpcf7_enqueue_scripts(){
        $active_def_css = hw_wpcf7_option('enable_wpcf7_css','1');    //default enable if not defined
        if(!$active_def_css){
            wp_dequeue_style('contact-form-7');     //remove contact form 7 style
        }
        if(!hw_wpcf7_option('enable_wpcf7_js','1')){      //default enable if not defined
            wp_dequeue_script('contact-form-7');	#remove contact form 7 script

        }
    }
    public function get_form_skin() {

    }
    /**
     * when isntance contact form
     * @param $contact_form
     */
    public function _hw_wpcf7_contact_form($contact_form){
        /*if($this->current && $this->current->id() != $contact_form->id()){  //new contact form

        }*/
        $this->current = $contact_form; //save last current contact form
    }
    /**
     * modify form class attribute display on website
     * @param $class
     */
    public function _hw_wpcf7_form_class_attr($class){
        $form = $this->current; //current contact form object
        $wpcf_id = $this->current->id();     //contact form id
        $form_class = $form->prop('hw_form_class_attr');  //form class attr
        //$form = WPCF7_ContactForm::get_instance($this->current->id());  //get wpcf7 by id

        $class .= ' hw-wpcf7-'.$wpcf_id;      //generate current form class
        //get current contact forn skin
        $skin = $form->prop('hw_wpcf7_skin');            #$skin = $this->current->prop('hw_wpcf7_skin');
        $priority = 'hwcf7-'.$skin;      //purpose of given priority for handler to hooks of current skin, (required)
        //
        global $wp_filter;

        $file = $this->setting->skin->get_skin_file($skin);
        if($skin && //if exists skin name
            file_exists($file)){

            $theme_setting = $this->setting->skin->get_file_skin_setting();  //extract theme setting from skin
            //current skin options
            $skin_setting = $form->prop('hw_wpcf7skin_setting') ;
            if($skin_setting) $options = $skin_setting['skin_options'];
            else $options = array();

            $skin_options = $this->setting->skin->get_file_skin_options();

            if(file_exists($theme_setting)) include($theme_setting);
            if(file_exists($skin_options)) include ($skin_options);

            //first load skin & init hooks
            include ($file);
            //note add hook does't work if include statement inside a function with 2 level
            #$skin_data = HW_SKIN::include_skin_file($file);    //note that you need to include once to prevent duplicate function in skin file
            /*if(!empty($skin_data) && is_array($skin_data)) {
                extract($skin_data);
            }*/
            //parse theme options
            if(isset($theme) && isset($theme['options'])) $default_options = $theme['options']; //default options
            else $default_options = array();

            if( isset($theme_options)) {
                $options = HW_SKIN::merge_skin_options_values($options, $default_options, $skin_options);
            }
            if(!empty($options['form_class_attr'])) $class .= ' '.$options['form_class_attr'];
            /*
             *solution is: different skin use different function name, to prevent php error
             *
            * */
            //put each skin with each handle & stored in this context
            if(isset($wp_filter['hw_wpcf7_form_class_attr']) && count($wp_filter['hw_wpcf7_form_class_attr'])){
                if(!isset($this->keep_filter_handles[$skin]) && isset($wp_filter['hw_wpcf7_form_class_attr']['hwcf7-'.$skin])) {
                    $this->keep_filter_handles[$skin] = $wp_filter['hw_wpcf7_form_class_attr']['hwcf7-'.$skin];
                }
            }
            remove_all_filters('hw_wpcf7_form_class_attr');     //unbind all handles for the filter
            $wp_filter['hw_wpcf7_form_class_attr']['hwcf7-'.$skin] = $this->keep_filter_handles[$skin];     //re-assign hook handle for this skin

            //finally call the hook
            $class .= ' '.trim(apply_filters('hw_wpcf7_form_class_attr'/*,$class*/,null));  //filter form class, don't pass & combine param to handle in return statement. It cause concat values by all handlers bind to this filter

        }
        if($form_class) $class .= " {$form_class}";

        return $class;
    }
    /**
     * form HTML to output on website, but we will include selected skin for this form
     */
    public function _hw_wpcf7_form_elements($html){
        $skin = $this->current->prop('hw_wpcf7_skin');  //get current contact form skin
        $custom_css = $this->current->prop('hw_custom_css');    //get custom css for this form

        $enable_skin = $this->current->prop('hw_wpcf7_use_skin');   //check whether this skin is active for the contact form

        if($enable_skin !== 'on') return $html; //don't use private skin for this form

        //get skin file
        $file = $this->setting->skin->get_skin_file($skin);
        if(file_exists($file)) {
            $theme[] = array();
            ob_start();
            include_once($file);

            if(!isset($theme['styles'])) $theme['styles'] = array();
            if(!isset($theme['scripts'])) $theme['scripts'] = array();

            /*if(count($theme['styles']) || count($theme['scripts'])){    //enqueue stuff from skin
                $this->setting->skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
            }*/
            //enqueue stuff from skin
            HW_SKIN::enqueue_skin_assets(array(
                'instance' => $this->setting->skin,
                'hash_skin' => $skin,
                'skin_file' => $file,
                'theme_settings' => $theme
            ));
            //addition css
            echo '<style>';
            echo $this->format_custom_css($custom_css);
            echo '</style>';
            do_action('hw_wpcf7_contact_form_css',$this->current);
            $content = ob_get_contents();
            ob_clean();
            $html .= $content;

        }
        return $html;
    }
    /**
     * change form id attribute
     * @hook wpcf7_form_id_attr
     * @param $id
     * @return mixed
     */
    public function _hw_wpcf7_change_form_id($id) {
        $form = $this->current; //current contact form object
        $wpcf_id = $this->current->id();     //contact form id
        //get form id attribute
        $form_id = $form->prop('hw_form_id_attr');
        if($form_id) return $form_id;

        return $id;
    }

    /**
     * change form name attribute
     * @hook wpcf7_form_name_attr
     * @param $name
     * @return mixed
     */
    public function _hw_wpcf7_change_name($name) {
        $form = $this->current; //current contact form object
        $wpcf_id = $this->current->id();     //contact form id
        //get form name attribute
        $form_name = $form->prop('hw_form_name_attr');
        if($form_name) return $form_name;

        return $name;
    }

    /**
     * change form enctype
     * @hook wpcf7_form_enctype
     * @param $enctype
     * @return mixed
     */
    public function _hw_wpcf7_change_form_enctype($enctype) {
        $form = $this->current; //current contact form object
        //get form name attribute
        $form_enctype = $form->prop('hw_form_enctype_attr');
        if($form_enctype) return $form_enctype;

        return $enctype;
    }
    /**
     * change contact form ajax loader
     * @hook wpcf7_ajax_loader
     */
    public function _hw_wpcf7_change_ajax_loader($src){
        $ajax_loader = hw_wpcf7_option('loadingImg');
        if(count($ajax_loader)) $ajax_loader = HW_APF_Field::get_skin_link($ajax_loader);
        else $ajax_loader = get_template_directory_uri() . '/images/ajax-loader.gif';

        return  $ajax_loader;  //change ajax loading for wpcf7
    }
}
function ham1($file){
    include($file);
}