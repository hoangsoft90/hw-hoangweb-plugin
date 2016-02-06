<?php
/**
 * Class HW_Sudoku_Game
 */
class HW_Sudoku_Game {
    public static function getSize() {
        //game size
        $size = isset($_GET['size'])? $_GET['size'] : '3';
        return $size;
    }
    /**
     * check item
     * @param string $size
     * @param string $matrix
     */
    public static function hwdoku_check_item($size, $matrix) {
        global $wpdb;
        if(empty($size)) $size = self::getSize();    //get sudoku size

        $matrix = unserialize($matrix);
        $i=$_GET['i'];
        $j=$_GET['j'];
        $a=$_GET['a'];
        #echo $size;

        $game1=new HQ_Sudoku($size);
        $game1->grid=$matrix;

        $valid=$game1->valid_item($i,$j,$a);
        if($valid)
        {
            $game1->set_item($i,$j,$a);

            //save to db
            try{

                $exists = $wpdb->get_var('select count(*) from '.HWDOKU_DB_TABLE);
                if(!$exists) {
                    $wpdb->insert(HWDOKU_DB_TABLE, array('grid' => $game1->matrix_string()));
                }
                else {
                    $query = $wpdb->prepare( "UPDATE ".HWDOKU_DB_TABLE." SET grid = %s LIMIT 1", $game1->matrix_string() );
                    $wpdb->query($query);
                }
            }
            catch(Exception $err) {

            }
            //$db->query("update sudoku set grid='".$game1->matrix_string()."'");

            echo '<textarea>'.$game1->matrix_string().($game1->is_full_matrix()?'[DONE]':'').'</textarea>';

            echo '<script>hwdoku_debug("'.valid_str_in_str($game1->suggest_result(false,'ok')).'");</script>';

            exit();
        }
        else
        {	//out1($i.'-'.$j);
            $game1->remove_item($i,$j);

            echo '<textarea>'.$game1->matrix_string().'[FALSE]</textarea>';
            #exit();
        }
    }

    /**
     * suggestion item
     * @param string $size
     * @param string $matrix
     * @param string $matrix_origin
     */
    public static function hwdoku_suggest_item($size , $matrix, $matrix_origin) {
        #$size = self::getSize();    //get sudoku size

        $i=$_GET['i'];
        $j=$_GET['j'];
        $matrix_origin=unserialize($matrix_origin);
        $matrix=unserialize($matrix);

        //compare with matrix original to get item
        $game1=new HQ_Sudoku($size);
        $game1->grid=$matrix_origin;

        #var_dump($matrix_origin);

        $item_value=$game1->get_item($i,$j);
        echo $i.'-'.$j.'=>'.$item_value;

        $game1->grid=$matrix;
        $game1->set_item($i,$j,$item_value);

        $done=$game1->is_full_matrix()? '*[DONE]':'';

        echo '<textarea>'.$item_value.'*'.$game1->matrix_string().$done.'</textarea>';
    }

    /**
     * valid game
     * @param null $size
     * @param $matrix
     * @param $items_string
     */
    public static function hwdoku_valid_game($size , $matrix, $items_string) {
        #$size = self::getSize();    //get sudoku size

        $matrix=unserialize($matrix);
        #$items_string=$_GET['items_string'];

        $items=explode(',',$items_string);

        $game1=new HQ_Sudoku($size);
        $game1->grid=$matrix;

        $valid=1;

        foreach($items as $v)
        {
            $item=explode(':',$v);
            $ij=explode('-',$item[0]);

            $game1->set_item($ij[0],$ij[1],$item[1]);
        }

        foreach($items  as $v)
        {
            $item=explode(':',$v);
            $ij=explode('-',$item[0]);

            if(!$game1->valid_item($ij[0],$ij[1],$item[1]))
            {

                $valid=0;
                break;
            }
        }

        echo '<textarea>'.$valid.'</textarea>';
    }
}