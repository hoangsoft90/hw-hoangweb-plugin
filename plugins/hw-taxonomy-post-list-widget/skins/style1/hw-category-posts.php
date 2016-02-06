<?php
/**
Plugin Name: style 1
**/
?>
<?php 
if(empty($instance['thumb_w'])) $instance['thumb_w']='60';
if(empty($instance['thumb_h'])) $instance['thumb_h']='60';

include('theme-setting.php');

echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $this->get_holder_id()?>" class="">
	<ul class="smoothDivScroll <?php echo $hwtpl_scrollbar_wrapper_class?>">
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array();
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
				?>
					<li <?php post_class('list_new')?>>
						<h6 class="new_title">
						<a href="<?php the_permalink()?>" title="<?php the_title_attribute(); ?>"><?php the_title()?></a>
						</h6><span class="new_date"><?php the_date('d/n/Y')?></span>
						<a class="f_link" href="<?php the_permalink()?>" title="<?php the_title_attribute()?>">
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
						</a>
						<a class="new_img" href="<?php the_permalink()?>" title="<?php the_title_attribute(); ?>">
						<img alt="<?php the_title_attribute(); ?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="/>
						</a>
						<div class="type_info">
							<p class="news_tea"><?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?></p>
						</div>
						<?php edit_post_link( __( 'Edit', 'hoangwebtheme' ), '<span class="edit-link">', '</span>' ); ?> 
					</li>	 		
				<?php	
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