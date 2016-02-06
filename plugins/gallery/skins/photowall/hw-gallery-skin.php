<?php
/**
 * HW Template: photowall
 */
$options = isset($skin_data['skin_options'])? $skin_data['skin_options'] : array();
$options['el'] = '#envira-gallery-' . sanitize_html_class( $args['wrapper_id'] );   // Gallery element

$photos = array();
foreach ($galleries as $id=>$item) {
    $photos[$id] = array(
        'id'=> $id, 'img'=> esc_url($item['img_src']), 'width' => $item['img_width'], 'height' => $item['img_height'],
        'th' => array('src'=> esc_url($item['thumb_src']), 'width'=> $item['thumb_width'], 'height' => $item['thumb_height'], 'zoom_src' => $item['thumb_src'],'zoom_factor' => 1.5)
    );
}

$hw_tpl->_display('gallery.twig', array(
    'galleries' => $galleries,
    'args'=> $args,
    'json_options' => HW_SKIN_Option::build_json_options($options),
    'photos' => $photos
));
/*
?>

<div id="envira-gallery-<?php echo sanitize_html_class( $args['wrapper_id'] ) ?>" class="envira-gallery-public envira-gallery-<?php echo  sanitize_html_class( $args['wrapper_class'] ) ?>-columns envira-clear isotope" data-envira-columns="<?php echo $args['columns_class']?>">
    <div class="body">
    </div>
<?php
*/
/*foreach($galleries as $id => $item) {
    echo '<div id="envira-gallery-item-' . sanitize_html_class( $id ) . '" class="' . $item['item_classes'] . '" style="margin-bottom: ' . $item['margin_bottom'] . '" >';
    echo '<a href="' . esc_url( $item['link'] ) . '" class="envira-gallery-' . sanitize_html_class( $data['id'] ) . ' envira-gallery-link" rel="enviragallery' . sanitize_html_class( $data['id'] ) . '" title="' . esc_attr( $item['title'] ) . '" data-thumbnail="' . esc_url( $item['thumb'] ) . '">';

    echo '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image-' . $item['index'] . '" src="' . $item['src'] . '" data-envira-src="' . $item['src'] . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . esc_attr( $item['title'] ) . '" />';
    echo '</a>';
    echo '</div>';
}*/
/*
?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        PhotoWall.init(
            <?php echo HW_SKIN_Option::build_json_options($options);?>
        );
        // Max image width form Picasa
        // 94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912,
        // 1024, 1152, 1280, 1440, 1600

        PhotoWall.load(<?php echo json_encode($photos)?>);

    });
</script>
<?php
*/