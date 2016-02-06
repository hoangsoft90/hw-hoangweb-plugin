if(typeof $ == 'undefined') var $ = jQuery.noConflict();
/**
 * ready document event
 */
jQuery(document).ready(function(){
	var skin_select_tag = jQuery('#metaslider_configuration select[name="settings[theme]"]');
	skin_select_tag.closest('tr').css({'background':'#dadada'});
	//when change theme metaslider
	skin_select_tag.attr('onchange','hw_skin_hw_mlslider_skinsa07be6a16211cf49476724f245164943.skin_change_event(this.value,"ml_theme_thumb")');
	
	//expand cols into one by set colspan="2" to td table
    jQuery('#ml_theme_thumb').closest('td').attr('colspan','2');
});