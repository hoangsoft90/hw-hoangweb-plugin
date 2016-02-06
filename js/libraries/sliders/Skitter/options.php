<?php
/**
 * remember that file theme options named 'options.php' associated with current theme folder
 */
$theme_options[]=array(
    'name' => 'theme',
    'type' => 'select',
    'options' => array('clean','minimalist', 'round', 'square'),
    'description' => "Chọn theme cho slider, chấp nhận giá trị: 'clean','minimalist', 'round', 'square'."
);
$theme_options[] = array(
    'name' => 'fullscreen',
    'type' => 'checkbox',
    'description' => "Chế độ toàn màn hình."
);
$theme_options[] = array(
    'name' => 'numbers',
    'type' => 'checkbox',
    'description' => 'Nút số chỉ định chuyển slide. <img src=""/>'
);
$theme_options[] = array(
    'name' => 'numbers_align',
    'type' => 'select',
    'options' => 'center,left,right',
    'description' => 'Vị trí nút định hướng slides'
);
$theme_options[] = array(
    'name' => 'progressbar',
    'type' => 'checkbox',
    'description' => 'Hiển thị progressbar'
);
$theme_options[] = array(
    'name' => 'dots',
    'type' => 'checkbox',
    'description' => 'Hiển thị nút?'
);
$theme_options[] = array(
    'name' => 'preview',
    'type' => 'checkbox',
    'description' => 'Hiển thị xem trước ảnh xem slide hiện tại.'
);

?>