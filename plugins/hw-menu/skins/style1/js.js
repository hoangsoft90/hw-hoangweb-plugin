jQuery(document).ready(function($) {
    $(".hw-menu-style1-container#menu li").hover(function ()
    {
        $(this).find('ul').stop(true, true).slideDown();console.log('hover');
        $(this).find('.sub-menu:eq(0)').show();
    }, function ()
    {
        $(this).find('ul').stop(true, true).slideUp();
    });
});