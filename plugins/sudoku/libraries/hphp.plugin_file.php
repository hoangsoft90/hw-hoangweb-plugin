<?php
/*
	@file plugin
	@Author: hphp-hoàng
*/
function file_name($f,$opt=false)
{
	if(isStr($f)){
		preg_match('|([^\/]+$)|',$f,$r);
		$file=$r[0];
		if(preg_match('|(.+)?(?=\.)|',$file,$d)&&$opt==true) $file=$d[0];
		return $file;
	}
}

function file_ext($f)
{
	if(isStr($f)){
		preg_match("/[^.]+$/",$f,$d);
		if(count($d))return $d[0];
	}
}

function valid_file_name($fs){
	if(isStr($fs)) return preg_match('/[\\/:*?"<>|]/',$fs)?preg_replace('/[\\/:*?"<>|]/','_',$fs):$fs;
	if(isDrw($fs)){
		$d=array();
		foreach($fs as $v)$d[]=valid_file_name($v);
		return $d;
	}
}
?>