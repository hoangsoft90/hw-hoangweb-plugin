jQuery(function($) {
	var loaded_metabox = false;
	var display = $('#hw-yarpp-related-posts');
	function hw_metabox_display() {
		if ( !$('#hw_yarpp_relatedposts .inside').is(':visible') ||
			 !display.length ||
			 !$('#post_ID').val() )
			return;
		if ( !loaded_metabox ) {
			loaded_metabox = true;
			$.ajax({type:'POST',
				url: ajaxurl,
				data: {
					action: 'hw_yarpp_display',
					domain: 'metabox',
					ID: $('#post_ID').val(),
					'_ajax_nonce': $('#hw_yarpp_display-nonce').val()
				},
				success:function(html){display.html(html)},
				dataType:'html'});
		}
	}
	$('#hw_yarpp_relatedposts .handlediv, #hw_yarpp_relatedposts-hide').click(
		function() {
			setTimeout(hw_metabox_display, 0);
		});
    hw_metabox_display();
});
