/**
 * Created by Hoang on 23/05/2015.
 */
/**
 * get url parameters
 * @param sParam
 * @returns {boolean}
 */
function hw_getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i, data={};

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        data[sParameterName[0]] = (sParameterName[1] === undefined ? true : sParameterName[1]);
        if (sParameterName[0] === sParam) {
            return data[sParameterName[0]];
        }
    }
    if(typeof sParam == 'undefined') return data;
};
/**
 * go url with confirm dialog
 * @param url
 */
function hw_confirm_url(url, msg) {
    if(!msg) msg = "Are you sure ?";
    if(!confirm(msg)) return false;
    if(url) window.open(url, '_self');
}