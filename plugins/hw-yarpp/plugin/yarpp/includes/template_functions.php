<?php

// two YARPP-specific Template Tags, to be used in the YARPP-template Loop.

function hw_yarpp_the_score() {
	echo hw_yarpp_get_the_score();
}

function hw_yarpp_get_the_score() {
	global $post;

	$score = $post->score;
	return apply_filters('hw_yarpp_get_the_score', $score);
}
