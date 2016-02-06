<?php
/*
type: constant
	@global constants,variables
*/
/*------------------------CSS-------------------------------
	REMOVE_CSS_ANCHOR: xóa 1 thuộc tính css.
*/
$const=array(
	'REMOVE_CSS_ANCHOR',
/*---------------------------number-----------------------------
	number: ký tự là số
*/
	'number',
/*-------------------ngăn chứa Array, MYSQL----------------------------
	KEYS: Nhãn ngăn chứa
	VALS: giá trị của nhãn
	INDEX: đánh chỉ số cho nhãn.
	AUTO: nếu trùng nhãn thì đánh chỉ số.
	
	SQL: chuỗi lệnh SQL.
	BIND: điều kiện.
	INHERT: lấy điều kiện thiết lập trước.
	STRUCT: cấu trúc bảng
	DATA: dữ liệu bảng.
	FOLLOWED: no use
	
	H_OBJECT: đối tượng
	COPY_OF_IT: thay thế bởi bản copy của nó.
	
	EOF: đã đọc đến dữ liệu cuối, phần tử cuối của ngăn chứa.
*/
	'KEYS_VALS',
	'VALS',	
	'KEYS',	
	'INDEX',
	'EXIST_KEY',
	'NOT_EXIST_KEY',
	'KEY_VAL_AUTO',
	'STRUCT_AND_DATA',
	'ONLY_STRUCT',
	'FOLLOWED',
	'H_OBJECT',
	'COPY_OF_IT',
	'EOF',

/*----------------------html element---------------------
	E_CREATE: tạo html element.
*/
	'E_CREATE',

/*--------------------------------------module----------------------------------------
	NO_EXIST_CONFIG_ITEM: không tồn tại mục cấu hình (thuộc ứng dụng ie: module).
	constructor: gọi phương thức khởi tạo (constructor) của lớp.
*/
	'constructor',
	'NO_EXIST_CONFIG_ITEM'
);
foreach($const as $v)eval("define('{$v}','{$v}');");
	
/*#-------------------------------------html-------------------------------------*/
/*bullet*/
define('BULLET_LOWER_I','i');
define('BULLET_UPPER_I','I');
define('BULLET_LOWER_A','a');
define('BULLET_UPPER_A','A');
define('BULLET_SQUARE','square');
define('BULLET_CIRCLE','circle');
$bullet=array(BULLET_LOWER_I,BULLET_UPPER_I,BULLET_LOWER_A,BULLET_UPPER_A,BULLET_SQUARE,BULLET_CIRCLE);

/*#-------------------------------------document-------------------------------------*/
global $DOMActived;

/*#-------------------------------------file-----------------------------------------
	write:attampt to create if not exist
	read:
	zero: clear file contents
	begin: begin of file (pointer)
	end:end of file (pointer)
	warning:if file already exist
*/
define('write_begin_zero','w');
define('read_begin','r');
define('read_and_write_begin','r+');
define('read_and_write_begin_zero','w+');
define('write_end','a');
define('read_and_write_end','a+');
define('write_warning','x');
define('read_and_write_warning','x+');
?>