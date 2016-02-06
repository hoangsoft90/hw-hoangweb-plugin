<?php
/**
 * @Game sudoku
 * @author: Q. Hoang, 01663.930.250
 */
class HQ_Sudoku extends matrix {

    /**
     * Đưa ra 1 phần tử ở vị trí dòng $i, cột $j
     * @param $i
     * @param $j
     */
    public function generate_item($i,$j){

        $mis=$this->get_total_items($i,$j);

        if(($picks=$this->group_top_right_items($i,$j))!==false)
        {
            //if($i==1&&$j==2) out1($picks,'b');
            $picks=_drw($picks);

            if(count(($e=$picks->removes($mis)->O))){
                //if($i==1&&$j==2) out1($e,'b');
                if(count($e)>1){

                    if($this->get_col_items($j+1)!==null){
                        $e=cross_array('max',true,$this->get_col_items($j+1),$e);

                    }
                }

                $get=get_rand_item($e);
                //$get=pick_rand_number(1,$size,$mis);

            }else
                $get=pick_rand_number(1,$this->size,$mis);

        }else{
            $get=pick_rand_number(1,$this->size,$mis);

        }
        if($get==false){
            echo '{-----------<br>'.($i).','.($j-1);
            dump($this->group_top_right_items($i,$j-1));
            echo '-------------}<br>';
        }
        return $get;
    }

    /**
     * Các phần tử ở phía góc top-right so với phần tử ($i,$j)
     * @param $i
     * @param $j
     */
    public function group_top_right_items($i,$j){
        if($i>=1){
            $ds=array('max',true);
            $res=array();
            for($q=0;$q<$i+1;$q++){
                $ds_=$this->get_row_items($q);
                shift($ds_,$j+1);
                if(count($ds_))$ds[]=$ds_;
            }
            //if($i==1&&$j==2) out1($ds);
            if(count($ds)>2){
                $res=cross_array($ds);
                if(!count($res)){
                    $ds[0]='min';
                    $res=cross_array($ds);

                }

                return $res;
            }else return false;
        }
        return false;
    }

    /**
     * Lấy hợp các phần tử của dòng & cột có giao với phần tử ($i,$j)
     * @param $i
     * @param $j
     */
    public function get_total_items($i,$j){
        $this->remove_item($i,$j);

        $total = $this->get_row_items($i);
        $total = merge_distinct($total,$this->get_col_items($j));
        return $total;
    }

    /**
     * Khởi tạo matrix Sudoku
     */
    public function init(){
        //do
        for($i=0; $i < $this->size; $i++)
            for($j=0;$j<$this->size;$j++)
            {
                $this->grid[$i][$j]=$this->generate_item($i,$j);
            }

        $this->rand_rows();
    }

    /**
     * Kiểm tra phần tử ($i,$j) có hợp lệ
     * @param $i
     * @param $j
     * @param $a
     */
    public function valid_item($i,$j,$a)
    {
        #echo '['.$i.','.$j.']=>'.$a;    #dump($this->get_total_items($i,$j));
        return !in_array($a,$this->get_total_items($i,$j));
    }

    /**
     * Chuyển $grid thành dữ liệu chuỗi
     */
    public function matrix_string()
    {
        return serialize($this->grid);
    }

    /**
     * Hiển thị kết quả sudoku
     * @param $opt
     * @param $id
     */
    public function suggest_result($opt=true,$id='sudoku'){
        $view='<table border=1 class="view" id="'.$id.'">';

        for($i=0;$i<$this->size;$i++)
        {
            $tr='<tr >';
            for($j=0;$j<$this->size;$j++)
            {
                $tr.='<td width="50">'.$this->grid[$i][$j].'</td>';
            }
            $tr.='</tr>';
            $view.=$tr;
        }

        $view.='</table>';
        if($opt==true) echo $view;
        return $view;
    }
    /**
     * Hiển thị trò chơi
     * @param $opt1
     * @param $opt2
     */
    public function show_game($opt1=true, $opt2='miss_items'){
        $view='<table class="table" id="sudoku" border="1px solid gray" cellspadding=0 cellspacing=0 class="hwdoku-view">';
        $items_hidden='';

        for($i=0;$i<$this->size;$i++)
        {
            $hide = pick_rand_more(0,$this->size-1,pick_rand_number(1,$this->size-1));
            $tr='<tr >';
            for($j=0;$j<$this->size;$j++)
            {
                if(in_array($j,$hide)){
                    $items_hidden.=$i.'-'.$j.',';
                    $str='<input onclick="hw_game.focus_item(this)" onBlur="hw_game._input_item_event(this)"  class="hwdoku-hide" pos="'.$i.'-'.$j.'" value=""/>';
                    //$this->grid[$i][$j]
                }else
                    $str=$this->grid[$i][$j];

                $tr.='<td width="50">'.$str.'</td>';
            }
            $tr.='</tr>';
            $view.=$tr;
        }

        $view.='</table>';

        if($opt1==true) echo $view;

        $game1 = new HQ_Sudoku($this->size);
        $game1->grid = $this->grid;
        $game1->remove_item(explode(',',$items_hidden));

        return ($opt2=='miss_items')?substr($items_hidden,0,strlen($items_hidden)-1):$game1->grid;
    }

    /*------------------------------end of class-------------------------------------*/
}