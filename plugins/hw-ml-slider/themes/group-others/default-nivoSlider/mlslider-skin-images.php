<?php
/**
Plugin Name: Slider riêng chu?n
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
    if(!empty($images)):
        foreach ( $images as $attachment ) {

            $thumb = wp_get_attachment_image_src($attachment->ID,'thumbnail'); //wp_get_attachment_image
            $img = wp_get_attachment_image_src($attachment->ID,'full');

            //<img src=...
            //echo wp_get_attachment_image( $attachment_id, 'thumbnail' );

            ?>
            <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="" data-thumb="<?php echo $thumb[0]?>" data-transition="" alt="" title=""  />

            </a>

        <?php
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
            <?php /*{
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
        } */ ?>
        );
    });
</script>