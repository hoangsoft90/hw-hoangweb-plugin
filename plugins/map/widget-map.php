<?php

/**
 * Class HW_Gmap_Widget
 */
class HW_Gmap_Widget extends WP_Widget {

    /**
     * HW_SKIN
     * @var null
     */
    public $skin = null;

    /**
     * constructor
     */
    function __construct() {
        parent::__construct(
        // Base ID of your widget
            'hw_gmap',

            // Widget name will appear in UI
            __('Bản đồ', 'hoangweb'),

            // Widget description
            array( 'description' => __( 'Hiển thị bản đồ', 'hoangweb' ), )
        );
    }
    /**
     * for backend
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {

    }
    /**
     * Creating widget front-end
     * @param $args
     * @param $instance
     */
    // This is where the action happens
    public function widget( $args, $instance ) {

        //widget title
        $title = apply_filters( 'widget_title', $instance['title'] ,$instance, $this->id_base);
        $map = HW_Module_Gmap::get();

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

        //render map
        echo $map->_hw_render_google_map($instance);

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
            $title = __( '' );
        }
        $address = isset($instance['address'])? $instance['address'] : '';   //location
        $width = isset($instance['width'])? $instance['width'] : '';    //width
        $height = isset($instance['height'])? $instance['height'] : ''; //height
        $show_searchbox = isset($instance['show_searchbox']) && $instance['show_searchbox']? 1 : 0; //show search iinput box

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label><br/>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e( 'Vị trí:' ); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('address')?>" id="<?php echo $this->get_field_id('address')?>" value="<?php echo $address?>"/><br/>
            (<em>Để trống lấy mặc định bởi cài đặt module Map.</em>)<br/>
            <a href="<?php echo HW_Module_Settings_page::get_module_setting_page('map')?>" target="_blank">Cấu hình gmap</a>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width (px/%):' ); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('width')?>" id="<?php echo $this->get_field_id('width')?>" value="<?php echo $width?>"/>(mặc định px)<br/>
            (<em>Để trống lấy mặc định bởi cài đặt module Map.</em>)
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height (px/%):' ); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('height')?>" id="<?php echo $this->get_field_id('height')?>" value="<?php echo $height?>"/>(mặc định px)<br/>
            (<em>Để trống lấy mặc định bởi cài đặt module Map.</em>)
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'show_searchbox' ); ?>"><?php _e( 'Hiển thị tìm kiếm:' ); ?></label><br/>
            <input type="checkbox" name="<?php echo $this->get_field_name('show_searchbox')?>" id="<?php echo $this->get_field_id('show_searchbox')?>" <?php checked($show_searchbox)?>/><br/>
            (<em>Để trống lấy mặc định bởi cài đặt module Map.</em>)
        </p>
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
        $instance['show_searchbox'] = ( ! empty( $new_instance['show_searchbox'] ) ) ? 1 : 0;
        return $instance;
    }
    /**
     * init widget. Register and load the widget
     */
    public static function init() {
        register_widget(__CLASS__);
    }
}
add_action( 'hw_widgets_init', 'HW_Gmap_Widget::init' );