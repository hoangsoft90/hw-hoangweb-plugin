<?php
/**
 * Plugin Name: skin1 pagenavi
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
include('theme-setting.php');

//wrap a's and span's in li's
$out = str_replace("<div class='wp-pagenavi'>",'<ul class="'.$theme['pagination_class'].'">', $html);
$out = str_replace('</div>', '</ul>', $out);
$out = str_replace("<a","<li><a",$out);
$out = str_replace("</a>","</a></li>",$out);
$out = str_replace("<span","<li><a",$out);
$out = str_replace("</span>","</a></li>",$out);
return $out;
?>
