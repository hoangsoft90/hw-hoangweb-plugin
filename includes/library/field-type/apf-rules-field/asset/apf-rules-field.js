if(typeof $ == 'undefined') var $ = jQuery;
/**
 * receive object values via ajax request
 */
__apf_rules_field.request_object_values = function(obj){
    var result_holder = jQuery(obj).parent().next().next(),
        act = obj.value,    //action
        id = jQuery(obj).attr('data-id'),
        fname = jQuery(obj).attr('data-fieldname'),
        index = id.replace(fname+'__',''),  //fields row index
        conditions = __apf_rules_field.get_binding_values(fname);    //get binding fields

    if(act == '-1') return;     //none action
    __apf_rules_field.setLoadingState(result_holder);   //add loading state to holder of result

    jQuery.ajax({
        url: __apf_rules_field.ajax_handle_url,
        data : {object: act, aField: __apf_rules_field.aField ,id: id, fname : fname, bindingFields : conditions},
        method: 'POST',
        success: function(data) {
            //var _fname = 'field-'+fname;
            __apf_rules_field.removeLoadingState(result_holder);    //remove loading state

            jQuery(result_holder).html(data);
            //update manage_fields
            if(__apf_rules_field.manage_fields[fname] && __apf_rules_field.manage_fields[fname][index]) {
                console.log('updated manage_fields',fname,index);
                __apf_rules_field.manage_fields[fname][index].fields = jQuery(obj).closest('.apf-rules-field-container').find('.hw-apf-field-wrapper select');
            }
        }
    });
}
/**
 * get objects name value
 * @param fname: hw rules field name
 */
__apf_rules_field.get_binding_values = function(fname) {
    var result ={};
    if(typeof __apf_rules_field.manage_fields == 'object' && __apf_rules_field.manage_fields[fname]) {
        for(var i in  __apf_rules_field.manage_fields[fname]) {
            var fields = __apf_rules_field.manage_fields[fname][i].fields;
            var item = {}, value = null;

            for(var j=0; j < fields.length; j++) {
                value = fields.eq(j).val();    //field value
                if(j == 0) item.act = value;
                else if(j == 1) item.compare = value;
                else if(j == 2) item.value = value;
            }
            result[item.act] = item;
        }
    }

    return result;
}
/**
 * set loading status
 */
__apf_rules_field.setLoadingState = function(container){
    if(!__apf_rules_field.loading) {    //no longer use
        ///wp-admin/images/wpspin_light.gif
        __apf_rules_field.loading = jQuery('<img/>').attr('src',__apf_rules_field.IMAGES_URL + '/wpspin_light.gif');
    };
    jQuery(container).empty().addClass('spinner');
}
/**
 * remove loading status
 */
__apf_rules_field.removeLoadingState = function(container){
    jQuery(container).removeClass('spinner');
}
/**
 * register APFCallback for adding repeatable field
 * @constructor
 */
__apf_rules_field.APFCallback__added_repeatable_field = function(nodeField,sFieldType,sFieldTagID,sCallType) {
    //get hw rules field name
    var fname = sFieldTagID.replace(/__\d+$/g,'').replace(/^field-/g,''),
        id = sFieldTagID.split('field-')[1],
        index = sFieldTagID.replace('field-'+fname+'__',''),    //field index
        act_field = nodeField.find('.hw-apf-field-wrapper:first select');

    //console.log(nodeField,sFieldTagID);
    if(typeof __apf_rules_field.manage_fields == 'undefined' ) {
        __apf_rules_field.manage_fields = {};
    }
    if(typeof __apf_rules_field.manage_fields[fname] == 'undefined') {
        __apf_rules_field.manage_fields[fname] = {};
    }
    nodeField.find('.hw-apf-field-wrapper select:eq(2)').empty();   //clear select options for third field
    __apf_rules_field.manage_fields[fname][index] = ({tagID: sFieldTagID, fields: nodeField.find('.hw-apf-field-wrapper select')});   //add field to manager

    //valid old fields
    __apf_rules_field.valid_fields(fname);

    act_field.attr('id', id + '_' + act_field.data('name') );   //add/modify id attribute
    act_field.attr({'data-id': id});
    console.log('APFCallback__added_repeatable_field');
};
/**
 * remove field from manager
 * @param nodeField
 * @param sFieldType
 * @param sFieldTagID
 * @param sCallType
 * @constructor
 */
__apf_rules_field.APFCallback__removed_repeatable_field = function(nodeField,sFieldType,sFieldTagID,sCallType) {
    var fname = sFieldTagID.replace(/__\d+$/g,'');
    if(typeof __apf_rules_field.manage_fields == 'object'
        && __apf_rules_field.manage_fields[fname])
    {
        delete __apf_rules_field.manage_fields[fname] ;   //remove field from manager
    }
    //valid fields
    __apf_rules_field.valid_fields(fname);
}
/**
 * valid fields
 * @param fname: hw rules field name
 */
__apf_rules_field.valid_fields = function(fname) {
    if(/*jQuery.isArray*/(__apf_rules_field.manage_fields) && __apf_rules_field.manage_fields[fname]) {
        for(var i in __apf_rules_field.manage_fields[fname]) {
            var id = __apf_rules_field.manage_fields[fname][i].tagID,  //tag id
                fields = __apf_rules_field.manage_fields[fname][i].fields; //fields

           for(var j=0; j< fields.length; j++) {
               var obj = jQuery(fields.eq(j));
                var name = obj.attr('name'),
                    id_number = name.match(/\[(\d+)\]/g)[0].replace('[','').replace(']',''),
                    f_name = obj.attr('data-name'),
                    field_name = obj.attr('data-fieldname');

                //set attributes
               obj.attr({
                    'id' : field_name + '_' + id_number + '_' + f_name,    //this attribute need to modify in next fields
                    'data-id' : field_name + '__' + id_number
                });
            };
        }
    }
};
/**
 * gather saved fields
 * @param fname: hw rules field name
 */
__apf_rules_field.initFields = function(fname) {
    //collect fields;   # .apf-rules-field-container .hw-apf-field-wrapper
    jQuery('#fields-'+fname+' div[data-type=hw_condition_rules]').each(function(i,v){
        var field_wrapper = jQuery(v).find('.apf-rules-field-container .hw-apf-field-wrapper'),
            index = field_wrapper.eq(0).children().data('id').match(/__(\d+)$/i)[1];

        if(typeof __apf_rules_field.manage_fields == 'undefined') __apf_rules_field.manage_fields = {}; //prepare fields data
        if(typeof __apf_rules_field.manage_fields[fname] == 'undefined') __apf_rules_field.manage_fields[fname] = {}; //prepare fields data

        //for(var i = 0; i< field_wrapper.length; i++) {
            __apf_rules_field.manage_fields[fname][index] = ({fields: field_wrapper.find('select')});
        //}
    });
}
//init
jQuery(function(){
    //__apf_rules_field.initFields('field-'+__apf_rules_field.fieldname);
});