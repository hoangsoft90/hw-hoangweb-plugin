<?php
# used by includes/settings/hw-nhp-theme-options.php
/**
 * Class NHP_Options_ads
 */
class NHP_Options_gallery extends HW_NHP_Options {
    /**
     * class constructor
     * @param array $sections
     */
    public function __construct(&$sections) {
        parent::__construct($sections);

    }

    /**
     * fields definition
     * @param $sections
     */
    public function get_fields(&$sections) {
        //install plugins link
        if(is_plugin_active('multi-image-metabox')) {
            $install_multi_image_link = hw_install_plugin_link('multi-image-metabox','Cài đặt');
        }
        else $install_multi_image_link = '';

        //help for multi image metabox
        if(class_exists('HW_HELP_HOANGWEB',false) ) {
            //$multi_image_help = HW_HELP_HOANGWEB::current()->help_static_link('multi-image-metabox.html');
            $multi_image_help = HW_HELP::generate_help_popup(array('HW_HELP_HOANGWEB','multi-image-metabox.html'), 'Hướng dẫn.','Hướng dẫn Multi Image Metabox');
        }
        else $multi_image_help = '';

        //gallery
        $sections['gallery'] =  array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_001_leaf.png',
            'title' => 'Gallery',
            'fields' => array(
                /**
                 * use multi-image-metabox plugin
                 */
                'enable_multi_images' => array(
                    'id' =>'enable_multi_images',
                    'type' => 'checkbox',
                    'title' => 'Kích hoạt gallery ảnh',
                    'desc' => 'Kích hoạt gallery ảnh cho mỗi bài viết/post type. '.$install_multi_image_link,
                    'sub_desc' => $multi_image_help
                ),
                'posttype_multi_images' => array(
                    'id' =>'posttype_multi_images',
                    'type' => 'hw_post_type_multi_select',
                    'title' => 'Chọn post types cho multi-images.',
                    'sub_desc' => 'Chọn một hoặc nhiều post type sử dụng tính năng gallery ảnh.',
                    //'options'=>array('item1','item2')
                    'size' => 8,
                    //'desc' => HW_HELP::generate_help_popup(HW_HELP_HOANGWEB::current(), 'Hướng dẫn.')
                ),
                'multi_images_num' => array(
                    'id' =>'multi_images_num',
                    'type' => 'text',
                    'title' => 'Số lượng multi-images.',
                    'sub_desc' => 'Số lượng ảnh gallery cho phép sử dụng.',
                    'std' => '200'  //default 200 excerpt
                ),
                'divide' => array(
                    'type'=>'hw_divide',
                    'label' => 'Envira Gallery'
                ),
                /**
                 * envira gallery
                 */
                'envira_exclude_posttypes' => array(
                    'id' => 'envira_exclude_posttypes',
                    'type' => 'hw_post_type_multi_select',
                    'title' => 'Loại bỏ post types',
                    'sub_desc' => 'Chọn một hoặc nhiều post type không muốn tích hợp Envira gallery.'
                )
            )
        );
    }
    /**
     * allow post types use multi-image-metabox
     */
    public static function hw_nhp_support_image_cpt($pt){
        $cpts = hw_option('posttype_multi_images',(array)$pt);
        return $cpts;
    }

    /**
     * show images list with cpt plugin
     */
    public static function hw_nhp_list_images_cpt(){
        $num = (int)hw_option('multi_images_num',10);
        $picts = array();
        //I only need two pictures
        for($i=1;$i <= $num ;$i++){
            $picts['image'.$i] = "_image{$i}";  //ie: to get image item using get_post_meta($id, '_image1',true);
        }

        return $picts;
    }
    /**
     * add actions
     */
    public static function setup_actions() {
        add_filter('images_cpt', 'NHP_Options_gallery::hw_nhp_support_image_cpt', 10);
        add_filter('list_images',  'NHP_Options_gallery::hw_nhp_list_images_cpt' );
    }
}
#init
if(hw_option('enable_multi_images')){
    NHP_Options_gallery::setup_actions();
}