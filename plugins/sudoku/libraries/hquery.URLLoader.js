/*
type: sys-lib
location: libraries/URLLoader.js
	url loader
*/
function URLLoader(_url){
	//check include libs
	if(hquery==null) return false;
	
	var p=this,P=URLLoader;
	
	p.url=_url;
	
	p.method='GET';
	
	var rq=(window.XMLHttpRequest)?new XMLHttpRequest():new ActiveXObject("Microsoft.XMLHTTP");
	
	p.setUrl=function(u){if(_.isStr(u))p.url=u;};
	
	p.setMethod=function(method){
		if(_.isStr(method)){
			p.method=method;
			if(!_.DRW.inDrw(method.toUpperCase(),['GET','POST'])) p.method='GET';
		}else
			p.method='GET';
	};

/*
	load url.
*/
p.load=function(d){
	var a=arguments,
	
	item=function(t){
		var s=eval('d.'+t);
		if(_.ispack(s))s(res());
	},
	
	res=function (){
		return {html:rq.responseHTML,text:rq.responseText,xml:rq.responseXML};
	};
	
	p.setUrl(d.url);
	
	p.setMethod(d.method);//config({method});
	
	rq.onreadystatechange=function(){
		var r=this.readyState,stt=200;
		if(stt==404){
			item('notFound');
			return 0;
		}
		if(r==0){
			alert('Lỗi không load được trang !');
			item('error');
		}
		if(r==1)item('established');
		if(r==2)item('received');
		if(r==3)item('processing');
		if(r==4&&stt==200)item('success');
	}
	rq.open(p.method,p.url,true);
	rq.send(true); 
}

p.load({
	method:'get',
	url:'http:',
	error:function(){},
	success:function(dt){}
});

/*rq.setRequestHeader("Content-Type", "application/json"); */
p.set_header=function(d){
	var a=arguments;
	if(isDrw(d))rq.setRequestHeader(d[0],d[1]);
	return p;
}


}