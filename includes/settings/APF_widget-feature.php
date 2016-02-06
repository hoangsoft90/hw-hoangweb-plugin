<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 03/07/2015
 * Time: 06:50
 */
if(!class_exists('APF_WidgetFeatures')) :
class APF_WidgetFeatures extends  HW_APF_Field{
    /**
     * setting group
     */
    const SETTINGS_GROUP = 'my_widgetfeature_settings';
    /**
     * class instance. Importantant: to make distingish HW_APF_Field fields
     * @var
     */
    public  static $instance;
    /**
     * all registered widgets
     * @var
     */
    private $registered_widgets = array();

    /**
     * all registered widget features
     * @var array|void
     */
    private $registered_features = array();
    private $msgs = array();

    /**
     * main class constructor
     */
    public function __construct() {
        if(function_exists('hwawc_get_all_widgets')) $this->registered_widgets = hwawc_get_all_widgets();
        if(class_exists('HW_AWC_WidgetFeatures')) $this->registered_features = HW_AWC_WidgetFeatures::get_all_features();
        else
            $this->msgs[] = 'Không tìm thấy HW Any Widget class plugin.';
    }

    /**
     * setup widget feature
     * @call HW_AWC call from method HW_AWC/_hw_awc_in_widget_form
     * @param WP_Widget $widget
     */
    public static function setup_features_widgets(WP_Widget $widget) {
        static $active_features_widgets ;
        if(empty($active_features_widgets)) {
            $active_features_widgets = hw_get_setting(array(self::SETTINGS_GROUP,'active_features_widgets'), array());
        }
        foreach($active_features_widgets as $item) {
            if(isset($item['active']) && strtolower($item['active']) == 'on'
                && $item['widget'] == $widget->id_base )
            {
                HW_AWC_WidgetFeatures::register_widget_feature($widget, $item['feature'] );//_print($widget->id_base);
                //break;    //please do not break because one widget has more than one feature
            }
        }
    }
    /**
     * get field setting
     * @return array
     */
    public function get_field_definition() {
        return array(
            'field_id' => 'active_features_widgets',
            'type' => 'hw_table_fields',
            'title' => 'Kích hoạt features cho widgets',
            'description' => 'Kích hoạt features cho widgets. '. (!empty($this->msgs)? 'Chú ý: '. join($this->msgs,"\n") : ''),
            'repeatable' => true,
            'show_root_field' => false,
            'attributes'=>array(
                'hw_table_fields' => array()
            ),
            'fields' => array(
                //root field
                'col1' => array(    //select taxonomy
                    'name' => 'widget',
                    'type' => 'select',
                    'options' => $this->registered_widgets,
                    /*'event' => array(
                        'change'=>''
                    ),
                    'attributes' => array(
                        'style'=>'color:blue;'
                    ),*/
                    'description' => ''
                    //'static' => true
                ),
                'col2' => array(    //term
                    'name' => 'feature',   //'Cấu hình hiển thị',
                    'type' => 'select',
                    'options' => $this->registered_features,
                    'description' => ''
                ),

                'col3' => array(    //taxonomy template
                    'name' => 'active',
                    'type'=>'checkbox',

                )
            ),
            'table_header' => array(
                'col1' => 'Widget',
                'col2' => 'Widget feature',
                'col3' => 'Kích hoạt'
            ),
        );
    }

    /**
     * @param $oAdminPage
     * @return mixed|void
     */
    public static function replyToAddFormElements($oAdminPage) {
        #$oAdminPage->addSettingField(self::SETTINGS_GROUP); //group

        /*$this->addSettingField(array(
            'field_id' => 'aa',
            'type' => 'checkbox',    //'posttype',
            'title' => 'aa',
            'description' => 'xx',

        ));*/
        $setting = self::$setting;
        $tab = $setting->get_tabs(self::$setting_tab['slug']);
        //add section
        $oAdminPage->addSettingField($setting::HW_SETTINGS_PAGE); //group
        $oAdminPage->addSettingSections(
            $setting::HW_SETTINGS_PAGE,   // target page slug
            array(
                'section_id' => self::SETTINGS_GROUP,
                'title' => $tab['title'],
                'description' => $tab['description'],
                'section_tab_slug' => 'setting_tabs'

            )
        );
        //set group for just one field
        $oAdminPage->addSettingFields(
            self::SETTINGS_GROUP);

        self::register_field($oAdminPage);

        //submit button
        $oAdminPage->addSettingFields(
            array(
                'field_id' => 'submit',
                'type' => 'submit',
                'label' => 'Lưu lại',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            )
        );
    }
    /**
     * @param $slug
     * @param $tab
     * @param $setting
     */
    public static function init($slug, $tab, $setting) {
        $tab['slug'] = $slug;
        self::$setting = $setting ;
        self::$setting_tab = $tab ;
        add_action('load_' . $setting::HW_SETTINGS_PAGE . '_' . $setting::valid_tab_slug($slug), __CLASS__. '::replyToAddFormElements');
    }
}
endif;