/*
type:
location:
	quản lý ứng dụng web.
*/
function webapp(){
	var p=this,P=webapp;
	
	P.setIcon=function(u){var f=$('link'),y,l=f?(y=1,f):(new Ele('link'));if(Ele.isObject(u))u=$(u).attr('src');if(isStr(u))$(l.sh(0)).attr({rel:"SHORTCUT ICON",src:u});if(!y){$($('head').sh(0)).addChild(l);}}
	
	
}