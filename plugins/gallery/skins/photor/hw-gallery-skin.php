<?php
/**
 * HW Template: photor
 */
$options = isset($skin_data['skin_options'])? $skin_data['skin_options'] : array();
$gallery_id = 'gallery-' . sanitize_html_class( $args['wrapper_id'] );   // Gallery element
$hw_tpl->_display('gallery.twig', array(
    'galleries' => $galleries,
    'args'=> $args,
    'gallery_id' => $gallery_id
));
/*
?>
<div class="photor <?php echo  sanitize_html_class( $args['wrapper_class'] ) ?>" id="<?php echo $gallery_id ?>">

    <div class="photor__viewport">

        <div class="photor__viewportLayer">

            <!-- Add photos -->
            <?php foreach ($galleries as $id=>$item) {?>
            <img src="<?php echo $item['img_src']?>" data-thumb="<?php echo $item['thumb_src']?>">
            <?php }?>

        </div>

        <div class="photor__viewportControl">
            <div class="photor__viewportControlPrev"></div>
            <div class="photor__viewportControlNext"></div>
        </div>

    </div>

    <div class="photor__thumbs">
        <div class="photor__thumbsWrap"></div>
    </div>

</div>
*/
?>
<script>
    jQuery(document).ready( function($) {
        jQuery( '#<?php echo $gallery_id?>' ).photor(<?php echo HW_SKIN_Option::build_json_options($options)?>);

    } );
</script>