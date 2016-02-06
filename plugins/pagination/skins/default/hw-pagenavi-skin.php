<?php
/**
 * Plugin Name: default pagenavi
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//cover: http://www.dreamtemplate.com/dreamcodes/documentation/pagination.html
?>
<?php
include ('theme-setting.php');
//wrap a's and span's in li's
/*$out = str_replace("<span","<a",$html);
$out = str_replace("</span>","</a>",$out);

$out = str_replace('class="page','class="apage',$out);
$out = str_replace('class="larger page','class="apage',$out);
$out = str_replace("current","cpage",$out);
$out = str_replace("nextpostslink","apage",$out);
$out = str_replace("last","apage",$out);
*/
$out = '';
//wrap a's and span's in li's
$out = str_replace("<div class='wp-pagenavi'>",'<ul class="'.$theme['pagination_class'].'">', $html);
$out = str_replace('</div>', '</ul>', $out);
$out = str_replace("<a","<li><a",$out);
$out = str_replace("</a>","</a></li>",$out);
$out = str_replace("<span","<li><a",$out);
$out = str_replace("</span>","</a></li>",$out);
//$out = '<ul class="tsc_pagination tsc_paginationA tsc_paginationA01">'.$out.'</ul>';
return $out;