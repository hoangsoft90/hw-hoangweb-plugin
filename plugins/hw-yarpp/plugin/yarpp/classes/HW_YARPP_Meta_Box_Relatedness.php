<?php

class HW_YARPP_Meta_Box_Relatedness extends HW_YARPP_Meta_Box {
    public function display() {
        global $hw_yarpp;
        ?>
        <p><?php _e( 'YARPP limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.', 'hw-yarpp' ); ?> <span class='hw_yarpp_help' data-help="<?php echo esc_attr( __( 'The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.', 'hw-yarpp' ) ); ?>">&nbsp;</span></p>

        <?php
        $this->textbox( 'threshold', __( 'Match threshold:', 'hw-yarpp' ) );
        $this->weight( 'title', __( "Titles: ", 'hw-yarpp' ) );
        $this->weight( 'body', __( "Bodies: ", 'hw-yarpp' ) );

        foreach ( $hw_yarpp->get_taxonomies() as $taxonomy ) {
            $this->tax_weight( $taxonomy );
        }

        $this->checkbox( 'cross_relate', __( "Display results from all post types", 'hw-yarpp' )." <span class='hw_yarpp_help' data-help='" . esc_attr( __( "When \"display results from all post types\" is off, only posts will be displayed as related to a post, only pages will be displayed as related to a page, etc.", 'hw-yarpp' ) ) . "'>&nbsp;</span>" );
        $this->checkbox( 'past_only', __( "Show only previous posts?", 'hw-yarpp' ) );
    }
}
