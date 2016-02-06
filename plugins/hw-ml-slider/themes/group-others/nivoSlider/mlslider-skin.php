<?php
/**
 * Plugin Name: nivoSlider
 * Theme: skitter
*/
?>
<?php

?>
<div class="hwml-slider-container">
    <div class="slider-wrapper theme-default" id="nivo-slider-<?php echo $slider_id?>" class="skitter" >
<!-- <ul> -->
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
    $url=get_post_meta($query->post->ID,'hw-ml-slider_url','true');

    ?>
    <!-- <li>: don't put any wrapper tag just only <a -->
        <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="<?php echo $excerpt?>" data-thumb="<?php echo $img[0]?>" data-transition="" alt="<?php echo $excerpt?>" title="<?php echo $excerpt?>"  /></a>
        <!-- <div class="label_text"><p><?php echo $query->post->post_excerpt;?></p></div> -->
    <!-- </li> -->
<?php
}
?>
  <!--  </ul> -->
    </div>
</div>
<script type="text/javascript">
 <?php
$options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options);
?>
 jQuery(window).load(function () {    //because it render directly so please don't wrapper in jquery event that will make ui crash & any lib (here is nivoSlider) not work
     jQuery('#nivo-slider-<?php echo $slider_id?>').nivoSlider(
         <?php echo $options?>
 );
});


</script>