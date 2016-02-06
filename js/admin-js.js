
/**
 * start installing avaiables langs in the plugin
 * @param btn
 * @param langs
 */
__hw_localize_object.start_install_wplangs = function(btn,langs){
    if(__hw_localize_object.upload_lang_state == undefined)  __hw_localize_object.upload_lang_state = false;
    if(__hw_localize_object.upload_lang_state == true) return;    //upload processing, please wait

    jQuery(btn).addClass('loading');    //status loading animation
    langs = jQuery(langs).val();
    if(!langs) {
        langs =[];
        alert("Vui lòng chọn ngôn ngữ?");
        return;
    }
    __hw_localize_object.upload_lang_state = true;  //mark doing this job

    jQuery.ajax({
        url: __hw_localize_object.upload_lang_ajax+'&langs='+langs.join(','),
        success:function(data){
            jQuery(btn).removeClass('loading');    //remove loading state
            __hw_localize_object.upload_lang_state = false; //complete job
            //refresh page
            window.location.href=window.location.href;
        }
    });
};
/**
 * move static content url such as image to sub domain but still stored in folder wp-content/uploads
 * @param btn
 */
__hw_localize_object.change_static_url_subdomain = function(btn){
    if(__hw_localize_object.serve_img_subdomain_working == undefined) __hw_localize_object.serve_img_subdomain_working = false;
    if(__hw_localize_object.serve_img_subdomain_working) return;    //wait until complete process
    jQuery(btn).addClass('loading');    //status loading animation
    jQuery.ajax({
        url: __hw_localize_object.serve_static_content_subdomain_ajax,
        success: function(result){
            jQuery(btn).next().html(result);
        }
    });
};
__hw_localize_object.related_templates = {

};
/**
 * setup callbacks
 * @param obj
 */
__hw_localize_object.setup_callbacks = function(obj) {
    //register callbacks data
    obj.callbacks = {};

    //get callback function
    obj.get_callback = function(id, name) {
        if(obj.callbacks[id] && typeof obj.callbacks[id][name] != 'undefined') return obj.callbacks[id][name];
    }
    //register new callback
    obj.add_callback = function(id, name, cb) {
        if(!obj.callbacks[id]) obj.callbacks[id] = {};
        obj.callbacks[id][name] = cb;
    }
    //do callback before
    obj.do_callback_before = function(id,callbacks) {
        //get callbacks
        if(typeof callbacks == 'string') {
            callbacks = this.get_callback(id, callbacks);
        }
        //before ajax
        if(callbacks && typeof callbacks.before_ajax == 'function') {
            callbacks.before_ajax();
        }
    }
    //do callback after
    obj.do_callback_after = function(id,callbacks,data) {
        //get callbacks
        if(typeof callbacks == 'string') {
            callbacks = this.get_callback(id, callbacks);
        }

        //after ajax
        if(callbacks && callbacks.after_ajax && typeof callbacks.after_ajax == 'function') {
            callbacks.after_ajax(data);
        }
    }
};
/**
 * initialize
 */
__hw_localize_object.init = function() {
    jQuery('a[href="admin.php?page=hoangweb-theme-options&tab=tip"]').removeAttr('href');   //no longer work moved to hw-nhp-theme-options.php
}
//init
jQuery(document).ready(function($){
    __hw_localize_object.init();

});