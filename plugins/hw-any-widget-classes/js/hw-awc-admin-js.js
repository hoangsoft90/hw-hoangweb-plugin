if(typeof HW_AWC == 'undefined') var HW_AWC = {};
jQuery(function($){
    $('.invalid-sidebar-name').closest('tr').addClass('invalid-sidebar-field');
    //set change color to value attribute
    if(this.color == undefined) $('input.color').attr('onchange',"jQuery(this).attr('value','#'+this.color);");

});
/**
 * set jscolor
 * @param obj: color picker
 * @param color: hex color
 * @returns {color}
 */
HW_AWC.jscolor_set = function(obj,color){
    if(typeof jscolor!=='undefined') {
        var myPicker = new jscolor.color(obj, {});
        myPicker.fromString(color);  // now you can access API via 'myPicker' variable
        return myPicker;
    }
};

/**
 * setup upload dialog for button
 * @param btn_obj: upload popup feature when click on this button
 * @param inputText: input field to set value
 */
function hw_awc_btn_upload_image(btn_obj,inputText){
    jQuery( btn_obj).on( 'click', function() {
        tb_show('test', 'media-upload.php?type=image&TB_iframe=1');

        window.send_to_editor = function( html )
        {
            var imgurl = jQuery( 'img',html ).attr( 'src' );
            jQuery(inputText).val(imgurl);
            tb_remove();
        }

        return false;
    });
}
