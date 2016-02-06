<?php
/**
Plugin Name: Testimonial
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
<div id="<?php echo $this->get_holder_id()?>" class="hwtpl-wrapper testimonial-list-homepage ">
   <div class="bx-wrapper" style="width:100%; margin: 0px auto;">
	<div class="bx-viewport jcarousellite <?php echo $hwtpl_scrollbar_wrapper_class?>" style="width: 100%; height: 161px;"><!-- style="" -->
	<ul class="bxslider"><!-- class="smoothDivScroll" ->for scrolling content option -->
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
                <li <?php post_class('testimonial-items')?> >
                    <div class="item">
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
                                    <?php the_post_thumbnail( 'thumbnail', array(
                                    'style'=>""
                                    ,'class'=>'circularAvatar')); ?>
                                <?php
                                else:
                                    ?>
                                    <img class="circularAvatar" src="<?php echo HW_SKIN::current()->get_skin_url('images/placeholder.png')?>" />
                                <?php
                                endif;
                                ?>
                            </a>
                        </div>
                        <div class="testimonial-head">
                            <h2>
                                <a class="testimonial-title" href="<?php the_permalink()?>">
                                    <?php if(function_exists('hwtpl_limit_str')) echo hwtpl_limit_str(get_the_title());
                                    else the_title();
                                    ?>
                                </a>
                            </h2>
                        </div>
                        <div class="testimonial-details">
                            <?php if(in_array('excerpt', $arrExlpodeFields)) the_excerpt()?>
                        </div>

                        <div class="submited-date">
                            <?php the_date()?>
                        </div>
                        <div class="guest-name">
                            - <?php the_author()?> -
                        </div>
                        <?php edit_post_link( __( 'Edit', 'hoangweb' ), '<span class="edit-link">', '</span>' ); ?>
                    </div>
                </li>


				<?php	
			}
			?>
		</ul>
	</div>
       <!-- <div class="bx-controls-direction"><a class="bx-prev" >Prev</a><a class="bx-next" >Next</a></div> #dont need -->
   </div>
			<div class="clearfix"></div>
<script>
    //jQuery(document).ready(function ($) {     //because it render directly so please don't wrapper in jquery event that will make ui crash & any lib (here is nivoSlider) not work
        $('#<?php echo $this->get_holder_id()?> ul').bxSlider({
            auto: true,
            pager: false,
            controls:true
        });

    //});

</script>
<!-- show pagination -->
<?php 
$this->load_pagination()?>
</div>
<?php
echo $after_widget;