<?php
/**
 * HW Template: simplyScroll v2
 */

?>
<ul class="<?php echo $marquee_wrapper?>" id="<?php echo $marquee_id?>">
    <?php if($data instanceof WP_Query){
        while($data->have_posts()){
            $data->the_post();
            $data->next_post();     //only for hw-ml-slider

            $thumb = wp_get_attachment_image_src($query->post->ID,'thumbnail'); //wp_get_attachment_image
            $img = wp_get_attachment_image_src($query->post->ID,'full');
            $excerpt = str_replace(array("\r","\n"),"",$query->post->post_excerpt); #right way
            $url=get_post_meta($query->post->ID,'hw-ml-slider_url',true);

            ?>
        <li><img src="<?php echo $img[0]?>" width="" height="" alt="<?php echo $excerpt?>"></li>
    <?php
        }
    }?>
</ul>
<script type="text/javascript">
    (function($) {
        $(function() {
            $("#<?php echo $marquee_id?>").simplyScroll(<?php echo $json_config?>);
        });
    })(jQuery);

</script>