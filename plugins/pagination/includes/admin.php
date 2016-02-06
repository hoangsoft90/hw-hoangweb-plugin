<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/05/2015
 * Time: 11:32
 */

if(class_exists('scbAdminPage')):
/**
 * Class HWPageNavi_Options_Page
 * since wp-pagenavi use scb framework to build options form
 */
class HWPageNavi_Options_Page extends scbAdminPage {
    /**
     * Text domain for wp-pagenavi plugin
     * @var string
     */
    public $textdomain = 'wp-pagenavi';

    /**
     * HW_SKIN
     * @var
     */
    public $skin;

    /*public function __construct($file, $options){
        parent::__construct($file, $options);
    }*/
    /**
     * setup callback
     */
    function setup() {
        $this->textdomain = 'wp-pagenavi';

        $this->args = array(
            'page_title' => __( 'Cài đặt giao diện', $this->textdomain ),
            'menu_title' => __( '' ),
            'page_slug' => 'pagenavi',  //we use same page slug with wp-pagenavi plugin, so my options will show bellow
        );
        if(class_exists('HW_SKIN')) {
            // instance skin, note: if use create instance from class you should use property 'skin'
            $this->skin = new HW_SKIN($this,HW_PAGENAVI_PATH,'hw_pagenavi_skins','hw-pagenavi-skin.php','skins');
            $this->skin->plugin_url = HW_PAGENAVI_URL;          //set plugin url or url to app that use hw_skin
            $this->skin->enable_external_callback = false;     //turn off/on external callback
        }

    }

    /**
     * @param array $new_data
     * @param array $old_data
     * @return array
     */
    function validate( $new_data, $old_data ) {
        $options = wp_parse_args($new_data, $old_data);
        foreach ( array( 'style', 'num_pages', 'num_larger_page_numbers', 'larger_page_numbers_multiple' ) as $key )
            $options[$key] = absint( @$options[$key] );

        foreach ( array( 'use_pagenavi_css', 'always_show' ) as $key )
            $options[$key] = intval(@$options[$key]);

        HW_SKIN::save_enqueue_skin(array(
            'type'=> 'resume_skin' ,
            'skin' => $options['hw_skin'],
            'object' => 'pagenavi',
            'status' => 1
        ));

        return $options;
    }

    /**
     * output content to page
     */
    function page_content() {
        $rows = array(
            array(
                'title' => __( 'Giao diện', $this->textdomain ),
                'type' => 'custom',
                'name' => 'hw_skin',
                'extra' => 'style="width:200px;"',
                'desc' => __('<br />
					Vui lòng chọn giao diện cho phân trang', $this->textdomain ),
                'render' => array($this, 'pagination_skin_scb_field')
            ),
            /*array(
                'title' => __('Điều kiện'),
                'type' => 'custom',
                'name' => 'hw_skin_condition',
                'extra' => '',
                'desc' => __('Chọn điều kiện template sử dụng skin.'),
                'render' => array($this, 'pagination_skin_condition_scb_field')
            )*/
        );

        $out = $this->table( $rows );
        $out = $this->form_wrap( $out );
        $out .= html('p','Hướng dẫn: nhấn nút help ở phải trên cùng để xem hướng dẫn sử dụng.');

        echo $out;

    }
    /**
     * render scb hw_skin field type
     * @param $value current field value
     * @param $field
     */
    public function pagination_skin_condition_scb_field($value, $field) {
        if(!$this->skin) return;
        $out = $this->skin->get_skin_template_condition_selector('hw_skin_condition',$value);
        return $out;
    }
    /**
     * render scb hw_skin field type
     * @param $value
     * @param $field
     */
    public function pagination_skin_scb_field($value, $field){
        if(!$this->skin) return;
        //get hoangweb scb options
        $options = HWPageNavi_Core::getOptions()->get();

        //get hw_skin config
        if(empty($options['hw_skin_config'])) {
            $hwskin_config = $this->skin->get_config(true);
        }
        else $hwskin_config = $options['hw_skin_config'];
        //skin condition
        $skin_condition = isset($options['hw_skin_condition'])? $options['hw_skin_condition'] : '';

        #$out = $this->skin->get_skins_select_tag('hw_skin',$value,array('name' => "hw_skin[hash_skin]",'class'=>'hw-skin-selector'),false);
        //get skin config
        #$out .= '<input type="hidden" name="hw_skin_config" id="hw_skin_config" value="'.$hwskin_config.'"/>';
        $out = $this->skin->create_total_skin_selector('hw_skin', $value, array(), array(
            'show_main_skin' =>1,
            'show_config_field' => 1,
            'show_condition_field' => 1,
            'show_skin_options' => 1,

        ));

        $out .= '<em>'.$field->desc.'</em>';
        return $out;
    }

    /**
     * footer page
     */
    function page_footer() {
        parent::page_footer();
        // Reset all forms
        ?>
        <script type="text/javascript">
            (function() {
                var forms = document.getElementsByTagName('form');
                for (var i = 0; i < forms.length; i++) {
                    forms[i].reset();
                }
            }());
        </script>
    <?php
    }
}
endif;