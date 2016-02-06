<?php
/*
type: sys-lib
location: libraries/Element.php
	DOMELEMENT
*/
/*
	extends: to_extend
*/
class hEle extends to_extend
{
public $O=null;

public $dom;

static $usedDocument;

public $tagName;

public $_O=null;

function __call($f,$args){
	$c=$this->methods;
	if(ispack($c)) $c($this,$f,$args,$this->O);
	if(isset($this->O->{$f}))return (!count($args)?$this->O->{$f}:$this->O->{$f}($args));
	return $this;
}

function _($t){
	if(!$this->_O) $this->_O=isset($this->O)?$this->O:self::$usedDocument->O;
	
	$p=$this->_O;$m=new DRW();$t=trim($t);$z=explode(' ',$t);
	
	if(count($z)==1&&isEle($p))
	{
		$fix=null;
		if($t{0}=='#'||$t{0}=='.'){
			$fix=$t{0};$t=substr($t,1,strlen($t)-1);
		}
		if($fix=='#')
			$this->_O=$p->getElementById($t);
		elseif($fix=='.'){
			$h=array();$a=$p->getElementsByTagName('*');
			
			foreach($a as $v)
				if(_($v)->attr('class')==$t)$h[]=$v;
				
			if(count($h)) $this->_O=$h; else  $this->_O=null;
			
		}else if(($a=$p->getElementsByTagName($t)))$this->_O=$a;return $this->_O;
	}
	else
	{
		if(!isDrw($p)){foreach($z as $l)$k=$this->_($l);return $k;}else foreach($p as $v)$m->push(_($v)->_($t));return pure($m->O);
	}
}

function create($t,$atrs=null){
	$a=func_get_args();
	$e=$this->dom->createElement($t,isStr($atrs)?$atrs:'');
	return _($e)->do_method($a);
}

function more(){return isDrw($this->O);}

function hEle($e=null,$atrs=null,$opt='selector'){
	$this->dom=(self::$usedDocument)?self::$usedDocument->dom:new DOMDocument();
	
	$z=OBJ($e,false);$this->O=(isEle($z)||isDrw($z))?$z:(isStr($e)&&$opt==E_CREATE?self::create($e,$atrs)->O:null);
	
	if($opt=='selector'){$this->O=(isStr($e)?$this->_($e):nodeList2array($e));}
	
	$this->methods=function($ref,$n,$args,$targ=null){
		$a=count($args)?$args[0]:null;
		if(strpos($n,'css_')===0){
			preg_match_all('/([A-Z])?[a-z]+/',substr($n,4,strlen($n)),$m);$s='';foreach($m[0] as $v)concat($s,$v,'-');$ref->css($s,$a);
		}else{
			$ref->target=null; $ref->do_method($n,$a);if(is_object($targ)){$ref->target=$targ;$ref->do_method($n,$a);}
		}
	};
	$this->tagName=(isEle($this->O)&&type($this->O)!=='DOMText')?$this->O->tagName:null;
}

function addChild($nodes){
	if(!$this->more()){
		$a=func_get_args();pure_args($a);
		if(isEle(OBJ($a))) $this->O->appendChild($a);
		else 
			if(isDrw($a))
				foreach($a as $v)
				{
					if(isDrw($v))
					{
						while(list($i,$u)=_each($v,$k))
							$this->addChild($u);
							
					}elseif(isEle($v)) $this->addChild($v);
				}
		
	}else foreach($this->O as $v)_($v)->addChild(func_get_args());return $this;
}

function removeChild($nodes){
	if(!$this->more()){
		$a=func_get_args();$a=pure_args($a);
		
		if(isDrw($nodes))
			while(list($i_,$v_)=_each($nodes,$l)) $this->removeChild($v_);
		elseif(isEle($nodes)) $this->O->removeChild($nodes);
		
		while(list($i,$v)=_each($a,$k))$this->removeChild($v);
	}
	else 
		foreach($this->O as $v)_($v)->removeChild(func_get_args());
	return $this;
}

function insertBefore($node,$w){
	$insert=function ($p,$node,$w){
		if(isEle($node)) $p->insertBefore($node,$w);
		if(isDrw($node)) foreach($node as $v)insert($p,$v,$w);
	};
	if(!$this->more()) insert($this->O,$w);
	else 
		foreach($this-> O as $v)$this->insertBefore($node,$w);
	return $this;
}

function eq($i=null){
	if(!$this->more()){
		if(!$this->O->hasChildNodes()) return null;
		
		$h=array();$t=$this->O->childNodes;
		
		foreach($t as $v) isEle($v)?$h[]=$v:'';
		
		if(!func_num_args()) return _($h);
		else 
			if(is_int($i))return _($this->eq()->O[$i]);
	}
	else
	{ 
		if(!func_num_args())return $this->O;else if(is_int($i))return $this->O[$i];
	}
}

function insertAfter($node,$w){
	$this->insertBefore($node,$w->nextSibling);
	return $this;
}

function _clone($t=false){return $this->O->cloneNode($t);}

function attr($a=null,$v=null){
	$m=func_get_args();
	if(!$this->more()){
		if(!func_num_args()){
			$s=new DRW();
			if($this->O->hasAttributes())
				foreach($this->O->attributes as $v) $s->push(array($v->name=>$v->value));
			return ($s->isEmpty()?null:$s);
		}
		
		if(isStr($a)&&(isStr($v)||isNum($v))) $this->O->setAttribute($a,$v);
		
		if(isStr($a)&&$v==null){
			if(preg_match('/\w/',$a,$q)&&$q[0]=='~')
				$this->O->removeAttribute($a);
			else 
				return ($this->O->hasAttribute($a)?$this->O->getAttribute($a):null);
		}
		
		foreach($m as $v)
			if(isDrw($v))
				while(list($i,$v1) =_each($v,$k))$this->attr($i,$v1);
		
	}else{
		$h=Array();foreach($this->O as $o)$h[]=_($o)->attr(func_get_args());pure($h);if(count($h))return $h;
	}
	return $this;
}

static function c_attrs($a,$v=null){
	$p=func_get_args();
	$m=array();
	if(isStr($a)&&isNumStr($v)){
		$s=cur_doc()->createAttribute($a);
		$s->value=$v;
		return $s;
	}
	foreach($p as $v)
		if(isDrw($v))
			foreach($v as $i=>$k) $m[]=self::c_attrs($i,$k);
	return pure($m);
}

function css($pr=null,$v=null){
	$b=func_get_args();
	if(!$this->more()){
		if(!func_num_args()) return $this->attr('style');
		else 
			foreach($b as $k) if(isDrw($k))foreach($k as $i=>$v1)if(isNum($i))$this->css($v1);else $this->css($i,$v1);
			
		if(isStr($pr)&&(isStr($v)||isNum($v))){
			$pr=strtolower($pr);no_space($pr);$this->attr('style',preg_replace('/([^;]+)?'.$pr.'.*?[^;]+[;|\w,-]/','',$this->css()));preg_match('/.{1,};?[\s|;]+/',$cs=$this->css(),$n);if($v!=REMOVE_CSS_ANCHOR)$this->attr('style',$cs.(!count($n)||$n[0]!=$cs?';':'').$pr.':'.strtolower($v).';');
		}
		
		if(func_num_args()==1&&isStr($pr)){
			preg_match('/([^;]+)?'.$pr.'.*?[^;]+[;|\w,-]/',$this->css(),$m);
			if(count(explode(':',$pr))>=2||count(explode(';',$pr))>=2)
			{
				foreach(explode(';',$pr) as $v1)if(count($a=explode(':',$v1))==2)$this->css($a[0],$a[1]);else $this->css($a[0]);
			}
			else
			{ 
				if(substr($pr,0,1)=='~') $this->css(str_replace('~','',$pr),REMOVE_CSS_ANCHOR);
				else 
					if(count($m)) return (count($s=explode(':',$m[0]))>=2)?str_replace(';','',$s[1]):null;
			}
		}
	}
	else 
		foreach($this->O as $o){
			$s=_($o)->css($b);
			$h=Array();
			if(!func_num_args()||!preg_match('/[(:|;)]/',$pr)) array_push($h,$s);
			if(count($h)) return $h;
		}
	return $this;
}

function isSameNode($q){
	if(!$this->more()) 
		return $this->O->isSameNode($q);
	else 
		foreach($this->O as $v) if($v->isSameNode($q)) return _($v);
}

function parent(){if(!$this->more()) return _($this->O->parentNode);}

function replaceChild($new,$old=null){
	if(!$this->more()){
		if(isDrw($new)||type($new)=='DRW')
			while(list($i,$v)=_each($new,$k)) $this->replaceChild($i,$v);
			
		if(isEle($new)&&isEle($old)) $this->O->replaceChild($new,$old);
	}else 
		foreach($this->O as $v)_($v)->replaceChild($new,$old);
	return $this;
}

function click($cal){ $this->bind('click',$cal);return $this; }

function className($cs=null){
	if(!$this->more()){
		if($cs==null) return $this->attr('class');
		$this->attr('class',$cs);
	}else{
		$h=Array();
		foreach($this->O as $v) ($k=_($v)->className($cs))&&($h[]=$v);
		if(count($h)) return $h;
	}
	return $this;
}

function bind($evt,$js=null){
	if(!$this->more()){
		if(isStr($evt)&&isStr($js)){
			if(strtoupper(substr($evt,0,2))!='ON')$evt='on'.$evt;$m=$this->attr($evt);$this->attr($evt,($m?$m.';':'').$js);
		}
		if(isDrw($evt)) 
			foreach($evt as $i=>$v)$this->bind($i,$v);
	}else 
		foreach($this->O as $v)_($v)->bind($evt,$js);
	return $this;
}

function html($txt=null){
	if(!$this->more()){
		if($txt===null)return $this->O->nodeValue/*textContent*/;else $this->O->nodeValue=$txt;
	}else{
		$h=array(); 
		foreach($this->O as $v) ($s=_($v)->html($txt))&&($s!=$this?$h[]=$s:1);
		if(count($h)) return $h;
	}
	return $this;
}

function value($v){
	if(!$this->more()) $this->attr('value',$v);else foreach($this->O as $o)_($o)->value($v);
	return $this;
}

function sh($i=0){return $this->more()?_drw($this->O)->eq($i):$this->O;}

function count_elements(){return count($this->O);}

function to_html(){
	if(!$this->more())
		return htmlspecialchars_decode($this->O->ownerDocument->saveHTML($this->O));
	else{
		$str=''; 
		for($i=0;$i<$this->count_elements();$i++) $str.=_($this->sh($i))->to_html();
		return $str;
	}
}

function to_xml(){
	if(!$this->more())
		return $this->O->ownerDocuemnt->saveXML($this->O);
	else{
		$xml='';
		for($i=0;$i<$this->count_elements();$i++) $xml.=_($this->sh($i))->to_xml();
		return $xml;
	}
}

};
?>