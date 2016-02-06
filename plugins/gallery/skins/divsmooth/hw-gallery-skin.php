<?php
/**
 * HW Template: divsmooth
 */
$options = isset($skin_data['skin_options'])? $skin_data['skin_options'] : array();
$gallery_id = '#makeMeScrollable-' . sanitize_html_class( $args['wrapper_id'] );   // Gallery element
?>
<div id="makeMeScrollable-<?php echo sanitize_html_class( $args['wrapper_id'] ) ?>" class="hw-smoothdivscroll hw-jgallery-container <?php echo  sanitize_html_class( $args['wrapper_class'] ) ?>">
    <!-- <div class="album" data-jgallery-album-title="Album 1"> -->
        <?php foreach ($galleries as $id=>$item) {?>
        <a href="<?php echo esc_url($item['img_src'])?>"><img src="<?php echo esc_url($item['thumb_src'])?>" alt="<?php echo $item['title']?>" /></a>
        <?php }?>
    <!-- </div> -->

</div>
<script>
    jQuery(document).ready( function($) {
        jQuery( '<?php echo $gallery_id?>' ).smoothDivScroll(<?php echo HW_SKIN_Option::build_json_options($options)?>);
        <?php if(!empty($options['pause_on_hover']) && $options['pause_on_hover']){?>
        // Mouse over
        jQuery("<?php echo $gallery_id?>").bind("mouseover", function(){
            $(this).smoothDivScroll("stopAutoScrolling");
        });

        // Mouse out
        jQuery("<?php echo $gallery_id?>").bind("mouseout", function(){
            $(this).smoothDivScroll("startAutoScrolling");
        });
        <?php }?>
    } );
</script>