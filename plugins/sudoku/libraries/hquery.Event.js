/*
type: sys-lib
location:
	quản lý sự kiện
*/
(function(){
function EVT(kind){
	var p=this,P=EVT,
	
	c=P.create=function(evt,tp){var e = document.createEvent(evt);e.initMouseEvent(tp, true, true, window,0, 0, 0, 0, 0, false, false, false, false, 0, null);return e;},
	
	type=P.type="click mousedown mouseup mousemove mouseover mouseout error load unload focus blur change dblclick mouseenter mouseleave select submit keydown keypress keyup resize scroll focusin focusout DOMContentLoaded".split(' '),
	
	tp={CLICK:c('MouseEvents','click'),MOUSEDOWN:c('MouseEvents','mousedown'),MOUSEUP:c('MouseEvents','mouseup'),MOUSEMOVE:c('MouseEvents','mousemove'),MOUSEOVER:c('MouseEvents','mouseover'),MOUSEOUT:c('MouseEvents','mouseout')
	/*,KEYDOWN:c('KeyEvent','keydown'),KEYUP:c('KeyEvent','keyup'),KEYPRESS:c('KeyEvent','keypress')*/},
	
	tp1={
		/*MouseEvents:{click,mousedown,mouseup,mousemove,mouseover,mouseout},
		KeyEvents:{keydown,keyup,keypress}*/
	},
	
	Element=P.Element=function(e){return (isEvent(e))? $((e.target)?e.target:e.srcElement):null},
	
	isEvent=P.isEvent=function(wh){return (wh instanceof Event);},
	
	stopSpread=P.stopSpread=function(e){e.stopPropagation();},
	
	prevent=P.prevent=function(e){e.preventDefault();},
	
	dispose=P.dispose=function(p,n_evt){if(isStr(n_evt)){p=OJ(p);for(var i in tp) if(n_evt.toUpperCase()==i) p.dispatchEvent(tp[i]);}else if(isEvent(n_evt)) p.dispatchEvent(n_evt)};
	
	p.kerl=function(){if(!isStr(kind)) return false;return (tp[kind])?tp[kind]():undefined}
}
})();