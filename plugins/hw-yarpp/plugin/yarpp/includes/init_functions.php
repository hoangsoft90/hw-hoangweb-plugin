<?php
/* Init Functions ---------------------------------------------------------------------------------------------------*/

function hw_yarpp_init() {
	global $hw_yarpp;
	$hw_yarpp = new HW_YARPP;
}

function hw_yarpp_plugin_activate($network_wide) {
    update_option('hw_yarpp_activated', true);
}

function hw_yarpp_set_option($options, $value = null) {
	global $hw_yarpp;
	$hw_yarpp->set_option($options, $value);
}

function hw_yarpp_get_option($option = null) {
	global $hw_yarpp;
	return $hw_yarpp->get_option($option);
}
