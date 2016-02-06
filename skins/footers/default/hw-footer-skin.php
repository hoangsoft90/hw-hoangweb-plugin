<?php
/**
 * PLugin Name: footer default
 */
//include('functions.php');
include_once('theme-setting.php');
?>
<div class="hw-footer footer">
    <div class="master-wrapper-content">
        <div class="footer-address">
            <?php ?>
            <?php if(isset($col)) hw_dynamic_sidebar($col);else echo hw_option('footer')?>
        </div>
        <div class="footer-menu-wrapper">
            <?php if(isset($col_1)) hw_dynamic_sidebar($col_1);?>
        </div>
        <div class="footer-disclaimer">

            <div id="Powerby">
                Thiết kế bởi <a href='http://www.hoangweb.com' title='Thiết kế web giá rẻ' target='_blank'><strong>Hoangweb</strong></a>
            </div>
        </div>
        <div class="follow-us">
            <ul>
                <li class="facebook">
                    <a href="<?php echo hw_option('fb_url')?>" target="_blank">
                        <img src="<?php echo HW_SKIN::current()->get_skin_url('images/facebook.png')?>" alt="Facebook">
                    </a>
                </li>
                <li class="twitter"><a href="<?php echo hw_option('twitter_url')?>" target="_blank"><img src="<?php echo HW_SKIN::current()->get_skin_url('images/twitter.png')?>" alt="Twitter"></a></li>
                <li class="rss"><a href="#"><img src="<?php echo HW_SKIN::current()->get_skin_url('images/rss.png')?>" alt="Rss"></a></li>
                <li class="youtube"><a href="<?php echo hw_option('youtube_url')?>" target="_blank"><img src="<?php echo HW_SKIN::current()->get_skin_url('images/youtube.png')?>" alt="You Tube"></a></li>
                <li class="google-plus"><a href="<?php echo hw_option('gplus_url')?>" target="_blank"><img src="<?php echo HW_SKIN::current()->get_skin_url('images/gplus.png')?>" alt="Googel PLus"></a></li>
            </ul>
        </div>
        <div>
            <p><img src="<?php echo get_theme_mod('image_logo') ?>" alt="home" width="94" height="47">&nbsp; &nbsp;</p>
        </div>


        <?php wp_footer(); ?>

    </div>

</div>