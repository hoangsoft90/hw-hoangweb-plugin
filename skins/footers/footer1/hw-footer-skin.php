<?php
/**
 * PLugin Name: footer 1
 */
include('functions.php');
include('theme-setting.php');
?>
<div id="fullbottom">
    <div id="page">
        <div id="contentbottom" class="top15">
            <?php if(isset($col)) hw_dynamic_sidebar($col);?>
            <div id="copy">
                <div id="tag">
                    <?php hw_print_tags()?>
                </div>
                <div id="copyright">
                    <div class="cotcopy" style="text-align:right; border-right:1px solid #fff;">
                        <img src="<?php echo get_theme_mod('image_logo') ?>" width="150" height="48"><br>
                        <span style="color:#fff">Copyright © 2014 <?php bloginfo('name')?>.</span>
                    </div>
                    <div class="cotcopy">
                        <?php echo hw_option('footer')?>
                    </div>
                </div>
            </div>

        </div>
        <div id="contentbottom">
            <?php //dynamic_sidebar('sidebar-footer')?>
        </div>
    </div>
</div>

<?php wp_footer(); ?>