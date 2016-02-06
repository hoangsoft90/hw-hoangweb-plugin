if(typeof $ == 'undefined') var $ = jQuery.noConflict();
if(typeof HW_WPCF7 == 'undefined') HW_WPCF7 = {};

/**
 * bind change storage hook for contact form event
 * @param object obj: select tag
 */
HW_WPCF7.wpcf_hookdata_change_event = function(obj){
	var hook = obj.value;
	HW_WPCF7.change_storage_hook_tab(hook);
};
/**
 * when change storage hook
 * @param string active: active hook
 */
HW_WPCF7.change_storage_hook_tab = function(active){
	for(var name in HW_WPCF7.storages_hook){
		if(active == name) jQuery('#hook_'+name).show();
		else jQuery('#hook_'+name).hide();
	}
};
/**
 * fetch google form fields by form ID
 * @param formID: gform ID input
 * @param btn: btn jquery object.
 * @param onload_trigger: fetch google form fields after DOM ready
 */
HW_WPCF7.fetch_googleform_fields = function(formID,btn,onload_trigger){
	
	var open = 1;
	if(!HW_WPCF7.fetch_googleform_fields_event_handle){
		HW_WPCF7.fetch_googleform_fields_event_handle = function(){
			/*if(!$(btn).data('first_set_open_true')) {
				$(btn).data({first_set_open_true:1 , open:1});	//open ajax default
			}*/
		    if(/*!$(btn).data('hw_open')*/!open) return;	//fetching data
		    
			var btn = jQuery(this),
		        result_holder = jQuery('#hw_parse_gform_result'),    //parse result container
		        btn_text = btn.text();
		    
			//$(btn).data({hw_open:0});	//lock ajax in next time until this ajax complete	//locked
			open =0 ;	//locked
			
		    btn.html('loading..').attr('disabled');   //temporary disable this button
		    result_holder.empty();  //clear previous result

            jQuery.ajax({
		        url: HW_WPCF7.fetch_gfields_url,
                type:'post',
                data:{gform_id: encodeURIComponent(jQuery(formID).val())},
		        success:function(data){
		            btn.removeAttr('disabled').html(btn_text); //resume the button
                    jQuery(result_holder).html(data);
		            open =1;	//unlock
		        }
		    });
		};
	}
	//bind click event to this btn
    jQuery(btn).click(HW_WPCF7.fetch_googleform_fields_event_handle);
	//onload trigger
	if(onload_trigger) jQuery(btn).trigger('click');
};
jQuery(document).ready(function($){
	/**
	 * init skin wpcf7 change event
	 */
	var skin_select_tag = $('#hw_wpcf7_skin');
	
	//when change theme wpcf7.note: access object by name as format of 'hw_skin_{folder_name}'
	skin_select_tag.attr('onchange','hw_skin_hw_wpcf7_skinsc561665965045d55280dc94db92980ce.skin_change_event(this.value,"preview_skin")');
	
	//expand cols into one by set colspan="2" to td table
	$('#ml_theme_thumb').closest('td').attr('colspan','2');
	/**
	 * init load wpcf storage hook
	 */
	$('#hwcf_data_hook').trigger('change');
	/**
	 * convernion when you sticky save form button
	 */
	$("div.save-contact-form").sticky({topSpacing:80});
});
/**
 * ready 
 */
jQuery(function($){    
	HW_WPCF7.fetch_googleform_fields($('#hw_gformID'), $('#grab_gform_fields'),true);
	
});