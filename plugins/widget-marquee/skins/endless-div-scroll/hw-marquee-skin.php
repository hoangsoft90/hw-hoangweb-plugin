<?php
/**
 * HW Template: jQuery Endless Div Scroll
 */
?>
<div class="<?php echo $marquee_wrapper?>" id="<?php echo $marquee_id?>">
    <?php echo $data?>
</div>
<script type="text/javascript">
    jQuery(window).load(function () {
        jQuery("#<?php echo $marquee_id?>").endlessScroll(<?php  echo $json_config?>);
    });

</script>