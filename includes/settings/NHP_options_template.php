<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/06/2015
 * Time: 13:26
 */
class NHP_Options_template extends HW_NHP_Options {
    public function __construct(&$sections) {
        parent::__construct($sections);

    }
    public function get_fields(&$sections) {
        global $wp_filter;
        $hw_register_hooks= array(
            'hw_before_main_content' => 'hw_before_main_content',
            'hw_after_main_content' => 'hw_after_main_content',
            'hw_after_header' => 'hw_after_header',
            'hw_before_loop' => 'hw_before_loop',
            'hw_after_loop' => 'hw_after_loop',
        );
        /*foreach($wp_filter as $tag => $priority){
            //echo "<br />&gt;&gt;&gt;&gt;&gt;t<strong>$tag</strong><br />";
            ksort($priority);
            $data[$tag] = $tag;
        }*/
        $layouts = HW__Template::getTemplates();    //get template layouts

        /*float left right advertising*/
        $sections['template'] =  array(
            'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_064_lightbulb.png',
            'title' => 'Giao diện',
            'fields' => array(
                'content_classes' => array(
                    'id' => 'content_classes',
                    'type' => 'hw_table_fields',
                    'title' => 'Thêm class vào thẻ body và loop post.',
                    'desc' => 'Thêm class vào thẻ body và loop post.',
                    'data_base_field' => 'col1',
                    'show_root_field' => true,       //set root field to true
                    'fields' => array(
                        //root field
                        'col1' => array(
                            #'name' => 'taxonomy',          // group by root field don;t need give full field setting
                            #'type' => 'select',
                            'options' => $layouts,
                            //'static' => true
                        ),
                        //active fields bellow
                        'col2' => array(
                            'name' => 'body_class',
                            'type'=>'text',
                            'description' => 'Thêm class vào thẻ body',
                            //'value' => '',
                            'attributes' => array(
                                'style' => 'border:1px solid red;',
                                'class' => 'hw-class'
                            )
                        ),
                        'col3' => array(
                            'name' => 'post_class',
                            'type' => 'text',
                            'description' => 'Thêm class vào filter post_class'
                        ),
                        'col4' => array(
                            'name' => 'remove_default',
                            'type' => 'checkbox',
                            'description' => 'Xóa classes mặc định.'
                        ),
                        /*'col4' => array(
                            'name' => 'txt1',
                            'type' => 'text',
                            'description' => ''
                        )*/
                    ),
                    'table_header' => array(
                        'col1' => 'Template/trang',
                        'col2' => 'Body class',
                        'col3' => 'Post class',
                        'col4' => 'Xóa mặc định',
                        #'col4' => 'vd'
                    )
                ),
                /*'post_class' => array(
                    'id' => 'post_class',
                    'type' => 'text',
                    'title' => 'Thêm class vào loop item.',
                    'desc' => 'Thêm class vào loop item.'
                ),*/
                'show_breadcrumb' => array(
                    'id' => 'show_breadcrumb',
                    'type' => 'select',
                    'title' => 'Hiển thị thanh định hướng',
                    'desc' => 'Hiển thị thanh định hướng (breadcrumb) vào trước nội dung hook này.',
                    'options' => $hw_register_hooks
                )
            ),

        );
    }

}