<?php
/*
	@plugins: Element
	
*/

function createTextNode($txt){ return cur_doc()->createTextNode($txt);}

function meta($name,$content){return cEle('meta',array('name'=>$name,'content'=>$content));}

function c_link($o_html,$url='',$targ='_parent'){
	$a=cEle('a',array('html'=>(isStr($o_html)?$o_html:''),'target'=>$targ,'href'=>$url));
	if(isEle(OBJ($o_html,false))) $a->addChild(OBJ($o_html));
	return $a;
}

function assign_link(&$o,$url='',$targ='_parent'){
	if(type(OBJ($o,false))=='a') 
		_($o)->attr(array('href'=>$url,'target'=>$targ));
	else {
		$l=c_link($o,$url,$targ);
		_($o)->parent()->replaceChild($o,$l);
		$o=$l;
	}
}

function bullet($u,$tp){
	global $bullet;
	foreach($u as $v)
		if(isIn($v,$bullet))
		{
			$t=$v;$u=_drw($u)->remove($v)->O;break;
		}
	$c=cEle($tp,array('type'=>isset($t)?$t:null));
	foreach($u as $v){
		$a=(isStr($v)||isDrw($v))?li($v):$v;
		$c->addChild($a);
	}
	return $c;
}

function ul($a){return bullet(func_get_args(),'ul');}

function ol($a){return bullet(func_get_args(),'ol');}

function li($txt,$tp=null){
	$s=func_get_args();
	$a=cEle('li');
	foreach($s as $v)
		if(isStr($v))
			!$a->html()?$a->html($v):(!$a->attr('type')?$a->attr('type',$v):'');
	$a->do_method($s);
	return $a;
}

function img($src,$attr=null){
	$c=_drw(array('src'=>$src))->push($attr);
	return cEle('img',$c->O);
}

function comboBox_years($from, $to)
{
	
}

function comboBox_range($from, $to, $assign='',$opt=0)
{
	if(isNum($from)&&isNum($to)&&$from<=$to)
	{
		if(!isStr($assign)) $assign='';
		
		$list=createForm()->select->create(array());
		
		for($i=$from;$i<=$to;$i++)
			$list->add_option($i,(($opt<=0)?$assign.$i:$i.$assign));
		
		
		return $list;
	}
}

function list_months(){
	return comboBox_range(1,12,'Tháng ');
}

function list_days(){
	return comboBox_range(1,31);
}


?>