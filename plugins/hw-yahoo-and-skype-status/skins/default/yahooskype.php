<?php
/**
 * Plugin Name: default skin 
 */

/*
?>
<style>
.support-online .phone-icon{
    background:url(<?php if(HW_SKIN::current()->get_skin_instance('phone_icons')) echo HW_SKIN::current()->get_skin_instance('phone_icons')->get_skin_link($instance['phone_icon'])?>);
    background-size: 20px 20px;
  background-repeat: no-repeat;
  padding-top: 5px;
  padding-bottom: 5px;
  padding-left: 25px;
}
.support-online .email-icon{
    background:url(<?php if(HW_SKIN::current()->get_skin_instance('mail_icons'))echo HW_SKIN::current()->get_skin_instance('mail_icons')->get_skin_link($instance['mail_icon'])?>);
    background-size: 20px 20px;
  background-repeat: no-repeat;
  padding-top: 5px;
  padding-bottom: 5px;
  padding-left: 25px;
}
    .support-online{text-align: center;}
</style>
<?php

echo $before_widget;
echo $before_title .$instance["title"]. $after_title;
if($instance["addition_text"]) echo '<div style="text-align:center;padding:10px;">'.$instance["addition_text"].'</div>';
echo "<div class='support-online' >";


$hw_tpl->_display('yahoo-skype-status.twig', array(
    'data' => $data,
    'yk' => $this
));

foreach($data as $phone=>$inst){

    $nickcount=0;
    $count=0;
    ?>
			<div style="" class="">
				<strong><?php echo $inst["nick_name"]?></strong><br/>
				<span style="color: #ff0000;font-size: 16px;"><strong class="phone-icon"><?php echo $phone?></strong></span><br/>
				<span><strong class="email-icon"><?php echo $inst["email"]?></strong></span><br/>
				<span>
				    <?php if(isset($inst['avatar'])){?><img src="<?php echo $inst['avatar']?>"/><?php }?>
				</span>
				<?php foreach($inst["services"] as $type=>$null):
					$count++;
					$id=$inst[$type]['id'];
				?>
				<?php if($type=='yahoo'){
					$nickcount++;
				?>
				<div >
				<?php echo $this->nick_yahoo_status_link($inst[$type])?>
				</div>
				<?php }?>
				<?php if($type=="skype"){
					$nickcount++;
				?>
				<div >
				<?php echo $this->nick_skype_status_link($inst[$type])?>				
				</div>
				<?php }?>
				<?php endforeach;?>
			</div>
	<?php	
			
}

echo "</div>";

echo $after_widget;
*/
?>