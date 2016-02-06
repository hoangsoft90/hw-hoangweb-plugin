<?php
/**
 * Plugin Name: Slider Skitter
 * Theme: skitter
*/
?>
<?php
include_once('theme-setting.php');
?>
<div class="hwml-slider-container">
    <div class="box_skitter box_skitter_large" id="skitter-slider-<?php echo $slider_id?>" class="skitter" >
<ul>
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
    <li>
        <a href="<?php echo $url?>"><img src="<?php echo $img[0]?>" alt="<?php echo $excerpt?>" data-thumb="<?php echo $img[0]?>" data-transition="" alt="" title="<?php echo $excerpt?>"  /></a>
        <div class="label_text"><p><?php echo $query->post->post_excerpt;?></p></div>
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
$theme_opt = isset($theme_options['theme'])? $theme_options['theme']:'clean';
$numbers_align_opt = isset($theme_options['numbers_align'])? $theme_options['numbers_align']:'center';
$progressbar_opt = (int)isset($theme_options['progressbar'])? 1:0;
$dots_opt = (int)isset($theme_options['dots'])? 1:0;
$preview = (int)isset($theme_options['preview'])? 1:0;
*/
$options = APF_hw_skin_Selector_hwskin::build_json_options($user_theme_options);

?>
    jQuery(document).ready(function($) {
        $('#skitter-slider-<?php echo $slider_id?>').skitter(
            <?php echo $options?>
            <?php /*
            {
            theme: '<?php echo $theme_opt?>',
            numbers_align: '<?php echo $numbers_align_opt?>',
            progressbar: <?php echo $progressbar_opt?>,
            dots: <?php echo $dots_opt?>,
            preview: <?php echo $preview?>,
            with_animations: ['paralell', 'glassCube', 'swapBars']
            } */ ?>
        );
    });

</script>