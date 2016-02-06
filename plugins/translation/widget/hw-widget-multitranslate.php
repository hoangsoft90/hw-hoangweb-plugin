<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 01/07/2015
 * Time: 10:34
 */
class HW_Multilang_Widget extends WP_Widget {
    /**
     * constructor
     */
    function __construct() {
        parent::__construct(
        // Base ID of your widget
            'hw_multilang',

            // Widget name will appear in UI
            __('Đa ngôn ngữ', 'hoangweb'),

            // Widget description
            array( 'description' => __( 'Lựa chọn đa ngôn ngữ', 'hoangweb' ), )
        );
        $this->setup_actions();
    }
    /**
     * setup actions
     */
    public function setup_actions() {

    }
    /**
     * Creating widget front-end
     * @param $args
     * @param $instance
     */
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $content = isset($instance['content'])? $instance['content'] : '';  //marquee content
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
        echo NHP_Options_mqtranslate::hw_get_langs_switcher();
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
            $title = __( 'Ngôn ngữ', 'hoangweb' );
        }

        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <a href="<?php echo NHP_Options_mqtranslate::get_setting_tab_page()?>" target="_blank">Cài đặt</a>
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
        return $instance;
    }

    /**
     * init widget. Register and load the widget
     */
    public static function init() {
        register_widget( 'HW_Multilang_Widget' );
    }
}
add_action( 'widgets_init', 'HW_Multilang_Widget::init' );