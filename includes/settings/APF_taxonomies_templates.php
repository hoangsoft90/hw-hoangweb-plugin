<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/06/2015
 * Time: 16:30
 */
/**
 * Class APF_Page_Templates
 */
if(! class_exists('APF_Page_Templates')) :
class APF_Page_Templates extends  HW_APF_Field{
    /**
     * group name
     */
    const SETTINGS_GROUP = 'my_taxonomy_settings';
    /**
     * taxonomies data
     * @var
     */
    public $tax_data = null;

    /**
     * list theme templates
     * @var null
     */
    public $theme_templates_list = null;

    /**
     * class instance
     * @var
     */
    public  static $instance = null;

    /**
     * main class constructor
     */
    public function __construct() {
        //list taxonomies
        $tax_data = HW_POST::hw_list_taxonomies(array('hw-ml-slider'));
        //array_unshift($tax_data, '--Select--');
        HW_UI_Component::empty_select_option($tax_data);
        $this->tax_data = $tax_data;

        //list templates for current theme
        $this->theme_templates_list = array(
            '-1' => 'Mặc định'
        );
        $templates = hw_list_active_theme_templates();
        foreach($templates as $file){
            $this->theme_templates_list[base64_encode($file['path'])] = $file['name'];
        }
        //add action filters
        $this->add_actions();
    }
    /**
     * hooks actions
     */
    private function add_actions() {
        //modify each field data in apf_table_fields type
        add_filter('apf_table_column_field', array($this, '_apf_table_column_field'),1, 3);
    }
    /**
     * filter each field for apf table field type
     * @param $field
     * @param $value
     * @param $field_attrs
     * @hook apf_table_column_field
     */
    public function _apf_table_column_field(/*$field, $value ,$field_attrs*/$s,$id, $args){
        static $taxs_terms = array();
        static $remind_taxonomy = null;    //save taxonomy in row
        $value = $args['value'];

        //valid
        if(strpos($args['aAttributes']['name'], '[taxonomies_template]')  === false || $args['aField']['field_id'] !== $id) return ;

        //remind taxonomy
        if($args['field']['name'] == 'taxonomy' && $value) {
            $remind_taxonomy = $value;
        }
        //term col
        if($args['field']['name'] == 'term' && $remind_taxonomy !== null){
            if(!isset($taxs_terms[$remind_taxonomy])) {
                $terms = get_terms($remind_taxonomy, array('fields'=>'all', 'hide_empty' => false));
                $data = array('-1' => '---Mặc định--');
                if(count($terms))
                    foreach($terms as $t) {
                        $data[$t->slug] = $t->name;
                    }
                $taxs_terms[$remind_taxonomy] = (array)$data;
            }

            $args['field']['options'] = $taxs_terms[$remind_taxonomy];
            if(!isset($data) || !count($data)) $args['field']['description'] = 'Dữ liệu chọn không có data';
        }
        //return $field;
    }
    /**
     * return instance of this class
     * @return APF_Page_Templates
     */
    public static function getInstance() {
        return self::get_instance();
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
    /**
     * taxonomy tab
     * @param $oAdminPage
     * @return mixed|void
     */
    public static function replyToAddFormElements($oAdminPage) {
        //list taxonomies
        $tax_data = self::get_instance()->tax_data;
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
        /**
         * taxonomy tab
         */
        $oAdminPage->addSettingFields(
            self::SETTINGS_GROUP,
            /*array(
                'field_id' => 'tax_icon',
                'type' => 'checkbox',
                'title' => 'Ảnh đại diện danh mục',
                'description' => 'Kích hoạt ảnh đại diện cho category/taxonomy'
            ),*/
            array(
                'field_id' => 'allow_taxonomies_image',
                'type'=>'select',
                'title' => 'Thêm trường nhập ảnh cho',
                'description' => 'Thêm trường nhập ảnh cho',
                //'taxonomy_slugs' =>'category',
                'is_multiple' => true,
                'attributes' => array(
                    'select' => array('multiple'=>true, 'size' => 10),
                ),
                'label' => $tax_data
            )
        );
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
     * get field setting
     * @return array
     */
    public function get_field_definition() {

        return array(
            'field_id' => 'taxonomies_template',
            'type' => 'hw_table_fields',
            'title' => 'Cài đặt template',
            'repeatable' => true,
            //'data_base_field' => 'col1',
            'show_root_field' => false,
            'attributes'=>array(
                'hw_table_fields'=>array()
            ),
            'fields' => array(
                //root field
                'col1' => array(    //select taxonomy
                    'name' => 'taxonomy',
                    'type' => 'select',
                    'options' => $this->tax_data,
                    'event' => array(
                        'change'=>''
                    ),
                    'attributes' => array(
                        'style'=>'color:blue;'
                    ),
                    'description' => ''
                    //'static' => true
                ),
                'col2' => array(    //term
                    'name' => 'term',
                    'type' => 'select',
                    'options' => array(),
                    'description' => ''
                ),

                'col3' => array(    //taxonomy template
                    'name' => 'template',
                    'type'=>'select',
                    'options' => $this->theme_templates_list
                ),
                'col4' => array(
                    'name' => 'loop_template',
                    'type' => 'custom',
                    'callback' => array($this, '_custom_column_taxonomies_template')
                ),
                'col5' => array(    //single template
                    'name' => 'single_template',
                    'type' => 'select',
                    'options' => $this->theme_templates_list
                ),
                'col6' => array(    //enable template
                    'name' => 'enable',
                    'type' => 'checkbox',
                )
            ),
            'table_header' => array(
                'col1' => 'Dữ liệu',
                'col2' => 'Term',
                'col3' => 'Taxonomy Template',
                'col4' => 'Loop Template',
                'col5' => 'Single Template',
                'col6' => 'Kích hoạt'
            ),
            'description' => 'Gọi theo thứ tự từ trên xuống dưới. Chọn dữ liệu và nhấn update để cập nhật lại cột Terms.'
        );
    }
    /**
     * custom column hw_table_field
     * @param $args
     * @param $this
     */
    public function _custom_column_taxonomies_template($args, $inst) {
        if($args['field']['name'] == 'loop_template') {
            $attributes = $args['attributes'];  //field attributes

            $value = $args['value'];    //field value
            //$values = $args['values'] ;     //row values
            $hash_skin_value = isset($value['hash_skin'] )? $value['hash_skin'] : '';       //current skin value

            $basename = $attributes['name'];   //$args['field_basename'];
            $attributes['name'] .= '[hash_skin]';   //modify name for skin field

            $out = '';
            if(class_exists('HW_SKIN')){
                $this->skin = new HW_SKIN($this,HW_HOANGWEB_PATH,'hw_loop_skins','hw-loop-template.php','skins');
                $this->skin->plugin_url = HW_HOANGWEB_URL;          //set plugin url or url to app that use hw_skin
                $this->skin->enable_external_callback = false;     //turn off/on external callback

                //$this->skin->custom_skins_preview = true; //use own skins viewer
                $this->skin->set_template_header_info(array(
                    'name' => 'HW Template',
                    'description' => 'Description',
                    'author' => 'Author'
                ));
                //add more skin file
                $this->skin->add_skin_name_list(array('hw-category-posts.php'));
                $this->skin->match_skin_name_list('#yarpp-template-.*#');

                //set migrate data + compatible vars from active skin if found together
                //set migrate data with this skin
                $this->skin->migrate(array(
                    'cat_posts' => 'wp_query',  //array($this, '_get_wp_query'),     //warning: this make infinite loop or too heveay data for hwskin_config,
                    'metaFields' => array(),
                    'arrExlpodeFields' => array('title','excerpt','comment_num','date','thumb','author'),
                    'instance' => array(),
                    'hwtpl_wrapper_id' => 'hwtpl_wrapper_id-hw-loop-template',
                    'hwtpl_scrollbar_wrapper_class' => 'hwtpl_scrollbar_wrapper_class-hw-loop-template',
                    'hwtpl_pagination_class' => 'hwtpl_pagination_class',
                    'awc_enable_grid_posts' => false,
                    'before_widget' => '',
                    'after_widget' => '',
                    'open_title_link' => '',
                    'close_title_link' => '',
                    'before_title' => '',
                    'widget_title' => '',
                    'after_title' => ''
                ));

                $out .= $this->skin->get_skins_select_tag(null,$hash_skin_value,$attributes,false);
                //save skin config in field
                $out .= $this->skin->create_config_hiddenfield($basename/*, $value*/);
            }
            return $out;
        }
    }
    /**
     * get template for current context
     */
    public static function get_current_context_template() {
        global $post;
        $taxonomies_template = hw_get_setting(array('my_taxonomy_settings','taxonomies_template'),array());

        //taxonomy template
        foreach($taxonomies_template as $item) {
            if(!isset($item['enable']) || strtolower($item['enable']) == 'off' || !$item['enable']
            )  continue;

            //taxonomy template
            if((is_tax() || is_category()) && (/*$item['template'] != -1 &&*/
                  (!empty($item['term'])
                        && (is_tax($item['taxonomy'], $item['term']) || is_category($item['term'])) )
                    || (empty($item['term']) || $item['term'] == -1)) )
            {
                $item['found'] = 'taxonomy';
                $item['template'] = base64_decode($item['template']);   //get real path
                /*if(file_exists($item['template'])) {
                    $template = $item['template'];
                }
                else*/
                if(file_exists(locate_template($item['template']))) {
                    $item['template'] = locate_template($item['template']);
                }
                //return $item;
                break;
            }
            //single template
            if(is_single() && (/*$item['single_template'] != -1 && */
                       (!empty($item['term']) && in_category($item['term'], $post))
                    || $item['term'] == -1))
            {
                $item['found'] = 'single';
                $item['single_template'] = base64_decode($item['single_template']);     //decode path
                /*if(file_exists($item['single_template'])) {
                    return $item['single_template'];
                }
                else*/
                if(file_exists(locate_template($item['single_template']))) {
                    $item['single_template'] = locate_template($item['single_template']);
                }
                //return $item;
                break;
            }
        }
        if( isset($item['found']) ) {
            if(isset($item['loop_template']) && isset($item['loop_template']['hwskin_config'])) {
                //parse loop template
                $item['skin'] = HW_SKIN::resume_skin($item['loop_template']['hwskin_config']);
            }
            return $item;
        }
    }
    /**
     * loop template, don't mask as static because in skin file maybe using $this variable
     * @param $loop_file
     */
    public function hw_loop_template($loop_file = 'content') {
        if(empty($loop_file)) $loop_file = locate_template( 'content.php');     //default loop template

        $item = APF_Page_Templates::get_current_context_template();

        //get template from skin
        if($item && !empty($item['loop_template']) && !empty($item['skin'])) {
            //extend skin from plugin HW_Taxonomy_post_list_widget
            if( isset($item['loop_template']['hash_skin'])) {
                $skin = $item['skin'];

                $file = $skin->get_skin_file($item['loop_template']['hash_skin']);
                $theme_setting = $skin->get_file_skin_setting();   // (new HW_SKIN)->get_file_skin_setting();
                if(file_exists($theme_setting)) include($theme_setting);

                if(file_exists($file)) {
                    //enhanced from plugin hw-yarpp/includes/hwrp-website.php
                    $loop_file = $file;
                    extract($skin->get_migrate());

                    /* Start the Loop */
                    do_action ('hoangweb_before_loop');
                    include ($loop_file);
                    do_action ('hoangweb_after_loop');

                    //load skin resource
                    //valid
                    if(!isset($theme['scripts'])) $theme['scripts'] = array();
                    if(!isset($theme['styles'])) $theme['styles'] = array();

                    if(count($theme['styles']) || count($theme['scripts'])) {
                        $skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']); //enqueue stuff from skin
                    }
                    return true;
                }
            }
        }
        return false;
    }

}
endif;