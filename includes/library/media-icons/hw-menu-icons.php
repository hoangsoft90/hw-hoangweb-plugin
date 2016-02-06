<?php
class HW_Menu_Icons {
    /**
     * Inline style for icon size, etc
     *
     * @since  0.4.0
     * @param  array  $values Menu item metadata value
     * @return string
     */
    public static  function get_style( $values ) {
        if(class_exists('Menu_Icons', false) ) return;

        $style_d = Menu_Icons::get( 'default_style' );
        $style_a = array();
        $style_s = '';

        if ( ! empty( $values['vertical_align'] ) ) {
            $style_a['vertical-align'] = $values['vertical_align'];
        }

        $style_a = array_diff_assoc( $style_a, $style_d );

        if ( ! empty( $style_a ) ) {
            foreach ( $style_a as $key => $value ) {
                $style_s .= sprintf( '%s:%s;', esc_attr( $key ), esc_attr( $value ) );
            }
        }

        return $style_s;
    }
    /**
     * render icon
     * @param array $values icon params
     * @param string $default default text
     * @param string $text text to display with icon
     * @return string
     */
    public static function render_icon($values, $default = '', $text = '') {
        if(empty($values['type'])) return esc_attr__($default);

        #font icon
        if($values['type'] !== 'image') {
            $class= array('_icon');
            if(isset($values['type'])) $class[] = $values['type'];
            if(isset($values['dashicons-icon'])) $class[] = $values['dashicons-icon'];
            //style attribute
            $css = '';
            if(!empty($values['vertical_align'])) $css .= ';vertical-align: '.$values['vertical_align'];
            if(!empty($values['font_size'])) $css .= ';vertical-align: '.$values['font_size'].' em';

            $icon_html = ('<i class="' .implode(' ', $class). '" style="'. $css .'"></i>');

        }
        #image icon
        else {
            $icon = get_post( $values['image-icon'] );
            if ( ! ( $icon instanceof WP_Post ) || 'attachment' !== $icon->post_type ) {
                return $default;
            }
            /*$t_class = ! empty( $values['hide_label'] ) ? 'visuallyhidden' : '';
            $title   = sprintf(
                '<span%s>%s</span>',
                ( ! empty( $t_class ) ) ? sprintf( ' class="%s"', esc_attr( $t_class ) ) : '',
                $default
            );
            */

            $i_class  = '_mi';
            $i_class .= empty( $values['hide_label'] ) ? " _{$values['position']}" : '';
            $i_style  = self::get_style( $values );
            $i_attrs  = array( 'class' => $i_class );

            if ( ! empty( $i_style ) ) {
                $i_attrs['style'] = $i_style;
            }

            $icon_html = wp_get_attachment_image(
                $icon->ID,
                $values['image_size'],
                false,
                $i_attrs
            );


        }
        $title = sprintf(
            '%s%s%s',
            'before' === $values['position'] ? '' : $text,
            $icon_html,
            'after' === $values['position'] ? '' : $text
        );
        return $title ;
    }
}