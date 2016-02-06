<?php
/**
 * HW Template: everwebcodebox
 */
?>
<div class="<?php echo $marquee_wrapper?>" id="<?php echo $marquee_id?>">
    <?php echo $data?>
</div>
<script type="text/javascript">
    jQuery(function($){
        //see more at js/libraries/marquee/everwebcodebox/README.md
        var $marquee = $(".<?php echo $marquee_wrapper?>").marquee(<?php  echo $json_config?>);
        $(".<?php echo $marquee_wrapper?>").hover(function(){
            $marquee.marquee("pause");
        }, function(){
            $marquee.marquee("resume")
        });
    });
</script>