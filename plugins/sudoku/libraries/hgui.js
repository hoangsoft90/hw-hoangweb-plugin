function sh(dw){var t=$('#sh');for(var i in dw){t.html(t.html()+'|'+i+':'+dw[i])}}

function sh1(d){var v='';for(var i in d)v+=i+':'+d[i]+',';return v}

function post(v){var o=$('#sh');o.html(o.html()+v)}

function post1(v){var o=$('#sh1');o.html(o.html()+v)}

var hgui=(function(){
	var NUM=hq.hNumber,PAC=hq.hPack,ele=hq.hElement,DRW=hq.hDrawer,STR=hq.hString,each=hq.each,OQueue=[],extend=hq.extend,
	
	queue=function(O){
		var p=this,P=queue,i,t=new TIMER(10),seek=function(f,op){var s,c=false;for(i in OQueue) if(OQueue[i][0]==O){c=true;s=OQueue[i][1];if(op)s=(OQueue[i][1]=[]);if(f)s.push(f);break;};if(!c){OQueue.push([O,new DRW().kerl()]);s=OQueue[OQueue.length-1][1];if(f)s.push(f)}return s};p.check=function(){};p.exec=function(){var s=seek(),m;if(s.length){if(O.ready||O.ready==undefined){O.ready=false;m=s.shift();if(DRW.isArray(m))for(i in m)m[i]();else m()};t.start(p.exec)}};p.add=function(cb,opt){if(opt==true&&(O.ready||O.ready==undefined))seek(cb);if(opt==false||opt==undefined)seek(cb);if(opt=='clear')seek(cb,1); p.exec();return p};p.clear=function(){};p.Queue=function(ps){}
	},

	TIMER=function(z_){
		var p=this,tm,z=(NUM.isNum(z_))?z_:0;p.callback=function(){};p.start=function(t,f){var _z,_call;if(t)(NUM.isNum(t))?_z=t:(PAC.isPack(t))?_call=t:'';if(f) if(PAC.isPack(f)&&NUM.isNum(t)) _call=f;tm=setTimeout((_call)?_call:p.callback,(_z)?_z:z);return p};p.stop=function(){clearTimeout(tm);return p}
	},
	
	hopSmooth=function(t,dk,callb,ti,opt){
		var c=t/1000,q=0,h=[],h_=[],l,m,loop,z,v,F;while(l=h.length-1,h.push((l>-1)?h[l]+c:0),(q+=h[h.length-1],c!=0&&q<=t)?true:false){};F=h[1];(loop=function(){if(dk()){var tm=new TIMER((ti!=null)?ti/1000:c);(h.length)?h_.push((v=h.shift(),callb(v),v)):'';if(!opt||opt=='trip')(!h.length)?(v=h_.pop(),(v)?callb(v):callb(F)):'';tm.start(loop)}})()
	},
	
	slide=function(targ,opt,inter){
		if(!ele.isObject(targ)&&!(opt.toUpperCase()!=='DOWN'||opt.toUpperCase()!=='UP')) return false;var O=$(targ),que=new queue(targ,'resize'),cl=O.clone(true),siz=cl.css('overflow:;height:auto;border:0px;padding:0px').hei();O.css('overflow:hidden');var t=(NUM.isNum(inter))?parseFloat(inter)/1000:100/1000,s=(opt.toUpperCase()=='UP')?false:true,loop=function(m_){O.hei(NUM.round(eval(O.hei()+((!s)?'-':'+')+m_)))},pQUE=function(){hopSmooth(siz/2,function(){if((!s)?O.hei()>0:O.hei()<siz){targ.queue['resize']=false;return true}else {targ.queue['resize']=true;return false}},loop,t)};que.add(pQUE)
	},
	
	slideDown=function(tar,time){slide(tar,'down',time);},
	
	slideUp=function(tar,time){slide(tar,'up',time);},
	
	collapsible=function(tog,content,opt,pack){
		if(!(ele.isObject(tog)||DRW.isArray(tog))&&!ele.isObject(content)) return false;
		var s1,s2,anchor;if(DRW.isArray(tog)){ if(tog.length){ anchor=(ele.isObject(tog[0]))?$(tog[0]):null; s1=tog[1];s2=tog[2];}else{anchor=(ele.isObject(tog['anchor']))?$(tog['anchor']):null;s1=tog['stateClose'];s2=tog['stateOpen']}} else anchor=$(tog);if(!anchor) return false;if(opt==true){ s1='[+]';s2='[-]';};
		var ob=$(content).hide().css('visibility:visible;width:auto');if(!ob.src_attr('collap')) ob.src_attr('collap','off');
		if(s1) anchor.html(s1);var callback=function(state){(PAC.isPack(opt))?opt({p:anchor,stt:state}):(PAC.isPack(pack))? pack({p:anchor,stt:state}):'';};
		anchor.bind('click',function(e){if(ob.src_attr('collap')=='off'){ slideDown(content,500);$(e.target).html(s2);ob.src_attr('collap','on');callback('on');}else{ slideUp(content,500);$(e.target).html(s1);ob.src_attr('collap','off');callback('off');};});
	},
	
	g_collap=function(g){
		var g_=[];var relav=function(w){var res=[];if(DRW.isArray(w)){var w_=DRW.onlyOBJDrawer(w); if(DRW.len(w_)>=2){ res.push(DRW.seek(w_,0,true).data);res.push(DRW.seek(w_,1,true).data);}};if(ele.isObject(w)){if($(w).eq(0)) res.push($(w).eq(0));if($(w).eq(1)) res.push($(w).eq(1));};return res;};if(arguments.length==1&&ele.isObject(g)) for(var p in $(g).eq()) g_.push($(g).eq(p));else for(var t in arguments){if(DRW.isArray(arguments[t])) if(DRW.len(arguments[t])>=2) g_.push(arguments[t]);if(ele.isObject(arguments[t])) DRW.put(arguments[t],g_);};for(var i in g_){ var q=relav(g_[i]);if(q.length) collapsible(q[0],q[1],function(e){for(var n in g_){var q=relav(g_[n]); if(q.length){ if(q[0]!=e.p.Object){ slideUp(q[1],500);q[1].collap='off'}}}});else continue;}
	},
	
	k=0,cssdb=[],
	
	effect=function(O,opts,if_,tm,cP,aP,opt){
		if(!ele.isObject(O))return false;var p=this,que=new queue(O),gAtr=function(tp){var r=$(O).css(tp);return parseFloat(NUM.isNum(r)? r:(STR.isStr(r))?r.replace('px',''):(STR.pure(tp)=='opacity')?1:0)},s,i,i1,j,d=0,chuj,n=0,pac=function(){(PAC.isPack(cP))?cP():''};var toAdd=function(atr){return function(){PAC.isPack(aP)?aP():'';var c=atr[1].toString().charAt(0),tp=atr[0],to=(c=='+'||c=='-')?eval($(O).css(tp).replace('px','')+c+atr[1].substr(1,atr[1].length)):atr[1],s=Math.abs(gAtr(tp)-to)/2;hopSmooth(s,function(){var r=if_(O,[atr[0],to]);if(!r) --n;if(n<=0){if(!r){ O.ready=true;pac()}};return r},function(m){if(tm)m+=m/(tm/1000);var f,i,f=gAtr(tp);if(f!=to){if(f<to){$(O).css(tp,(f+m>to)?to:f+m);}else{ $(O).css(tp,(f-m<to)?to:f-m)}}},tm)}},dw=[];for(i1 in opts){d=0;for(i in cssdb){chuj=[];for(j in cssdb[i]){chuj.push(cssdb[i][0]+'-'+cssdb[i][j]);if(cssdb[i][j]==i1&&j==0)d=1;}chuj.shift();if(d)break};if(d)for(j in chuj){dw.push(toAdd([chuj[j],opts[i1]]))}else dw.push(toAdd([i1,opts[i1]]))}que.add(dw,opt);n=dw.length
	};
	
	function effectTo(O,opt){
		var gAtr=function(tp){var r=$(O).css(tp);return parseFloat(NUM.isNum(r)? r:(STR.isStr(r))?r.replace('px',''):(STR.pure(tp)=='opacity')?1:0)},to=opt[1],f=gAtr(opt[0]),d=(f<to)?true:(f>to)?false:null;if(f==NaN){return false}if(d==null){return false}else if(f!=to)return true
	}
	
	var fadeIn=function(O,cP){effect(O,{opacity:1},effectTo,0,cP,function(){$(O).show()})},
	
	fadeOut=function(O,cP){fadeTo(O,0,function(){$(O).hide()})},
	
	fadeTo=function(O,to,cP){var t=(NUM.isNum(to))?(to>1)?to/100:to:1;$(O).show();effect(O,{opacity:t},effectTo,0,cP)},
	
	animate=function(O,dw,tm,opt,cP){effect(O,dw,effectTo,tm,opt,cP)},
	
	hTabs=function(){
		var b,t,s,p={kerl:b,bar:t,shw:s};for(var i in p) p[i]=new ele('div').kerl();p.bar.css('padding:3px;background:pink');p.shw.css('border:1px solid gray');p.kerl.addChild(p.bar.Object).addChild(p.shw.Object);
		this.addTab=function(titl,cont){var nw=new hgui.hLabel(titl);p.bar.addChild(nw.Object)};this.core=function(){p.kerl;}
	};
	
	DRW.put([['border','left-width','top-width','right-width','bottom-width'],['padding','left','top','right','bottom'],['margin','left','top','right','bottom'],['font','size']],cssdb);
/*
	hLabel: label, button, item
*/
function hLabel(tOb)
{
	var p=this,STR=hq.hString,DRW=hq.hDrawer,MDL=hq.hModule,ele=hq.hElement,O=new ele('div').kerl(),txtF=new ele('div').kerl(),onOver='background:#DADADA;',offOvr=[],dt,savCS,hlightCS,
	
	csdw=function(cs,opt){var h=[],d=STR.isStr(cs)?DRW.str2drw(cs):cs;if(opt==true){each(d,function(i,v){var s=new MDL().kerl();s[i]=null;DRW.put(s,h);});return h;}else return d;},

	over_evt=function(e){e.stopPropagation();DRW.put(csdw(O.css()),offOvr);O.css(onOver)},
	
	out_evt=function(e){e.stopPropagation();O.css(csdw(onOver,true)).css(offOvr)};
	
	p.kerl=function(){return O};
	
	p.hover=function(cs){if(!(STR.isStr(cs)||DRW.isArray(cs))) return false;onOver=cs;return p};
	
	p.hlight=function(cs){hlightCS=cs;O.css(cs);O.pause('mouseover',over_evt).pause('mouseout',out_evt);return p};
	
	p.unhlight=function(){O.css(csdw(hlightCS,true)).css(offOvr);O.rebind('mouseover',over_evt).rebind('mouseout',out_evt);return p}
	
	O.bind('mouseover',over_evt).bind('mouseout',out_evt).css('overflow:hidden;border:1px solid gray;display:inline-block;margin:0px;padding:2px;padding-left:5px;padding-right:5px;');
	txtF.css("display:inline-block;");O.addChild(txtF.Object);
	
	p.setContent=function(txtOb,opt){if(!(STR.isStr(txtOb)||hq.hNumber.isNum(txtOb)||ele.isObject(txtOb))) return p;dt=txtOb;(opt==true)?'':txtF.html("");STR.isStr(dt)?dt=STR.TextNode(dt):'';txtF.addChild(dt);return p;
	};
	
	p.getContent=function(){return dt;};
	
	p.bind=function(n_evt,pk,opt){O.bind(n_evt,function(e){if(PAC.isPack(pk))pk(e,p)},opt);if((n_evt.toUpperCase()=='CLICK'||n_evt.toUpperCase()=='ONCLICK')&&hq.hPack.isPack(pk)) O.css('cursor:pointer');return p;};
	
	p.dispose=function(n_evt){hq.Event.dispose(O.Object,n_evt);}
	
	p.removeEvents=p.unbind=function(n_evt,ipk,opt){O.unbind(n_evt,ipk,opt)}
	
	p.removeAllEvent=function(){}
	
	p.resumeEvents=function(n_evt,ipk,opt){O.rebind(n_evt,ipk,opt);}
	
	p.suspendEvents=function(n_evt,ipk,opt){O.pause(n_evt,ipk,opt);}
	
	p.enable=function(opt){if(opt==true){O.css({opac:0.5});for(var n in O.src_attr('bag').bagEvents) O.pause(n);}else{O.css({opac:1});for(var n in O.src_attr('bag').bagEvents) O.rebind(n);}}
	
	p.setContent(tOb);
}
/*
	hTree
*/
function hTree(){var p=this,ele=hq.hElement,STR=hq.hString;
function core(){
	var p=this,dir='images/',
	
	b0=new ele('div',{css:'margin-left:3px;display:inline;'}).kerl(),bound,bar=[],turn=false;
	
	p.tog=new ele('IMG',{src:dir+'none.png'}).kerl();
	
	p.ico=$(p.tog.clone()).attr('src',dir+'file.gif').bind('dblclick',function(){hq.hEvent.dispose(p.tog,'click')});
	
	p.uder=$(b0.clone()).css('display:block;border-left:1px dotted #000000;margin-left:8px;');
	
	bound=$(p.uder.clone()).css('border:;heigth:auto;');
	
	p.text=new hLabel();p.text.kerl().css('border:;padding:0px;margin-left:2px');
	
	bound.addChild(b0).addChild(p.uder);
	
	for(var i=0;i++<3;){ b0.addChild(bar['i'+i]=new ele('span'));b0.addChild((i==1)?p.tog:(i==2)?p.ico:p.text)};
	
	p.tog.bind('click',function (e){if(turn){var o=e.target;if(o.src.indexOf('plus.png')!=-1){p.uder.show().css('width:inherit;height:inherit');o.src=dir+'minus.png';p.ico.attr('src',dir+'fd_open.gif');}else{p.uder.hide();o.src=dir+'plus.png';p.ico.attr('src',dir+'fd.gif');}}});
	
	p.addItem=function(txt,pack,ico_){turn=true;p.tog.attr('src',dir+'plus.png');p.ico.attr('src',dir+'fd.gif');var i_=new core(),s;if(STR.isStr(pack)) s=pack;if(STR.isStr(ico_)) s=ico_;if(s) i_.ico.attr('src',s);i_.text.setContent(txt).bind('click',pack);p.uder.addChild(i_.kerl().Object).hide();return i_;};
	
	p.kerl=function(){return bound;}
};
var b=new ele('div').kerl();

	p.setWidth=function(x){b.wid(x);return p};
	
	p.addItem=function(str,pack,ico){if(!(STR.isStr(str)||parseFloat(str))) return false;var O_=new core(),u;O_.text.setContent(str).bind('click',pack);if(STR.isStr(pack)) u=pack;if(STR.isStr(ico)) u=ico;O_.ico.attr('src',u);b.addChild(O_);return O_};
	
	p.kerl=function(){return b;}
}
/*sử dụng ngoài*/
return {
	hLabel:		hLabel,
	hTree:			hTree,
	TIMER:			TIMER,
	slide:			slide,
	slideUp:		slideUp,
	slideDown:	slideDown,
	collapsible:	collapsible,
	g_collap:		g_collap,
	hTabs:			hTabs,
	fadeIn:		fadeIn,
	fadeOut:		fadeOut,
	fadeTo:		fadeTo,
	animate:		animate
};
})();