<?php
/**
 * this feature base on plugin menu-icons but we are moved to internal plugin located in hw-hoangweb plugin
 *
 */
if(class_exists('HW_AWC_WidgetFeature')) :
class AWC_WidgetFeature_fonticons extends HW_AWC_WidgetFeature{

    /**
     * parse font icon into array
     * @param string $font_icon
     */
    private static function decode_icon($font_icon = '') {
        $icon_obj = !empty($font_icon)? unserialize(base64_decode($font_icon)) : array();
        return $icon_obj;
    }

    /**
     * encode icon into string
     * @param string|array $font_icon
     */
    private static function encode_icon($font_icon) {
        return is_string($font_icon)? $font_icon : base64_encode(serialize($font_icon));
    }
    public function do_widget_feature_frontend($widget, $instance) {}
    public function is_active(){}
    /**
     * load option font icons
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        //valid
        if(!class_exists('Menu_Icons_Admin_Nav_Menus',false)) {
            echo 'Không tìm thấy class (Menu_Icons_Admin_Nav_Menus)';
            return;
        };

        $this->widget_instance = $instance;

        $item_id= 10000+ (int)$t->number;
        $input_id   = sprintf( 'menu-icons-%d', $item_id );
        $input_name = sprintf( 'menu-icons[%d]', $item_id );
        $type_ids   = array_values( array_filter( array_keys( Menu_Icons_Admin_Nav_Menus::_get_types() ) ) );
        $current = array(
            'hide_label' => '',
            'position' => 'before',
            'image_size' => 'full',
            'vertical_align' => 'middle',
            'font_size' => 1.9
        );
        //field values
        $font_icon = $this->get_field_value('font_icon');   //font icon
        $font_icon_obj = self::decode_icon($font_icon);

        $title = AWC_WidgetFeature_title_link::get_widget_title($t,$instance);  //find widget title

        echo '<div class="awc-widget-feature-fonticons menu-icons-wrap"><fieldset><legend>Biểu tượng</legend>';

        printf(
            '<a id="menu-icons-%1$d-select" class="_select button" href="javascript:void(0)" title="%2$s" data-id="%1$d" data-text="%2$s">%3$s</a>',
            esc_attr__( $item_id ),
            esc_attr(HW_Menu_Icons::render_icon($font_icon_obj, 'Chọn')),
            HW_Menu_Icons::render_icon($font_icon_obj, 'Chọn') //'Select'
        );

        echo 'Biểu tượng cho tiêu đề widget';
        echo '<p><em>Chú ý: Đặt tiêu đề widget và nhấn lưu widget một lần nữa.</em></p>';
        //require one or more field to indicate this feature form' fields in order to call 'validation' method.
        echo '<input type="hidden" name="'.$this->get_field_name('font_icon').'" id="'.$this->get_field_id('font_icon').'" value="'. $font_icon.'"/>';
        echo '<input type="hidden" name="'. $this->get_field_name('widget_id') .'" id="'. $this->get_field_id('widget_id') .'" value="'. $item_id .'"/>';

        ?>
        <!-- hidden fields -->
        <input type="hidden" name="<?php echo $this->get_field_name('widget_name')?>" id="<?php echo $this->get_field_id('widget_name')?>" value="<?php echo $t->id_base?>"/>
        <input type="hidden" name="<?php echo $this->get_field_name('title')?>" id="<?php echo $this->get_field_id('title') ?>" value="<?php echo $title ?>"/>

        <div class="original hidden">
           <?php ?>
            <p class="description">
                <label for="<?php echo esc_attr( $input_id ) ?>-type"><?php esc_html_e( 'Icon type' ); ?></label>
                <?php printf(
                    '<select id="%s-type" name="%s[type]" class="_type hasdep" data-dep-scope="div.menu-icons-wrap" data-dep-children=".field-icon-child" data-key="type">',
                    esc_attr( $input_id ),
                    esc_attr( $input_name )
                ) ?>
                <?php foreach ( Menu_Icons_Admin_Nav_Menus::_get_types() as $id => $props ) : ?>
                    <?php printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr( $id ),
                        selected( ( isset( $current['type'] ) && $id === $current['type'] ), true, false ),
                        esc_html( $props['label'] )
                    ) ?>
                <?php endforeach; ?>
                </select>
            </p>
            <?php ?>
            <?php foreach ( Menu_Icons_Admin_Nav_Menus::_get_types() as $props ) : ?>
                <?php if ( ! empty( $props['field_cb'] ) && is_callable( $props['field_cb'] ) ) : ?>
                    <?php call_user_func_array( $props['field_cb'], array( $item_id, $current ) ); ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach ( Menu_Icons_Admin_Nav_Menus::_get_fields( $current ) as $field ) :
                $field = Kucrut_Form_Field::create(
                    $field,
                    array(
                        'keys'               => array( 'menu-icons', $item_id ),
                        'inline_description' => true,
                    )
                );
                ?>
                <p class="description field-icon-child" data-dep-on='<?php echo json_encode( $type_ids ) ?>'>
                    <?php printf(
                        '<label for="%s">%s</label>',
                        esc_attr( $field->id ),
                        esc_html( $field->label )
                    ) ?>
                    <?php $field->render() ?>
                </p>
            <?php endforeach; ?>
        </div>
        <?php
        echo '</fieldset></div>';
    }
    /**
     * update widget icon
     * @param mixed $values  widget feature fields
     */
    private function update_widget_icon($values= ''){
        $icons = get_option('hwawc_widget_icons');
        if(!$icons) $icons = array();

        if(is_array($values)){
            //get widget anchor icon
            if(isset($values['font_icon'])) {
                $icon = $values['font_icon'];
            }
            //get widget title
            if(isset($values['title']) ){
                $title = $values['title'];
            }

        }
        if(isset($title) /*&& isset($icon)*/){
            $san_title = sanitize_title(strtolower($title));
            $icons[$san_title] = $values;    //add new or modify widget title link
            //update widget links
            update_option('hwawc_widget_icons', $icons);
        }
    }
    /**
     * return all widgets icons data or for specific widget
     * @param string $title: get widget icon by title (optional)
     * @return mixed|void
     */
    public static function get_all_widgets_icons($title = ''){
        static $widget_icons;
        if(!$widget_icons) $widget_icons = get_option('hwawc_widget_icons', array());
        if($title) {
            $san_title = sanitize_title(strtolower(HW_String::vn_str_filter( $title )));
            return isset($widget_icons[$san_title])? $widget_icons[$san_title] : '';
        }
        return $widget_icons;
    }
    /**
     * validation widget instance
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        if(isset($_POST['menu-icons']) && isset($new_wf_instance['widget_id'])
            && isset($_POST['menu-icons'][$new_wf_instance['widget_id']])) {

            if(!empty($_POST['menu-icons'][$new_wf_instance['widget_id']]['type'])) {
                $new_wf_instance['font_icon'] = self::encode_icon($_POST['menu-icons'][$new_wf_instance['widget_id']]);//base64_encode(serialize());
                $this->update_widget_icon($new_wf_instance);
            }
            elseif(isset($old_instance[$this->get_widget_feature_name()])) {
                $new_wf_instance = $old_instance[$this->get_widget_feature_name()];
            }

        }

        return $new_wf_instance;
    }

    /**
     * do while create the class instance
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget){
        if ( ! class_exists( 'Kucrut_Form_Field' ) && class_exists('Menu_Icons', false)) {
            require_once Menu_Icons::get( 'dir' ) . 'includes/library/form-fields.php';
        }

        add_action( 'wp_loaded', array( __CLASS__, '_init' ), 9 );
    }

    /**
     * @wp_hook action wp_loaded
     */
    public static function _init() {
        //enqueue assets
        if(class_exists('Menu_Icons_Settings',false)) {
            add_action( 'admin_enqueue_scripts', 'Menu_Icons_Settings::_enqueue_assets' , 99 );
        }
    }
    public function run(){

    }
    /**
     * modify widget title by insert icon next to title
     * @param string $title: widget title
     */

    static public function _widget_title_link($title){
        if(empty($title)) return;
        $icons = self::get_all_widgets_icons($title);
        if(!empty($icons)) {
            $icon = self::decode_icon($icons['font_icon']);
            $title = HW_Menu_Icons::render_icon($icon,'', $title);

        }
        //HW_Menu_Icons::render_icon();

        return $title;
    }
}
add_filter('widget_title', 'AWC_WidgetFeature_fonticons::_widget_title_link');
endif;