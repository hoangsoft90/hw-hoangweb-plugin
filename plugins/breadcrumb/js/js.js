/**
 * highlight bcn options will override
 */
__hw_breadcrumb.highlight_bcn_options = function(opts){
    for(var name in opts) {
        jQuery('label[for='+name+']').addClass('hw-bcn-option-highlight');//css('border','1px solid red');
        jQuery('#'+name).val(opts[name]).attr('value',opts[name]);  //set form field value
    }
};