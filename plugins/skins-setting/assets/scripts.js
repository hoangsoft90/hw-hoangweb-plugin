jQuery(function($){
    if($.colorbox != undefined) {
        var options = {transition:"none", width:"65%", height:"65%"};
        if(typeof __hw_module_colorbox !='undefined') options = __hw_module_colorbox;
        $('a.hw-pretty-skinconfig-ajax').colorbox(options);
        //colorbox ajax
        $('a.hw-colorbox-ajax').colorbox({
            rel: 'gal',
            title: function(){
                return $(this).attr('title');
            },
            onComplete: function() {
                if($(this).data('status')=='1') {
					$(this).html("<span >Đã hủy kích hoạt</span>");
				}
                else {
					$(this).html("<span>Đã kích hoạt</span>");
				}
				$(this).children().unwrap();	//remove link
            }

        });
    }
});