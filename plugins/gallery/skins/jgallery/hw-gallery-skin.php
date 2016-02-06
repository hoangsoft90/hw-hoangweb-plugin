<?php
/**
 * HW Template: jgallery
 */
$options = isset($skin_data['skin_options'])? $skin_data['skin_options'] : array();
$gallery_id = '#gallery-' . sanitize_html_class( $args['wrapper_id'] );   // Gallery element
?>
<div id="gallery-<?php echo sanitize_html_class( $args['wrapper_id'] ) ?>" class="hw-jgallery-container <?php echo  sanitize_html_class( $args['wrapper_class'] ) ?>">
    <div class="album" data-jgallery-album-title="Album 1">
        <?php foreach ($galleries as $id=>$item) {?>
        <a href="<?php echo esc_url($item['img_src'])?>"><img src="<?php echo esc_url($item['thumb_src'])?>" alt="<?php echo $item['title']?>" /></a>
        <?php }?>
    </div>

</div>
<script>
    jQuery(document).ready( function($) {
        for(var i=0;i<4;i++)
            jQuery( '<?php echo $gallery_id?>' ).jGallery(<?php echo HW_SKIN_Option::build_json_options($options)?>);

    } );
</script>