/**
 * @vietcodex
 * track tabs content scrolling
 * @example
 * jQuery(document).ready(function($){
       jQuery('.tab-contents').vcdx_trackContentPos({tabs_topSpacing:0,debug:true});
    });
 */
(function ( $ ) {
$.fn.vcdx_trackContentPos = function(options) {
    var tabs = {},
        content_ids=[],
        tabs_ui = $(this).find('a[href^="#"]');

    //validate
    if(tabs_ui.length==0) return;   //empty tabs
    if($.scrolltracker == undefined){
        alert("Plugin jquery-scrolltracker không tìm thấy!\nCài đặt: https://github.com/rusackas/jquery-scrolltracker");
        return;
    }

    // This is the easiest way to have default options.
    var settings = $.extend({
        // These are the defaults.
        tabs_topSpacing: 0,
        debug:false,
        current_tab_class:'current-tab'
    }, options );

    /**
     * setup scroll to element
     */
    tabs_ui.each(function(i,v){
        //tabs={'#tab1':$('<span/>'),'#tab2':$('<span/>'),'#tab3':$('<span/>'),'#tab4':$('<span/>')};
        tabs[$(this).attr('href')] = $('<span/>');
        content_ids.push($(this).attr('href'));


        jQuery(v).on('click', function(event) {
            //console.log($(this).attr('href'));
            var target = $($(this).attr('href'));
            if( target.length ) {
                event.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top-50
                }, 1000);
            }
        });
    });
    //setup sticky tabs
    $(this).sticky({topSpacing: settings.tabs_topSpacing});

    //get tabs item
    var lastItem =$(tabs_ui[0]),   //set current state on first tab
        _tab, pos_related_win={}, count= 1, debug_str,
        current ;    //set current state on first tab

    //set focus on first tab
    $(tabs_ui.eq(0)).closest('li').addClass(settings.current_tab_class);

    $( window).scroll(function() {
        for(var i in tabs){
            tab=  i;//tabs[i];
            pos_related_win[tab] = ($(tab).offset().top - $(window).scrollTop());
            $(tab).attr('data-top',pos_related_win[tab]);

        }
    });
    //enable debug
    if(settings.debug) {
        for(var i in tabs){
            tabs[i].addClass('fixed').html('N').offset({left:(count++)*300}).appendTo('body');
        }
    }
    /**
     * change class on elememt which contain content for each tab
     */
    $(content_ids.join(',')).bind('vcdx_tab_class_change', function() {
        var t = $(this).attr('class').replace(settings.current_tab_class ,'');
        //console.log($(this).attr('id'), $(this).attr('class').replace('content-tab',''));
        _tab='#'+ $(this).attr('id');
        current = $('a[href^=' + _tab+']:eq(0)');

        if(settings.debug) {
            debug_str =_tab+' => '+Math.round(pos_related_win[_tab]) + " | "+t;
            tabs[_tab].html(debug_str);
        }


        if(
            ($(this).is('.onfromtop') && jQuery(_tab).offset().top-jQuery(window).scrollTop()+$('#tab2').height()>=90)
                ||
                ($(this).is('.onfrombottom,.onscreen_vert') && Math.round(pos_related_win[_tab])>=0 && Math.round(pos_related_win[_tab])<=90
                    && !current.parent().is('.'+ settings.current_tab_class)
                    )
                ||
                $(this).is('.overflowing_vert')
            ){
            current.parent().addClass(settings.current_tab_class);

            if(lastItem && lastItem.attr('href')!=_tab) {
                tabs[_tab].html(debug_str + " | "+lastItem.attr('href'));
                lastItem.parent().removeClass(settings.current_tab_class);

            }
            lastItem=  current;

        }

    });
    //track element visible on screen

    $.scrolltracker(content_ids.join(','));
};
}(jQuery));