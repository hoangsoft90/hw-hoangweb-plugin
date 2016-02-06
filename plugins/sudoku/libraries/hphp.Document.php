<?php
/*
type: sys-lib
location: libraries/domDocument.php
	DomDocument
*/
/*
	extends: hEle, to_extend
*/
class hDocument extends hEle{
private $body;

private $head;

private $scripts=Array();

private $css=Array();

private $metas=Array();

public $title='my site';

public $dom;

function __construct(){
	$this->dom=new DOMDocument();
	$this->methods=function($p,$n,$arg){
		if(!isIn($n,array('loadHTML','loadXML'))) return 0;$p->dom->{$n}($arg[0]);
	};
}

function __call($n,$arg){
	if(isset($this->O->{$n})||ispack($this->O->{$n})) 
		return !count($args)?$this->dom->{$n}:$this->dom->{$n}($args);
	return $this;
}

function addScript($js){
	$e=self::create('script',Array('type'=>'text/javascript','src'=>$js));
	$this->scripts[]=$e;
	return $this;
}

function addCSS($c,$media='all'){
	$e=self::create('link',Array('type'=>'text/css','href'=>$c,'media'=>$media));
	$this->css[]=$e;
	return $this;
}

function addMeta($name,$content){
	$e=self::create('meta',Array('name'=>$name,'content'=>$content));
	$this->metas[]=$e;
}

function setDescription($i){$this->addMeta('description',$i);}

function setKeywords($i){$this->addMeta('keywords',$i);}

function load($html){$this->dom->loadHTML($html);}

function importNode($n,$t=true){return $this->dom->importNode($n,$t);}

function parse(){
	$this->O=cEle('html',Array('xmlns'=>'http://www.w3.org/1999/xhtml','xml:lang'=> 'en','lang'=>'en'))->O;
	$this->body=cEle('body');
	$this->head=cEle('head');
	$doctype='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML TRANSITIONAL 1.0//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$title=self::create('title',$this->title);
	
	foreach($this->css as $v)$this->head->addChild($v);
	foreach($this->scripts as $v)$this->head->addChild($v);
	foreach($this->metas as $v)$this->head->addChild($v);
	
	$this->head->addChild($title);
	$this->addChild($this->head,$this->body);
	$this->dom->appendChild($this->O);
	
	echo($this->dom->saveHTML());
}

};
?>