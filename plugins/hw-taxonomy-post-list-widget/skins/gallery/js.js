jQuery(".hwtpl-wrapper .model_list .item a.link").mousemove(function(k){
    function _e(){return jQuery.browser.opera?window.innerWidth:jQuery(window).width()}
    function getClientSTop(){return self.pageYOffset||(document.documentElement&&document.documentElement.scrollTop)||(document.body&&document.body.scrollTop)};

    var viewer = jQuery(".hwtpl-wrapper .popup_big_foto");

    viewer.css({display:"block",top:"-1000px",left:"-1000px"});
    var l=k.pageY-10;var h=k.pageX+20;var n=_e();var j=jQuery(self).height();
    var i=getClientSTop();var p=h-jQuery("body").children().offset().left;var g=viewer.width();
    var o=viewer.height();var m=l-i;
    if(k.pageX+32+g>n){
        var h=k.pageX-g-20;
    }
    if(m+o>j){l=i+j-o-15}
    //set new position of current item
    viewer.css({top:l,left:h});
    viewer.find('.foto img').attr('src', jQuery(this).data('image'));
});
jQuery(".hwtpl-wrapper .model_list .item a.link").mouseover(function (h){
    $(".hwtpl-wrapper .popup_big_foto .foto img").remove();
    $(".hwtpl-wrapper .popup_big_foto .name .txt").text($(this).attr("rel"));
    if($(this).next(".free").length){
        $(".hwtpl-wrapper .popup_big_foto .icon").attr("class","icon free").text("free")
    }else{
        if($(this).next(".om").length){
            $(".hwtpl-wrapper .popup_big_foto .icon").attr("class","icon om").text("om")
        }else{
            if($(this).next(".pro").length){
                $(".hwtpl-wrapper .popup_big_foto .icon").attr("class","icon pro").text("pro")
            }else{
                $(".hwtpl-wrapper .popup_big_foto .icon").attr("class","icon");
            }
        }
    }
    var g=new Image();
    if($.browser.msie&&$.browser.version<9){
        g.src=$(this).attr("rev")+"?"+new Date().getTime();
    }else{
        g.src=$(this).attr("rev")
    }
    $(".hwtpl-wrapper .popup_big_foto .foto").prepend(g);
    $(g).hide();
    $(g).load(function(){
        $(".hwtpl-wrapper .popup_big_foto .foto img:first").css({display:"block"},500)
    });
    return false
});
jQuery(".hwtpl-wrapper .model_list .item a.link").mouseout(function (){ jQuery(".hwtpl-wrapper .popup_big_foto").css({display:"none"});});