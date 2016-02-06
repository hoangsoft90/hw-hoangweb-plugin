<?php
/**
 * Class HW_Marquee_Widget
 */
class HW_Marquee_Widget extends WP_Widget {
    /**
     * @var array
     */
    private $libraries = array();
    /**
     * HW_SKIN
     * @var null
     */
    public $skin = null;
    /**
     * data source
     * @var array
     */
    protected $sources = array();

    /**
     * constructor
     */
    function __construct() {
        parent::__construct(
        // Base ID of your widget
            'hw_marquee_text',

            // Widget name will appear in UI
            __('Nội dung chạy', 'hoangweb'),

            // Widget description
            array( 'description' => __( 'Tạo chữ/nội dung chạy', 'hoangweb' ), )
        );
        $this->setup_actions();

        $this->libraries = array(
            'everwebcodebox' => 'everwebcodebox'
        );
        //data source
        $this->sources = array(
            'content' => __('Văn bản'),
            'slides' => __('Slides')
        );

        if(class_exists('HW_SKIN')) {
            $this->skin = new HW_SKIN($this,HW_MARQUEE_PLUGIN_PATH, 'hw_marquee_skins','hw-marquee-skin.php','skins');
            //$this->skin->set_group('marquee');
            $this->skin->plugin_url = HW_MARQUEE_PLUGIN_URL;
            $this->skin->enable_external_callback = false;
            $this->skin->enable_template_engine();
            $this->skin->init();
        }
    }

    /**
     * setup actions
     */
    public function setup_actions() {
        if(!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, '_wp_enqueue_script'));
        }
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
        add_filter('hw_skin_data', array($this, '_hw_skin_data_filter'),10,3);
    }
    /**
     * filter skin data in method HW_SKIN::load_skins_data
     * @hook hw_skin_data
     * @param $data
     * @return mixed
     */
    public function _hw_skin_data_filter($data, $skin, $inst) {
        $config = $inst->get_config(false);
        if($config['skin_name'] == 'hw-marquee-skin.php') {
            $info = ($inst->get_skin_info());
            $data['name'] = $info['name'];
            $data['md5'] = md5($data['screenshot']);
        }

        return $data;
    }
    /**
     * for backend
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        if(HW_HOANGWEB::is_current_screen('widgets')) {
            //ckeditor
            HW_Libraries::enqueue_jquery_libs('ckeditor');
            wp_enqueue_script('media');
            wp_enqueue_media();
        }

    }
    /**
     * enqueue stuffs
     * @hook wp_enqueue_scripts
     */
    public function _wp_enqueue_script() {
        //HW_Libraries::enqueue_jquery_libs('marquee/everwebcodebox');
    }
    /**
     * HW_SKIN::apply_skin_data callback after
     * @param $context
     * @param $params
     */
    public function _hw_skin_apply_skin_data_callback_after ($params){
        extract($params);
        #(new HW_SKIN)->get_skin_info();
        if(isset($skin) && isset($hash_skin)) {
            //$skin_data = $skin->get_skin_data($hash_skin);
            /*
            //marquee params
            if(!isset($skin_options)) $skin_options = array();
            $json_config = HW_SKIN_Option::build_json_options($skin_options);

            //for everwebcodebox
            if($skin_data['name'] == 'everwebcodebox' && isset($marquee_wrapper) ) {
                echo '<script type="text/javascript">
                jQuery(function($){
                //see more at js/libraries/marquee/everwebcodebox/README.md
                    var $marquee = $(".'.$marquee_wrapper.'").marquee('.$json_config.');
                    $(".'.$marquee_wrapper.'").hover(function(){
                            $marquee.marquee("pause");
                        }, function(){
                            $marquee.marquee("resume")
                        });
                });
                </script>';
            }*/
        }
    }
    /**
     * HW_SKIN::apply_skin_data callback before
     * @param $context
     * @param $params
     */
    public function _hw_skin_apply_skin_data_callback_before ($params){
        extract($params);
        //params
        $marquee_wrapper = 'hw-marquee-container';
        if(isset($skin) && isset($hash_skin)) {
            //$skin_data = $skin->get_skin_data($hash_skin);

            //marquee params
            if(!isset($skin_options)) $skin_options = array();
            $json_config = HW_SKIN_Option::build_json_options($skin_options);
        }
        return array(
            'marquee_wrapper' => $marquee_wrapper,
            'marquee_id' => uniqid($this->id),
            'json_config' => $json_config
        );
    }
    /**
     * Creating widget front-end
     * @param $args
     * @param $instance
     */
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] ,$instance, $this->id_base);

        //$content = isset($instance['content'])? $instance['content'] : '';  //marquee content
        $source = isset($instance['source'])? $instance['source'] : 'content';  //data source
        $data = isset($instance['data'])? $instance['data'] : array();
        if(isset($data[$source])) {  //get data content
            $content = $data = $data[$source];
        }
        //parse source for slides
        if($source == 'slides' && is_numeric($data)) {
            $data = hwmq_get_mlslider_data($data);
        }

        //skin
        $hash_skin = isset($instance['skin'])? $instance['skin'] : 'default';

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];


        // This is where you run the code and display the output
        if( $this->skin){
            $skin_options = isset($instance['skin_settings'])? $instance['skin_settings'] : array();

            HW_SKIN::apply_skin_data(array(
                    'instance' => $this->skin,
                    'hash_skin' => $hash_skin,
                    'skin_options' => $skin_options
                )
                , array(
                    'callback_before' => array($this, '_hw_skin_apply_skin_data_callback_before'),
                    'callback_after' => array($this, '_hw_skin_apply_skin_data_callback_after'),
                ),
                array('instance' => $instance, 'data' => $data)

            );
        }

        echo $args['after_widget'];
    }


    /**
     * Widget Backend
     * @param $instance
     */
    public function form( $instance ) {
        //widget title
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Chữ chạy', 'hoangweb' );
        }

        //$library  = isset($instance['library'])? $instance['library'] : ''; //library
        $current_source = isset($instance['source'])? $instance['source'] : 'content';  //data source
        $data = isset($instance['data'])? $instance['data'] : array() ; //data
        //content scrolling
        $content = isset($data['content'])? $data['content'] : '';

        //skin
        $skin_setting = isset($instance['skin_settings'])? $instance['skin_settings'] : '';
        $skin = isset($instance['skin'])? $instance['skin'] : '';
        // Widget admin form
        ?>
        <p><strong>Lưu ý</strong>: nhấn nút lưu widget trong lần đầu tiên khởi tạo widget.</p>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <!--
        <p>
            <label for="<?php //echo $this->get_field_id('library')?>"><?php _e('Thư viện')?></label><br/>
            <select name="<?php //echo $this->get_field_name('library')?>" id="<?php //echo $this->get_field_id('library')?>">
                <?php /*foreach($this->libraries as $lib => $text) {
                    $selected = ($library == $lib)? 'selected="selected"' : '';
                    printf( '<option value="%s" %s>%s</option>', $lib, $selected, $text);
                }*/
                ?>

            </select>
        </p>
        -->
        <?php if(!empty($this->skin)):?>
        <p>
            <label for="<?php ?>"><?php _e('Thư viện')?></label>
            <?php echo $this->skin->get_skins_select_tag('skin',null,array('class'=>'widefat'),false);?>
            <!-- show skin options if exists -->
            <?php echo $this->skin->prepare_skin_options_fields('skin_settings', $skin_setting, $skin);?>
        </p>
        <?php endif;?>
        <p>
            <label for="<?php echo $this->get_field_id('source')?>"><?php _e('Nguồn')?></label><br/>
            <select name="<?php echo $this->get_field_name('source')?>" id="<?php echo $this->get_field_id('source')?>">
                <?php
                foreach($this->sources as $source => $text) {
                    $selected = selected($source, $current_source, false);
                    printf('<option %s value="%s">%s</option>',$selected, $source, $text);
                }
                ?>
            </select>
        </p>

        <!-- slides data -->
        <?php if($current_source == 'slides' ){
            $module = HW_TGM_Module_Activation::get_modules('hw-ml-slider', OBJECT);
            if(hw_is_active_module('hw-ml-slider')){
                $current_slider = isset($data['slider'])? $data['slider'] : '';
                $slides = hwmq_get_all_mlsliders();
            ?>
        <p id="<?php echo $this->get_field_id('source_slides')?>" style="">
            <label for="<?php echo $this->get_field_id('slider')?>"><?php _e('Slider')?></label><br/>
            <select name="<?php echo $this->get_field_name('data')?>[slides]" id="<?php echo $this->get_field_id('slides')?>">
                <?php

                foreach($slides as $id => $name) {
                    $selected = selected($id, $current_slider, false);
                    printf('<option %s value="%s">%s</option>', $selected, $id, $name);
                }
                ?>
            </select>
        </p>
        <?php } else echo __('Vui lòng kích hoạt module '.$module->name );
        }
            elseif($current_source == 'content'){ //default?>
        <!-- content text -->
        <p id="<?php echo $this->get_field_id('source_content')?>" style="">
            <label for="<?php echo $this->get_field_id('content')?>"><?php _e('Nội dung chạy')?></label><br/>
            <textarea class="" style="width:100%; min-height:200px" name="<?php echo $this->get_field_name('data')?>[content]" id="<?php echo $this->get_field_id('content')?>"><?php echo isset($data['content'])? $data['content'] : ''?></textarea>
        </p>
            <script>
                jQuery(function($){
                    CKEDITOR.config.extraPlugins = "hw_wp_media_button";
                    //CKEDITOR.config.width = 700;
                    var editor = '<?php echo $this->get_field_id('content');?>',
                        id = '<?php echo $this->number?>',
                        editor_obj = CKEDITOR.instances[editor];

                    var config = {
                        language: "vi",
                        //uiColor: "#9AB8F3",
                        width: 700,
                        extraPlugins : "hw_wp_media_button",
                        resize_enabled : true,
                        resize_minWidth: 300,
                        resize_minHeight: 300,
                        resize_maxWidth : 2000,
                        resize_maxHeight: 2000,
                        resize_dir: 'both'
                    };
                    // Replace the <textarea id="editor1"> with a CKEditor
                    // instance, using default configuration.
                    /*if (editor_obj) {
                     editor_obj.destroy(true);
                     CKEDITOR.remove(editor_obj);
                     }*/
                    if(!(!jQuery.isNumeric(id) && editor_obj)) CKEDITOR.replace( editor ,config);

                    $('#<?php echo $this->get_field_id('savewidget')?>').bind('click', function(e){
                        console.log('savewidget');
                        $('#'+editor).val( CKEDITOR.instances[editor].getData());
                    });
                });

            </script>
            <?php };?>

    <?php
    }

    /**
     * Updating widget replacing old instances with new
     * @param $new_instance
     * @param $old_instance
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $new_instance;
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['content'] = ( ! empty( $new_instance['content'] ) ) ? $new_instance['content']  : '';
        $instance['skin'] = ( ! empty( $new_instance['skin'] ) ) ? $new_instance['skin']  : ''; //skin selector
        //save current skin to db for this widget
        /*$this->skin->save_skin_assets(array(
            'skin' => array(
                'hash_skin' => $new_instance['skin'],
                'hwskin_condition' => $new_instance['skin_condition'],
                'theme_options' => $new_instance['theme_settings']
            )
        ));*/
        return $instance;
    }

    /**
     * init widget. Register and load the widget
     */
    public static function init() {
        register_widget(__CLASS__);
    }

}
add_action( 'hw_widgets_init', 'HW_Marquee_Widget::init' );