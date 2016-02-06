<?php
/*
type: sys-lib
location: libraries/forms.php
	Quản lý html form.
*/
/*
	extends: hEle, to_extend
*/
class Form extends hEle{
public $O;

public $name;

public $target;

public $method;

public $enctype;

public $accept;

public $accept_charset;

function __construct($n=null,$method='GET',$act=null)
{
	$add=new DRW(Array('text'=>null,'button'=>null,'checkbox'=>null,'radio'=>null,'file'=>null,'hidden'=>null,'image'=>null,'password'=>null,'reset'=>null,'submit'=>null,'textarea'=>null,'select'=>null,'fieldset'=>null,'legend'=>null,'optgroup'=>null,'option'=>null,'label'=>null));
	
	while(list($i)=$add->each($u))
	{
		$s=new form_utilizes();$s->type=$i;$add->push('replace',Array($i=>$s));
	}
	
	$this->extend('members',$add);
	$this->O=cEle('form')->O;
	
	foreach(func_get_args() as $v)
		if(isDrw($v)&&OBJ($v))
			foreach($v as $i=>$v1)$this->attr($i,$v1);
		elseif(isStr($v))
			foreach(Array('name','method','action','target','encrypt','accept','accept-charset') as $l)
			{
				if(!$this->attr($l))
				{
					$this->attr($l,$v);break;
				}
			}
}

function explorer($a,DRW &$r){
	while(list($i,$v)=_each($a,$k))
		if(isDrw($v))
			$this->explorer($v,$r);
		else 
			$r->push(Array($i=>$v));
}

function create($m,$n=null){
	$a=new DRW(func_get_args());$a->next();
	$r=new DRW();
	
	$this->explorer($a,$r);
	
	return cEle($a->get(0),$r)->set_methods(
	function($ref,$n,$args){
		if($n=='setLegend'){
			$legend=cEle('legend');
			foreach($args as $v)
				if(isDrw($v)&&OBJ($v))
					foreach($v as $i=>$v1)
					{
						$legend->do_method(Array($i=>$v1));
					}
			$ref->addChild($legend);
		}
		
		if($n=='add_option'){
			$e=cEle('option');
			foreach(args_level_1($args) as $v)
				if(isDrw($v))
					foreach($v as $i=>$v1) $e->do_method(array($i=>$v1));
				else{
					if(!$e->attr('value')) $e->attr('value',$v);
					else
						$e->html($v);
					$ref->addChild($e);
				}
		}
		
		if($n=='add_options')foreach($args as $v)$ref->add_option($v);
	});
}

function cItems($n){
	$m=cEle('div');
	$h=array();$a=args_level_1(func_get_args());
	foreach($a as $v)
		if(!isDrw($v))
			$m->addChild((isStr($v)?cEle('label',$v):$v));
		else 
			$h[]=$this->cItems($v);
	return (count($h)?$h:$m);
}

};

/*sub-class*/

class form_utilizes extends Form{
public $type;

function __construct(){}

function create($m=null,$n=null){
	$a=new DRW(func_get_args());
	while(list($i1,$v)=$a->each($l))
	{
		if(isDrw($v))
			foreach($v as $i=>$v1)
				if($i=='type')unset($a->O[$i1][$i]);
	}
	$tp='input';
	
	if(!isIn($this->type,Array('textarea','select','label','fieldset','legend','optgroup','option')))
		$a->push(Array('type'=>$this->type));
	else $tp=$this->type;
	
	return Form::create($tp,$a->O);
}

};
?>