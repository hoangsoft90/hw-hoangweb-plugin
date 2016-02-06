/**
 * setup pagination with ajax feature
 * @param id
 * @param container
 */
function hwtpl_setup_ajax_pagination(id,container){
	jQuery(id).find('a[href]').bind('click',function(event){
		event.preventDefault();
		jQuery(id).addClass('hwtpl-disabled');	//disable element from mouse event
		jQuery(id).find('.ajax-loader').html('<img src="'+__hwcpl_object.home_url+'/wp-admin/images/loading.gif"/>');
		jQuery.ajax({
			url: jQuery(this).attr('href'),
			success:function(html){
				jQuery(container).html(html);
			}
		});
	});
}