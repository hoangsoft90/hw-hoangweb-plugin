/**
 * register callbacks
 */
//register callbacks
if(typeof __hw_localize_object !== 'undefined') {
    __hw_localize_object.setup_callbacks(__hwcpl_object);
}

/**
 * get post type custom fields
 * @param pt: post types
 * @param widget_id : widget id
 * @param field_holder: container
 */
__hwcpl_object.get_customfields_by_type = function(pt,widget_id,field_holder){
    var posttype = jQuery(pt).val();
    jQuery('#'+field_holder).empty().html('<img src="'+__hwcpl_object.images_url+'/loading.gif"/>');
    jQuery.ajax({
        url: __hwcpl_object.fetch_customfields_bytype_URL+'&pt='+posttype.join(',')+'&widget='+widget_id,
        success:function(data){
            jQuery('#'+field_holder).html(data);    //show field more meta keys
        }
    });
}

/**
 * change terms of taxonomy
 * @param obj
 * @param url
 * @param target
 * @param callbacks
 */
__hwcpl_object.hwtpl_change_terms_taxonomy = function(obj,url,target, callbacks)
{
	var tax = obj.value,
        id = jQuery(obj).data('id');
	jQuery(target).html('<img src="'+__hwcpl_object.home_url+'/wp-admin/images/loading.gif"/>');
    jQuery(obj).attr('disabled', 'disabled');   //disable select tag until ajax complete working

    this.do_callback_before(id,callbacks);  //do callback before ajax

	jQuery.ajax({
		//dataType:'json',  //if case of ajax content not valid json in output
		url:url+'&tax='+tax,
		success:function(data){
            if(typeof data == 'string') data = JSON.parse(jQuery.trim(data));

			jQuery(target).html(' ').append(data.html);
            jQuery(obj).removeAttr('disabled');
            //after ajax
            __hwcpl_object.do_callback_after(id, callbacks,data);

		}
	});
}
/**
 * change post type taxonomies
 * @param obj
 * @param target
 * @param callbacks
 */
__hwcpl_object.hwtpl_change_taxonomies_posttype = function(obj, target, callbacks) {
    var pt = jQuery(obj).val(), //selected post type
        id = jQuery(obj).data('id'),
        url = __hwcpl_object.ajax_url + '?action=hw_change_posttype_taxonomies&nonce=' + __hwcpl_object.hw_change_pt_taxes_nonce,
        select_tag = jQuery(target).find('select:eq(0)');   //.empty().clone();

    jQuery(obj).attr('disabled', 'disabled').addClass('hw-bg-loading');   //disable trigger object
    jQuery(target).find('select:eq(0)').html(' ').append(jQuery('<option>', {value:'',text : 'Loading..'}));     //show loading status

    ////before ajax
    this.do_callback_before(id,callbacks);

    jQuery.ajax({
        //dataType:'json',  //if ajax content not valid json in output
        url: url,
        method: 'POST',
        data : {'posttype': pt},
        success:function(data){
            if(typeof data == 'string') data = JSON.parse(jQuery.trim(data));

            //clear all options tag
            select_tag.html(' ').append(jQuery('<option>', {
                value: "",
                text: "------Select------"  //.data[value]
            }));
            jQuery.each(data.data, function(value, text) {
                select_tag.append(jQuery('<option>', {
                    value: value,
                    text: text  //.data[value]
                }));
            });

            jQuery(obj).removeAttr('disabled').removeClass('hw-bg-loading');
            //jQuery(target).append(select_tag);
            //after ajax
            __hwcpl_object.do_callback_after(id,callbacks, data);
        }
    });
}
/**
 * change query data option
 * @param obj
 */
__hwcpl_object.change_wp_query_option = function(obj) {
    var context = obj.value,
        types = jQuery(obj).closest('p.hwtpl_query_types').find('input[type=radio]'),
        id = jQuery(obj).data('id');

    //if(context == 'current_context') {
        jQuery('#'+id).show();
        jQuery(types).each(function(i,ele) {
            if(jQuery(ele).data('id') != id) jQuery('#'+jQuery(ele).data('id')).hide();    //hide others
        });
    //}
}
/**
 * query posts with criteria
 */
__hwcpl_object.query_posts = function (btnTog, form_selector, callbacks) {
    if(typeof form_selector == 'string') form_selector = jQuery(form_selector); //get form object
    var fields_values = jQuery(form_selector).find(':input').serializeArray(),
        btnLabel = jQuery(btnTog).html(),   //current
        url = __hwcpl_object.ajax_url + '?action=hw_query_posts&nonce=' + __hwcpl_object.hw_query_posts_nonce,
        $this = this,   //refer to scope
        id = jQuery(btnTog).data('id'); //current widget id

    if(jQuery(btnTog).hw_is_ajax_working({loadingText: "loading.."})) return;     //ajax working

    //before ajax
    $this.do_callback_before(id,callbacks);

    //alter event show from element to script
    if(jQuery(btnTog).attr('onclick')) {
        jQuery(btnTog).removeAttr("onclick");
        //rebind click event to button toggle
        jQuery(btnTog).bind('click', function() {
            $this.query_posts(btnTog, form_selector, callbacks);
        });
    }
    //addition params
    fields_values.push({name: 'widget_id_base', value: __hwcpl_object.widget_id_base});
    fields_values.push({name: 'widget_id', value: id});

    //makeing call ajax request
    jQuery.ajax({
        url : url,
        method : 'POST',
        //dataType : 'json',
        data : fields_values,
        success: function(result) {
            if(typeof result == 'string') result = JSON.parse(jQuery.trim(result));
            jQuery(btnTog).hw_reset_ajax_state();

            $this.do_callback_after(id,callbacks, result);
        }
    });
};