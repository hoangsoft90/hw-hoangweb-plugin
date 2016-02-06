<?php
/*
	@Matrix
	@Author: Q. Hoang, 01663.930.250
	@Quản lý ma trận.
*/
class matrix{
	public $num_rows;
	
	public $num_cols;
	
	public $grid;
	
	public $size;
	
	function __construct($i,$j=null){
		if(isNum($i)) $this->num_rows=$i;
		if(isNum($j)) $this->num_cols=$j;
		else $this->set_square_matrix($i);
		
		$this->grid=array();
	}
	
	function set_square_matrix($size){
		if(isNum($size)&&$size>0){
			$this->num_rows=$size;
			$this->num_cols=$size;
			$this->size=$size;
		}
	}
	
	function put_cols($j,$items=array()){
		if($j>=0 and $j<$this->num_cols)
		{
			$n=0;
			foreach($items as $v){
				if($n>=$this->num_rows) break;
				$this->grid[$n][$j]=$v;
				$n++;
			}
		}
		return $this;
	}
	
	function put_rows($i,$items=array()){
		if($i>=0 and $i<$this->num_rows){
			$n=0;
			foreach($items as $v)
			{
				if($n>=$this->num_cols) break;
				$this->grid[$i][$n]=$v;
				$n++;
			}
		}
		return $this;
	}
	
	function rand_rows(){
		rand_array_items($this->grid);
	}
	
	function rand_cols(){
		$auto_indexs=pick_rand_more(0,$this->num_cols-1,$this->num_cols);
		foreach($this->grid as &$line)
		{
			sort_by_indexs($line,$auto_indexs);
		}
	}
	
	function get_row_items($i){
		if($i>=0&&$i<$this->num_rows)
		{
			$items=array();
			for($n=0;$n<$this->num_cols;$n++)
			if(isset($this->grid[$i][$n]))
				$items[]=$this->grid[$i][$n];
			return $items;	
		}
	}
	
	function get_col_items($j){
		if($j>=0&&$j<$this->num_cols)
		{
			$items=array();
			for($n=0;$n<$this->num_rows;$n++)
			if(isset($this->grid[$n][$j]))
				$items[]=$this->grid[$n][$j];
			
			return $items;
		}
	}
	
	function set_matrix($a){
		$a=func_get_args();
		$n=0;
		foreach($a as $v){
			if($n>=$this->num_rows) break;
			$this->put_rows($n,$v);
			$n++;
		}
	}
	
	function check_row($i){
		return ($i>=0&&$i<$this->num_rows);
	}
	
	function check_col($j)
	{
		return ($j>=0&&$j<$this->num_cols);
	}
	
	function check_item($i,$j)
	{
		return ($this->check_row($i)&&$this->check_col($j));
	}
	
	function get_item($i,$j){
		if(isset($this->grid[$i][$j])) return $this->grid[$i][$j];
	}
	
	function set_item($i,$j,$a)
	{
		if($this->check_item($i,$j))
		{
			if($a=='')
				$this->grid[$i][$j]=null;
			else
				$this->grid[$i][$j]=isNum($a)?(int)$a:$a;
		}
	}
	
	function remove_item($i,$j=null)
	{
		if(isStr($i)&&$j===null)
		{
			$t=explode('-',$i);
			$this->remove_item($t[0],$t[1]);
			return;
		}
		
		if(isNum($i)&&isNum($j))
		{
			$i=(int)$i;
			$j=(int)$j;
			if($this->check_item($i,$j))
				$this->grid[$i][$j]=null;
			return;
		}
		foreach(func_get_args() as $v)
		{
			if(isDrw($v))
				foreach($v as $l)
				{
					if(isStr($l)) $this->remove_item($l);
				}
			elseif(isStr($v)) $this->remove_item($v);
			
		}
	}
	
	function is_full_row($i)
	{
		if($this->check_row($i))
		{
			for($j=0;$j<$this->num_cols;$j++)
				if(!isset($this->grid[$i][$j])||$this->grid[$i][$j]==null) return false;
		}
		return true;
	}
	
	function is_full_col($j)
	{
		if($this->check_col($j))
		{
			for($i=0;$i<$this->num_rows;$i++)
				if(!isset($this->grid[$i][$j])||$this->grid[$i][$j]==null) return false;	
		}
		return true;
	}
	
	function is_full_matrix()
	{
		for($i=0;$i<$this->num_rows;$i++)
			if($this->is_full_row($i)==false) return false;
		return true;
	}
}
/*located: libraries/matrix.php*/
?>