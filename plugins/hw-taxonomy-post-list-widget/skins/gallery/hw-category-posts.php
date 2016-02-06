<?php
/**
Plugin Name: gallery
**/
?>
<?php 
if(empty($instance['thumb_w'])) $instance['thumb_w']='170';
if(empty($instance['thumb_h'])) $instance['thumb_h']='170';

include('theme-setting.php');
//show view all link
#echo $view_all_link;
//other way to track count items
HW_POST::reset_item_counter();

if(!is_ajax()) {
echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
if(isset($instance["widget_title"])) echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $hwtpl_wrapper_id?>" class="hwtpl-wrapper">
<?php
}
?>
    <!-- large tooltip -->
    <div class="popup_big_foto" style="display: none; ">
        <div class="foto"><img src="" style="display: block;"></div>
        <div class="name">
            <div class="icon pro">pro</div>
            <span class="txt">
                                            Electric sofa 6-18
                                        </span></div>
    </div>

	<div class="<?php echo $hwtpl_scrollbar_wrapper_class?>">
	<div class="smoothDivScroll model_list"><!-- class="smoothDivScroll" ->for scrolling content option -->
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array('item');
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
        //other way
            //HW_POST::get_item_class();

                if($awc_enable_grid_posts && class_exists('HW_POST') && isset($instance['awc_grid_posts_cols'])) {
                    $classes = HW_POST::get_item_class($instance['awc_grid_posts_cols'],$classes);
                }

                //display post custom fields
                if(in_array('duration',$metaFields)){
                    $duration = get_post_meta(get_the_ID(),'duration',true);
                }
                $classes = implode(' ',$classes);
				?>
                <div <?php post_class($classes)?>>
                    <a class="link" href="<?php the_permalink();?>" rel="<?php the_title_attribute()?>" data-image="
                                    <?php echo wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()) );?>
                        ">
                        <?php
                        if (
                            function_exists('the_post_thumbnail') &&
                            current_theme_supports("post-thumbnails") &&
                            //in_array("thumb",$arrExlpodeFields) &&
                            has_post_thumbnail()
                        ) :
                            ?>
                            <?php the_post_thumbnail( array($instance["thumb_w"],$instance["thumb_h"]), array(
                            'style'=>"",
                            'class'=>'model-thumb')); ?>
                        <?php
                        else:
                            ?>
                            <img src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" alt="<?php the_title_attribute()?>" width="170px" height="170px"/>
                        <?php
                        endif;
                        ?>

                    </a>
                    <div class="pro">pro</div><div class="icons hover"><a href="<?php the_permalink()?>" class="icon_reiting">
                            60
                        </a><a href="<?php the_permalink()?>" class="icon_views">
                            2
                        </a><a href="<?php the_permalink()?>" class="icon_enter">
                            39
                        </a></div>
                </div>



                            <?php
                #if(function_exists('hwtpl_limit_str')) echo hwtpl_limit_str(get_the_title());
                          #  else the_title();
                            ?>

					<?php //if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?>
					<?php //edit_post_link( __( 'Edit', 'hoangweb' ), '<span class="edit-link">', '</span>' ); ?>

				<?php	
			}
			?>
        </div>
	</div>
			<div class="clearfix"></div>
	
<!-- show pagination -->
<?php 
$this->load_pagination($hwtpl_pagination_class);
?>
<?php if(!is_ajax()) {?>
</div>
<?php
echo $after_widget;
}
?>