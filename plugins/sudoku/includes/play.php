<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

?>
<script>

//if(<?php echo (isset($_GET['do'])&&$_GET['do']=='game')||(!isset($_GET['do']))?1:0?>)
	//var hwdoku_debugger = new get_debugger(2);
</script>

<?php
/*display error*/
#ini_set('display_errors','On');

/*global for main*/
$size=isset($_GET['size']) ? $_GET['size']: (isset($size)? $size : 3);
$auto_check = (isset($_GET['auto_check']) && $_GET['auto_check'] != 'off')? true: (isset($auto_check)? $auto_check : false);

/**
 * @do=game
 */
?>
    <br/>
    <div class="hw-sudoku-tool">
    <form action='<?php  the_permalink()?>' method='GET' onsubmit="if(confirm('<?php _e('Do you wan to reset the game?', 'hwdoku')?>')) return true;else return false ">
        <input class="hw-button" onclick="if(confirm('<?php _e('turn on/off one by one validation. Do you wan to reset the game?','hwdoku')?>')) this.form.submit();else this.checked=!this.checked" type='checkbox' <?php echo isset($_GET['auto_check'])?'checked':''?> name='auto_check'/><span> <?php _e('Check one by one', 'hwdoku')?></span>
    <br/>
        <select name='size'>
            <?php
            for($i=3;$i<=10;$i++)
            {
                echo '<option '.($size==$i?'selected':'').' value="'.$i.'">'.$i.'</option>';
            }
            ?>
        </select>
        <input type='submit' class="hw-button" onclick="" value='<?php _e('Reset', 'hwdoku')?>'/>
        <a class='hw-button' href='javascript:void(0)' onclick="hw_game.suggest_item()"><?php _e('>> Suggest Me','hwdoku')?></a>
    </form>
        </div>

<?php
	$game=new HQ_Sudoku($size);

	$game->init();
	$game_grid=serialize($game->show_game(true,'grid'));
?>

<textarea id='matrix_string' style='visibility:hidden;'><?php echo $game_grid;?></textarea>

<script>
    <?php if(isset($game)){?>
    var hw_game = new HW_Sudoku({
        'enabled_valid' : <?php echo (int)$auto_check?>,
        'win_msg' : '<?php _e('Congratulation, You are win ! ^_^', 'hwdoku')?>',
        'fail_msg' : '<?php _e('Fail Game.', 'hwdoku')?>',
        'matrix_data' : "<?php echo $game->matrix_string()?>",
        'size' : <?php echo $size?>
    });


    <?php } ?>
</script>