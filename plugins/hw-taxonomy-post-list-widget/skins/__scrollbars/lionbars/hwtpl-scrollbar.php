<?php
/**
 * Plugin name: lionbars
 */
//include theme setting
include('theme-setting.php');
//valid theme options
if(!empty($scrollbar_theme_options['width'])) $width = self::format_unit($scrollbar_theme_options['width']);
if(!empty($scrollbar_theme_options['height'])) $height = self::format_unit($scrollbar_theme_options['height']);
?>
<script type="text/javascript">
    jQuery(function($)
    {
        $("#<?php echo $hwtpl_wrapper_id?> .<?php echo $hwtpl_scrollbar_wrapper_class?>").lionbars();
    });
</script>
<style>
    #<?php echo $hwtpl_wrapper_id?> .<?php echo $hwtpl_scrollbar_wrapper_class?>{
        <?php if(isset($width)){?>width: <?php echo $width?> !important;<?php }?>
        <?php if(isset($height)){?>height: <?php echo $height?> !important;<?php }?>
    }
</style>