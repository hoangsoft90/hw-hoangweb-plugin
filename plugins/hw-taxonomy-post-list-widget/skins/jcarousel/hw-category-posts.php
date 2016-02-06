<?php
/**
Plugin Name: default jcarouselite
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
if(isset($this)) $holder_id = $this->get_holder_id();else $holder_id = '';

echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $holder_id?>" class="jcarousel-skin1">
        <a href="javascript:void(0)" class="prev navigate-btn"></a>
        <a href="javascript:void(0)" class="next navigate-btn"></a>
    <div class="jcarousellite <?php echo $hwtpl_scrollbar_wrapper_class?>">
	<ul class="">
		<?php
        if(!empty($cat_posts))
        while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array();
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';

                //display post custom fields
                if(in_array('duration',$metaFields)){
                    $duration = get_post_meta(get_the_ID(),'duration',true);
                }
				?>
				<li <?php post_class()?>>
					<a href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>">
						<?php
						if (
								function_exists('the_post_thumbnail') &&
								current_theme_supports("post-thumbnails") &&
								in_array("thumb",$arrExlpodeFields) &&
								has_post_thumbnail()
							) :
						?>
							<?php the_post_thumbnail( 'thumbnail', array($instance["thumb_w"],$instance["thumb_h"] 
										,'style'=>"width:100px;height:70px"
										,'class'=>'img')); ?>
							<?php
							else:
							?>
							<img src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" width="100px" style="width:150px; height:118px;"/>
							<?php
							endif;
							?>
                        </a>
					<h4><a href="<?php the_permalink()?>">
                            <?php if(method_exists('HW_Taxonomy_Post_List_widget','limit_str')) {
                                echo HW_Taxonomy_Post_List_widget::limit_str(get_the_title());
                            }
                            else the_title();
                            ?>
                        </a></h4>
					<?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?>
					<?php //edit_post_link( __( 'Edit', 'hoangweb' ), '<span class="edit-link">', '</span>' ); ?>

				</li>
						 		
				<?php	
			}
			?>
		</ul>
	</div>
			<div class="clearfix"></div>
	
<!-- show pagination -->
<?php 
#if(isset($this)) $this->load_pagination()?>
</div>
<script>
    jQuery(function($){
        $('#<?php echo $holder_id?>').hover(function(){
            $(this).find('.navigate-btn').show();
            //$(this).find('.s9imageItempanel').css('opacity','1');
        }, function(){
            $(this).find('.navigate-btn').hide();
            //$(this).find('.s9imageItempanel').css('opacity','0');
        });
    });
</script>
<?php
echo $after_widget;