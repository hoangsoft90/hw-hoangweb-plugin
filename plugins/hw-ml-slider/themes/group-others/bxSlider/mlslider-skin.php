<?php
/**
 * Plugin Name: bxSlider
 * Theme: skitter
*/
?>
<?php

?>
<div class="hwml-slider-container">
    <div class="slider-wrapper" id="bxslider-slider-<?php echo $slider_id?>" class="" >
<ul class="bxslider">
    <?php
    if(isset($query))
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
    <li>
        <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="<?php echo $excerpt?>" data-transition="" alt="<?php echo $excerpt?>" title="<?php echo $excerpt?>"  /></a>
        <!-- <div class="label_text"><p><?php echo $query->post->post_excerpt;?></p></div> -->
    </li>
<?php
}
?>
  </ul>
    </div>
</div>
<script type="text/javascript">
 <?php
 /*
$mode = isset($theme_options['mode']) && $theme_options['mode']? $theme_options['mode']:'fade';
$captions = isset($theme_options['captions']) && $theme_options['captions']? (int)$theme_options['captions']:'false';
$speed = isset($theme_options['speed']) && $theme_options['speed']? $theme_options['speed']:500;
$slideMargin = isset($theme_options['slideMargin']) && $theme_options['slideMargin']? $theme_options['slideMargin']:0;
$startSlide = isset($theme_options['startSlide']) && $theme_options['startSlide']? $theme_options['startSlide']:0;
$randomStart = isset($theme_options['randomStart']) && $theme_options['randomStart']? $theme_options['randomStart']:'false';
$slideSelector = isset($theme_options['slideSelector']) && $theme_options['slideSelector']? $theme_options['slideSelector']:'';
$infiniteLoop = isset($theme_options['infiniteLoop'])? 'true' :'false';
$hideControlOnEnd = isset($theme_options['hideControlOnEnd']) ? 'true':'false';
$easing = isset($theme_options['easing']) && $theme_options['easing']? $theme_options['easing']:'linear';
$captions = isset($theme_options['captions']) ? 'true':'false';
$ticker = isset($theme_options['ticker']) ? 'true': 'false';
$tickerHover = isset($theme_options['tickerHover']) ? 'true': 'false';
$slideSelector = isset($theme_options['slideSelector']) && $theme_options['slideSelector']? $theme_options['slideSelector']:'';
$adaptiveHeight = isset($theme_options['adaptiveHeight']) ? 'true': 'false';
$adaptiveHeightSpeed = isset($theme_options['adaptiveHeightSpeed']) && $theme_options['adaptiveHeightSpeed']? $theme_options['adaptiveHeightSpeed']:'500';
$video = isset($theme_options['video']) ? 'true':'false';
$responsive = isset($theme_options['responsive']) ? 'true':'false';
$useCSS = isset($theme_options['useCSS']) ? 'true':'false';

$preloadImages = isset($theme_options['preloadImages']) && $theme_options['preloadImages']? $theme_options['preloadImages']:'visible';
$touchEnabled = isset($theme_options['touchEnabled'])? 'true':'false';
$swipeThreshold = isset($theme_options['swipeThreshold']) && $theme_options['swipeThreshold']? $theme_options['swipeThreshold']:'50';
$oneToOneTouch = isset($theme_options['oneToOneTouch'])? 'true':'false';

$preventDefaultSwipeX = isset($theme_options['preventDefaultSwipeX'])? 'true':'false';
$preventDefaultSwipeY = isset($theme_options['preventDefaultSwipeY'])? 'true':'false';

$controls = isset($theme_options['controls'])? 'true':'false';
$nextText = isset($theme_options['nextText']) && $theme_options['nextText']? $theme_options['nextText']: '>>';
$prevText = isset($theme_options['prevText']) && $theme_options['prevText']? $theme_options['prevText']: '<<';
$nextSelector = isset($theme_options['nextSelector']) && $theme_options['nextSelector']? $theme_options['nextSelector']: '';
$prevSelector = isset($theme_options['prevSelector']) && $theme_options['prevSelector']? $theme_options['prevSelector']: '';
$autoControls = isset($theme_options['autoControls']) ? 'true': 'false';
$auto = isset($theme_options['auto'])? 'true': 'false';
$pause = isset($theme_options['pause']) && $theme_options['pause']? $theme_options['pause']: '4000';
$autoStart = isset($theme_options['autoStart'])? 'true': 'false';
$autoDirection = isset($theme_options['autoDirection']) && $theme_options['autoDirection']? $theme_options['autoDirection']: 'next';
$autoHover = isset($theme_options['autoHover']) ? 'true': 'false';
*/
$options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options);
?>
 jQuery(window).load(function () {    //because it render directly so please don't wrapper in jquery event that will make ui crash & any lib (here is nivoSlider) not work
 jQuery('#bxslider-slider-<?php echo $slider_id?> .bxslider').bxSlider(
     <?php echo $options?>
     <?php /*{
     mode: 'fade',
     captions: <?php echo $captions?>,
     speed:<?php echo $speed?>,
     slideMargin : <?php echo $slideMargin?>,
     startSlide : <?php echo $startSlide?>,
     randomStart : <?php echo $randomStart?>,
     infiniteLoop: <?php echo $infiniteLoop?>,
     hideControlOnEnd : <?php echo $hideControlOnEnd?>,
     easing : '<?php echo $easing?>',
     ticker : <?php echo $ticker?>,
     tickerHover : <?php echo $tickerHover?>,
     <?php if($slideSelector){?>slideSelector : '<?php //echo $slideSelector?>',<?php }?>
     <?php if($adaptiveHeight){?>adaptiveHeight : '<?php //echo $adaptiveHeight?>',<?php }?>
     */ ?>

 );
 });


</script>