<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 20:46
 */
if(! class_exists('APF_Theme_Templates')) :
class APF_Theme_Templates extends  HW_APF_Field{
    /**
     * setting group
     */
    const SETTINGS_GROUP = 'my_templates';
    /**
     * class instance
     * @var
     */
    public  static $instance;

    /**
     * @var
     */
    protected $layouts;
    /**
     * widgets settings data get from db
     * @var mixed
     */
    protected $saved_widgets_settings = null;

    public function __construct() {
        //list layouts
        $this->layouts= HW__Template::getTemplates();

        //only accept widget with id base 'hw_taxonomy_post_list_widget'
        if(class_exists('AWC_WidgetFeature_saveconfig')) {
            $this->saved_widgets_settings = AWC_WidgetFeature_saveconfig::get_widgets_settings(' where widget="hw_taxonomy_post_list_widget"');
        }

    }
    /**
     * get field setting
     * @return array
     */
    public function get_field_definition() {
        //select widgets setting data
        $widgets_settings = array();
        if(is_array($this->saved_widgets_settings))
        foreach ($this->saved_widgets_settings as $item) {
            $widgets_settings[$item->id] = $item->id. '-'.$item->name;
        }
        //$sidebars =  hwawc_get_all_active_sidebars();   //get all registered sidebars
        if(function_exists('hwawc_get_active_sidebars_select')) {
            $sidebars_field_data = hwawc_get_active_sidebars_select();
        }
        else{
            global $wp_registered_sidebars;
            $sidebars_field_data = array();
            foreach($wp_registered_sidebars as $sidebar) {
                $sidebars_field_data[$sidebar['id']] = $sidebar['name'];
            }
        }
        //get sidebar wiget skins
        $sidebar_skins_data = array();
        if(class_exists('HW_AWC_Sidebar_Settings')) {
            $sidebar_skins = HW_AWC_Sidebar_Settings::available_widget_skins(); //other way: just call HW_AWC_Sidebar_Settings::available_widget_skins(true)
            foreach($sidebar_skins as $theme_name => $opt) {
                $sidebar_skins_data[$theme_name] = $opt['title'];

            }
        }
        else $tip = 'Không tìm thấy HW Any Widget class plugin.';

        return array(
            'field_id' => 'main_loop_content_style',
            'type' => 'hw_table_fields',
            'title' => 'Lặp nội dung chính',
            'description' => 'Áp dụng cho template sử dụng lặp nội dung.'. (!empty($tip)? 'Chú ý: '. $tip : ''),
            'repeatable' => true,
            //'data_base_field' => 'col1',
            'show_root_field' => false,
            'attributes'=>array(
                'hw_table_fields' => array()
            ),
            'fields' => array(
                //root field
                'col1' => array(    //select taxonomy
                    'name' => 'layout',
                    'type' => 'select',
                    'options' => $this->layouts,
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
                    'name' => 'widget_config',   //'Cấu hình hiển thị',
                    'type' => 'select',
                    'options' => $widgets_settings,
                    'description' => ''
                ),

                'col3' => array(    //taxonomy template
                    'name' => 'sidebar',
                    'type'=>'select',
                    'options' => $sidebars_field_data
                ),
                'col4' => array(
                    'name' => 'sidebar_widget_skin',
                    'type' => "select",
                    'options' => $sidebar_skins_data
                )
            ),
            'table_header' => array(
                'col1' => 'Layout',
                'col2' => 'Cấu hình nội dung + hiển thị',
                'col3' => 'Box',
                'col4' => 'Chọn một giao diện'
            ),

        );
    }

    /**
     * @param $oAdminPage
     * @return mixed|void
     */
    public static function replyToAddFormElements($oAdminPage) {
        /**
         * taxonomy tab
         */
        /*$oAdminPage->addSettingFields(
            APF_Theme_Templates::SETTINGS_GROUP
        ,array(
            'field_id' => 'main_loop_content_style',
            'type' => 'text',
            'descrition' => 'Lặp nội dung chính'
        )
        );*/
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
        //add taxonomies_template field
        self::register_field($oAdminPage);

        //add submit button
        $oAdminPage->addSettingField(array(
            'field_id' => 'submit',
            'type' => 'submit',
            'label' => 'Lưu lại',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
        ));
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

        add_action('load_' . $setting::HW_SETTINGS_PAGE . '_' . $setting::valid_tab_slug($slug), __CLASS__.'::replyToAddFormElements');
    }
}
endif;