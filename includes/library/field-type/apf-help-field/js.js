/**
 * help popup
 */
__hw_modules_help.view_help_popup = function(module, file) {
    jQuery.colorbox({
        transition:"none",
        width:"65%",
        height:"80%",
        data: {},    //post data send to ajax
        href: __hw_modules_help.help_url + '&file=' + encodeURIComponent(file) + '&nonce=' + __hw_modules_help.nonce,
        //Error message given when ajax content for a given URL cannot be loaded.
        xhrError: 'Lỗi nạp URL',
        iframe : true,
        title : jQuery(this).attr("title")
    });
}
jQuery(document).ready(function(){
    jQuery('a[data-hw-module]').each(function(i, v) {
        jQuery(v).click(function() {
            var file = jQuery(this).data('hw-help-file'),
                module=  jQuery(this).data('hw-module');
            __hw_modules_help.view_help_popup(module, file);
        });
    });
});