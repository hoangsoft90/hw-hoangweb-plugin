<?php
/*
type: sys-lib
location: libraries/file.php
	class File
*/
/*
	extends: to_extend
*/
class file extends to_extend{

public static $EXT;

public $file;

public $name;/*r,w,w+,r+,rb*/

function __construct($f=null,$t='r'){
	if(isStr($f)){
		$this->name=$f;
		$this->file=fopen($f,$t)/* or die('Loi file !')*/;
		$this->extend('members',pathinfo($f));
	}
	if(is_resource($f))$this->file=$f;
	self::$EXT=array('doc','docx','php','asp','php','xml','txt','*');
}

function exist(){return is_resource($this->file);}

static function valid_load_file($d){
	$m=_drw(OBJ($d));
	while(list($i,$v)=$m->each($k))
		if(!_file($v)->exist())$m->removes($v);
	return $m->O;
}

function open($f){
	$this->name=$f;
	$this->file=fread($f,'r') or die('Loi mo file!');
	return $this;
}

static function read_files_in_dir($dir){
	$f=array();$d=new DRW();
	
	$r=function($l,&$s,&$f){
		if(!is_dir($l))return $l;
		$u=opendir($l);
		while(($y=readdir($u))!=false)
		{
			if($y=='.'||$y=='..')continue;
			$loca=$l.$y;
			if(is_dir($loca)) $s->push($loca);
				else $f[]=$loca;
		}
	};
	
	$q=function(&$d,&$f,$q,$r){
		$e=new DRW();
		while(($v=$d->pop())!=null)
		{
			$e=new DRW();
			$r($v[1],$e,$f);
		}
		$d->push($e->O);
		if($d->count()) $q($d,$f,$q,$r);
	};
	
		$r($dir,$d,$f);
		if($d->count()) $q($d,$f,$q,$r);
		return $f;
}

/*no using file pointer*/
function read($len=null){
	if(!isNum($len)) $len=filesize($this->name);
	return fread($this->file,$len);
}

function get_contents($len=-1,$offset=-1){
	if(!$this->file)return false;
	return stream_get_contents($this->file,$len,$offset);
}

/*same: file,but different:readfile*/
static function file_contents($f){return file_get_contents($f);}

function get_lines($len=null){
	if(!$this->file)return false;
	if($this->name)return file($this->name);
	
	$k=array();
	while(($d=$this->cur_line($len))!==false)$k[]=$d;
	
	return $k;
}

function cur_line($len=null){
	if(!$this->file)return false;
	if(!isNum($len))
		return fgets($this->file);
	else 
		return fgets($this->file,$len);
}

function eof($calback=null){
	if(!$this->file)return false;
	if(ispack($calback))
		while(!feof($this->file))$calback($this);
	else return feof($this->file);
}

function get_pointer(){if($this->file)return ftell($this->file);}

function reset(){rewind($this->file);return $this;}

function set_pointer($i=0){fseek($this->file,$i);return $this;}

function write($str){
	if(!is_resource($this->file))return false;
	fwrite($this->file,$str);
	return $this;
}

function append($str=null,$f=null){
	$n=($f==null)?$this->name:$f;
	$s=_file($n,'a');
	if(isStr($str))$s->write($str);
	return $s;
}

/*feature*/
function mk($f){return _file($f,'w');}

function del(){if($this->name)unlink($this->name);return $this;}

function close(){fclose($this->file);}

};
?>