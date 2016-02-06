jQuery(function($){
    $('.hw-menu-def-container.header-menu li').hover(function(e){
        $(this).find('a:eq(0)').addClass('hover');
        $(this).find('.sublist:eq(0)').addClass('active');
    },function(){
        $(this).find('a:eq(0)').removeClass('hover');
        $(this).find('.sublist:eq(0)').removeClass('active');
    });
});