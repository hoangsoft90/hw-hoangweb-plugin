function hw_yarppMakeTheSwitch($,data,url){
        $.get(
            url,
            data,
            function(resp){
                if(resp === 'ok'){
                    window.location.href = './options-general.php?page=hw-yarpp';
                }
            }
        );
}

jQuery(document).ready(function($){
    $('.hw_yarpp_switch_button').on('click',function(e){
        e.preventDefault();
        var url = $(this).attr('href'),
            data = { go : $(this).data('go') };
            
        if(data.go === 'basic'){
            $('#wpwrap').after(
                    '<div id="hw_yarpp_pro_disable_overlay">'+
                    '</div>'+
                    '<div id="hw_yarpp_pro_disable_confirm">'+
                        '<p>'+
                            'Are you sure you would like to deactivate YARPP Pro?'+
                            '<br/>'+
                            'Doing so will remove all <strong>YARPP Pro</strong> '+
                            'content from your site, including sidebar widgets.'+
                        '</p>'+
                        '<br/>'+
                        '<a id="hw_yarpp_proceed_deactivation" class="button">Deactivate YARPP Pro</a>'+
                        '&nbsp;&nbsp;&nbsp;&nbsp;'+
                        '<a id="hw_yarpp_cancel_deactivation" class="button-primary">Cancel Deactivation</a>'+
                    '</div>'
                );
            $('#hw_yarpp_proceed_deactivation').on('click',function(){
                hw_yarppMakeTheSwitch($,data,url);
            });
            
            $('#hw_yarpp_cancel_deactivation').on('click',function(){
                window.location.reload();
            });
            
        } else {
            hw_yarppMakeTheSwitch($,data,url);
        }
    });

    $('#hw-yarpp-display-mode-save').on('click',function(e){
        e.preventDefault();
        var url  = $(this).attr('href'),
            data = {ypsdt : true, types : []};

        $(this).after($('<span class="spinner"></span>'));

        $i = 0;
        $('input','#hw-yarpp-display-mode').each(function(idx,val){
            if(val.checked) {
                data.types[$i] = val.value;
                $i++;
            }
        });

        $.get(url,data,function(resp){
            setTimeout(function(){
                if(resp === 'ok'){
                    $('.spinner','#hw-yarpp-display-mode').remove();
                } else {
                    $('#hw-yarpp-display-mode').append($('<span style="vertical-align: middle" class="error-message">Something went wrong saving your settings. Please refresh the page and try again.</span>'));
                }
            },1000);
        });
    });

});