<?php
/**
Plugin Name: Default Slider
*/
?>
<?php
include('theme-setting.php');
//get options
$slider_theme = !empty($user_theme_options['theme'])? $user_theme_options['theme'] : 'default';
?>
<div class="slider-wrapper theme-<?php echo $slider_theme?>">
    <div class="ribbon"></div>
    <div id="nivo-slider-<?php echo $slider_id?>" class="nivoSlider" >

        <?php
        if($query):
            while ( $query->have_posts() ) {
                $query->next_post();    //only for metaslider
                //$query->the_post();     //for posttype source data

                $thumb = wp_get_attachment_image_src($query->post->ID,'thumbnail'); //wp_get_attachment_image
                $img = wp_get_attachment_image_src($query->post->ID,'full');
                //<img src=...
                //echo wp_get_attachment_image( $attachment_id, 'thumbnail' );

                $excerpt = str_replace(array("\r","\n"),"",$query->post->post_excerpt); #right way
                //get url
                $url=get_post_meta($query->post->ID,'hw-ml-slider_url',true);

                $skin->render_skin_template( array(
                    'url' => $url,
                    'img_src' => $img[0],
                    'excerpt' => $excerpt,
                    'thumb_src' => $thumb[0],
                    'show_title' => $show_title,
                    'title' => get_the_title()
                ), true, false,'loop.tpl');
                /*
                ?>
                <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="<?php echo $excerpt?>" data-thumb="<?php echo $thumb[0]?>" data-transition="" alt="" title="<?php echo $excerpt?>"  />
                    <?php if($show_title) the_title();?>
                </a>

            <?php
                */
            }
        endif;
        ?>
    </div>
</div>
<?php
$options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options,'template_file');
?>
<script type="text/javascript">
    jQuery(window).load(function () {
        jQuery('#nivo-slider-<?php echo $slider_id?>').nivoSlider(
            <?php echo $options?>
        );
    });
</script>