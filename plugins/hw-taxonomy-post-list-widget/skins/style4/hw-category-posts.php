<?php
/**
Plugin Name: sstyle 4
**/
?>
<style>

</style>
<?php 
if(empty($instance['thumb_w'])) $instance['thumb_w']='60';
if(empty($instance['thumb_h'])) $instance['thumb_h']='60';

include('theme-setting.php');

//show view all link
#echo $view_all_link;
//other way to track count items
HW_POST::reset_item_counter();

echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $this->get_holder_id()?>" class="hwtpl-wrapper">
	<div class="<?php echo $hwtpl_scrollbar_wrapper_class?>">
	<!-- <ul class="smoothDivScroll"> class="smoothDivScroll" ->for scrolling content option -->
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array('dongC');
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
        //other way
            //HW_POST::get_item_class();

                if($awc_enable_grid_posts) {
                    $classes = HW_POST::get_item_class($instance['awc_grid_posts_cols'],$classes);
                }

                //display post custom fields
                if(in_array('duration',$metaFields)){
                    $duration = get_post_meta(get_the_ID(),'duration',true);
                }
                $classes = implode(' ',$classes);
				?>
                <div <?php post_class($classes)?>>
                    <div class="img"><a href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>">
                            <?php
                            if (
                                function_exists('the_post_thumbnail') &&
                                current_theme_supports("post-thumbnails") &&
                                in_array("thumb",$arrExlpodeFields) &&
                                has_post_thumbnail()
                            ) :
                                ?>
                                <?php the_post_thumbnail( 'thumbnail', array($instance["thumb_w"],$instance["thumb_h"]
                            ,'style'=>""
                            ,'class'=>'img')); ?>
                            <?php
                            else:
                                ?>
                                <img src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" width="171" height="118" border="0"/>
                            <?php
                            endif;
                            ?>

                            </a>
                    </div>

                    <div class="ndtexttin">
                        <h3><a href="<?php the_permalink();?>" class="tintuc_khac">
                                <?php if(function_exists('hwtpl_limit_str')) echo hwtpl_limit_str(get_the_title());
                                else the_title();
                                ?>
                            </a></h3>
                        <p style="text-align: justify;"><strong><?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?></strong></p>
                    </div>
                    <a class="chitiet" href="<?php the_permalink();?>">Chi tiết</a>
                    <?php edit_post_link( __( 'Edit', 'hoangweb' ), '<span class="edit-link">', '</span>' ); ?>
                </div>

				<?php	
			}
			?>
<!-- </ul> -->
	</div>
			<div class="clearfix"></div>
	
<!-- show pagination -->
<?php 
$this->load_pagination()?>
</div>
<?php
echo $after_widget;