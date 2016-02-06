<?php 
/**
 * Plugin Name: default
 */
?>
<?php 
#wp_enqueue_script('jquery-scroll-to-top',HW_SKIN::current()->get_skin_url('jss-script.js'));
wp_enqueue_script('jquery-scroll-to-top',plugins_url('jss-script.js', __FILE__));
wp_enqueue_style('jquery-scroll-to-top-css',plugins_url('jss-style.css', __FILE__));
?>
<style>
a#scroll-to-top {
	
	/* Background image, replace in images folder */
	background: url(<?php echo $image_url;?>) no-repeat center center;
	background-size:100% 100%;
	/* Match to background image size */
	width: 50px;
	height: 50px;
	
	/* Postion on the page */
	position: fixed;	
	right: 30px;
	bottom: 30px;
	
	/* Hide link text */
	text-indent: -9999px;
	font-size: 0;
	
	/* Other */
	cursor: pointer;	
	outline: 0;
	
}
</style>
<!-- the html button which will be added to wp_footer  -->
<a id="scroll-to-top" href="#" title="<?php _e('Scroll to Top','hoangweb'); ?>"><?php _e('Top'); ?></a>