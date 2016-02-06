<?php
/*
Plugin Name: default popular posts
*/
?>
<?php
//widget_title, custom before_title
add_filter('hwwpp_widget_before_title','hwwpp_widget_title_start');
function hwwpp_widget_title_start($start){
	return $start;
}
//widget_title, custom before_title
add_filter('hwwpp_widget_after_title','hwwpp_widget_title_end');
function hwwpp_widget_title_end($end){
	return $end;
}

add_filter('hwwpp_no_posts','_hwwpp_no_posts');
function _hwwpp_no_posts($str){
	return $str;
}
//start wrapper
add_filter('hwwpp_start','_hwwpp_start');
function _hwwpp_start(){
	return '<div class="popular-posts"><ul class="wpp-list">';
}
//end wrapper
add_filter('hwwpp_end','_hwwpp_end');
function _hwwpp_end(){
	return '</ul></div>';
}
//loop item
add_filter('hwwpp_loop_custom_html','_hwwpp_loop_custom_html');
function _hwwpp_loop_custom_html(){
	/*title,excerpt=summary,stats,image=thumb,thumb_img,url,text_title,author,category,views,comments,date,id,title_sub,rating (result),rating_live*/
	return '
	<li>
		<!-- thumbnail {thumb} -->
		<a href="{url}" title="{text_title}" target="{target}">{thumb_img}</a>
		<!-- title -->
		<a href="{url}" title="{text_title}" class="wpp-post-title" target="{target}">{title_sub}</a>
		{summary}
		<!-- stats -->
		<span class="post-stats">{stats}</span>
		{rating_live}
	</li>
	';
}
//loop item support php coding
add_filter('hwwpp_loop_post','_hwwpp_loop_post');
function _hwwpp_loop_post($data){
	extract($data);
	$thumb = ( !empty($thumb) ) 
	  ? '<a ' . ( ( $obj->current_post_id == $p->id ) ? '' : 'href="' . $permalink . '"' ) . ' title="' . esc_attr($title) . '" target="' . $obj->user_settings['tools']['link']['target'] . '">' . $thumb . '</a> '
	  : '';
	
	$_stats = ( !empty($_stats) ) 
	  ? ' <span class="post-stats">' . $_stats . '</span> '
	  : '';
	
	$content =
		'<li>'
		. $thumb
		. '<a ' . ( ( $obj->current_post_id == $p->id ) ? '' : 'href="' . $permalink . '"' ) . ' title="' . esc_attr($title) . '" class="wpp-post-title" target="' . $obj->user_settings['tools']['link']['target'] . '">' . $title_sub . '</a> '
		. $excerpt . $_stats
		. $rating
		. "</li>\n";
	return $content;	
}

//addition feature to display $mostpopular ->never use this
//add_filter('wpp_custom_html', 'wpp_custom_html_',10,2);
function wpp_custom_html_($mostpopular, $instance){
	return '';
}
//custom content before echo
add_filter('wpp_post','hw_wpp_post',10,3);
function hw_wpp_post($content, $p, $instance){
	return $content;
}
?>