<?php
/*
type: sys-lib
location: libraries/table_struct
	table_store
*/
/*
	extends: DRW, to_extend
*/
class table_struct extends DRW{
public $fields;
private $cache=null;

function __construct($d){$this->fields=$d;}

private function add_field(&$f,$t){
	if(isDrw($t))foreach($t as $i=>$v)if(isset($f[$i]))$f[$i]=$v;
	if(isNumStr($t))foreach($f as $i=>$v)if($v==null){$f[$i]=$t;break;}
}

function addRow($s){
	$r=_drw($this->fields)->flip()->apply_val()->O;
	foreach(func_get_args() as $v)$this->add_field($r,$v);
	$this->push(array($r));
	return $this;
}

function rs(){return $this->cache->O;}

function _and($b){
	$a=func_get_args();$a=(array)pure($a);$d=($this->cache)?$this->cache:$this;$h=new DRW();
	foreach($a as $v){
		$c=0;
		foreach($d->O as $m)
			if(isStr($v)&&($s=explode('=',$v))&&count($s)==2&&$m[$s[0]]==$s[1]){
				$c=1;if(!$h->inDrw($m))$h->push(array($m));
			}
		$d=$h;if(!$c)$h=new DRW();
	}
	$this->cache=$h;
}

function _or($b){
	$d=($this->cache)?$this->cache:$this;$h=new DRW();
	foreach(func_get_args() as $v)foreach($d->O as $m)
		if(isStr($v)&&($s=explode('=',$v))&&count($s)==2&&$m[$s[0]]==$s[1]&&!$h->inDrw($m))$h->push(array($m));
	$this->cache=$h;
}

function condition($c){
	if(isStr($c)){
		$s=str($c)->repl1(array('_and'=>'$this->_and','_or'=>'$this->_or'))->str;
		out1($s,'b');
		$this->cache=null;eval("$s;");
	}
	return $this;
}

function del($c){$this->removes($this->condition($c)->rs());return $this;}

function table_html(){$t=new table();return $t->c_rows($this->fields)->c_rows($this->O);}
};

/*
	extends: table_struct, DRW, to_extend
*/
class table_store extends table_struct{
public $tables=array();

function __construct($t=null){foreach(func_get_args() as $v)$this->c_table($v);}

function valid($a){
	$t=$f=0;
	foreach($a as $i=>$v){if(isDrw($v))$t=1;if(isStr($v))$f=1;}
	return ($f*$t)?false:($t==0?0:1);
}

function c_table($t){
	if($this->valid($t)==1)foreach($t as $i=>$v){$this->tables[]=$this->{$i}=new table_struct($v);}
	elseif($this->valid($t)==0) $this->push(array(new table_struct($t)));
	return $this;
}

function filter_fields($a,$t=1){
	$h=array();
	if(isDrw($a))foreach($a as $i=>$v){$s=($t)?$v:$i;if((!isNum($s))&&isStr($s))$h[]=$s;}
	return (count($h)?$h:null);
}

function c_table_from_data($rs,$n=null,$fields=null){
	if(!isDrw($rs)&&!count($rs))return $this;
	if(isDrw($fields)&&($f=$this->filter_fields($fields))&&$f)
		$this->c_table(isStr($n)?array($n=>$f):$f);
	else foreach($rs as $v)
		if(($f1=$this->filter_fields($v,0))==null)continue;
		else{$this->c_table(isStr($n)?array($n=>$f1):$f1);break;}
	if($f1){
		$t=!isStr($n)?$this->eq(EOF):$this->{$n};foreach($rs as $v)if(isDrw($v))$t->addRow($v);
	}
}

};
?>