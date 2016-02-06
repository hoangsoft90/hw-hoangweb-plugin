<?php
/*
type: bootstrap
location: libraries/init/require.php
	Nạp file này vào phần đầu của ứng dụng để sử dụng thư viện chung.
	Note: file này được nạp sau khi đã cấu hình biến, hằng cho ứng dụng.
*/
//which is important for start all.
if(!defined('app_libs')) 
	exit("Ứng dụng của bạn chưa được cấu hình (không tồn tại hằng app_libs) ?");

/*base: /libs */
define('lib_dir',(substr(app_libs,-1)!='/')?app_libs.'/':app_libs);
//define('prefix_libs','hphp.');

include lib_dir.'hphp.constants.php';
include lib_dir.'hphp.php';
include lib_dir.'hphp.file.php';
include lib_dir.'hphp.plugin_file.php';

/*
	tạo hằng đường dẫn các thư viện khác, với tên trùng tên của file.
*/

$list_class_dir=package(lib_dir,null,false);

foreach($list_class_dir as $name)
{
	$file=file_name(file_name($name,true));
	$const=str_replace('hphp.','',$file);
	
	define($const,$name);
}
?>