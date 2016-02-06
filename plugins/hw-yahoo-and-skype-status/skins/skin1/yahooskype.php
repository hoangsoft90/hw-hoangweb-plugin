<?php
/*
Plugin Name: My Skin 1
*/
echo $before_widget;
echo $before_title .$instance["title"]. $after_title;
if($instance["addition_text"]) echo '<div style="text-align:center;padding:10px;">'.$instance["addition_text"].'</div>';
echo "<div class='support-online' >";
$data=array();
$count_colspans=array();
for($i=1;$i<= $nums;$i++){
	if(!empty($instance["nick_".$i])){
		$count_colspans[$instance['nick_type_'.$i]]=1;
		
		$instance['id']=$i;
		if(!isset($data[$instance['phone_'.$i]])) $data[$instance['phone_'.$i]]=array_merge(array(),$instance);
		
		if(!isset($data[$instance['phone_'.$i]]['services'])){
			$data[$instance['phone_'.$i]]['services']=array();
		}
		$data[$instance['phone_'.$i]]['services'][$instance['nick_type_'.$i]]=1;
		$data[$instance['phone_'.$i]][$instance['nick_type_'.$i]]=$instance;
	}
}
foreach($data as $phone=>$inst){
	
				$nickcount=0;
				$count=0;
				?>
			<div style="" class="line">
				<strong><?php echo $inst["nick_name_".$inst['id']]?></strong><br/>
				<span style="color: #ff0000;font-size: 16px;"><strong><?php echo $phone?></strong></span>
				<span><?php echo $inst["email_".$inst['id']]?></span>
				
				<?php foreach($inst["services"] as $type=>$null):
					$count++;
					$id=$inst[$type]['id'];
				?>
				<?php if(/*isset($inst['yahoo']) */$type=='yahoo'){
					$nickcount++;
				?>
				<span >
				<a class="seoquake-nofollow" href="ymsgr:sendim?<?php echo $inst[$type]["nick_".$id]?>" rel="nofollow"><img border="0" class="" src="http://opi.yahoo.com/online?u=<?php echo $inst[$type]["nick_".$id]?>&g=m&t=<?php echo $inst[$type]["yahoo_status_type_".$id]?>" alt="<?php echo $inst[$type]["nick_name_".$id]?>" border="0" style="vertical-align:middle;"/></a>
				</span>
				<?php }?>
				
				
				<?php if(/*isset($inst['skype'])*/$type=="skype"){
					$nickcount++;
				?>
				<span >
				<a class="seoquake-nofollow" href="skype:<?php echo $inst[$type]["nick_".$id]?>?call" title="Talk with me via Skype " rel="nofollow"><img border="0" class="" src="http://mystatus.skype.com/<?php echo $inst[$type]["skype_status_type_".$id].'/'.$inst[$type]["nick_".$id]?>" alt="<?php echo $inst[$type]["nick_name_".$id]?>" border="0" style="vertical-align:middle;"/></a>
				</span>
				<?php }?>
				<?php endforeach;?>
			</div>
			
	<?php	
			
}
echo "</div>";
echo $after_widget;
?>