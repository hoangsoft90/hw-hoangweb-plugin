=== Plugin Name ===
Contributors: Easy PHP Sudoku Game
Donate link: http://hoangweb.com/
Tags: game, sudoku
Requires at least: 1.0.0
Tested up to: 1.0.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple sudoku game base php and javascript

== Description ==
A Simple Sudoku game written in PHP & javascript.

Features:
- Change game size
- you can enable to check one by one whenever you enter new item.
- Suggest item by clicking on field you want to suggest and click on ">> Suggest me" button.

== Installation ==
Note: this plugin need sqlite3 PHP extension.
1. Upload the entire easy-php-sudoku-game folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Usage ==
Display sudoku game on frontend we use this shortcode [hw-sudoku] to insert into post/page content or widget text.
See shortcode params in detail:

- Change sudoku matrix size
 [hw-sudoku size=6]

- Enable check one by one sudoku item.
 [hw-sudoku auto_check=1]

- You can combine params like this:
 [hw-sudoku size=8 auto_check=1]

== Screenshot ==
http://i.imgur.com/br7iCdM.png