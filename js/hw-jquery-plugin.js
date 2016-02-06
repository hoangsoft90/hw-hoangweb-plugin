/**
 * remove duplicate items from array
 * @returns {Array}
 */
jQuery.fn.hw_unique = function() {
    var result = [];
    $.each(this, function(i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
}
/**
 * ajax
 */
jQuery.hw_ajax = new function() {
    var data={};
    var count=0;
    //request ajax
    function _ajax(args){
        //_ajax.id=_ajax.id||0;
        var id=count++;
        var old_callback = args.success;
        if(typeof args.success=='function') args.success = (function(_id) {
            return function(data){
                old_callback(data);
                delete data[_id];
            }
        })(id);
        data[id]= jQuery.ajax(args);
        return data[id];
    }
    //before reload page
    window.onbeforeunload = function() {
        for(var i in data)
            if(data[i] !== undefined) {
                data[i].abort();
            }
    }
    this.request=_ajax;
};
/**
 * check wether ajax working that fire by button toggle
 * @param options plugin options
 */
jQuery.fn.hw_is_ajax_working = function(options) {
    if(this.data('hw_ajax_working')) {
        if(options && options.alert == 1) alert("Xin lỗi ! Đang sử lý...");
        return true;
    }
    this.data( "hw_ajax_working", 1 );  //set event status

    var data = {};

    //prepare data that i will used to save some info on element
    if(!this.data('hw-data')) {
        this.data('hw-data', data);
    }
    else data = this.data('hw-data');

    //get current content on element
    if(this.is('input[type=button],input[type=submit]')) data.text = this.val();
    else {
        if(!data.text ) data.text = this.text();
        if(!data.html ) data.html = this.html();
    }

    // This is the easiest way to have default options.
    var settings = jQuery.extend({
        // These are the defaults.
        color: "#556b2f",
        backgroundColor: "white"
    }, options );

    //change object text
    if(settings.loadingText) {
        if(this.is('input[type=button],input[type=submit]')) this.val(settings.loadingText);
        else this.text(settings.loadingText);
    }

    //update data on element
    this.data('hw-data', data);
    return false;
};
/**
 * reset ajax to work
 */
jQuery.fn.hw_reset_ajax_state = function(){
    this.data("hw_ajax_working", 0);    //allow make to call ajax
    var data = this.data('hw-data');
    //resume element content
    if(data  ) {
        var resume_txt = (data.html? data.html : data.text);
        if(this.is('input[type=button],input[type=submit]')) this.val(resume_txt);
        else if(typeof this.html === 'function') this.html(resume_txt);
    }
};
/**
 * add loading image to container
 * @param options
 */
jQuery.fn.hw_set_loadingImage = function(options) {
    //prepare loading img
    if(! this.data('hw_loading_img') ) {
        var img = jQuery('<img/>', {src : __hw_global_object.loading_image});
        this.data('hw_loading_img', img);
    }
    // This is the easiest way to have default options.
    var settings = jQuery.extend({
        // These are the defaults.
        target: "",
        place: "after"
    }, options );
    var img = jQuery(this.data('hw_loading_img')).show();  //get loading element, and make it visible

    //valid
    if(!settings.target) settings.target = this;    //target object to render loading image

    //append loading image to container
    if(settings.place == 'replace') jQuery(settings.target).empty().append(img);
    else if(settings.place == 'after') jQuery(settings.target).append(img);
    else if(settings.place == 'before') jQuery(settings.target).prepend(img);
};
/**
 * remove loading image from container get from current element
 * @param target (optional) ->seem no longer use
 */
jQuery.fn.hw_remove_loadingImage = function(target) {
    if(! this.data('hw_loading_img') ) return ;
    if(!target) target = this;  //target is current element

    var img = this.data('hw_loading_img');
    //jQuery(target).remove(img);
    jQuery(img).hide(); //best way
};
/**
 * count items in json object
 * @returns {number}
 */
jQuery.hw_len = function(obj) {
    var d=obj, c=0;
    for(var k in d) {
        c++;
    }
    return c;
};
/**
 * count json or array items
 * @returns {number}
 */
jQuery.fn.hw_len = function() {
    var c=0;
    jQuery(this).each(function(i,v){
        c++;
    });

    return c;
};
/**
 * get url parameters
 * @param sParam
 * @param query
 * @returns {boolean}
 */
jQuery.hw_getUrlParameter = function(sParam, query) {
    var sPageURL = query? query : decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i, data={};

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        data[sParameterName[0]] = (sParameterName[1] === undefined ? true : sParameterName[1]);
        if (sParam!== null && sParameterName[0] === sParam) {
            return data[sParameterName[0]];
        }
    }
    if(typeof sParam == 'undefined') return data;
};
/**
 * send post data to current url
 * @param post_data
 * @param get_data
 */
jQuery.post_to_self = function(post_data, get_data) {
    //valid
    if(typeof get_data =='undefined') get_data = {};
    if(typeof get_data =='string' && get_data) get_data = jQuery.hw_getUrlParameter(null, get_data);
    get_data['_sendpost'] = '1';
    var url = window.location.href;
    if(jQuery.hw_len(jQuery.hw_getUrlParameter())) {
        url += '&'+ jQuery.param(get_data);
    }
    else url += '?' + jQuery.param(get_data);
    //create form tag
    var mapForm = document.createElement("form");
    mapForm.target = "_self";
    mapForm.method = "POST"; // or "post" if appropriate
    mapForm.action = url;//"http://localhost/2.php?d=10";
    mapForm.style.cssText='display:none;';
    //create param field
    var create_param = function(name, value) {
        if(jQuery.isNumeric(name)) return ; //invalid field name
        var mapInput = document.createElement("input");
        mapInput.type = "text";
        mapInput.name = name;
        mapInput.value = value;
        mapForm.appendChild(mapInput);
    };
    for(var i in post_data) create_param(i, post_data[i]);
    jQuery('body').append(mapForm);

    map = window.open("", "_self", "toolbar=no,status=no,menubar=no,scrollbars=no,resizable=no,title=0,height=600,width=800,visible=none");
    if(map && !jQuery.hw_getUrlParameter('_sendpost')) {
        mapForm.submit();
    }
}