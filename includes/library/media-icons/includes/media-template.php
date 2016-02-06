<script type="text/html" id="tmpl-menu-icons-settings-field-text">
	<span>{{ data.label }}</span>
	<input type="text" data-setting="{{ data.id }}" value="{{ data.value }}" />
</script>

<script type="text/html" id="tmpl-menu-icons-settings-field-number">
    <!-- by hoangweb -->
    <# if ( data.id == 'font_size' ) { #>  <div style="border:1px solid #dadada;background: #008726;color:#ffffff" class="hw-menu-icon-setting">  <# } #>

    <span>{{ data.label }}</span>
	<input type="number" min="{{ data.attributes.min }}" step="{{ data.attributes.step }}" data-setting="{{ data.id }}" value="{{ data.value }}" />
	<# if ( data.description ) { #><em>{{ data.description }} </em><# } #>

    <# if ( data.id == 'font_size' ) { #>  </div>  <# } #><!-- by hoangweb -->
</script>

<script type="text/html" id="tmpl-menu-icons-settings-field-select">
	<!-- by hoangweb -->
    <# if ( data.id == 'vertical_align' || data.id == 'image_size') { #>  <div style="border:1px solid #dadada;background: #008726;color:#ffffff" class="hw-menu-icon-setting">  <# } #>

    <span>{{ data.label }}</span>
	<select data-setting="{{ data.id }}">
		<# _.each( data.choices, function( choice ) { #>
			<# if ( data.value === choice.value ) { #>
				<option selected="selected" value="{{ choice.value }}">{{ choice.label }}</option>
			<# } else { #>
				<option value="{{ choice.value }}">{{ choice.label }}</option>
			<# } #>
		<# } ); #>
	</select>
        <# if ( data.id == 'vertical_align' ) { #>  </div>  <# } #><!-- by hoangweb -->
</script>

<?php do_action( 'menu_icons_js_templates' );
