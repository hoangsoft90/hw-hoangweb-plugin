<?php
/**
Plugin Name: Travel box
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
if(isset($instance["widget_title"])) echo $instance["widget_title"];
echo $close_title_link.$after_title;
?>
<div id="<?php echo $this->get_holder_id()?>">
	<div class="travel-box <?php echo $hwtpl_scrollbar_wrapper_class?>">
	<div class="smoothDivScroll"><!-- class="smoothDivScroll" ->for scrolling content option -->
		<?php while ( $cat_posts->have_posts() )
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
                <div  <?php post_class('item-box')?>>
                    <div class="category-item">
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
                                ,'style'=>"width:100px;height:70px"
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
                        <div class="picture-shadow"></div>
                        <h2 class="title">
                            <a href="<?php the_permalink()?>" title="<?php the_title_attribute(); ?>">
                                <?php if(method_exists(self, 'limit_str')) echo self::limit_str(get_the_title());
                                else the_title();
                                ?>
                            </a>
                        </h2>
                        <div class="category-description">
                            <?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?>
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