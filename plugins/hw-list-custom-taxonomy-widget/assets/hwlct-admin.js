//register callbacks
if(typeof __hw_localize_object !== 'undefined') {
    __hw_localize_object.setup_callbacks(__hwlct_object);
}
/**
 * change taxonomy
 * @param obj
 * @param target
 * @param callbacks
 */
__hwlct_object.change_taxonomy = function(obj, target, callbacks) {
    var tax = jQuery(obj).val(), //selected post type
        id = jQuery(obj).data('id'),
        url = __hwlct_object.ajax_url + '?action=hw_change_taxonomy&nonce=' + __hwlct_object.hw_change_tax_nonce,
        select_tag = jQuery(target).find('select:eq(0)');   //.empty().clone();

    jQuery(obj).attr('disabled', 'disabled').addClass('hw-bg-loading');   //disable trigger object
    jQuery(target).find('select:eq(0)').html(' ').append(jQuery('<option>', {value:'',text : 'Loading..'}));     //show loading status

    ////before ajax
    this.do_callback_before(id,callbacks);

    jQuery.ajax({
        //dataType:'json',  //if ajax content not valid json in output
        url: url,
        method: 'POST',
        data : {'_tax': tax},
        success:function(data){
            if(typeof data == 'string') data = JSON.parse(jQuery.trim(data));

            select_tag.html(' ');        //clear all options tag
            jQuery.each(data.data, function(value, text) {
                select_tag.append(jQuery('<option>', {
                    value: value,
                    text: text  //.data[value]
                }));
            });

            jQuery(obj).removeAttr('disabled').removeClass('hw-bg-loading');
            //jQuery(target).append(select_tag);
            //after ajax
            __hwlct_object.do_callback_after(id,callbacks, data);
        }
    });
}