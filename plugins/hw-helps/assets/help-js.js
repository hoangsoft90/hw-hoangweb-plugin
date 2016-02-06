
jQuery(function($){
    if($.colorbox != undefined) {
        //help link popup power by colorbox plugin
        $('a.hw-help-link-popup').each(function(i, v){
            jQuery(v).colorbox({
                transition:"none",
                width:"65%",
                height:"80%",
                data: {},    //post data send to ajax
                //Error message given when ajax content for a given URL cannot be loaded.
                xhrError: 'Lỗi nạp URL',
                iframe : true,
                title : jQuery(this).attr("title")
            });
        });
    }
    //SyntaxHighlighter
    if(typeof SyntaxHighlighter != 'undefined') SyntaxHighlighter.all();
});