<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * shortcode display socials share buttons
 * @param $args
 * @param $content
 */
function hwss_shortcode_socials_share($args, $content = ''){
	$prop = array(
        'buttons' => 'facebook_share,googleplus' ,
        'style' => 'horizontal',
        'size' => 'medium'
    );
    $d = shortcode_atts($prop,$args);
    extract($d);

	hwss_socials_share($buttons, $style, $size);
}
add_shortcode('hwss_sharebuttons','hwss_shortcode_socials_share');
/**
 * addthis compact button
 * @param $args
 * @param $content
 */
function hwss_shortcode_addthis_compact_button($args , $content = ''){
	$d = shortcode_atts(array( 'img' => 'sharebtn.png' ), $args );
    extract($d);

	hwss_compact_socials_share_addthis($img );
}
add_shortcode('hwss_compact_button', 'hwss_shortcode_addthis_compact_button');
?>