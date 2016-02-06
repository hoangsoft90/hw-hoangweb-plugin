<?php
/**
create wpcf7 fields
 */
add_action('wpcf7_init','hw_wpcf7_create_fields');
function hw_wpcf7_create_fields(){
    wpcf7_add_shortcode(array('province', 'province*'), '_hw_wcpf7_province_shortcode_ddlQuanHuyen',true);

    //register country field
    wpcf7_add_shortcode(array('nationality','nationality*'), '_hw_wcpf7_country_field_shortcode',true);

    //wpcf7_add_shortcode(('tinhthanh'), '_shortcode_ddlTinhthanh',true);
    //wpcf7_add_shortcode(('tinhthanh*'), '_shortcode_ddlTinhthanh',true);
}

/**
 * callback wpcf7 province form field
 * @param $tag
 * @return string
 */
function _hw_wcpf7_province_shortcode_ddlQuanHuyen($tag) {
    if (!is_array($tag)) return '';
    $tag = new WPCF7_Shortcode( $tag );

    if ( empty( $tag->name ) )
        return '';

    $validation_error = wpcf7_get_validation_error( $tag->name );

    $class = wpcf7_form_controls_class( $tag->type );

    if ( $validation_error )
        $class .= ' wpcf7-not-valid';

    $atts = array();

    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    if ( $tag->is_required() )
        $atts['aria-required'] = 'true';

    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $multiple = $tag->has_option( 'multiple' );
    $include_blank = $tag->has_option( 'include_blank' );
    $first_as_label = $tag->has_option( 'first_as_label' );

    //$values = $tag->values;
    $values = array('0'=>'Select country');
    $values = array_merge($values , hw_wpcf7_field_districts_data());
    $labels = $tag->labels;

    $html = '';
    foreach ( $values as $key => $value ) {

        $item_atts = array(
            'value' => $value,
            );

        $item_atts = wpcf7_format_atts( $item_atts );

        $label = isset( $labels[$key] ) ? $labels[$key] : $value;

        $html .= sprintf( '<option %1$s>%2$s</option>',
            $item_atts, esc_html( $label ) );
    }
    if ( $multiple )
        $atts['multiple'] = 'multiple';

    $atts['name'] = $tag->name . ( $multiple ? '[]' : '' );

    $atts = wpcf7_format_atts( $atts );

    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
        sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

    return $html;
}

/**
 * render wpcf7 field for counties
 * @param $tag
 */
function _hw_wcpf7_country_field_shortcode($tag){
    if (!is_array($tag)) return '';
    $tag = new WPCF7_Shortcode( $tag );

    if ( empty( $tag->name ) )
        return '';

    $validation_error = wpcf7_get_validation_error( $tag->name );

    $class = wpcf7_form_controls_class( $tag->type );

    if ( $validation_error )
        $class .= ' wpcf7-not-valid';

    $atts = array();

    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    if ( $tag->is_required() )
        $atts['aria-required'] = 'true';

    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $multiple = $tag->has_option( 'multiple' );
    $include_blank = $tag->has_option( 'include_blank' );
    $first_as_label = $tag->has_option( 'first_as_label' );

    //$values = $tag->values;
    $values = array('0'=>'Select country');
    $values = array_merge($values, hw_wpcf7_field_countries_data());

    $labels = $tag->labels;

    $html = '';
    foreach ( $values as $key => $value ) {

        $item_atts = array(
            'value' => $value,
        );

        $item_atts = wpcf7_format_atts( $item_atts );

        $label = isset( $labels[$key] ) ? $labels[$key] : $value;

        $html .= sprintf( '<option %1$s>%2$s</option>',
            $item_atts, esc_html( $label ) );
    }
    if ( $multiple )
        $atts['multiple'] = 'multiple';

    $atts['name'] = $tag->name . ( $multiple ? '[]' : '' );

    $atts = wpcf7_format_atts( $atts );

    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
        sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

    return $html;
}
/**
 * return districts data
 */
function hw_wpcf7_field_districts_data(){
    $data = array(
        'ha-noi' => 'Ha Noi'
    );
}

/**
 * return countries list in array
 * @return array
 */
function hw_wpcf7_field_countries_data(){
    $data = array(
        'United States','Canada','Afghanistan','Albania','Algeria','American Samoa','Andorra','Angola'
    );
    return $data;
}