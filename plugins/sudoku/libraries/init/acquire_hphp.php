<?php
function initialize_hphp()
{
	if(!defined('app_libs')){
		exit('Không t?n t?i h?ng "app_libs".');
	}
	include rtrim(app_libs,'/').'/init/require.php';
}
?>