/*----------------------------------------------------------------------------------------------------------------
type: js-core-lib
location:/libraries/hquery.js
	HQUERY=>@Author: hplusdeveloper@yahoo.com - quachhoang_2005@yahoo.com - version 1.0
	document at: http://hquery.link4vn.com
*/
/*
#slogan:
	-công việc chung rõ ràng.
	-tối ưu.
	-lối tắt (not important).
	
#Lối viết:
Giao thông trong các lớp vỏ bao kế thừa ở các tầng sâu:
	-gọi vỏ bao (không tạo bản sao) không thể sử dụng kế thừa.
	-javascript tuân theo: vị trí tuyệt đối, định luật hàng xóm.
	-giao thông trong 1: thành phần sẽ nhấc ra riêng & các đối tượng sẽ chỉ nằm ở đó.
	-không thể kế thừa kiểu prototype sử dụng vị trí tương đối.
	
------------------------------------------------------------------------------------------------------------------*/

(function(){
/*
	variables
*/
var id=0,

	file_name='hquery',
	
	version=1.1,
	
	plugins={},

	__libs_path__='libraries';
/*
	constants
*/
var Items={
	/*duyệt đến phần tử cuối*/
	EOF:'__EOF__'
}

var constants=[
	/*tiếp tục sử dụng thông tin trước*/
	'WHAT_USED_BEFORE'
	];

for(var i in constants)
	eval("var "+constants[i]+"='__"+constants[i]+"__';");

/*///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////MODULES//////////////////////////////////////////////
@Có 2 tính năng của lớp vỏ: tính năng static (default) & thành phần đóng gói.
*/
/*-----------------------------------------------------------------------------------------------
	package
*/
function package(){
	var P=package,p=this;
}

/*-----------------------------------------------------------------------------------------------
	cache
*/
function c_cache(){
	var p=this;
	
	p.cache=_drw();
	
	
}

/*-----------------------------------------------------------------------------------------------
	quản lý gói/hàm
*/
function Pack(){
	
}

/*-----------------------------------------------------------------------------------------------
	quản lý module.
*/

function MDL(dt){
	var p=this,_p=MDL;
	
	p.O=new Object();
	
	/*kế thừa DRW: put,...*/
	
	_p.clone=function(obj) {
		var target = {},i;
		for (i in obj) {if (obj.hasOwnProperty(i)) {target[i] = obj[i];}}
		return target;
	}
	
	_p.equal=function(t1,t2){
		return func_name(t1)==func_name(t2);	
	}
}

/*-----------------------------------------------------------------------------------------------
	Sử lý phép toán số
*/
function NUM(){
	var p=this,_p=NUM;
	
	_p.sqr=function(a){return Math.sqrt(a)}
	
	_p.fibo=function(x){
		var c5=_p.sqr(5);return Math.round((1/c5)*(Math.pow((1+c5)/2,x)-Math.pow((1-c5)/2,x)));
	}
	
	_p.round=function(n,i){return Math.round(n)}/*modify*/
	
	_p.int=function(w){return parseInt(w);}
	
	_p.float=function(w){return parseFloat(w);}

	function max_min(d,t){
		var i=0,s;
		if(!isDrw(d))return d;
		else 
			for(i in d){
				if(s==null)s=d[i];
				if((t=='max')?s<d[i]:s>d[i])s=d[i];
			}
		return s;
	}
	
	_p.max=function(d){return max_min(d,'max');}
	
	_p.min=function(d){return max_min(d);}
	
	_p.currency=function(num){
		
	}
	
	_p.pickRand=function(n,i){
   	var r=_drw();
		if(i>n)i=n;
		if(!isNum(i))i=1;
		function getNum(){return Math.floor(Math.random()*(n+1));}
   	while(r.count()<i)r.push(getNum(),'distinct');
		return r.O;
	}
}
/*
	common module to extend
*/	
function to_extend(){
	var p=this;
	
	p.has_O=function(){return !isNull(p.O);}
}
/*--------------------------------------------------------------------------------------------------
	Sử lý chuỗi.
		Đầu vào có thể là đối tượng nguyên bản or bản sao của lớp này.
*/
function STR(txt){
	var p=this,_p=STR;

	function to(w){
		var o=OJ(w);
		return isStr(o)?o.toString():'';
	}
	
	p.O=to(txt);
	
	_p.TextNode=function (str){
		if(isStr(str)||isNum(str)) return document.createTextNode(to(str));
	}
	
	_p.pos=function(a,s,t,opt){
		var h=[],j;
		if(!isNum(t))t=0;
		if(isStr(s))
			while((j=a.indexOf(s,t))!==false){t=j+1;h.push(j);}
		return h.length?(opt==true?h:h[0]):null;
	}
	
	function indexOf_or_and(s,t,opt){
		var i,opt_=opt.toUpperCase();
		if(isStr(t)){
			return s.indexOf(t);
		}else if(isDrw(t)){
			for(i in t){
				if(opt_=='OR'&&indexOf_or_and(s,t[i])!=false)return true;
				if(opt_=='AND'&&indexOf_or_and(s,t[i])==false)return false;
			}
			if(opt_=='OR')return false;
			if(opt_=='AND')return true;
		}
	}
	
	_p.indexOf_or=function(s,t){
		return indexOf_or_and(s,t,'or');
	}
	
	_p.indexOf_and=function(s,t){
		return indexOf_or_and(s,t,'and');
	}
	
	p.pure=function (){
		if(p.has_O())p.O=p.O.replace(/\s/g,'');
		return p;
	}
	
	p.trim=function(m){
		var a=arguments,b,i,v,c,lef,rig;
		optimize_args(a);
		p.O=p.O.match(/\b.+\b/g)[0];
		if(!isDrw(a))return p;
		if(isNum(a[0])) b=a.shift();else b=0;
		for(i=0;i<a.length;i++)
		for(v in a)
		if(isStr(a[v])){
			if(a[v]=='number')c='\d';else c=a[v];
			lef='^(\\/?(\\s+|)('+c+'+))';
			rig='('+c+'+)?(\\s+|)$';
			
			p.O=p.O.replace(eval('/'+(b==0?lef+'|'+rig:(b<0?lef:rig))+'/g'),'');
		}
		return p;
	}
	
	p.r_trim=function(a_){
		var a=_drw(arguments).push(1,'begin');
		p.trim(a.O);
		return p;
	}
	
	p.l_trim=function(a_){
		var a=_drw(arguments).push(-1,'begin');
		p.trim(a.O);
		return p;
	}
	
	p.standard=function (){
		if(p.has_O())p.O=p.O.replace(/([\s])+/g,'$1');
		return p.trim();
	}
	
	p.truncate=function (f){
		if(isStr(f)&&p.has_O())
			p.O=p.O.replace(eval('/^('+f+')+|('+f+')+$/g'),'');
		return p;
	}
	
	_p.charFromRight=function(s,i){
		return s.charAt(s.length-i-1);
	}
	
	_p.split_more=function (txt,d){
		var g=_drw(),i,h=[];
		function s(c){
			var q=_drw();
			if(h.length)for(i in h){q.push(h[i].split(c));h=q.O;}else h=txt.split(c);
		}
		for(i in d)s(d[i]);
		return h;
	}
	
	function len(){return _p.len(p.O);}
	
	_p.len=function(s){if(!has_O())return p.O.length;}
	
	p.setCharAt=function (c,i) {
		if(i <= len()-1) p.O=p.O.substr(0,i) + c + c.substr(i+1);
		return p;
	}
	
	/*ie: {'huy hoang':'khanh','\s':'$1'}*/
	p.clrBundleChar=function (f,r){
		var rpl,i;
		if(!isDrw(f)){
			if(r==WHAT_USED_BEFORE)rpl='$1';
			else if(isStr(r))rpl=r.toString();
				else rpl='';
			p.O=p.O.replace(eval('/('+to(f)+')+/g'),rpl);
		}else 
			for(i in f)p.clrBundleChar(i,f[i]);
		return p;
	}
	
	/*ie: aaaaabbbbb -> ab
	var temp_var=pattern_func({leaveOneChar:WHAT_USED_BEFORE,clear:null},function(){
		p.{key}=function(c,i){
			var i;
			if(!isDrw(c))p.clrBundleChar(c,{val});
			else for(i in c)p.clrBundleChar(c[i],{val});
		}
	},true); -->không sử dụng được ở đây vì tạo nên chu trình*/
	
	p.leaveOneChar=function(c,i){
		var i;
			if(!isDrw(c))p.clrBundleChar(c,WHAT_USED_BEFORE);
			else for(i in c)p.clrBundleChar(c[i],WHAT_USED_BEFORE);
	}
	
	p.clear=function(c,i){
		var i;
			if(!isDrw(c))p.clrBundleChar(c);
			else for(i in c)p.clrBundleChar(c[i]);
	}
	
	_p.getWidStr=function (str){
		
	}
	
	_p.getHeiStr=function (str){
		
	}
	
}

/*--------------------------------------------------------------------------------------------------
	quản lý ngăn chứa Array. Cũng giống như php, ngăn chứa javascript không tự xắp xếp.
*/
function DRW(d){
var p=this,_p=DRW;

function to(w){
	var o=OJ(w);
	return isDrw(o)?o:d;
}

p.O=to(d);

/*also ie: to_div, to_span...*/
_p.to_drw=function(a){
	return [to(a)];
}

_p.list_items_path=function (d){
	var i,r=[],z,z1,j;
	function c_r(k){
		var i,j;
		if(isStr(k))r.push(''+k+'');
		else for(i in r){
				if(r[i].indexOf(k[0])!==-1){for(j in k[1])r.push(r[i]+'.'+j+'');delete r[i];break;}
			}
	}
	if(!z)z=d;
	while(_p.count(z)){
		z1=[];
		for(i in z){
			c_r((z==d)?i:z[i]);
			if(z==d&&isDrw(z[i]))z1.push([i,z[i]]);
			else for(j in (q=z[i][1]))if(isDrw(q[j]))z1.push([j,q[j]]);
				
		}
		z=[];for(i in z1)z.push(z1[i]);
	}
	return r;
}
	
_p.list_all=function (d){
	var i,s=[],m=_p.list_items_path(d);
	function c(z){
		var j,x=z.split('.'),r='';
		for(j in x)
		{
			r+=r?('.'+x[j]):x[j];
			if(!DRW.inDrw(r,s))s.push(r);
		}
	}
	for(i in m)c(m[i]);
	return s;
}

_p.join=function(d,c){
	var s=to(d);
	if(isDrw(s))return s.join(isStr(c)?c:'');
}

p.join=function(c){_p.join(p.O,c);}

_p.values=function (s){
	var y=[],i;
	if(isDrw(s)){
		for(i in s)y.push(p.O[i]);
		return y;
	}
}

_p.keys=function (d){
	var y=[],i;
	if(isDrw(d)){
		for(i in d)y.push(i);
		return y;
	}
}

function count(){return _p.count(p.O);}

_p.count=function(s){
	var i,j=0;
	if(isDrw(s))for(i in s)j++;return j;
}

p.each=function(f){
	for(var i in p.O)if(ispack(f))f(i,p.O[i]);
	return p;
}

_p.copy=function (d1,d2){
	var i,s;
	if(isDrw(d1)&&isDrw(d2)){
		s=_str(d1);
		for(i in d2) s.push(d2[i]);
		return s.O;
	}
}

p.get_index=function(e){
	var i,j=0;
	for(i in p.O){if(p.O[i]==e)return j;j++;}
	return false;
}

p.get_key=function(e){for(var i in p.O)if(p.O[i]==e)return i;return false;}

p.eq=function(i,opt){
	var i,j=0;
	for(i in p.O){
		if((i==Items.EOF&&j==count())
		||i==j)
			return opt==true?[i,p.O[i]]:p.O[i];
		j++;
	}
}

p.get=function(l){return p.O[l]}

p.dels=function(z){
	var i,a=arguments,q;
	function d(r){
		var i;
		if(!isDrw(r))p.dels(r);
		else for(i in r)p.dels(r[i]);
	}
	if(a.length==1){q=p.get_key(z);if(q!=false)delete p.O[q]}
	else for(i in a)d(a[i]);
	return p;
}

p.del_keys=function(z){
	var i;
	if(!isDrw(z))delete p.O[z];else for(i in z)p.del_keys(z[i]);
	return p;
}

p.del_indexs=function(z){
	var i,d=[];
	if(isNum(z))d.push(p.eq(z));else if(isDrw(z))for(i in z)p.del_indexs(z[i]);
	p.dels(d);
	return p;
}

p.clear=function(){p.O=[];return p;}

p.pure=function (t){
	var i,q=[];
	for(i in p.O)if(p.O[i]==null)q.push(i);
	p.del_keys(q);
	if(t==true&&count())return p.eq(0);
	return p.O;
}
	
_p.onlyOBJDrawer=function (b,opt){
	var d=[],i;if(isDrw(b)) for(i in b) if(isEle(b[i])) d.push(b[i]);return (opt==true)?pure(d):d;
}
	
p.str2drw=function (str,c){
	if(!isStr(str))return false;
	var ch=isStr(c)?c:',',a=str.split(ch),i,h=_drw([]);
	for(i in a)h.push(a[i].split(':'));
	return h.O;
}
	
p.pop_shift=function (t,n){
	var i,r=[],s;
	if(!isNum(n))n=1;
	while(n--){
		s=(t=='pop')?p.O.pop():p.O.shift();r.push([p.get_key(s),s]);
	}
	p.dels();
	return n>1?r:r[0];
}

p.pop=function(n){return pop_shift('pop',n);}

p.shift=function (n){return pop_shift('shift',n);}

p.push=function (a,opt){
	var i,m;
	function add(r,t){
		if(t)p.O.push(r);else p.O[r[0]]=r[1];
	}
	if(!isDrw(a)){
		if(opt=='distinct'&&p.get_key(a)==false)add(a,1);else add(a,1);
	}else 
	for(i in a)
	switch(opt){
		case 'only_key':if(!p.get(i))add([i,a[i]]);break;
		case 'distinct':if(p.get_key(a[i])==false)add([i,a[i]]);break;
		case 'auto':if(!p.get(i))add([i,a[i]]);else add(a[i],1);break;
		case 'byIndex':add(a[i],1);break;
		case 'begin':
			m=p.O;p.O=[];
			push(a);push(m);
			break;
		default:add([i,a[i]]);
	}
}
	
p.random=function (){
	function rand(){return (Math.round(Math.random())-0.5);}
	var h=[];p.copy(h);h.sort(rand);
	return h;
}

function inDrw(a,opt){return _p.inDrw(a,p.O,opt);}

_p.inDrw=function (a,d,opt){
	var i;for(i in d)if((opt==true)?(d[i]===a):(d[i]==a))return true;
}

p.reverse=function (){
	var h=[];p.copy(h);h.reverse();return h;
}
	
_p.equal=function (d1,d2){
	/*cần sử dụng (Pack,Module,..).equal*/
}

p.multi_keys=function(d){
	var i,h=[];
	function copy(l1,l2){
		if(!isStr(l1))return 0;
		var l=p.get(l1),i;
		if(!isDrw(l2))
		{
			h[l2]=l;
			p.push(h);
		}
		else for(i in l2) copy(l1,l2[i]);
	}
	if(isDrw(d))
	for(i in d)
		copy(i,d[i]);
	
	return p;
}

p.same_keys_vals=function(){
	var h;
	p.each(function(l,v){
		if(isStr(v)){
			p.del_keys(l);
			h=[];h[v]=v;
			p.push(h);
		}
	});
	
	return p;
}

}


/*
/////////////////////////////////////////END MODULES////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
*/


/*include*/
function import_js(url){
	var head=document.getElementsByTagName('head')[0],js;
	js=document.createElement('script');
	js.setAttribute('src',url);
	js.setAttribute();
	if(url.match(/.+\.js/g)==url)head.appendChild(js);
}

function import_css(url){
	var head=document.getElementsByTagName('head')[0],css;
	css=document.createElement('style');
	css.innerHTML="@import url('"+url+"')";
	if(url.match(/.+\.css/g)==url)head.appendChild(css);
}

/*mk golobal, traffic*/
function mk_public(d,opt){
	var i,m,u,h;
	function g(t){
		if(isStr(t)){
			h=_drw(t.split(',')).same_keys_vals();
			h.each(function(i,v){
				var k=[];
				k[i]=eval(i);
				h.push(k);
			});
			
			return h.O;
		}
		if(isDrw(d)) return d;
	}
	if(isDrw(d)){
		u=DRW.list_items_path(d);
		for(i in u){
			m='d'+mk_str_path(u[i]);
			eval(m+"=g(eval(m));");
		}
	}else 
		if(isStr(d))d=g(d);
	
	
	each(d,function(l,v){
		if(eval('typeof '+l)=='undefined'||opt==true){eval(l+'=v');}
	});
}

function activate(path){
	var i,s=DRW.list_all(path);
	for(i in s)if(ispack(s[i]))eval(s[i]+'()');
}

/*plugins manager*/
function __plugins(d){
	var r=[],i,s;
	for(i in d)
	r[i]=(function(_i){
			return function(){
				s=file_name+'.'+_i;
				import_js((d[_i]==0?__libs_path__:+d[_i])+'/'+s+'.js');
			}
		})(i);
	extend(plugins,r);
}

/*set lib path*/
function set_libs_path(path){__libs_path__=path;}

/*@checker*/
function ispack(wh){
	if(isStr(wh)&&eval("typeof "+wh+"=='function'"))return true;
	if(typeof wh=='function'&&wh.constructor==Function)return true;
	return false;
}

function is_module(wh){return (wh)?(wh.toString()=='[object Object]'&&!isDrw(wh)):false;}

function isNull(w){return (w==undefined||w==null)}

function isBool(wh){return (wh===true||wh===false);}

function isNum(t){return t==undefined?false:t.toString().match(/[\d,.]+/g)==t}

function isDrw(w){
	if(!isNull(w))
	if(
		(w.length>=0&&!isStr(w)&&!isEle(w)&&!ispack(w))
		||w.constructor==DRW
		||w.constructor=={}.constructor
	) return true;
	return false;
}

function isfixedDrawer(wh){
	return (isDrw(wh))? (wh.constructor!=Object&&wh.constructor!=Array):false;
}

function isStr(s){
	return !isNull(s)&&(typeof(s)=='string'||s.constructor==STR);
}

function isEle(wh){return (wh)?(wh.tagName||wh.toString()=='[object Text]')&&(typeof wh=='object'||wh.toString()=='[object]'):false}

function is_dir(s){if(isStr(s))return !isNull(s.match(/\//g));}

/*output*/
function out(s){document.write(s)}

function out1(s){document.writeln(s)}

function sh(d){var i,s='';for(i in d)s+=','+i+':'+d[i];return s.substr(1,s.length);}

/*document, os*/
function DOC(){return $(document.body);}

function ready(p){
	function call(){
		if(ispack(p))p(this);
	}
	window.onload=call;
}

function auto_load(p){
	DOC().bind('load',function(){if(PAC.ispack(p))p();});
}

/*functions/class*/
function func_name(f){
	function to_str(){
		if(is_module(f))return f.constructor.toString();
		if(ispack(f))return f.toString();
		return false;
	}
	if(to_str()!=false)
		return to_str().replace(/\v|\s+/g,' ').replace(/function\s+(\w+)|.+/g,'$1');
	return f;
}

function get_class(s){return new s;}

function func_str_content(f){
	if(ispack(f))return f.toString().replace(/\v/g,'').replace(/function.+{|(}$)/g,'');
}

/*truyền các thông tin khác nhau vào cùng 1 gói, nhưng chỉ sử lý 1 lần*/
function pattern_func(d,f,opt){
	var i,m,d1=[];
	for(i in d)
		d1.push((function(key,val){
			return function(){
				var txt=str(func_str_content(f)).clrBundleChar({'{key}':key,'{val}':val,'\/\*|\*\/':''}).O;
				eval(txt);
			}
		})(i,d[i]));
	if(opt==true)for(i in d1)d1[i]();
	return d1;
}

/*extends*/
function extend(d2,d1){
	var l=arguments.length,i,j;
	function valid(r){return (ispack(r)||is_module(r)||isDrw(r))}
	if(l==2){
		if(!valid(d2)||!valid(d1))return false;
		if(ispack(d2)){
			if(ispack(d1))eval(func_name(d2)+'.prototype=new '+d1);
			else {eval(func_name(d2)+'.prototype='+standard_drw(sh(d1)));}
		}
		else{_drw(d2).push(standard_drw(d1));}
	}
	if(l==1&&isDrw(d2)){
		for(i in d2)if(!isDrw(d2[i]))extend(d2[i],i);else for(j in d2[i])extend(d2[i][j],i);
	}
}

/*utilities of Ele*/
function cEle(tp,attr){ return new Ele(tp,attr);}

function OJ(t){
	if(!isNull(t)){
		if(!isNull(t.O))return t.O;
		if(!isNull(t.Object))return t.Object;
	}
	return t;
}

/*utilities of DRW*/
function _drw(d){return new DRW(d)}

function each(d,f){for(var i in d)f(i,d[i])}

function standard_drw(d){
	var g=[],i,y;
	for(i in d){
		y=func_name(d[i]);
		g[(isNum(i)&&isStr(y))?y:i]=d[i];
	}
	return g;
}

function pure_args(a){
	var g=a;
	while((DRW.count(g)==1)&&(g=_drw(g).get(0)));
	a=g;
}

function optimize_args(a){
	if(!isDrw(a)) a=[a];
	else pure_args(a);
}

function mk_str_path(path,opt){
	var s='',i,k=path.split('.');
	function y(r){
		return "['"+r+"']";
	}
	for(i in k)
		if(i==0)s+=(opt==true)?k[i]:y(k[i]);else s+=y(k[i]);
	return s;
}

/*utilities of STR*/
function str(s){return new STR(s)}

function str_inline(str){
	if(isStr(str))return str.replace(/(\r\n|\n|\r)/gm);
}

function str_tight(str)
{
	if(isStr(str)) return str_inline(str).split(' ').join('');
}

/*utilities of Error*/
function error(msg){throw msg;}

/*utilities of cache*/
var d_cache=new c_cache();

/*_>@setup-init*/

/*kế thừa các lớp vỏ bao với nhau*/
extend({
	to_extend:DRW
});

/*init some class' members*/
get_class(DRW);

/*thông đường static, yêu cầu cài đặt đầu cuối*/
activate({
	STR:'',
	DRW:'',
	NUM:'',
	MDL:'',
	package:'',
	EVT:''
});

/*init plugins*/
__plugins({
	Element:0,
	Event:0,
	Browser:0,
	URLLoader:0,
	Date:0
});

/*public out*/
var items_public={hquery:"Items,version,__libs_path__,plugins,ready,import_js,import_css,mk_public,__plugins,activate,set_libs_path,error,each,extend,pure_args,optimize_args,Pack,MDL,NUM,STR,DRW,package,c_cache,to_extend,isStr,isNum,isNull,ispack"};

_drw(items_public).multi_keys({'hquery':['hq','_']});

mk_public(items_public);

})();