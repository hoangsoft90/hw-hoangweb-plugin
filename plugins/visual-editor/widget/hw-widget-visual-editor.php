<?php
/**
 * Class HW_VisualEditor_Widget
 */
class HW_VisualEditor_Widget extends WP_Widget {
    /**
     * constructor
     */
    function __construct() {
        parent::__construct(
        // Base ID of your widget
            'hw_visualeditor',

            // Widget name will appear in UI
            __('Chèn nội dung HTML', 'hoangweb'),

            // Widget description
            array( 'description' => __( 'Chèn nội dung HTML', 'hoangweb' ), )
        );
        $this->setup_actions();
        //register widget features
        if(class_exists('HW_AWC')) {
            //HW_AWC::register_widget_feature($this, 'fonticons');  //dynamic config
        }
    }
    /**
     * setup actions
     */
    public function setup_actions() {

        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));

    }
    /**
     * for backend
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
     * Creating widget front-end
     * @param $args
     * @param $instance
     */
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $content = isset($instance['content'])? $instance['content'] : '';  //HTML content

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];
        echo $content;
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
            $title = __( 'Tiêu đề', 'hoangweb' );
        }
        //content
        $content = isset($instance['content'])? $instance['content'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('content')?>"><?php _e('Nội dung')?></label><br/>
            <textarea class="" style="width:100%; min-height:200px" name="<?php echo $this->get_field_name('content')?>" id="<?php echo $this->get_field_id('content')?>"><?php echo $content?></textarea>
        </p>
        <script>
            jQuery(function($){
                if(!CKEDITOR) return;
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
                if(!(!jQuery.isNumeric(id) && editor_obj) && CKEDITOR) CKEDITOR.replace( editor ,config);


                $('#<?php echo $this->get_field_id('savewidget')?>').bind('click', function(e){
                    $('#'+editor).val( CKEDITOR.instances[editor].getData());
                });
            });

        </script>
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
        return $instance;
    }
    /**
     * init widget. Register and load the widget
     */
    public static function init() {
        register_widget( 'HW_VisualEditor_Widget' );
    }
}
add_action('hw_widgets_init', 'HW_VisualEditor_Widget::init');