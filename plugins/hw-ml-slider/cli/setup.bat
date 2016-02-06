@echo Off
SET PATH=%PATH%;E:/HoangData/xampp_htdocs/wp.phar
set wp=e:/HoangData/xampp_htdocs/wp.phar

rem add new slider
php %wp% hw-ml-slider add_slider --url=http://localhost/wp1 --path=e:/HoangData/xampp_htdocs/wp1
timeout /t 50