/**
 * Created by Hoang on 04/07/2015.
 */
/**
 * generate shortcode syntax for current widget settings
 * @param obj
 * @param form
 */
__awc_feature_shortcode_params.generate_shortcode = function(obj,form) {
    //form = jQuery(obj).closest('.form-wf-shortcode-params');
    var fields = form.find(':input'),
        //widget_form = jQuery(obj).closest('form'),
        result_holder = jQuery(obj).next(),
        sidebar = form.find('select.sidebar:eq(0)').val(),
        sidebar_skin = form.find('.sidebar_skin:eq(0)').val(),
        widget = form.find('input.widget_class:eq(0)').val(),
        widget_instance = form.find('.widget_instance:eq(0)').val(),
        config = form.find('.config_group:eq(0)').val();

    if(jQuery(obj).data('ajax_progress_state') == 'locked') {
        return ;    //working ajax
    }
    jQuery(obj).data('ajax_progress_state', 'locked');  //set locked status

    jQuery(result_holder).html('loading...');

    jQuery.ajax({
        url : this.generate_shortcode_url,
        data : {
            sidebar: sidebar,
            sidebar_skin : sidebar_skin,
            widget_class: widget,
            params: widget_instance,
            widget_config : config
        },  //fields.serialize(),
        method : 'POST',
        success: function(data) {
            console.log(data);
            result_holder.html(data);
            jQuery(obj).data('ajax_progress_state', 'unlocked') ;  //unlocked
        }
    });
};
/**
 * refresh widgets config
 */
__awc_feature_shortcode_params.refresh_saved_widgetsconfig = function(btn, select_tag_id) {
    //set loading status
    jQuery(btn).hw_set_loadingImage({place:'after'});

    jQuery.ajax({
        url : this.load_saveconfig_data_url,
        dataType : 'json',
        success: function(data) {
            var select = jQuery('#' + select_tag_id);
            select.html(' ');
            jQuery.each(data, function(val,txt){
                select.append(jQuery('<option>',{
                    value : val,
                    text : txt
                }));
            });
            jQuery(btn).hw_remove_loadingImage();   //remove image loading out of object

        }
    });
};