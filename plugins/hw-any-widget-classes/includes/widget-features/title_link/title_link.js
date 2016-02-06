/**
 * open link dialog allow to insert external link
 */
function hw_awc_open_link_dialog(insertLink_callback,field_id,test_link){
    var dlg = jQuery('#wp-link-wrap'),
        close_btn = dlg.find('#hw-link-close'),
        //set link function
        set_link =  function(link){
            if(typeof insertLink_callback == 'function'){
                //set test link
                jQuery(test_link).html('<a href="'+link+'" target="_blank">Mở liên kết</a>');
                insertLink_callback(link);
            }
        };

    //simple link enter
    var url = prompt("Nhập liên kết URL ?","http://");
    set_link(url);
    return;
    if(!dlg.data('initialize')){

        //close link popup event
        close_btn.bind('click',function(event){
            dlg.hide();
            //wpLink.close();
        });
        //when add link
        dlg.find('#wp-link-submit').bind('click',function(e){
            //e.preventDefault();
            var link = jQuery('#url-field').val();
            set_link(link);

            //hide dialog
            close_btn.trigger('click');
        });
        dlg.data({'initialize':true});
    }


    wpActiveEditor = true;
    if(typeof wpLink !== 'undefined') {
        wpLink.title = "Hello"; //Custom title example
        try{
            //wpLink.textarea = jQuery('#'+field_id);
            wpLink.open(field_id);    // Open the link popup    #from wp 4.0
        }
        catch(e){
            console.log(e);
        }

    }
    dlg.show(); //show popup
    //empty link
    jQuery('#'+field_id).empty().attr('value','');   //clear field value
    jQuery(test_link).empty();

    return false;
}