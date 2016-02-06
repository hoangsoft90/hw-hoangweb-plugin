<?php
$theme_options[] = array(
    'id' => 'col',
    'type' => 'sidebar',
    'title' => '',
    'desc' => 'Thiết lập sidebar cho các cột của footer.',
    'repeat' => apply_filters('hwskin_nhp_theme_option_repeat',2),

);
$theme_options[] = array(
    'id' => 'tips',
    'type' => 'string',
    'title' => 'Hướng dẫn',
    'desc' => file_get_contents(__DIR__.'/huongdan.html')
);
