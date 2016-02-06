<?php
/**
 * Class replace_array
 */
class HW_Array_replace_array{
    /**
     * find & replace
     * @param $paths
     * @param $old_key
     * @param $new_key
     * @return int
     */
    public function find_replace_check(&$paths,$old_key,$new_key)
    {
        $_path=$old_key.'/'.$new_key;    //patch of new path
        $f=0;
        foreach($paths as $i1=>&$path)
        {
            $f=0;
            if(($k=explode('/',$path)) && ($k[count($k)-1]==$old_key))    //if end segment of path math old key
            {
                $path.='/'.$new_key;    //assign new key to match their path
                $f=1;    //ok, was found
                break;
            }
            //if old key match near end path segment & new part of path not match old path
            elseif($old_key==$k[count($k)-2] && false===strpos($path,$_path))
            {
                array_pop($k);    //remove end segment from path
                $new_path=(join($k,'/').'/'.$new_key);    //new path
                $paths[]=$new_path;//set new path
                break;
            }
        }
        return $f;    //return status
    }
    /**
     * valid value path compare to what to find
     * @param $path
     * @param $v
     * @param $array
     * @param $opt
     */
    public static function valid_result_path($path,$v,&$array,$opt)    //only search for value
    {
        if($opt=='key') return true;    //not use for search by key
        
        eval('$result =$array'.self::drwOfPath(explode('/',$path)).'===$v;');    //check value of path in array
        return $result;
    }

    /**
     * @param $d
     * @return string
     */
    protected static function drwOfPath($d){
        $a='';
        foreach($d as $v) $a.='['.$v.']';
        return $a;
    }
    /**
     * main function
     * @param $find
     * @param $replace
     * @param $array
     * @param bool $t
     * @param string $for
     * @param bool $init
     * @param null $c
     * @return array
     */
    public function &find_replace($find,$replace,&$array,$t=true,$for='value',$init=true,&$c=null)
    {
        static $h=array();    //draft
        static $result=array();    //main result main
        static $draft_result_paths=array();    //purpose for draft
        static $paths=array();    //all paths found in array
        
        if($init)    //first init storages
        {
            $h=array();$result=array();$paths=array();
            $draft_result_paths=array();
        }
        $valid_recursive=true;    //valid recursive
        while($valid_recursive)
        {
            if(!$c) $o=&$array;else $o=&$c[1];    //fetch array remain
            if(is_array($o))
            foreach($o as $key=>&$v)
            {
                if(is_array($v))    //detect array without value
                {
                    if(isset($v->O)) $v=$v->O;
                    $h[]=array($key,&$v);    //save this item of array for map location of array
                    if($c)
                    {
                        //save path & valid for it
                        $path=$c[0].'/'.$key;    //prep path for this key
                        if(!count($paths) && !isset($draft_result_paths[$path]))    //set first path with path & their value of end item of segment
                        {
                            $paths[]=$path;
                            $draft_result_paths[$path]=1;    //track path
                        }    
                        if(!find_replace_check($paths,$c[0],$key))
                        {
                            foreach($paths as $u) 
                            {
                                if(false!==strpos($u,$c[0])) $tt=1;
                            }
                            if(!isset($tt) && !isset($draft_result_paths[$path])) //prevent of duplicate path
                            {
                                $paths[]=$path;
                                $draft_result_paths[$path]=1;    //track path
                            }
                        }
                    }
                }
                /*search for value or key*/
                $xpath=$c[0].'/'.$key;
                if(($for=='value' && ($find=='H_OBJECT'? 'object' : gettype($find))==gettype($v) && ($find=='H_OBJECT'? true : $v===$find))
                    || ($for=='key' && $find===$key))    //filter 1
                {
                    if(!count($paths) && !isset($draft_result_paths[$xpath])) //for distinct
                    {
                        $result[]=array(&$v,$xpath);    //if no any array in deep, get a result
                        $draft_result_paths[$xpath]=1;    //save this path of result
                    }
                    foreach($paths as $path)
                    {
                        if(false!==strpos($path,$xpath) && $path!==$xpath && self::valid_result_path($path,$v,$array,$for))    //valid array value for get right result, just apply to search for value
                        {
                            if(!isset($draft_result_paths[$path])) //filter distinct
                            {
                                $result[]=array(&$v,$path);$m=1;
                                $draft_result_paths[$path]=1;    //save this path of result
                            }
                        }
                    }
                    if(!isset($m))
                    foreach($paths as $path)
                    {
                        $w=explode('/',$path);
                        if($w[count($w)-1]==$c[0] && self::valid_result_path($path.'/'.$key,$v,$array,$for)) //valid array value for get right result, just apply to search for value
                        {
                            $new_path=$path.'/'.$key;    //get new path
                            if(!isset($draft_result_paths[$new_path])) //filter distinct
                            {
                                $result[]=array(&$v,$new_path);
                                $draft_result_paths[$new_path]=1;    //save this path of result
                            }
                        }
                        if($w[count($w)-2]===$c[0] && self::valid_result_path(join($w,'/').'/'.$key,$v,$array,$for)) //valid array value for get right result, just apply to search for value
                        {
                            array_pop($w);
                            $new_path=join($w,'/').'/'.$key;    //get new path
                            if(!isset($draft_result_paths[$new_path]))
                            {
                                $result[]=array(&$v,$new_path);
                                $draft_result_paths[$new_path]=1;    //save this path of result
                            }
                        }
                    }
                    if($t)$o[$key]=($find=='COPY_OF_IT'? _clone($o[$key]) : $find);
                }
            }
            if(count($h) && ($n=&$h[0]) && array_shift($h))
            {
                $c=&$n;
                if(isset($n->O)){ $c=&$n->O;}    //for DRW class instance
            }
            else $valid_recursive=false;
        }
        $jo=array($result,$paths);
        return $jo;
    }

}
?>