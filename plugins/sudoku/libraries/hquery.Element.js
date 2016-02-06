/*
type: sys-lib
location: libraries/hquery.Element.js
	quản lý thành phần giao diện.
*/
if(hquery)
(function(_){
/*
	tracker
*/
function track(){
	//thiết kế lại cache, lưu nhớ cho từng vùng.
}

/*
	Ele
*/
function Ele(){
	var p=this;

	p.create=function (t,atrs){
		var o=document.createElement(t),i;
		if(isDrw(atrs))
		for(i in atrs){
			if(ispack(eval('$(o).'+i)))eval('$(o).'+i)(atrs[i]);else $(o).attr(i,atrs[i]);
		}
		return o;
	}

	p.O=create(tp,attrs);

	equal=P.equal=function (elem1,elem2){return (elem1.isEqualNode)?elem1.isEqualNode(elem2):false},

	clone=P.clone=function(ob,opt){if(!is(ob)) return false;return $(ob.cloneNode((opt==true||opt!=0)?true:false));},

	posX=P.posX=function (obj){var curleft = 0;
    if(obj.offsetParent)
        while(1) {curleft += obj.offsetLeft;if(!obj.offsetParent) break;obj = obj.offsetParent;}
    else if(obj.x) curleft += obj.x;
    return curleft;
	},

	posY=P.posY=function (obj){var curtop = 0;
		if(obj.offsetParent)
			while(1){curtop += obj.offsetTop;if(!obj.offsetParent)break;obj = obj.offsetParent;}
		else if(obj.y) curtop += obj.y;
		return curtop;
	},

	size=P.size=function (obj,opt){if(!is(obj)) return false;
		var resul,parent=obj.parentNode,sibli=obj.nextSibling;DOCUMENT.addChild(obj);
		switch(opt)
		{
		case 'width':resul=(obj.style.width&&obj.style.width!='auto')? parseFloat(obj.style.width.replace('px','')):obj.offsetWidth;break;
		case 'height':resul=(obj.style.height&&obj.style.height!='auto')? parseFloat(obj.style.height.replace('px','')):obj.offsetHeight;break;
		default:resul={width:size(obj,'width'),height:size(obj,'height')};
		}
		if(parent!=null){ if(!sibli) parent.appendChild(obj);else parent.insertBefore(obj,sibli);}else document.body.removeChild(obj);return resul;
	};

	p.kerl=function(){return $(O)};
	
	/*------{Ele.Image}------*/
	P.Image=function(){
		var O=$(c('img')),p,P=Ele.Image,
	
		zoom=P.zoom=function (img,opt,size,W,H){
			var wid=img.width,hei=img.height,s1;
			if(W) wid=W;if(H) hei=H;
			s1=wid/hei;
			if(opt=='width'){img.height=size/s1;img.width=size;}else{img.width=size*s1;img.height=size;}
		},
	
		size=P.sizeIMG=function (obj_url,packSucc,packErro){
			var img=c('img');img.css('visibility:hidden;position:absolute');
			img.attr('src',(is(obj_url))? obj_url.src : (isStr(obj_url))?obj_url:'').bind('load',function(){packSucc(img.wid(),img.hei());document.body.removeChild(img.Object);}).bind('error',function(e){packErro(e);document.body.removeChild(img.Object);});
		}
	};
	
	/*------{Ele.Form}------*/
	P.Form=function(){
		var P=Ele.Form,p=this,
		select=P.select=function (idO){if($(idO).Object.tagName){$(idO).Object.focus();$(idO).Object.select();}},
		/*sử dụng lệnh này trong thẻ <body> để có thể tạo được iframe debugger*/
		_debugger=P.init_form_debuger=function(n){DOCUMENT().addChild(cEle('iframe',{name:n,css:'visibility:hidden;position:absolute;'}));},
		
		data=P.data=function(config,elements){return new function(){var p=this,s=cEle('input',{type:'submit'}),f=cEle('form',{method:config[0],action:config[1],target:config[2]}).addChild(elements,s);p.send=function(){f.Object.submit();}}};
	};
}

/*force namespace*/
Element=Ele;

/*-------------------------------------------------------------------------------------------------
	@selector
	Mỗi đối tượng source sẽ có thuộc tính khóa hid. Và dữ liệu của nó được quản lý qua cache.
*/

function $(str_o,from){
return new function(){
	var p=this,
	
	cs=undefined,
	
	dw=[];
	
	p.O;
	
	function getid(){return (!obj.hid)?(obj.hid='_'+(id++)):obj.hid;}
		
	function more(){return isDrw(p.O)}
	
	function getElements(f,by){
		var o=_drw(),i,a,h;
		if(isNull(by))by=document;
		if(isEle(by)){
		switch(f.charAt(0)){
			case '#':return by.getElementById(f.substr(1,f.length));break;
			case '.':
				a=by.getElementsByTagName('*');
				for(i=0;i<a.length;i++) if(a[i].className==f.substr(1,f.length)) o.put(a[i]);
				h=o.pure(true);
				return isDrw(h)?_drw(h):h;
			default:
				a=by.getElementsByTagName(f);
				h=o.put(a).pure(true);
				return isDrw(h)?_drw(h):h;
		}
		}else if(isDrw(by)){
			var s=_drw();
			for(i in by)s.put(getElements(f,by[i]));
			return s;
		}
	}
	
	p.$=function(s){
	var f,f1,f2,i,
	r;
	if(isEle(s)) p.O=p.get_object(s);
	if(isDrw(s)) p.O=DRW.onlyOBJDrawer(s,true);
	if(isStr(s)){
		/*ie: ".t1 #t2"*/
		f=str(s).leaveOneCharInStr(s,' ').O.split(':');
		f1=f[0].split(' ');
		f2=f[1];
		for(i in f1){
			if(i==0)r=getElements(f1[i]);
			else r=getElements(f1[i],r);
		}
		p.O=r;
	}
	}
	
	p.$(str_o);
	
	if(!more()&&has_O()){ 
		p.tag=p.O.tagName;
		track(p.O);
	}
	
	/*p.animate=function(dw,tm,opt,cP){
		if(!more())hgui.animate(obj,dw,tm,opt,cP);else for(var i in obj)hgui.animate(obj[i],dw,tm,opt,cP);return p;
	}*/
		
	p.sh=function(i){
		if(more())return p.O.eq(i);
		return p.O;
	}
	
	p.properties=function(n){
		if(!has_O())return false;
		var h=[],i;
		if(!more()) return eval('p.O.'+n);
		else 
			p.each(function(v){h.push($(v).properties(n));});
		return h;
	}
	
	p.parent=function(){return p.properties('parentNode');}
	
	p.next=function(){return p.properties('nextSibling');}
	
	p.each=function(c){
		if(more())p.O.each(function(i,v){c(v)});
	}
	
		function is_atr(a){
			if(isStr(a))return STR.indexOf_or(a,[':','='])==false;
		}
		function valid_atr(a){
			if(isStr(a))return str(a).pure().O;
		}
		function valid_val(v){
			if(isStr(v)||isNum(v))return str(v).standard();
		}
	
	p.css=function(atr,val){
		var arg=arguments,q,r,s,i;
		pure_args(arg);
		function set_css(a,v){
			var sepa=(STR.charFromRight(str(p.O.style.cssText).trim.O,0)!==';')?';':'';
			p.O.style.cssText+=sepa+a+((isStr(v)&&v.toString().toLowerCase()!=='null')?':'+v:'');
		}
		function do_atr(c){
			var i,s=valid_atr(c);
			if(s)
			{
				if(s.charAt(0)=='~')p.O.style.removeProperty(s.substr(1,s.length));
				else{
					d=p.O.style.cssText.match(/\b[^;]+\b/g);
					if(d.length)
						for(i in d){
							if(str(d[i].split(':')[0]).trim().O==s)return d[i].split(':')[1];
						}
				}
			}
		}
		if(!has_O()) return false;
		
		if(!more()){
		/*không tham số*/
		if(!arg.length){
			if(p.O.style) return p.O.style.cssText;else return null;
		}
		
		/*sử lý 1 tham số*/
		if(arg.length==1)
		{
			switch(true){
			case is_atr(atr):
				r=do_atr(valid_atr(atr));if(r!=null)return r;
				break;
			
			case isDrw(atr):
				for(i in atr){
					if(isNum(i)){ r=do_atr(atr[i]);if(r!=null)return r;}
					else{
						r=do_atr(i);if(r!=null)return r;
						set_css(i,atr[i]);
					}
				}
				break;
			case isStr(atr):
				set_css(atr);
				break;
			}
		}
		
		/*sử lý 2 tham số*/
		if(arg.length>=2)
		{
			if(isStr(arg[0])&&(isStr(arg[1])||isNum(arg[1])))p.css(eval("{'"+arg[0]+"':'"+arg[1]+"'}"));
			else for(i in arg) if(isDrw(arg[i]))p.css(arg[i]);
		}
		}else{
			q=[];
			p.each(function(v){
				r=$(v).css(arg);
				if(r!=p&&!isNull(r))q.push(r);
			});
			if(q.length)return q;
		}
		return p;
	}

	p.html=function(str){
		function c(){
			if(isStr(str)||isNum(str))return str.toString();
		}
		function s(){
			if(p.tag=='input'||p.tag=='textarea')
				return  c()?p.O.value=c():p.O.value;
			else c()?p.O.innerHTML=c():p.O.innerHTML;
		}
		var y,m=[];
			if(!more()){
				y=s();
				if(!c())return y;
			}else {
				p.each(function(v){
					var r=$(v).html(str);
					if(r!=p)m.push(r);
				});
				if(m.length)return m;
			}
		return p;
	}
	/*lưu thuộc tính width, height vào cache cho đối tượng*/
	p.hide=function(){
		if(!has_O()) return false;
		if(!more()){
			p.css('visibility:hidden;overflow:hidden;width:0;height:0;');
		}else 
			p.each(function(v){$(v).hide();});
		return p;
	}
	
	p.is_hide=function(){if(!more())return d_cache.get(getid());else{var i,k=[]; for(i in obj)k.push($(obj[i]).is_hide());return k;}}
	
	p.show=function(){
		if(!isO()) return false;if(!more()){var s=obj.size;$(obj).css('visibility:;overflow:;width:;height:;').css(s);}else for(var i in obj) $(obj[i]).show();return os;
	}
	
	p.visible=function(opt){if(opt==false) $(obj).hide();else $(obj).show();}
	
	p.fade=function(x){if(!isO()) return false;var f=(x<=1)? x:x/100,h=[];if(!more()){ if(isNum(x))$(obj).css("opacity:"+f+";filter:alpha(opacity="+(f*100)+")");else return ($(obj).css('opacity')!=null)?$(obj).css('opacity'):1;}else{ for(var i=0;i<obj.length;i++) (isNum(x))?$(obj[i]).fade(x):h.push($(obj[i]).fade());return h;}return os;
	}
	
	p.editable=function(t){$(obj).attr("contentEditable",(t==true)?true:false);return os;}
	
	p.gradient=function(clr1,clr2){
		if(!isO()&&!clr1) return false;
		if(!obj.length){
		var c1,c2;
		if(clr1&&clr2&&isStr(clr1)&&isStr(clr2)){c1=clr1;c2=clr2;}
		if(isDrw(clr1)){if(clr1.length){ c1=clr1[0];c2=clr1[1];}else{ c1=clr1.from;c2=clr1.to;}}
			$(obj).css("filter","progid:DXImageTransform.Microsoft.gradient(startColorstr='"+c1+"', endColorstr='"+c2+"')");
			$(obj).css("background","-webkit-gradient(linear, left top, left bottom, from("+c1+"), to("+c2+"))");
		}
		else for(var i=0;i<obj.length;i++) $(obj[i]).gradient(clr1,clr2);
		return os;
	}
	
	p.corner=function(coodis){
		if(!isO()) return false;
		if(!obj.length){
			var specifyCor=function(n,val){
				switch(n){
				case 0:$(obj).css({"cor-top-left":val});break;
				case 1:$(obj).css({"cor-top-right":val});break;
				case 2:$(obj).css({"cor-bottom-right":val});break;
				case 3:$(obj).css({"cor-bottom-left":val});break;
				default:break;
				}
			}
			if(arguments.length==1) for(var i=0;i<4;i++) specifyCor(i,coodis);
			if(arguments.length>1){
			var count=0;
			for(var i=0;i<arguments.length;i++){if(count>4) break;
			if(isNum(arguments[i])||isStr(arguments[i])){specifyCor(count,arguments[i]);count++;}
			if(isDrw(arguments[i])) for(var j=0;j<arguments[i].length;j++) if(isNum(arguments[i][j])||isStr(arguments[i][j])){specifyCor(count,arguments[i][j]);count++;}
			}
			}
		}else for(var i=0;i<obj.length;i++){var args=''; for(var j=0;j<arguments.length;j++) args+="arguments["+j+"],"; eval("$(obj["+i+"]).corner("+args+")");}
	}
	
	p.diselec=function(){if(!isO()) return false;if(!obj.length){ $(obj).css("-moz-user-select: none;-khtml-user-select: none;-webkit-user-select: none;user-select: none;");}else for(var i=0;i<obj.length;i++) $(obj[i]).diselec();return os;
	};
	
	p.attr=function(a_,v){if(!isO())return false;if(!(isStr(a_)||isDrw(a_))) return os;
		var i,j,h=[],l,a=isStr(a_)?STR.pure(a_):a_;if(!more()){if(isStr(a)&&!isNum(a)){if(v!=null)obj.setAttribute(a,v);else return obj.getAttribute(a)}}else{ for(j in obj)(l=$(obj[j]).attr(a_,v))!=os?h.push([obj[j],l]):'';if(h.length)return h}
		if(isDrw(a)) for(i in a)$(obj).attr(i,a[i]);return os;	
	}
	
	p.src_attr=function(str_mod,val){
		if(!isO()||!(isStr(str_mod)||isDrw(str_mod))) return false;
		var prop=(isStr(str_mod))? STR.pure(str_mod):null,i;
		if(arguments.length==2&&isStr(str_mod)){
			if(!more()) obj[prop]=val;else for(i in obj) $(obj[i]).src_attr(str_mod,val);
			return os;
		}
		if(arguments.length==1){
			if(isStr(str_mod)){
				if(!more()) return eval('obj.'+prop);else{var attrs=[]; for(i in i) attrs.push($(obj[i]).src_attr(str_mod));return attrs;}
			}else for(i in str_mod) $(obj).src_attr(i,str_mod[i]);
		}
	}
	
	p.addClass=function(n){if(!isStr(n)) return false;p.attr("class",n);return os;}
	
	p.clone=function(opt){if(!isO()) return false;
	if(!more())return obj.cloneNode((opt==true)?true:false);else{var c=[],i;for(i in obj)c.push($(obj[i]).clone(opt));return c}
	}
	
	p.eq=function(ix){if(!isO()) return false;
		if(!more()){
		var d=obj.childNodes,h=[],p=[],i;
		if(!(isNum(ix)||isStr(ix))){for(i=-1;++i<d.length;)Ele.isObject(d[i])?h.push(d[i]):'';return h;}
		if(isNum(ix)) return p.eq()[ix];
		if(isStr(ix))switch(ix){case 'first':return obj.firstChild;break;case 'last':return obj.lastChild;break;default:return p.eq();break;}
		}else{var d;if(isNum(ix)) return (d=DRW.seek(obj,ix,true),(d)?d.data:null);if(isStr(ix))switch(ix){case 'first':return obj[0];break;case 'last':return obj[obj.length-1];break;default:return obj;break;}else return obj}
	}
	
	p.wid=function(a){if(a!=undefined){ $(obj).size(a,'width');return self}else return $(obj).size().width;}
	
	p.hei=function(a){if(a!=undefined){ $(obj).size(a,'height');return self}else return $(obj).size().height;}
	
	p.size=function(a,opt){if(!isO()) return os;
		if(arguments.length>=1){if(!(isNum(a)||isStr(a))) return false;var v=(isStr(a))?STR.pure(a).replace('px',''):a,s;
		if(!more()){ s=(opt=='width'||opt=='height')?opt+':'+((v<0)?0:v)+'px':'width:'+((v<0)?0:v)+'px;height:'+((v<0)?0:v)+'px';$(obj).css(s);}else for(var i=0;i<obj.length;i++) $(obj[i]).size(a,opt);}
		if(!arguments.length){if(!obj.length) return Ele.size(obj);}else {var heis=[];for(var i=0;i<obj.length;i++) heis.push($(obj[i]).size());return heis;};return os;
	}
	
	p.pos=function(x,y){
		if(!isO()) return os;var z=arguments.length,h=[],i,c=function(y){var v;return (v=STR.str(y)!=null)?NUM.float(v.replace('px','')):NaN};
		if(!more()){
		set=function(x,y){if(c(x)!=NaN)$(obj).css('left:'+x);if(c(y)!=NaN)$(obj).css('top:'+y)};set(x,y);
		if(isDrw(x)){ if(isDrw(x)&&x.length) set(x[0],x[1]);else set(x.left,y.top);} obj.style.position='absolute';
		if(!z) return {left:obj.style.left,top:obj.style.top}
		}else{ for(i in obj) (z)?$(obj[i]).pos(x,y):h.push($(obj[i]).pos());if(!z)return h;}
		return os;
	}
	
	p.existChild=function(a){if(!isO())return false;if(!arguments.length) return os;var r=[];if(!more()){var args=arguments,i,j,s=$(obj).eq(),c=function(O,w,r){var s,j,m;if(Ele.isObject(s=OJ(w))){d=In(s);for(j in r)if(r[j][0]==O){m=1;(d)?r[j][1].push(s):''};if(!m){r.push([O,[d?s:'']])}}},In=function(n){var i;return (i=DRW.seek(s,n)[0])?i.val:false};for(i in args){if(isDrw(args[i]))for(j in args[i])c(obj,args[i][j],r);else c(obj,args[i],r)};return r;}else{var i,j;for(i=-1;++i<obj.length;) for(j in arguments) DRW.put($(obj[i]).existChild(arguments[j]),r);return r}
	}
	
	p.draggable=function(opt,pack){
		if(!isO()) return false;
		if(!obj.length){var isDrag=false;
		var _mouseX,_mouseY,_objX,_objY;
		if(obj.dragPack){ $(obj).unbind("mousedown",obj.dragPack.down);
		document.removeEventListener("mouseup",obj.dragPack.up);
		document.removeEventListener("mousemove",obj.dragPack.move);}
		if(opt==true){$(obj).css("position:absolute;");
		obj.dragPack={down:function(e){
			if(e==null) e=window.event;isDrag=true;
			_mouseX=e.clientX;_mouseY=e.clientY;
			_objX=obj.offsetLeft;_objY=obj.offsetTop;
			document.body.focus();
			return false;
		},move:function(e){
			if(isDrag){
			if(e==null) var e=window.event;
			obj.style.left=(_objX-_mouseX+e.clientX)+'px';
			obj.style.top=(_objY-_mouseY+e.clientY)+'px';
			if(ispack(pack)) pack((_objX-_mouseX+e.clientX),(_objY-_mouseY+e.clientY));
			}
		},up:function(){isDrag=false;}};
		$(obj).bind("mousedown",obj.dragPack.down);
		document.addEventListener("mouseup",obj.dragPack.up,false);
		document.addEventListener("mousemove",obj.dragPack.move,false);
		}
		}else for(var i=0;i<obj.length;i++)
				$(obj[i]).draggable(opt,pack);return os;
	}
	
	p.live=function(){}
	
	p.trigger=function(){}
	
	p.entrusted=function(){}
	
	p.bind=function(evt,c,f){if(!isO()) return false;
		if(!(isStr(evt)||isDrw(evt)))return os;
		if(!more()){if(!obj.bagEvents) obj.bagEvents=[];var i,m=function(e,c,t){if(!obj.bagEvents[e]) obj.bagEvents[e]=[];obj.bagEvents[e].push({default:c,convert:function(e){c(e)}/*,status:'ready'*/});if(obj.addEventListener) obj.addEventListener(e,obj.bagEvents[e][obj.bagEvents[e].length-1].convert,t);if(obj.attachEvent) obj.attachEvent('on'+e,obj.bagEvents[e][obj.bagEvents[e].length-1].convert)};if(isStr(evt))each(evt.split(' '),function(i,v){m(v,c,f)});else for(i in evt)m(i,ispack(evt[i])?evt[i]:evt[i][0],(evt[i][1]==true)?true:(c==true)?c:false);
		}else for(var i in obj)$(obj[i]).bind(evt,c,f);return os;
	}
	
	p.unbind=function(evt,c,f){if(!isO()) return false;
		if(!more()){if(!obj.bagEvents) return os;var i,k,j;
		if(evt!=undefined){var r=[],i,j,t=obj.bagEvents[evt],d;isNum(c)?d=((d=t[c])!=undefined)?d.default:0:d=ispack(c)?c:null;for(i in t){if(d==t[i].default||d==null){if(obj.removeEventListener){  obj.removeEventListener(evt,t[i].convert,f);}if(obj.detachEvent) obj.detachEvent('on'+evt,t[i].convert);delete t[i]}};obj.bagEvents[evt]=DRW.pure(t);}else for(i in obj.bagEvents)$(obj).unbind(i);}else for(var i in obj)$(obj[i]).unbind(evt,c,f);if(isDrw(evt)) for(var i in evt)$(obj).unbind(i,isDrw(evt[i])?evt[i][0]:evt[i],evt[i][1]!=null?evt[i][1]:c);return os;
	}
	
	p.rebind=function(evt,c,f){if(!isO()) return false;
		if(!more()){var t,d,h=[],i,j,s=[];if(evt!=undefined){for(i in t=obj.bagEvents[evt]){isNum(c)?d=((d=t[c])!=undefined)?d.default:0:d=ispack(c)?c:null;if(t[i].default==d||d==null)$(obj).unbind(evt,d,f).bind(evt,d,f);}}else for(i in obj.bagEvents)$(obj).rebind(i);}else for(var i=0;i<obj.length;i++) $(obj[i]).rebind(evt,c,f);if(isDrw(evt))for(var i in evt)$(obj).rebind(i,isDrw(evt[i])?evt[i][0]:evt[i],evt[i][1]!=null?evt[i][1]:c);return os;
	}
	
	p.pause=function(evt,c,f){if(!isO()) return false;
		if(!more()){var d,t,h=[],i,j;if(evt!=undefined){if(!obj.bagEvents[evt]) return os;t=obj.bagEvents[evt];for(j in t){isNum(c)?d=t[c].default:d=ispack(c)?c:null;if(t[j].default==d||d==null){$(obj).unbind(evt,d,f);obj.bagEvents[evt].push({default:d,convert:function(e){d(e)}})}}}else for(i in obj.bagEvents)$(obj).pause(i);
		}else for(var i=0;i<obj.length;i++) $(obj[i]).pause(evt,c,f);return os;
	}
	
	/*modfiy*/p.addChild=function(c){var a=arguments,k;if(!isO())return false;if(!more()){for(k in a)if(isDrw(a[k]))for(var i=0;i<a[k].length;i++)obj.appendChild(OJ(a[k][i]));else obj.appendChild(OJ(a[k])); }else for(var i in obj) $(obj[i]).addChild();return os;
	}
	
	p.insertBefore=function(c,w){if(!isO())return false;c=OJ(c);w=OJ(w);if(!Ele.isObject(c)) return os;if(!more()) obj.insertBefore(c,w); else for(var i in obj) $(obj[i]).insertBefore(c,w);return os;
	}
	
	p.insertAfter=function(c,w){w=OJ(w);if(Ele.isObject(w)) p.insertBefore(c,w.nextSibling); return os;}
	
	p.removeChild=function(c){if(!isO())return false;var d,i,j,k=[],a=arguments;for(i in a)DRW.put($(obj).existChild(a[i]),k);for(i in k)for(j in k[i]){if(j!=0&&k[i][j])for(h in k[i][j])if(k[i][j][h])k[i][0].removeChild(k[i][j][h])}return os;
	}
	var evts=[];each(EVT.type,function(i,v){evts[v]=function(c,f){p.bind(v,c,f);}});
	_.extend(os,evts);
}}
/*kế thừa vào $*/
_.extend({
	to_extend:$
});

})(hquery);