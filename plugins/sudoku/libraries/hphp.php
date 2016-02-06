<?php
/*
type: core-lib
location: libraries/hphp.php
*/
/*
	Chứa các gói/hàm, bản sao dùng chung.
/*
/*-----------------------------------to_extend------------------------------------------------*/
/*class apply all utilizes for all boundary/class*/
class to_extend{
static public $tmp;
public $methods;
public $target=null;

function set_methods($f){$this->methods=$f;return $this;}

function extend($t,$d){$do=function(&$d,$t,&$p){while(list($i,$v1)=_each($d,$z1)){switch($t){case 'tmp':self::$tmp[$i]=$v1;break;case 'members':$p->{$i}=$v1;break;default:$m=new DRW(isset($p->O)&&isDrw($p->O)?$p->O:$p->O);$m->push(Array($i=>$v1));break;}}};$args=func_get_args();mov_next($args);while(list($i,$v)=_each($args,$k))$do($v,$t,$this);return $this;}

function memsToDRW(){$g=Array();foreach($this as $i=>$v)$g[$i]=$v;return $g;}

function do_method($a,$v=null){$p=($this->target)?$this->target:$this;$args=optimize_args(func_get_args());/*get callback*/foreach($args as $z)if(ispack($z)&&type($z)=='object')$cb=$z;$methods=$this->methods($p);if(isStr($a)){if(isIn($a,$methods))$p->{$a}($v);else{ if(isset($cb))$cb($p,array($a,$v));else $p->attr($a,$v);}}else while(list($i,$v_)=_each($args,$k)){if(isStr($i))$this->do_method($i,$v_);if(isDrw(OBJ($v_)))foreach($v_ as $l=>$v1)$this->do_method($l,$v1);}return $this;}

function methods($o=null){return get_class_methods(is_object($o)?$o:$this);}

function vars($o=null){return obj2drw((is_object($o)?$o:$this));}

function _print($t=0){if($t==0)out1($this->O);else dump($this->O);}

function instance(){return $this;}
};

/*----------------------------------module_class-------------------------------------*/
class module_app extends to_extend{
function get_config($item){if(isset($this->config)&&isDrw($this->config)&&isset($this->config[$item]))return $this->config[$item];return NO_EXIST_CONFIG_ITEM;}


}
/*-----------------------------------DRW------------------------------------------------*/
/*
	extends: to_extend
*/
class DRW extends to_extend{
public $O=Array();

function __construct($s=null){$a=func_get_args();foreach($a as $v)$this->push('auto',$v);}

function push($m){
	$a=func_get_args();$f=$a[0];
	
	if(!isIn($f,array('replace','byIndex','auto','distinct','begin')))
		$f='auto';
	else mov_next($a);
	
	while(list($i,$v)=each($a)){
		push($this->O,$f,$v);
	}
	return $this;
}

function add_copy(&$d){
	if(!isDrw($d))return $this;$s=_drw($d);
	$d=$s->push('auto',$this->O)->O;
	return $this;
}

function get_rand_item($opt=false){
	return get_rand_item($this->O,$opt);
}

function remove_rand_item($opt=false){
	$item=$this->get_rand_item(true);
	$this->remove_keys($item[0]);
	return $opt?$item:$item[1];
}

function isEmpty(){return $this->count()?false:true;}

function apply_val($s=''){while(list($i,$v)=$this->each($k))$this->O[$i]=$s;return $this;}

function shift($n=1){return shift($this->O,$n);}

function pop($n=1){return pop($this->O,$n);}

function splice($f,$len=1,$repl=null){
	if($f!==null){
		array_splice($this->O,$f,$len,$repl);
	}
	return $this;
}

function removes($e){
	foreach(func_get_args() as $v)
		if(isDrw($v))
			foreach($v as $l){$this->splice($this->get_index($l));}
		else $this->splice($this->get_index($v));
	return $this;
}

function remove_keys($i){if(isDrw($i))foreach($i as $v)$this->remove_keys($v);else unset($this->O[$i]);return $this;}

function keys(){$this->O=array_keys($this->O);return $this;}

function vals(){$this->O=array_values($this->O);return $this;}

function join($c=''){
	$r=Array();
	foreach($this->O as $v)if(isStr($v)||is_float($v))$r[]=$v;
	return join($r,$c);/*or use: implode*/
}

function get_index($e){
	$i=0;$h=null;
	while(list($j,$v)=$this->each($k)){
		if($v==$e){$h=$i;break;}$i++;
	}
	return $h;
}

function set_point($o){$m=$this->get_index($o);$this->first();$this->next($m);return $this;}

function clear(){while(count($this->O))$this->pop($this->O);return $this;}

function count(){return count($this->O);}

function inDrw($s,$t=false){return in_array($s,$this->O,$t);}

function sort($t=SORT_REGULAR){asort($this->O,$t);return $this;}

function prev($n=1){return mov_prev($this->O,$n);}

function end(){return mov_end($this->O);}

function cur(){return mov_cur($this->O);}

function first(){return mov_fir($this->O);}

function next($n=1){return mov_next($this->O,$n);}

function copy(&$d){$d=$this->O;find_replace(H_OBJECT,COPY_OF_IT,$d);$j=0;$i=$this->cur();foreach($d as $i1=>$v)if($i[0]==$i1&&++$j)break;if($j>1)mov_next($d,$j-1);mov_prev($d,1);}

function extractHere(){$this->extend('members',$this->O);}

function each(&$k){return _each($this->O,$k);}

function search($w=null,$t='value'){return find_replace($w,'',$this->O,false,$t);}

function eq($i=null,$t=VALS){
	if($i===null)return $this->O;$j=0;
	foreach($this->O as $k=>&$v){
		if($i===EOF&&$j==$this->count()-1){
			if($t==VALS) $r=$v;
			if($t==KEYS) $r=$k;
			if($t==KEYS_VALS) $r=array($k,&$v);
			return isset($r)?$r:null;
		}elseif($j==$i){
			if($t==VALS) $r=$v;
			if($t==KEYS) $r=$k;
			if($t==KEYS_VALS) $r=array($k,&$v);
			return isset($r)?$r:null;
		}
		$j++;
	}
}

function get($k=null){
	if($k===null)return $this->O;return (isNumStr($k)&&isset($this->O[$k]))?$this->O[$k]:NOT_EXIST_KEY;
}

function set($l,$v,$t=KEYS){
	if($this->get($l)!=NOT_EXIST_KEY)$this->O[($t==INDEX?$this->eq($l,KEYS):$l)]=$v;
	return $this;
}

function flip(){$this->O=array_flip($this->O);return $this;}

function toCore(&$s){$s=$this->O;return $this;}

function toMDL(){return (Object)$this->O;}

function xpath(){return drwOfPath($this->O);}

function same_keys_vals(){$this->flip();while(list($i,$v)=$this->each($k))$this->O[$i]=$i;return $this;}

function remove_same_items(){$s=_drw();while(list($i,$v)=$this->each($k))$s->push('distinct',$v);$this->O=$s->O;return $this;}

function remove_items_index($i){
	$k=new DRW();
	foreach(func_get_args() as $s){
		if(isNum($s))$k->push('distinct',$this->eq($s,KEYS));
		elseif(isDrw($s))foreach($s as $v)if(isNum($v))$k->push('distinct',$this->eq($v,KEYS));
	}
	$this->remove_keys($k->O);
	return $this;
}

function __destruct(){}
};

function &_drw($o=null){$z=($o)?new DRW($o):new DRW();return $z;}

/*-----------------------------------Module------------------------------------------------*/
class MDL extends stdClass{public $str='';public $O;};

/*----------------------------------STR----------------------------------------------------*/
class STR{

const STR_LEN='STR_LEN';

public $O='';

function __construct($s=''){if(isStr($s))$this->O=$s;}

function sub($f,$len){
	return substr($this->O,$f,$len);
}

function pos($s,$t=0,$opt=false){
	$h=array();
	if(!isNum($t))$t=0;
	if(isStr($s))
		while(($j=strpos($this->O,$s,$t))!==false){$t=$j+1;$h[]=$j;}
	return count($h)?($opt==true?$h:$h[0]):null;
}

function last_pos($s){
	$t=$this->pos($s,0,true);
	if($t!=false){
		$p=mov_end($t);
		return $p[1];
	}
	return false;
}

private function pos_and_or($args){
	$a=new DRW(func_get_args());$opt=$a->first();$a->next();
	foreach($a->O as $v){
		if($opt[1]=='or'&&$this->pos($v)!==false)return true;
		if($opt[1]=='and'&&$this->pos($v)===false)return false;
	}
	if($opt[1]=='or')	return false;
	if($opt[1]=='and') 	return true;
}

function pos_and($a){return $this->pos_and_or(_drw($a)->push('begin','and')->O);}

function pos_or($a){return $this->pos_and_or(_drw($a)->push('begin','or')->O);}

function repl(&$c=null,$opt=false,$t=true,$s1,$s2=''){
	static $c1=0;static $s;
	if(isStr($s1)){
		preg_match_all('/[^'.$s1.']+/',$u=($s?$s:$this->O),$m);$s=new STR($u);if($s->pos($s1)===0)$l=1;elseif($s->pos($s1)==$s->len()-strlen($s1))$r=1;preg_match_all('/^['.$s1.']+|['.$s1.']+$/',$u,$p);$s=str_replace($s1,$s2,$u,$c);$c1+=$c;$c=$c1;if($t){$s='';foreach($m[0] as $v)$s.=$v.$s2;$s=substr($s,0,strlen($s)-strlen($s2));$s=(isset($l)?$s2.$s:$s).(isset($r)||count($p[0])==2?$s2:'');}if($opt=true)$this->O=$s;return $s;
	}
	if(isDrw($s1)){foreach($s1 as $i=>$v)$s=$this->repl($c,$opt,$t,$i,$v);return $s;}
}

function repl1($a,$b=null){if(isStr($a)&&isStr($b))$this->O=str_replace($a,$b,$this->O);foreach(func_get_args() as $v)if(isDrw($v))foreach($v as $i=>$l)$this->repl1($i,$l);return $this;}

function pure(){return $this->repl($c,true,true,' ','');}

function split($c=' '){
	if(is_string($c)) return new DRW(explode($c,$this->O));
	else return new DRW($this->O);
}

function split_more($d){
	if(!isDrw($d))return $this;
	$r=shift($d);
	$k=$this->split($r[1]);
	foreach($d as $v){
		$temp=_drw();
		while(($w=$k->shift())!=null)$temp->push(explode($v,$w[1]));
		$k=$temp;
	}
	return $k->O;
}

function chunk($i=1){return new DRW(str_split($this->O,$i));}

function len(){return strlen($this->O);}

function toLower(){return strtolower($this->O);}

function toUpper(){return strtoupper($this->O);}

function toDRW(){$s=explode(',',$this->O);$m;$k=Array();foreach($s as $v){$m=explode(':',$v);$k[$m[0]]=$m[1];};return $k;}

function trim($t=''){
	$a=optimize_args(func_get_args());if(isNum($a[0]))$b=shift($a);else $b=0;
	trim($this->O);
	for($i=0;$i<count($a);$i++)
	foreach($a as $v)
	if(isStr($v)){
		if($v=='number')$c='\d';else $c=$v;
		$lef='^(\/?(\s+|)('.$c.'+))';
		$rig='('.$c.'+)?(\s+|)$';
		$this->O=preg_replace('/'.(!isDrw($b)?$lef.'|'.$rig:($b[1]<0?$lef:$rig)).'/','',$this->O);
	}
	return $this;
}

function r_trim($a){$m=_drw(func_get_args())->push('begin',1)->O;$this->trim($m);return $this;}

function l_trim($a){$m=_drw(func_get_args())->push(-1)->O;$this->trim($m);return $this;}

function charAt($n=0){return $this->O{$n};}

function charFromRight($n=1){return $this->sub($this->len()-$n,$n);}

function insertCharAt($c,$i){
	$t=$this->sub(0,$i);
	$t1=$this->sub($i,$this->len());
	$this->O=$t.$c.$t1;
	return $this;
}

function r_pos($s){
	$_pos=array();
	
	$a=$this->pos($s,0,true);
	if(count($a)) $a=array_reverse($a);
	else return false;
	
	foreach((array)$a as $i)
		$_pos[]=$this->len()-$i-strlen($s);
		
	if(count($_pos)>1) return $_pos;
	if(count($_pos)==1) return $_pos[0];
	return false;
}

function __destruct(){}
};

/*------------------------------------{Number}------------------------------------------*/
class NUM{

}

/*-----------------------------------ENCODE/DECODE-----------------------------------------*/
class Secure{
static function urlen($u){return urlencode($u);}
static function urlde($u){return urldecode($u);}
}

/*-----------------------------------end of class----------------------------------------*/
/*@utilizes of DRW*/
function obj2drw($d){
	return is_object($d)?get_object_vars($d):$d;
}

function get_rand_item($d,$opt=false){
	if(isDrw($d)&&count($d)){
		$key=array_rand($d);
		return $opt?array($key,$d[$key]):$d[$key];
	}
}

function rand_array_items(&$array,$opt=false){
	$auto_index=pick_rand_more(0,count($array)-1,count($array));
	$copy=array();
	
	foreach($auto_index as $v)
	{
		$l=_drw($array)->eq($v,KEYS_VALS);
		//out1($v.'=>'.$l);
		if($opt==true)
			$copy[$l[0]]=$l[1];
		else 
			$copy[]=$l[1];
	}
	$array=$copy;
}

function sort_by_indexs(&$array,$indexs=array(),$opt=false){
	if(count($indexs))
	$copy=array();
	foreach($indexs as $v)
	{
		$q=_drw($array)->eq($v,KEYS_VALS);
		if($q!==null){
			if($opt==false) $copy[]=$q[1];
			if($opt==true) $copy[$q[0]]=$q[1];
		}
	}
	if(count($copy)==count($array)) $array=$copy;
}

function merge_distinct($a){
	$a=func_get_args();
	$d=_drw();
	foreach($a as $v){
		$d->push('distinct',$v);
	}
	return $d->O;
}

function cross_array($a){
	$a=optimize_args(func_get_args());
	$t=shift($a,2);
	
	$res=array();
	
	$set_count=function(&$res,$v){
		foreach($res as &$p){
			if($p[0]===$v){$p[1]++;$c=true;break;}
		}
		if(!isset($c))$res[]=array($v,1);
	};

	foreach($a as $v){
		foreach((array)$v as $l) $set_count($res,$l);
	}
	
	$max=2;
	$min=$res[0][1];
	$result=array();
	$result_=array();
	
	foreach($res as $v){
		switch($t[0][1]){
		case 'max':
			if($v[1]>=$max){$max=$v[1];$result[]=$v;}
			break;
		case 'min':
			if($v[1]<=$min){$min=$v[1];$result[]=$v;}
			break;
		default:
			if($v[1]>=2)$result[]=$v;
		}
		
	}
	
	foreach($result as $v) $result_[]=$v[1];
	if(count($result_)){
		if($t[0][1]=='max') $r=max($result_);
		if($t[0][1]=='min') $r=min($result_);
	}
	$result_=array();
	
	foreach($result as $v) if(!isset($r)||$v[1]===$r)$result_[]=$v;
	
	if($t[1][1]===true){
		$tt=array();
		foreach($result_ as $v) $tt[]=$v[0];
		$result_=$tt;
	}
	
	return $result_;
}

function _each($d,&$i){
	if(!isset($i))$i=0;
	switch(true){
		case type($d)=='DRW':$h=$d->eq();break;
		case is_object($d):$h=$d->memsToDRW();break;
		case is_array($d):$h=$d;break;
		default:break;
	}
	if(!isset($h))return null;
	mov_next($h,$i);$i++;
	return each($h);
}

function shift_pop(&$d,$n=1,$t=0){
	if(!is_int($n)||$n<=0) $n=1;
	$j=0;$shift=0;$r=Array();$d=_drw($d);
	for($i=0;$i<$n;$i++)
	if($t==0){
		if($d->eq($i)!==null)$r[]=array($d->eq($i,KEYS),$d->eq($i));
	}
	else{
		if($d->eq($n-$i)!==null)$r[]=array($d->eq($n-$i,KEYS),$d->eq($n-$i));
	}
	for($i=0;$i<$n;$i++)($t==0)?array_shift($d->O):array_pop($d->O);
	$d=$d->O;
	return count($r)?pure($r):null;
}

function mov_fir(&$d){return mov($d,'reset');}

function mov(&$d,$t,$n=1){
	for($i=-1;++$i<$n;)
	switch($t){
		case 'next':$k=next($d);break;
		case 'prev':$k=prev($d);break;
		case 'end':$k=end($d);break;
		case 'current':$k=current($d);break;
		default:$k=reset($d);break;
	}
	if(isset($k)){$l=array_search($k,$d);return Array($l,$k);}else return null;
}

function mov_next(&$d,$n=1){return mov($d,'next',$n);}

function mov_prev(&$d,$n=1){return mov($d,'prev',$n);}

function mov_cur($d){return mov($d,'current',1);}

function mov_end(&$d){return mov($d,'end',1);}

function push(&$d,$m){
	$put=function(&$d,$a,$t='auto'){
		if(!isDrw($d))return null;
		
		if(isDrw($a))
		foreach($a as $i=>$v){
			if(
				$t=='replace'
				||($t=='exist_key'&&!isset($d[$i]))
			
			)$d[$i]=$v;
			
			if($t=='distinct'&&!isIn($v,$d)){if(isset($d[$i]))$d[]=$v;else $d[$i]=$v;}
			
			if($t=='auto')array_key_exists($i,$d)?$d[]=$v:$d[$i]=$v;
			if($t=='byIndex')$d[]=$v;
		}
		elseif($a!==null){
			if($t=='distinct'){
				if(!isIn($a,$d))$d[]=$a;
			}else $d[]=$a;
		}
		
	};
	$args=func_get_args();$m=mov_next($args);

	$t=$m[1];
	if(!isIn($t,array('replace','byIndex','auto','distinct','begin')))$t='auto';
		else mov_next($args);
		
	if($t!='begin'){
		while(list($i,$v)=each($args)){
			$put($d,$v,$t);
		}
	}else{
		shift($args,2);$d=_drw(array())->push($args)->push($d)->O;
	}
}

function shift(&$d,$n=1){return shift_pop($d,$n);}

function pop(&$d,$n=1){return shift_pop($d,$n,1);}

//no in use
function seek($d,$s,$cal){if(!isDrw($d)||$s==null)return false;function filter($s,$m,$cal){static $k;if(!$k)$k=new DRW();$h=new DRW();foreach($m as $i=>$v){if(isDrw($v)){$h->push($v);}elseif($v===$s){$k->push(Array($i=>$s));}};if(count($h->O))filter($s,$h->O,$cal);else{ $r=$k->O;$cal(empty($r)?0:$r);}};filter($s,$d,$cal);}

function &pure(&$d){$j=0;if(!isDrw($d))return $d;else foreach($d as $i=>$v)if(!$v)unset($d[$i]);if(count($d)==1)$d=array_pop($d);return $d;}

function pure_args(&$a,$c=null){
	if($c!=null)$o=&$c;else $o=&$a;if(isDrw($o)&&count($o)==1&&($d=_drw($o)->get(0))!=NOT_EXIST_KEY)$a=$d;if(isset($d)&&isDrw($d)&&count($d))pure_args($a,$d);
}

function &optimize_args($arg_total){
	$ag=$arg_total;pure_args($ag);
	if(!isDrw($ag))$ag=(array)$ag;
	return $ag;
}

function args_level_1($a){return (count($a)==1)?(!isDrw($a[0])?(array)$a[0]:$a[0]):$a;}

function find_replace_check(&$y,$t1,$i){$path=$t1.'/'.$i;foreach($y as $i1=>&$l){$f=0;if(($k=explode('/',$l))&&($k[count($k)-1]==$t1)){$l.='/'.$i;$f=1;break;}elseif($t1==$k[count($k)-2]&&false===strpos($l,$path)){pop($k);$y[]=(join($k,'/').'/'.$i);break;}};return $f;}

function &find_replace($a,$b,&$d,$t=true,$for='value',$init=true,&$c=null){
	static $h=array();static $r=array();static $y=array();
	if($init){$h=array();$r=array();$y=array();}if(!$c)$o=&$d;else $o=&$c[1];
	if(is_array($o))
	foreach($o as $i=>&$v){
	if(is_array($v)||isDrw($v)){if(isset($v->O))$v=&$v->O;
		$h[]=array($i,&$v);if($c){$path=$c[0].'/'.$i;if(!count($y))$y[]=$path;if(!find_replace_check($y,$c[0],$i)){foreach($y as $u)if(false!==strpos($u,$c[0]))$tt=1;if(!isset($tt))$y[]=$path;}}
	}
		/*search for value or key*/$xpath=$c[0].'/'.$i;
		if(($for=='value'&&($a==H_OBJECT?'object':gettype($a))==gettype($v)&&($a==H_OBJECT?true:$v===$a))
		||($for=='key'&&isStr($a)&&$a===$i))
		{
			if(!count($y))$r[]=array(&$v,$xpath);
		foreach($y as $z)if(false!==strpos($z,$xpath)){$r[]=array(&$v,$z);$m=1;}
		if(!isset($m))foreach($y as $z){$w=explode('/',$z);if($w[count($w)-1]==$c[0])$r[]=array(&$v,$z.'/'.$i);if($w[count($w)-2]==$c[0]){pop($w);$r[]=array(&$v,join($w,'/').'/'.$i);}}
		if($t)$o[$i]=($b==COPY_OF_IT?_clone($o[$i]):$b);
		}
	}
	if(count($h)&&($n=&$h[0])&&array_shift($h)){if(isset($n->O))$n=&$n->O;find_replace($a,$b,$d,$t,$for,false,$n);}
	$jo=array($r,$y);return $jo;
}

function drwOfPath($d){$a='';foreach($d as $v)$a.='['.$v.']';return $a;}

function &get_item_path(&$d,$path_drw){$r;eval('$r=&$d'.drwOfPath($path_drw).';');return $r;}

function registry_array($s,$it=null){$a=($it==null)?array():$it;$a[]=$s;return $a;}

function nodeList2array($l){$h=array();if(type($l)=='DOMNodeList')for($i=0;$i<count($l);$i++)$h[]=$l->item($i);if(count($h))return $h;return $l;}

/*@utilizes of string*/
function concat(&$s,$m,$c){$s.=$c.$m;strpos($s,$c)==0?$s=substr($s,strlen($c),strlen($s)):null;}

function no_space(&$t){if(!isStr($t))return $t;$t=str_replace(' ','',$t);}

function match_in($a,$d,$opt=true){
	foreach($d as $v)
		if(
			($v===$a&&$opt===true)||
			(isStr($a)&&isStr($v)&&$opt===false&&str($v)->pos($a)!=false)
		)return $v;
	return null;
}

function vn_str_filter ($str){  
        $unicode = array(  
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',  
            'd'=>'đ',  
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',  
            'i'=>'í|ì|ỉ|ĩ|ị',  
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',  
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',  
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',  
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',  
            'D'=>'Đ',  
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',  
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',  
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',  
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',  
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',  
        );  
          
       foreach($unicode as $nonUnicode=>$uni){  
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);  
       }  
        return $str;  
    }

function str_inline($str){return preg_replace("(\r+)","",$str);}

function str_tight($str)
{
	return preg_replace('|\s+|','',str_inline($str));
}

function valid_str_in_str($html,$quote='"'){
	if(isStr($html))
	{
		return str_replace($quote,'\\'.$quote,$html);
	}
}

/*@clone object*/
function &_clone($w){if(isEle(OBJ($w)))$c=_($w)->_clone(1);else{if(is_object($w))$c=clone $w;else $c=$w;}return $c;}

function var_name(&$var, $scope=0)
{
    $old = $var;
    if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) return $key;  
}

/*@instances*/
function store($d=null){return new table_store($d);}

function str($s){return new STR($s);}

function __hw($e){OBJ($e);return new hEle($e);}

function cEle($tag,$atrs=null){return new hEle($tag,$atrs,E_CREATE);}

function &actived_hDoc_instance(){$dom=new hDocument();activedDocument($dom);return $dom;}

function createForm($n=null,$method='get',$act=null){return new Form(func_get_args());}

function _file($f,$t='r'){return new file($f,$t);}

function c_mdl(){return new MDL();}

function cFrame(){return new frame();}

function _date(){return new hDate();}

function &db_connect($config=Array()){$db=&mysql::mysql_instance($config);return $db;}

/*@utilizes of number*/
function currency(){}

function pick_rand_number($f,$t,$d=array()){
	if(isNum($f)&&isNum($t)&&$f<$t){
		$d=(array)$d;
		$filter=array();
		
		for($i=$f;$i<=$t;$i++)
		{
			if(!in_array($i,$d)) $filter[]=$i;
		}
		
		return count($filter)?get_rand_item($filter):false;
	}
}

function pick_rand_more($f,$t,$len){
	if(isNum($f)&&isNum($t)&&$f<$t){
		$r=_drw();
		$result=array();
		for($i=$f;$i<=$t;$i++) $r->push($i);
		
		if(!isNum($len))$len=1;
		if($len>$t-$f+1) $len=$t-$f+1;
		
		while($len--)$result[]=$r->remove_rand_item();
		return $result;
	}
}

/*@utilizes of _Date*/
function __date($t=null){return new _Date($t);}

function d_time($t){return __date($t)->get_datetime_string();}

/*chuỗi sử dụng định dạng: - ie: 01-10-12*/
function isDate($str)
{
	$stamp = strtotime($str); 
	if (!is_numeric($stamp)) return FALSE;
	if(checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp))) return TRUE;return FALSE;
}

/*@utilities of url class*/
function _url($u){return new url($u);}

/*@common OS*/
function import($f,$opt=true){
	if(isStr($f)&&$opt==true)include($f);
	elseif(isDrw($f))foreach($f as $v)import($v,$opt);
	return $f;
}

function package($dir,$specify=null,$opt=true){
	$list=array();
	if(is_dir($dir))
	{
		$list_file=array();$files_dir=file::read_files_in_dir($dir);
		
		foreach($files_dir as $file)
		{
			preg_match('|[^\/]+$|',$file,$r);
			if(str($r[0])->r_pos('.php')===0) $list_file[]=array($r[0],$file);
		}
		
		$is_in=function($a,$b,$is_in){
			if(isStr($a)&&isStr($b))
			{
				return strpos($a,$b);
			}else
			if(isDrw($b)&&count($b)){
				foreach($b as $v) 
					if($is_in($a,$v,$is_in)!=false) return true;
				return false;
			}
			return true;
		};
		
			foreach($list_file as $i)
			{
				if($is_in($i[0],$specify,$is_in)!==false) $list[]=import($i[1],$opt);
			}
	}
		elseif(is_file($dir))$list[]=import($dir,$opt);
	return $list;
}

function error($mes){throw new Exception($mes);}

function cur_doc(){return hEle::$usedDocument->dom;}

function &OBJ(&$o,$t=true){
	if(isset($o->O)){
		$m=&$o->O;if($t)$o=$m;
		return $m;
	}else return $o;
}

function activedDocument($doc){hEle::$usedDocument=$doc;}

function load_class($z){
	$a=func_get_args();
	$class_name;$dir;$prefix='';
	
	foreach($a as $v){
		if(isStr($v)){
			switch(true){
			case !isset($class_name): $class_name=$v;break;
			case !isset($dir): $dir=$v;break;
			case !$prefix: $prefix=$v;break;
			}
		}
	}
	
	if(!isset($class_name))return false;
	
	if(!class_exists($prefix.$class_name)){
		switch(true){
		case file_exists($class_name): include($class_name);break;
		case file_exists($dir.'/'.$class_name): include($dir.'/'.$class_name);break;
		default: return false;
		}
	}
	
	$r=_drw($a)->search('__construct','key');
	if(count($r[0]))foreach($r[0] as $k)$pass_construct=$k[0];
	
	$class=new $prefix.$class_name(isset($pass_construct)?$pass_construct:null);
	
	foreach($a as $v)
		if(isDrw($v))foreach($v as $member=>$var)
		{
			if(method_exists($class,$member))
				call_user_func(array($prefix.$class_name, $member),$var);
			else $class->{$member}=$var;
		}
	return $class;
}

/*@output*/
function out($w){print_r($w);}

function dump($w){var_dump($w);}

function sh($u){while(list($i,$v)=_each($u,$k)){echo $i.':';out($v);echo '<br>';}}

function out1($w,$t='n'){
	echo $t=='b'?'<b>':($t=='i'?'<i>':'');
	print_r($w);
	echo ($t=='b'?'</b>':($t=='i'?'</i>':'')).'<br>';
	return 1;
}

/*@checker, valid*/
function type($w){return is_object($w)?(get_class($w)=='DOMElement'?$w->tagName:get_class($w)):gettype($w);}

function ispack($w){return (isStr($w)&&function_exists($w))||(is_callable($w)&&$w instanceof Closure);}

function isDrw($t){return (is_array($t)||$t instanceof DRW||type($t)=='DOMNodeList')&&!isStr($t);}

function isNum($w){return isStr($w)?!preg_match('/\D/',$w):(is_int($w)||is_float($w))?true:false;}

function isIn($s,$d){
	if(isStr($s)&&isStr($d)) return (str($d)->pos($s)!=false);
	
	if(isDrw($d))
	while(list($i,$v)=each($d))
		if($v===$s)return true;
	return false;
}

function isNumStr($w){return isNum($w)||isStr($w);}

function isStr($y){return is_string($y)&&str_replace(' ','',$y)!='';}

function isEle($w){return ($w instanceof DOMElement||$w instanceof DOMText||type($w)=='hEle');}

function isBound($w){$c=new ReflectionClass($w);return is_object($w)&&$c->isInstance($w);}

function valid_email($email){return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);}

/*@extend*/
function extend($Odw,$d){$d->toCore($d);if(isDrw($d))foreach($d as $i=>$v)if(isDrw($Odw))$Odw[$i]=$v;elseif(type($Odw)=='object')$Odw->{$i}=$v;}

/*@server environment*/
function envir(){return (object)array(
	'path'=>__FILE__,
	'name'=>$_SERVER['SCRIPT_NAME']/*PHP_SELF*/,
	'uri'=>$_SERVER['REQUEST_URI'],
	'ip'=>$_SERVER['SERVER_ADDR'],
	'host'=>$_SERVER['SERVER_NAME'],
	'software'=>$_SERVER['SERVER_SOFTWARE'],
	'protocol'=>$_SERVER['SERVER_PROTOCOL'],
	'method'=>$_SERVER['REQUEST_METHOD'],
	'gotime'=>$_SERVER['REQUEST_TIME'],
	'args'=>$_SERVER['QUERY_STRING'],
	'root'=>$_SERVER['DOCUMENT_ROOT'],
	'connection'=>$_SERVER['HTTP_CONNECTION'],
	'referer'=>isset($_SERVER['REFERER'])?$_SERVER['REFERER']:null);
}

/*@loader*/
function import_css($url){echo "<script>hquery.import_css('".$url."')</script>";}

function js_script($url){echo "<script>hquery.import_js('".$url."')</script>";}


/*
	default language: 
*/
/*
call_user_func_array: gọi hàm truyền ngăn chứa là các thông số truyền vào gói.
serialize: đóng gói thành phần (giá trị) (trừ hàm) trong môi trường thành chuỗi byte.
unserialize: giải nén giá trị đối tượng được nén bởi serialize.
*/
?>