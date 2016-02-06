<?php
/**
 * Class HW_Array_List_items_path
 */
class HW_Array_List_items_path extends hwArray
{
    /**
     * @var array
     */
    var $data = array();
    /**
     * list items path in array
     */
    var $paths=array();
    /**
     * @var array
     */
    var $result = array();
    /**
     * delimeter
     * @var string
     */
    var $delimeter = '';

    /**
     * path
     * @param $k
     * @param $search
     */
    private function c_paths($k, $search= '')
    {
        //accept array key in string or number, ie array(0=>'A','t1'=>'T1')

        if((is_string($k) || is_numeric($k) )  ) {
            #if($search === '' || preg_match('%'.strtolower($search).'%', strtolower($k)) ) {
                array_push($this->paths,(string)$k);
           # }
        }
        else    //item value is array
        {
            foreach($this->paths as $i=>$v)
            {
                if($k[0]=='') continue;
                //let able to find number key of array in string
                if(strpos((string)$v,(string)$k[0])!==false)    //detect item of index & their value in seriaze
                {
                    foreach($k[1] as $j=>$v1)
                    {
                        /*if(0&&is_array($v1) && array_search($search, $v1)!==false){
                            _print($search === '' || ( is_array($v1) && array_search($search, $v1) !== false)
                                || (is_string($v1) && strpos(strtolower($v1), strtolower($search)) !==false));
                        }*/
                        if($search === '' || ( is_array($v1) && array_search($search, $v1) !== false)
                            || (is_string($v1) && preg_match('%'.strtolower($search).'%', strtolower($v1),$matches,PREG_OFFSET_CAPTURE, 3))
                            || $v == $search    //search by key
                        )
                        {
                            $path = ($this->paths[$i]. $this->delimeter .((string)$j));
                            array_push($this->paths, $path);    //list all array items of item
                            $this->result[] = $path;
                            #if(isset($this->paths[$i])) unset($this->paths[$i]);

                        }

                    }
                    #if(isset($this->paths[$i])) unset($this->paths[$i]);
                    break;
                }
            }
        }
    }

    /**
     * find & return path
     * @param $d
     * @param $search
     * @return array
     */
    public function find($d, $search = '') {
        if(!is_array($d)) return;    //valid array
        $z='';
        if(!$z) $z=$d;    //array pointer
        while(count($z))
        {
            $z1=array();    //reset array, save new array
            foreach($z as $i=>$v)
            {
                $this->c_paths((($z==$d)? $i : $v), $search);
                if($z==$d && is_array($v)) $z1[]=array($i,$v);    //value is new array
                else
                {
                    if(is_array($v[1]))
                    {
                        foreach($v[1] as $j=>$v1)
                        {
                            if(is_array($v1)) {
                                if($search ==='' || array_search($search, $v1) !== false)
                                    $z1[]=array($j,$v1);
                            }
                        }
                    }
                }
            }
            $z=array();    //reuse array
            foreach($z1 as $l=>$v2){ $z[]=$v2;}
        }
        return $this->paths;
    }
    /**
     * class construct
     * @param $d
     * @param $search
     */
    public function __construct($d = array(), $search='')
    {
        if(!empty($d)) $this->data = $d;    //save data of array

        $this->delimeter = '|'.uniqid(rand()) . '|';
        if(!empty($d)) $this->find($d, $search);
    }

    /**
     * @return mixed|string
     * @param $uplevel
     */
    public function get_search_item_path($uplevel=0) {
        if(!is_numeric($uplevel)) $uplevel = 0;   //validation

        $path = reset($this->result);
        $paths = explode($this->delimeter, $path);
        $max = count($paths)-$uplevel;

        $path = self::drwOfPath(array_slice($paths,0, $max));
        return $path;
    }
    /**
     * get result
     * @param $data
     * @return mixed
     */
    public function &get_search_item($uplevel=0, &$data = array() )
    {
        if(empty($data)) $data = $this->data;
        $path = $this->get_search_item_path($uplevel);

        if(!empty($data) && $path) {
            eval('$result=&$data'. $path.';');    //return all items path
            return $result;
        }
        return $path;
    }

    /**
     * remove search item found
     * @param $uplevel
     * @param array $data
     * @return mixed
     */
    public function remove_search_item($uplevel=0, &$data = array()) {
        if(empty($data)) $data = $this->data;
        $path = $this->get_search_item_path($uplevel);

        if(!empty($data) && $path) {
            eval('unset($data'. $path.');');    //return all items path
        }
    }
}
?>