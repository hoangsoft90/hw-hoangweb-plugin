<?php
/**
Plugin Name: FlexSlider
*/
?>
<?php

?>
<div class="slider-wrapper theme-default">
    <div id="flex-slider-<?php echo $slider_id?>" class="flexSlider" >

    <?php
    if(!empty($query))
while ( $query->have_posts() ) {
    $query->next_post();
    $thumb = wp_get_attachment_image($query->post->ID,'full');
    $img = wp_get_attachment_image_src($query->post->ID,'full');
    //<img src=...
    //echo wp_get_attachment_image( $attachment_id, 'thumbnail' );

    $excerpt = str_replace(array("\r","\n"),"",$query->post->post_excerpt); #right way
    //get url
    $url=get_post_meta($query->post->ID,'hw-ml-slider_url',true);

    ?>
    <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="<?php echo $excerpt?>" data-thumb="" data-transition="" alt="" title="<?php echo $excerpt?>"  /></a>

<?php
}
?>
    </div>
</div>
<?php
$options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options);
?>
<script type="text/javascript">
    jQuery(window).load(function () {
        jQuery('#nivo-slider-<?php echo $slider_id?>').nivoSlider({
            effect:'random', // Specify sets like: 'fold,fade,sliceDown'
            slices: 10, // For slice animations
            boxCols:  6 , // For box animations
            boxRows: 3, // For box animations
            animSpeed: 500, // Slide transition speed
            pauseTime: 3000, // How long each slide will show
            startSlide: 0, // Set starting Slide (0 index)
            directionNav: true, // Next & Prev navigation
            controlNav: true, // 1,2,3... navigation
            controlNavThumbs: false, // Use thumbnails for Control Nav
            pauseOnHover: false, // Stop animation while hovering
            manualAdvance: false, // Force manual transitions
            prevText: '', // Prev directionNav text
            nextText: '', // Next directionNav text
            randomStart: false // Start on a random slide
        });
    });
</script>