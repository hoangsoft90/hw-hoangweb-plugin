<?php
/**
Plugin Name: travel 1
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

echo $before_widget;
// Widget title
echo $before_title.$open_title_link;
echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $this->get_holder_id()?>" class="tour-list">
	<div class="<?php echo $hwtpl_scrollbar_wrapper_class?>">
	<div class="smoothDivScroll"><!-- class="smoothDivScroll" ->for scrolling content option -->
		<?php while ( $cat_posts->have_posts() )
			{ 
				$cat_posts->the_post(); 
				$classes=array();
if( 0 == $cat_posts->current_post || 0 == $cat_posts->current_post % 4 )
          $classes[] = 'first';
	 if(($cat_posts->current_post+1) % 4 == 0) $classes[]='lastcolumn';
                $classes[] = 'item-box';
				?>
            <div <?php post_class( join(' ',$classes))?>>
                <div class="tour-item" data-tourid="<?php the_ID()?>">
                    <div class="picture">
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
                    </div>

                    <div class="details">
                        <h2 class="tour-title">
                            <a href="<?php the_permalink()?>">
                                <?php if(defined('self') && class_exists(self,'limit_str')) echo self::limit_str(get_the_title());
                                else the_title();
                                ?>
                            </a>
                        </h2>
                        <?php
                        if(in_array('duration',$metaFields)){
                            $duration = get_post_meta(get_the_ID(),'duration',true);
                        ?>
                        <div class="duration">
                            <span class="label">Duration</span>: <span class="value" itemprop="duration"><?php echo $duration?></span>
                        </div>
                        <?php  }?>
                        <?php
                        if(in_array('destination',$metaFields)){
                        $destination = get_post_meta(get_the_ID(),'destination',true);
                        ?>
                        <div class="destinations">
                            <span class="label">Destinations</span>: <span class="value" itemprop="destiantions"><?php echo $destination?></span>
                        </div>
                        <?php
                        }
                        $description = '';
                        if(in_array('description',$metaFields))
                            $description = get_post_meta(get_the_ID(),'description',true);
                        elseif(in_array('excerpt', $arrExlpodeFields)) $description = get_the_excerpt() ;
                        $description = get_the_excerpt() ;
                        ?>
                        <div class="description">
                            <?php echo $description?>
                        </div>
                        <?php

                        ?>
                        <?php
                        if(in_array('price',$metaFields)){
                        $price = get_post_meta(get_the_ID(),'price',true);
                        ?>

                        <div class="add-info">
                            <div class="prices">
                                <span class="pricerange">From:</span>
                                <span class="price actual-price"><?php echo $price?> VND</span> <span class="pricerange">similar to </span> <span class="price actual-price">1460 USD</span>
                            </div>
                            <div class="buttons">
                                <input type="button" value="Details" class="detail-button1" onclick="location.href='<?php the_permalink()?>'" />

                            </div>

                        </div>
                        <?php }?>
                    </div>

					<?php edit_post_link( __( 'Edit', 'hoangweb' ), '<span class="edit-link">', '</span>' ); ?> 
					</div>
				</div>
						 		
				<?php	
			}
			?>
		</div>
	</div>
			<div class="clearfix"></div>
	
<!-- show pagination -->
<?php 
$this->load_pagination()?>
</div>
<?php
echo $after_widget;