<?php
/**
Plugin Name: style 3
**/
?>

<?php 
if(empty($instance['thumb_w'])) $instance['thumb_w']='60';
if(empty($instance['thumb_h'])) $instance['thumb_h']='60';

include('theme-setting.php');
//wp_enqueue_style('hwtpl-skin-style3',$this->skin->get_skin_url('style.css')); //un-safe
echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $this->get_holder_id()?>" class="<?php echo $hwtpl_scrollbar_wrapper_class?>">
	<ul class="real-listnews smoothDivScroll">
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array();
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
				?>
				<li <?php post_class('')?>>
        <?php if(in_array('title',$arrExlpodeFields)):?>
        <a href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>" class="new"><?php the_title()?></a>
        <?php endif;?>
        <!-- <span class="new">&nbsp;</span> -->
        <div style="clear:both;"></div>
        <?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?>
        <!-- <p class="lct"><strong>Vị trí: </strong>Phú Nhuận - <strong>Giá bán: </strong><b class="hcl ">115,000,000</b> vnđ</p> -->
        <?php edit_post_link( __( 'Edit', 'hoangwebtheme' ), '<span class="edit-link">', '</span>' ); ?>
        </li>
        
				  <?php /*
					<a href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>">
						<?php
						if (
								function_exists('the_post_thumbnail') &&
								current_theme_supports("post-thumbnails") &&
								
								has_post_thumbnail()
							) :
						?>
							<?php the_post_thumbnail( 'thumbnail', array($instance["thumb_w"],$instance["thumb_h"] 
										,'style'=>"width:130px;height:100px"
										,'class'=>'img')); ?>
							<?php
							else:
							?>
							<img src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" width="130px" height="100px"/>
							<?php
							endif;
							?>
                        </a>
					 
					<h2 style="font-size: 12px;"><a title="<?php the_title_attribute(); ?>" href="<?php the_permalink();?>"><?php the_title()?></a>  </h2> 
					<div class="des-news"><?php the_excerpt()?></div>
				*/
				?>
						  		
				<?php	
			}
			?>
		</ul>
			<div class="clearfix"></div>
<?php
if(method_exists($this, 'display_pagination')) {
    $this->load_pagination();
}
?>
</div>
<?php
echo $after_widget;