<?php
/** Single Product ********************************************************/

if ( ! function_exists( 'hwwoo_show_product_attributes' ) ) {

    /**
     * Output the product attributes before the single product summary.
     *
     * @subpackage	Product
     */
    function hwwoo_show_product_attributes() {
        wc_get_template( 'single-product/product-attribute.php' );
    }
}
if ( ! function_exists( 'hwoo_archive_result_count' ) ) {
    /**
     * products result count in archive page
     */
    function hwoo_archive_result_count() {
        $data = utility_data(func_get_args());
        global $wp_query;

        if ( ! woocommerce_products_will_display() )
            return;

        $paged    = max( 1, $wp_query->get( 'paged' ) );
        $per_page = $wp_query->get( 'posts_per_page' );
        $total    = $wp_query->found_posts;
        $first    = ( $per_page * $paged ) - $per_page + 1;
        $last     = min( $total, $wp_query->get( 'posts_per_page' ) * $paged );

        $context = HW_Timber::load($data['current_working_template']);
        $context['total'] = $total;
        $context['first'] = $first;
        $context['last'] = $last;
        $context['show_all'] = $total <= $per_page || -1 == $per_page;

        HW_Timber::_render(array('woo/loop/result-count.twig'), $context);

    }
}

if ( ! function_exists( 'dropdown_variation_attribute' ) ) {
    /**
     * @param $attribute_name
     * @param $options
     */
    function dropdown_variation_attribute($attribute_name, $options) {
        global $product;
        $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) : $product->get_variation_default_attribute( $attribute_name );
        wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
    }
}
if ( ! function_exists( 'hwoo_show_quantity_input' ) ) {
    /**
     * @param $product
     */
    function hwoo_show_quantity_input( $product) {
        if($product instanceof HWoo_TimberProduct) $product = $product->wc_product();
        woocommerce_quantity_input( array(
            'input_name'  => 'quantity[' . $product->id . ']',
            'input_value' => ( isset( $_POST['quantity'][$product->id] ) ? wc_stock_amount( $_POST['quantity'][$product->id] ) : 0 ),
            'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
            'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
        ) );
    }
}
if ( ! function_exists( 'hwoo_simple_product_quantity_input' ) ) {
    /**
     * @param $product
     */
    function hwoo_simple_product_quantity_input($product) {
        if($product instanceof HWoo_TimberProduct) $product = $product->wc_product();
        if ( ! $product->is_sold_individually() ) {
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product ),
                'input_value' => ( isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 )
            ) );
        }
    }
}
if ( ! function_exists( 'hwoo_cart_item_quantity_input' ) ) {
    /**
     * @param $_product
     * @param $cart_item_key
     * @param $cart_item
     * @param array $data
     */
    function hwoo_cart_item_quantity_input($_product, $cart_item_key,$cart_item,$data = array()) {
        if ( $_product->is_sold_individually() ) {
            $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
        } else {
            $product_quantity = woocommerce_quantity_input( array(
                'input_name'  => "cart[{$cart_item_key}][qty]",
                'input_value' => $cart_item['quantity'],
                'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                'min_value'   => '0'
            ), $_product, false );
        }

        echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
    }
}
if ( ! function_exists( 'hwoo_archive_product_orderby_options' ) ) {
    /**
     * @param array $data
     * @woo-template loop/orderby.php
     */
    function hwoo_archive_product_orderby_options($data = array()) {
        $data = utility_data(func_get_args());
        if(isset($data['catalog_orderby_options']))
        foreach ( $data['catalog_orderby_options'] as $id => $name ) :
            printf('<option value="%s" %s>%s</option>', esc_attr( $id ), selected( $data['orderby'], $id ,false),esc_html( $name ));
        endforeach;
    }
}
if ( ! function_exists( 'hwoo_archive_product_orderby_hidden' ) ) {
    /**
     * @param array $data
     * @woo-template loop/orderby.php
     */
    function hwoo_archive_product_orderby_hidden($data = array()) {
        // Keep query string vars intact
        foreach ( $_GET as $key => $val ) {
            if ( 'orderby' === $key || 'submit' === $key ) {
                continue;
            }
            if ( is_array( $val ) ) {
                foreach( $val as $innerVal ) {
                    echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
                }
            } else {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
            }
        }
    }
}
if ( ! function_exists( 'hwoo_display_pagination' ) ) {
    /**
     * @param array $data
     * @woo-template loop/pagination.php
     */
    function hwoo_display_pagination($data = array()) {
        global $wp_query;

        if ( $wp_query->max_num_pages <= 1 ) {
            return;
        }
        $links = paginate_links( apply_filters( 'woocommerce_pagination_args', array(
            'base'         => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
            'format'       => '',
            'add_args'     => '',
            'current'      => max( 1, get_query_var( 'paged' ) ),
            'total'        => $wp_query->max_num_pages,
            'prev_text'    => '&larr;',
            'next_text'    => '&rarr;',
            'type'         => 'list',
            'end_size'     => 3,
            'mid_size'     => 3
        ) ) );
        echo apply_filters('hwoo_pagination', $links);
    }
}
if ( ! function_exists( 'hwoo_shipping_method_options' ) ) {
    /**
     * generate shipping method options tag
     * @param array $data
     */
    function hwoo_shipping_method_options() {
        $data = utility_data(func_get_args());
        foreach ( $data['available_methods'] as $method ){
            printf('<option value="%s" %s>%s</option>',
                esc_attr($method->id ),
                selected( $method->id, $data['chosen_method'] , false),
                wp_kses_post( wc_cart_totals_shipping_method_label( $method ) )
            );
        }
    }
}
if ( ! function_exists( 'hwoo_shipping_method_list' ) ) {
    /**
     * @param array $data
     */
    function hwoo_shipping_method_list( ) {
        $data = utility_data(func_get_args());
        $index = $data['index'];
        $chosen_method = $data['chosen_method'];

        foreach ( $data['available_methods'] as $method ) {
            ?>
            <li>
                <input type="radio" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>_<?php echo sanitize_title( $method->id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" <?php checked( $method->id, $chosen_method ); ?> class="shipping_method" />
                <label for="shipping_method_<?php echo $index; ?>_<?php echo sanitize_title( $method->id ); ?>"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?></label>
            </li>
    <?php
        }
    }
}
if ( ! function_exists( 'hwoo_display_shipping_countries_options' ) ) {
    /**
     * @param array $data
     */
    function hwoo_display_shipping_countries_options($data = array()) {
        foreach( WC()->countries->get_shipping_countries() as $key => $value )
            echo '<option value="' . esc_attr( $key ) . '"' . selected( WC()->customer->get_shipping_country(), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
    }
}
if ( ! function_exists( 'hwoo_display_countries_states_options' ) ) {
    /**
     * @param array $data
     */
    function hwoo_display_countries_states_options( ) {
        $data = utility_data(func_get_args());
        foreach ( $data['states'] as $ckey => $cvalue )
            echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $data['current_r'], $ckey, false ) . '>' . __( esc_html( $cvalue ), 'woocommerce' ) .'</option>';
    }
}
/***************checkout*/
if ( ! function_exists( 'hwoo_checkout_billing_form_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_checkout_billing_form_fields() {
        $data = utility_data(func_get_args()) ;
        foreach ( $data['checkout']->checkout_fields['billing'] as $key => $field ) :
            woocommerce_form_field( $key, $field, $data['checkout']->get_value( $key ) );
        endforeach;
    }
}
if ( ! function_exists( 'hwoo_checkout_account_form_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_checkout_account_form_fields( ) {
        $data = utility_data(func_get_args());
        foreach ( $data['checkout']->checkout_fields['account'] as $key => $field ) :
            woocommerce_form_field( $key, $field, $data['checkout']->get_value( $key ) );
        endforeach;
    }
}
if ( ! function_exists( 'hwoo_checkout_shipping_form_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_checkout_shipping_form_fields( ) {
        $data = utility_data(func_get_args());
        $checkout = $data['checkout'];
        foreach ( $checkout->checkout_fields['shipping'] as $key => $field ) :
            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
        endforeach;
    }
}
if ( ! function_exists( 'hwoo_checkout_order_form_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_checkout_order_form_fields() {
        $data= utility_data(func_get_args());
        $checkout = $data['checkout'];
        foreach ( $checkout->checkout_fields['order'] as $key => $field ) :
            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
        endforeach;
    }
}
if ( ! function_exists( 'hwoo_form_edit_address_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_form_edit_address_fields() {
        $data= utility_data(func_get_args());
        $address = $data['address'];
        foreach ( $address as $key => $field ) {
            woocommerce_form_field( $key, $field, ! empty( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : $field['value'] );
        }
    }
}

if ( ! function_exists( 'hwoo_login_form' ) ) {
    /**
     * @param string $message
     * @param string $redirect
     * @param array $data
     */
    function hwoo_login_form($message='',$redirect='', $data = array()) {
        $args = func_get_args();
        $data = end($args);

        if($message ) $data['message'] = $message;
        if($redirect ) $data['redirect'] = $redirect? true: false;
        if(!isset($data['hidden'])) $data['hidden'] =  true;

        woocommerce_login_form(
            $data
        );
    }
}
if ( ! function_exists( 'hwoo_payment_fields' ) ) {
    /**
     * @param array $data
     */
    function hwoo_payment_fields($gateway) {
        //$gateway = $data['gateway'];
        if ( $gateway->has_fields() || $gateway->get_description() ) {
            echo '<div class="payment_box payment_method_' . $gateway->id . '" style="display:none;">';
            $gateway->payment_fields();
            echo '</div>';
        }
    }
}
/**
 * @param array $data
 */
function hwoo_form_pay_nonce_field($data= array()) {
    wp_nonce_field( 'woocommerce-pay' );
    echo '<input type="hidden" name="woocommerce_pay" value="1" />';
}
/**
 * @param array $data
 */
function hwoo_form_add_payment_method_nonce_field($data= array()) {
    wp_nonce_field( 'woocommerce-add-payment-method' );
    echo '<input type="hidden" name="woocommerce_add_payment_method" value="1" />';
}
/**
 * @param array $data
 */
function hwoo_form_save_account_details_nonce_field($data= array()) {
    wp_nonce_field( 'save_account_details' );
    echo '<input type="hidden" name="action" value="save_account_details" />';
}
/**
 * @param array $data
 */
function hwoo_form_edit_address_nonce_field($data= array()) {
    wp_nonce_field( 'woocommerce-edit_address' );
    echo '<input type="hidden" name="action" value="edit_address" />';
}
/**
 * @param array $data
 */
function hwoo_auth_form_login_nonce_field() {
    $data= utility_data(func_get_args());
    wp_nonce_field( 'woocommerce-login' );
    echo '<input type="hidden" name="redirect" value="'.esc_url( $data['redirect_url'] ).'" />';
}

/**
 * output review order shipping
 */
function hwoo_review_order_shipping() {
    if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
        do_action( 'woocommerce_review_order_before_shipping' );
        wc_cart_totals_shipping_html();
        do_action( 'woocommerce_review_order_after_shipping' );
    endif;
}

/**
 * @param array $data
 */
function hwoo_email_print_order_status($order, $data = array()) {
    switch ( $order->get_status() ) {
        case "completed" :
            echo $order->email_order_items_table( $order->is_download_permitted(), false, true );
            break;
        case "processing" :
            echo $order->email_order_items_table( $order->is_download_permitted(), true, true );
            break;
        default :
            echo $order->email_order_items_table( $order->is_download_permitted(), true, false );
            break;
    }
}
if ( ! function_exists( 'hwoo_myaccount_show_address' ) ) {
    /**
     * show address in myaccount page
     */
    function hwoo_myaccount_show_address() {
        wc_get_template( 'myaccount/my-address.php' );
    }
}
if ( ! function_exists( 'hwoo_myaccount_show_downloads' ) ) {
    /**
     * show current user downloads in myaccount page
     */
    function hwoo_myaccount_show_downloads() {
        wc_get_template( 'myaccount/my-downloads.php' );
    }
}
if ( ! function_exists( 'hwoo_myaccount_show_orders' ) ) {
    /**
     * show customer orders in myaccount page
     * @param $data
     */
    function hwoo_myaccount_show_orders($data = array()) {
        wc_get_template( 'myaccount/my-orders.php' );
    }
}
if ( ! function_exists( 'hwoo_order_display_details' ) ) {
    /**
     * @param array $data
     */
    function hwoo_order_display_details() {
        $data= utility_data(func_get_args());
        $order = $data['order'];

        foreach( $order->get_items() as $item_id => $item ) {
            wc_get_template( 'order/order-details-item.php', array(
                'order'   => $order,
                'item_id' => $item_id,
                'item'    => $item,
                'product' => apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item )
            ) );
        }
    }
}
if ( ! function_exists( 'hwoo_review_comment_form' ) ) {
/**
 * display comment form for reviewing product
 */
function hwoo_review_comment_form() {
    $commenter = wp_get_current_commenter();

    $comment_form = array(
        'title_reply'          => have_comments() ? __( 'Add a review', 'woocommerce' ) : __( 'Be the first to review', 'woocommerce' ) . ' &ldquo;' . get_the_title() . '&rdquo;',
        'title_reply_to'       => __( 'Leave a Reply to %s', 'woocommerce' ),
        'comment_notes_before' => '',
        'comment_notes_after'  => '',
        'fields'               => array(
            'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'woocommerce' ) . ' <span class="required">*</span></label> ' .
                '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
            'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'woocommerce' ) . ' <span class="required">*</span></label> ' .
                '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
        ),
        'label_submit'  => __( 'Submit', 'woocommerce' ),
        'logged_in_as'  => '',
        'comment_field' => ''
    );

    if ( $account_page_url = wc_get_page_permalink( 'myaccount' ) ) {
        $comment_form['must_log_in'] = '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a review.', 'woocommerce' ), esc_url( $account_page_url ) ) . '</p>';
    }

    if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
        $comment_form['comment_field'] = '<p class="comment-form-rating"><label for="rating">' . __( 'Your Rating', 'woocommerce' ) .'</label><select name="rating" id="rating">
							<option value="">' . __( 'Rate&hellip;', 'woocommerce' ) . '</option>
							<option value="5">' . __( 'Perfect', 'woocommerce' ) . '</option>
							<option value="4">' . __( 'Good', 'woocommerce' ) . '</option>
							<option value="3">' . __( 'Average', 'woocommerce' ) . '</option>
							<option value="2">' . __( 'Not that bad', 'woocommerce' ) . '</option>
							<option value="1">' . __( 'Very Poor', 'woocommerce' ) . '</option>
						</select></p>';
    }

    $comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . __( 'Your Review', 'woocommerce' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>';

    comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
}
}
