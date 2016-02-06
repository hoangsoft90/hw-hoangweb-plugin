<?php
/**
Plugin Name: style 2
**/
?>
<?php 
if(empty($instance['thumb_w'])) $instance['thumb_w']='60';
if(empty($instance['thumb_h'])) $instance['thumb_h']='60';

include('theme-setting.php');
//wp_enqueue_style('style2',$this->skin->get_skin_url('style.css')/*WP_CONTENT_URL.'/wcp_hw_skins/style2/style.css'*/);     //un-safe way

echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $hwtpl_wrapper_id?>" class="<?php echo $hwtpl_scrollbar_wrapper_class?>">
	<ul class="home_cat_new smoothDivScroll">
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array();
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
	 //first post
	 if($cat_posts->current_post == 0){
				?>
		<li <?php post_class('home_cat_list')?>>
<a href="<?php the_permalink()?>" title="<?php the_title_attribute(); ?>">
<div class="img">
<?php
	if (
			function_exists('the_post_thumbnail') &&
			current_theme_supports("post-thumbnails") &&
			/*in_array("thumb",$arrExlpodeFields) &&*/
			has_post_thumbnail()
		) :
	?>
	<?php the_post_thumbnail( 'thumbnail', array($instance["thumb_w"],$instance["thumb_h"] 
				,'style'=>""
				,'class'=>'img')); ?>
	<?php
	else:
	?>
	<img src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" width="50px"/>
	<?php
	endif;
	?>
</div>
<span class="title346546">
<?php the_title()?></span>
</a><?php edit_post_link( __( 'Edit', 'hoangwebtheme' ), '<span class="edit-link">', '</span>' ); ?> 
</li>		
	 <?php }else{?>		
		<li <?php post_class('home_cat_list')?>>
<a class="list" href="<?php the_permalink()?>" title="<?php the_title_attribute(); ?>"><?php the_title()?></a>
<?php edit_post_link( __( 'Edit', 'hoangwebtheme' ), '<span class="edit-link">', '</span>' ); ?> 
</li>		
				<?php	
			}
		}
			?>
		</ul>	
	
			<div class="clearfix"></div>

<?php
$this->load_pagination();
?>
</div>
<?php
echo $after_widget;