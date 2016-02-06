/*
type: sys-lib
location: libraries/Browser.js
	Quản lý main Browser
*/
var Browser=(function(){
	function open(url_,target_){if(!arguments[1]) target_='_self';window.open(url_, target_);}
	
	function setCookie(c_name,value,expiredays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate()+expiredays);
		document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toUTCString());
	}
	
	function getCookie(c_name)
	{
		if (document.cookie.length>0)
		{
			c_start=document.cookie.indexOf(c_name + "=");
			if (c_start!=-1)
			{
				c_start=c_start + c_name.length+1 ;
				c_end=document.cookie.indexOf(";",c_start);
				if (c_end==-1) c_end=document.cookie.length
				return unescape(document.cookie.substring(c_start,c_end));
			} 
		}
		return ""
	}
	
	function delCookies(c_name){setCookie(c_name,"",0);}
	
	return {
		open:open,
		setCookie:setCookie,
		getCookie:getCookie,
		delCookies:delCookies,
		info:navigator
	};
})();