<?php
/**
 * Class HW_YARPP_Meta_Box_Pool
 */
class HW_YARPP_Meta_Box_Pool extends HW_YARPP_Meta_Box {
	public function exclude($taxonomy, $string) {
		global $hw_yarpp;

		echo "<div class='hw_yarpp_form_row hw_yarpp_form_exclude'><div class='hw_yarpp_form_label'>";
		echo $string;
		echo "</div><div class='hw_yarpp_scroll_wrapper'><div class='exclude_terms' id='exclude_{$taxonomy}'>";

		$exclude_tt_ids = wp_parse_id_list( hw_yarpp_get_option( 'exclude' ) );
		$exclude_term_ids = $hw_yarpp->admin->get_term_ids_from_tt_ids($taxonomy, $exclude_tt_ids);
		if ( count( $exclude_term_ids ) ) {
			$terms = get_terms( $taxonomy, array( 'include' => $exclude_term_ids ) );
			foreach ( $terms as $term ) {
				echo "<input type='checkbox' name='exclude[{$term->term_taxonomy_id}]' id='exclude_{$term->term_taxonomy_id}' value='true' checked='checked' /> <label for='exclude_{$term->term_taxonomy_id}'>" . esc_html( $term->name ) . "</label> ";
			}
		}

		echo "</div></div></div>";
	}

	public function display() {
		global $hw_yarpp;
        $postTypeHelpMsg =
            'If you don&#39;t want one of these post types to display as related content, '.
            'uncheck the appropriate box in the &ldquo;Display Options&rdquo; panel below. Make sure you '.
            'click the &ldquo;Save Changes button&rdquo; at the bottom of this page.';

        include(HW_YARPP_DIR.'/includes/phtmls/hw_yarpp_meta_box_pool.phtml');
	}

}