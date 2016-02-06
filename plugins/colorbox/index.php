<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_Module_colorbox
 */
class HW_Module_colorbox extends HW_Module {
    public function __construct() {
        //enable tab settings
        $this->enable_tab_settings();
        $this->enable_submit_button();
    }

    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        #$this->enqueue_scripts();
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        #if($this->is_module_setting_page()) {
            HW_Libraries::enqueue_jquery_libs('jquery-colorbox');
        #}
        $data = self::get()->get_values();
        if(class_exists('HW_SKIN_Option')) {
            $data_json = HW_SKIN_Option::build_json_options($data);
            if($data_json) $data = (array)json_decode($data_json);
        }

        $handle = $this->enqueue_script('colorbox-module.js');
        $this->localize_script($handle, '__hw_module_colorbox', $data);
    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        $oAdminPage->addSettingFields(
        #$this->sSectionID,  // target section id
            array(
                'field_id'          => $this->create_field_name('transition'),
                'type'              => 'select',
                'label' => array('elastic'=>'elastic', 'fade'=>'fade','none'=>'none'),
                'title' => 'transition',
                'description' => 'The transition type.'
            ),
            array(
                'field_id'          => $this->create_field_name('speed'),
                'type'              => 'text',
                #'label_min_width'   => '100%',
                'title' => 'speed',
                'description' => 'Sets the speed of the fade and elastic transitions, in milliseconds.',
                'default' => '350'
            ),
            array(
                'field_id' => $this->create_field_name('href'),
                'type' => 'text',
                'title' => 'href',
                'description' => 'This can be used as an alternative anchor URL or to associate a URL for non-anchor elements such as images or form buttons. $("h1").colorbox({href:"welcome.html"});'
            ),
            array(
                'field_id' => $this->create_field_name('title'),
                'type' => 'text',
                'title' => 'title',
                'description' => 'This can be used as an anchor title alternative for Colorbox.'
            ),
            array(
                'field_id' => $this->create_field_name('rel'),
                'type' => 'text',
                'title' => 'rel',
                'description'=> 'This can be used as an anchor rel alternative for Colorbox. '
            ),

            array()
        );
    }

    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        foreach(array('xxx') as $option) {
            if(isset($values[$option])) $values[$option] = $values[$option]? true:false;
        }

        return $values;
    }
}
add_action('hw_modules_load', 'HW_Module_colorbox::init');