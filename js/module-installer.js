/**
 * search modules online
 * @param btn
 * @param s
 * @private
 */
function _search_module(btn, s) {
    //valid
    if(!s) return;  //nothing to search

    var url = __hw_global_object.main_ajax_url + '&ajax_name=search_module&s_module='+ encodeURIComponent(s);
    if($(btn).hw_is_ajax_working({loadingText: "working.."}) ) {
        return ;
    }

    $.hw_ajax.request({
        url: url,
        success: function(resp) {
            $(btn).hw_reset_ajax_state();
            $('#the-list').html(resp);
        }
    });

}