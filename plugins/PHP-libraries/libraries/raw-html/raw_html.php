<?php

/*
Created by Janis Elsts (email : whiteshadow@w-shadow.com) 
Licensed under the LGPL.
*/

define('RAWHTML_PLUGIN_FILE', __FILE__);

require 'include/tag-handler.php';
require 'include/formatting-override.php';

if ( is_admin() && file_exists(dirname(__FILE__).'/editor-plugin/init.php') ){
	require dirname(__FILE__) . '/editor-plugin/init.php';
}