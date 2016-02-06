<?php
/**
 * Class APF_Related_templates
 */
if(!class_exists('APF_Related_templates')):
class APF_Related_templates extends HW_APF_Field{
    /**
     * not clear, this portion now as part of posttype tab
     */
    const SETTINGS_GROUP = '';  //

    /**
     * store class instance
     * @var
     */
    public static $instance;
    /**
     * @var mixed
     */
    private $saved_widgets_settings;

    /**
     * constructor
     */
    public function __construct() {
        //only accept widget with id base 'hw_taxonomy_post_list_widget'
        if(class_exists('AWC_WidgetFeature_saveconfig')) {
            $this->saved_widgets_settings = AWC_WidgetFeature_saveconfig::get_widgets_settings(' where widget="hw_taxonomy_post_list_widget"');
        }

        $this->setup_hooks();   //setup hooks
    }

    /**
     * add hooks
     */
    public function setup_hooks() {
        add_filter('apf_table_column_field', array($this, '_apf_table_column_field') ,1,3);
    }
    /**
     * filter each field for apf table field type
     * @param $field
     * @param $value
     * @param $field_attrs
     */
    public function _apf_table_column_field(/*$field, $value,$field_attrs, $_aAttributes*/$s,$id,$args ) {
        static $taxs_terms = array();
        static $remind_pt;    //save taxonomy in row

        if(strpos($args['aAttributes']['name'], '[related_templates]')  === false || $args['aField']['field_id'] !== $id) return ;

        //remind taxonomy
        if($args['field']['name'] == 'posttype' ) {
            $remind_pt = $args['value'];
        }
        //term col
        if($args['field']['name'] == 'terms' ){
            if(!isset($taxs_terms[$remind_pt])) {
                $terms = HW_POST::get_all_terms_taxonomies($remind_pt, array('fields' => 'all', 'hide_empty' => false));
                //$terms = get_terms($remind_pt, array('fields'=>'all', 'hide_empty' => false));
                $data = array(/*'-1' => '---Mặc định--'*/);
                foreach($terms as $t) {
                    $data[$t->slug] = $t->name;
                }
                $taxs_terms[$remind_pt] = (array)$data;
            }

            $args['field']['options'] = $taxs_terms[$remind_pt];
            if(empty($data)) $args['field']['description'] = 'Dữ liệu chọn không có data';
        }
        //return $field;
    }
    /**
     * get field setting
     * @return array
     */
    public function get_field_definition() {

        $post_types = get_post_types( '', 'names' );    //list avaiable post types

        //select widgets setting data
        $widgets_settings = array('' => 'Không dùng');
        if(is_array($this->saved_widgets_settings))
            foreach ($this->saved_widgets_settings as $item) {
                $widgets_settings[$item->id] = $item->id. '-'.$item->name;
            }

        //$sidebars =  hwawc_get_all_active_sidebars();   //get all registered sidebars
        if(function_exists('hwawc_get_active_sidebars_select')) {
            $sidebars_field_data = hwawc_get_active_sidebars_select();
        }
        else $sidebars_field_data = array();

        //get sidebar wiget skins

        if(class_exists('HW_AWC_Sidebar_Settings')) {
            $sidebar_skins_data = HW_AWC_Sidebar_Settings::available_widget_skins(true);
        }
        else {
            $sidebar_skins_data = array();
            $tip = 'Không tìm thấy HW Any Widget class plugin.';
        }

        if(function_exists('is_plugin_active')) $tip = !is_plugin_active('yet-another-related-posts-plugin')? 'Yêu cầu Cài đặt '.hw_install_plugin_link('yet-another-related-posts-plugin','Yet Another Related Posts Plugin') : '';
        else $tip = '';

        $field = array(
            'field_id' => 'related_templates',
            'type' => 'hw_table_fields',
            'title' => 'Chọn template cho nội dung liên quan.'. (!empty($tip)? 'Chú ý: '. $tip : ''),
            'description' => 'Chọn template cho nội dung liên quan. Chọn post type và nhấn update để cập nhật cột Terms.<br/>'.$tip,
            'repeatable' => true,
            'show_root_field' => true,
            'attributes'=>array(
                'hw_table_fields'=>array()
            ),
            'fields' => array(
                //root field
                'col1' => array(    //select taxonomy
                    'name' => 'posttype',
                    'type' => 'select',
                    'options' => $post_types,
                    'attributes' => array(
                        'onchange' => 'console.log(0)'
                    )
                ),
                'col2' => array(
                    'name' => 'terms',
                    'type' => 'checkbox',
                    'options' => array('item1','item2'),    //multi-checkbox
                    'description' => ''
                ),
                'col3' => array(    //term
                    'name' => 'template',
                    'type' => 'custom',
                    'callback' => array($this, '_custom_column_related_templates'),
                    'description' => ''
                ),
                'col4' => array(
                    'name' => 'widget_config',
                    'type' => 'select',
                    'options' => $widgets_settings
                ),
                'col5' => array(    //taxonomy template
                    'name' => 'sidebar',
                    'type'=>'select',
                    'options' => $sidebars_field_data
                ),
                'col6' => array(
                    'name' => 'sidebar_widget_skin',
                    'type' => "select",
                    'options' => $sidebar_skins_data
                ),
                'col7' => array(
                    'name' => 'title',
                    'type' => 'text',
                    'description' => ''
                ),
                'col8' => array(
                    'name' => 'enable',
                    'type' => 'checkbox',
                )
            ),
            'table_header' => array(
                'col1' => 'Post Type',
                'col2' => 'Terms',
                'col3' => 'Giao diện',
                'col4' => 'Giao diện từ HWTPL',
                'col5' => 'Giao diện box',
                'col6' => 'Giao diện box widget',
                'col7' => 'Tiêu đề',
                'col8' => 'Kích hoạt'
            ),
        );
        return $field;
    }

    /**
     * custom column hw_table_field
     * @param $args
     * @param $this
     */
    public function _custom_column_related_templates($args, $inst) {
        if($args['field']['name'] == 'template') {
            $attributes = $args['attributes'];  //field attributes

            $value = $args['value'];    //field value
            //$values = $args['values'] ;     //row values
            $hash_skin_value = isset($value['hash_skin'] )? $value['hash_skin'] : '';       //current skin value

            $basename = $attributes['name'];   //$args['field_basename'];
            $attributes['name'] .= '[hash_skin]';   //modify name for skin field
            $out = '';

            if(!is_plugin_active('hw-yarpp/hw-yarpp.php')) return 'Vui lòng cài đặt plugin hw-yarpp';   //must active hw-yarpp plugin

            if(class_exists('HWRP_Meta_Box_Settings')) HWRP_Meta_Box_Settings::create_skin_manager($this);

            elseif(class_exists('HW_SKIN') ){
                $HWRP_PLUGIN_PATH = plugin_dir_path((HW_HOANGWEB_PATH)).'hw-yarpp/';
                $HWRP_PLUGIN_URL = plugins_url('hw-yarpp/',HW_HOANGWEB_PATH);

                $this->skin = new HW_SKIN($this, $HWRP_PLUGIN_PATH,'hw_relatedposts_skins','hw-relatedposts-skin.php','skins');
                $this->skin->plugin_url = $HWRP_PLUGIN_URL;          //set plugin url or url to app that use hw_skin
                $this->skin->enable_external_callback = false;     //turn off/on external callback

                //$this->skin->custom_skins_preview = true; //use own skins viewer
                $this->skin->set_template_header_info(array(
                    'name' => 'HWRP Template',
                    'description' => 'Description',
                    'author' => 'Author'
                ));
                $this->skin->add_skin_name_list(array('hw-category-posts.php'));
                $this->skin->match_skin_name_list('#yarpp-template-.*#');
            }
            if(! empty($this->skin)) {
                $this->skin->custom_skins_preview = false;
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
                    'awc_enable_grid_posts' => false
                ));

                $out .= $this->skin->get_skins_select_tag(null,$hash_skin_value,$attributes,false);
                //save skin config in field
                $out .= $this->skin->create_config_hiddenfield($basename/*, $value*/);  //alway get update by call get_config() again
            }
            return $out;
        }
    }
    /**
     * get related posts template by current post or specific post ID
     * @param $post_id: post ID or single post data
     */
    public static function get_relatedposts_template_by_post($post_id = '') {
        if(!empty($post_id) && is_numeric($post_id)) {
            $post = get_post($post_id);
        }
        if(!isset($post)){
            global $post;   //get current post
        }
        if(empty($post)) return;    //post not found (not single post page)

        $templates = hw_get_setting(array('my_posttype_settings','related_templates'));//_print($templates);
        $post_type = ($post->post_type);
        $match_item = null;     //result (match current context)

        foreach ($templates as $item) {
            if($item['posttype'] != $post_type || empty($item['enable'])) continue;

            $allow_terms = $item['terms'];
            if(HW_POST::check_post_terms($post, $allow_terms, false) ) {
                $match_item = $item;    //result (match current context)
                break;
            }
        }
        //get related posts skin for current post
        if(!empty($match_item) && isset($match_item['template']) && !empty($match_item['template']['hwskin_config'])
            && class_exists('HW_SKIN')) {

            $match_item['template']['instance'] = HW_SKIN::resume_skin($match_item['template']['hwskin_config']); //resume hw_skin instance
            return $match_item;
        }
    }
}
endif;