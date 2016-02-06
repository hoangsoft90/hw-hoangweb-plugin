<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 29/05/2015
 * Time: 15:01
 */
/**
 * Class HW_Breadcrumb
 */
class HW_Breadcrumb{

    /**
     * construct
     */
    public function __construct(){
        add_filter('bcn_breadcrumb_title', array($this, '_bcn_breadcrumb_title'));  //breadcrumb title
        //allow html in trait
        add_filter('bcn_allowed_html', array($this, '_bcn_allowed_html'));

        //when plugin loaded to init bcn settings
        add_filter('bcn_settings_init', array($this, '_bcn_settings_init'));

        //?
        add_filter('bcn_breadcrumb_trail_object', array($this, '_bcn_breadcrumb_trail_object'));
        //add trait template, since it do in skin file
        //add_filter('bcn_breadcrumb_template_no_anchor', array($this, '_bcn_breadcrumb_template_no_anchor'),10, 3);
        add_filter('bcn_breadcrumb_template', array($this, '_bcn_breadcrumb_template'), 10,3);
        add_filter('bcn_template_tags', array($this, '_bcn_template_tags'), 10,3);

        //Filter li_attributes adding attributes to the li element
        add_filter('bcn_li_attributes', array($this, '_bcn_li_attributes'), 10,3);
        add_filter('post_type_archive_title', array($this, '_post_type_archive_title'));

        //render breadcrumb navxt
        add_action('bcn_before_fill', array($this, '_bcn_before_fill'));
        add_action('bcn_after_fill', array($this, '_bcnext_remove_current_item'));

        add_filter('hw_breadcrumb_output', array($this, '_hw_breadcrumb_output'), 10, 2);
    }

    /**
     * replacement tags
     * @param $replacements
     * @param $type
     * @param $id
     */
    public function _bcn_template_tags($replacements, $type, $id){
        if(in_array('current-item', $type)) {
            //$replacements['%htitle%'] = '<a >'.$replacements['%htitle%'].'</a>';  //deligate from hook 'bcn_breadcrumb_template_no_anchor'
        }
        return $replacements;
    }

    /**
     * filter 'bcn_breadcrumb_template'
     * @param $template
     * @param $type
     * @param $id
     * @return mixed
     */
    public function _bcn_breadcrumb_template($template, $type, $id){
        return $template;
    }

    /**
     * current item template
     * @param $template
     * @param $type
     * @param $id
     * @return string
     */
    public function _bcn_breadcrumb_template_no_anchor($template, $type, $id){
        $template = '<a property="v:title" class="current-item">'.$template.'</a>';
        return $template;
    }
    /**
     * modify breadcrumb title
     * @param $title
     */
    public function _bcn_breadcrumb_title($title){
        return $title;
    }
    /**
     * load breadcrumb skin
     * @param $bcn_breadcrumb_trail
     */
    public function _bcn_before_fill($bcn){
        //_print($bcn->breadcrumbs);    //nothing, you can pre-append to begin trait object
    }

    /**
     * filter output breadrumb
     * @param $output
     * @param $inst
     * @return mixed
     */
    public function _hw_breadcrumb_output($output, $inst){
        return $output;
    }
    /**
     * modify default breadcrumb ouput
     * We're going to pop off the paged breadcrumb and add in our own thing
     * @param bcn_breadcrumb_trail $trail the breadcrumb_trail object after it has been filled
     */
    public function _bcnext_remove_current_item($trail){
        $this->get_settings();
        //Make sure we have a type
        if(isset($this->opt['hw_remove_current_item']) && $this->opt['hw_remove_current_item']
            && isset($trail->breadcrumbs[0]->type) && is_array($trail->breadcrumbs[0]->type) && isset($trail->breadcrumbs[0]->type[1]))
        {
            //Check if we have a current item
            if(in_array('current-item', $trail->breadcrumbs[0]->type))
            {
                //Shift the current item off the front
                array_shift($trail->breadcrumbs);
            }
        }
    }
    /**
     * filter post type archive title show in breadcrumb
     * @param $label
     * @return mixed
     */
    public function _post_type_archive_title($label){
        return $label;
    }

    /**
     * when display breadcrumb as list
     * @param $li_class
     * @param $bcn_type
     * @param $bcn_id
     * @return mixed
     */
    public function _bcn_li_attributes($li_class, $bcn_type, $bcn_id){//_print($li_class);
        return $li_class;
    }
    /**
     * get bcn settings
     */
    public function get_settings(){
        if(empty($this->opt)){
            $options = get_option('bcn_options');
            if(empty($options)) $options = get_site_option('bcn_options');
            $this->opt = wp_parse_args($options, array());
        }
        return $this->opt;
    }
    /**
     * allow html in breadcrumb output
     * @param $tags
     * @return mixed
     */
    public function _bcn_allowed_html($tags){
        //unset($tags['span']);
        return $tags;
    }

    /**
     * bcn settings
     * @param $opt
     * @return mixed
     */
    public function _bcn_settings_init($opt){
        return $opt;
    }

    /**
     * filter breadcrumb trail object
     * @param $bcn_breadcrumb_trail
     * @return mixed
     */
    public function _bcn_breadcrumb_trail_object($bcn_breadcrumb_trail){
        return $bcn_breadcrumb_trail;
    }

    /**
     * output breadcrumb navxt
     */
    public function display(){
        //get bcn settings
        $setting = $this->get_settings();
        $display = 'normal';
        $linked =  isset($setting['hw_allow_trail_link']) && $setting['hw_allow_trail_link']? true : false;
        $reverse =  isset($setting['hw_bcn_reverse']) && $setting['hw_bcn_reverse']? true : false;

        if(isset($setting['hw_active_skin']) && $setting['hw_active_skin']
            && class_exists('HW_SKIN') && isset($setting['hw_skin'])
            && isset($setting['hw_skin']['hwskin_config']))
        {
            #$skin = HW_SKIN::resume_skin($setting['hw_skin']['hwskin_config']);
            $skin = HW_SKIN::resume_hwskin_instance($setting['hw_skin']);
            if(!empty($skin)) {
                $file = $skin->instance->get_skin_file($setting['hw_skin']['hash_skin']);

                $content = $skin->instance->render_skin_template(0,false);
                if($content !==false) {
                    echo $content;
                }
                //if(file_exists($file)) {
                    //get theme setting from setting file or skin file
                    $setting = $skin->instance->get_file_skin_setting();
                    if(file_exists($setting)) include($setting);
                    else include($file);

                    if(isset($theme['display']) && $theme['display'] == 'list') $display = 'list';  //how to output breadcrumb
                    $opt = array('hseparator'=>'>>');
                    breadcrumb_navxt::setup_options($opt);

                    //valid setting
                    if(empty($theme['styles'])) $theme['styles'] = array();
                    if(empty($theme['scripts'])) $theme['scripts'] = array();

                    if(!empty($theme['filters'])) { //do filters & actions that defined in skin
                        $skin->instance->do_filters($theme['filters'], $theme);
                    }
                    /*if(count($theme['styles']) || count($theme['scripts'])) {   //enqueue stuff from theme
                        $skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                    }*/
                    //enqueue stuff from skin using new way
                    HW_SKIN::enqueue_skin_assets(array_merge(array(
                        'skin_file' => $file,
                        'theme_settings' => $theme
                    ), (array)$skin));
                //}
            }
        }
        //how to ouput breadcrumb function
        if($display == 'list') {
            $output = bcn_display_list(true, $linked, $reverse);
        }
        else $output = bcn_display(true, $linked, $reverse);
        echo '<div class="hw-breadcrumb">';
        echo apply_filters('hw_breadcrumb_output', $output, $this);
        echo '</div>';
    }
}

/**
 * @hook plugins_loaded
 */
add_action('hw_plugins_loaded', 'hw_bcn_init', 15);
function hw_bcn_init(){
    global $hw_breadcrumb;
    $hw_breadcrumb = new HW_Breadcrumb();
}
