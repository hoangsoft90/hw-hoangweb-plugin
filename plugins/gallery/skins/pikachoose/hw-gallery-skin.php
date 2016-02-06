<?php
/**
 * HW Template: pikachoose-96
 */
$options = isset($skin_data['skin_options'])? $skin_data['skin_options'] : array();
$gallery_id = 'gallery-' . sanitize_html_class( $args['wrapper_id'] );   // Gallery element

if(!empty($options['css'])) {
    HW_Libraries::get('pikachoose-96')->enqueue_styles($options['css']);    //addition css file
}
?>
<div class="pikachoose <?php echo  sanitize_html_class( $args['wrapper_class'] ) ?>" id="<?php echo $gallery_id ?>">

    <ul class="pikame" >
        <?php foreach ($galleries as $id=>$item) {?>
        <li><a href="<?php echo $item['url']?>"><img src="<?php echo $item['img_src']?>"/></a><span><?php echo $item['title']?></span></li>
        <?php }?>

    </ul>
</div>

<script>
    jQuery(document).ready( function($) {
        jQuery( '#<?php echo $gallery_id?> .pikame' ).PikaChoose(<?php echo HW_SKIN_Option::build_json_options($options)?>);

    } );
</script>